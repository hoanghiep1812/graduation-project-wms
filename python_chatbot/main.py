import os
import torch
import faiss
import numpy as np
import requests
import json
import asyncio
import hashlib
import re
import unicodedata
from fastapi import FastAPI
from fastapi.responses import PlainTextResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from huggingface_hub import snapshot_download
from sentence_transformers import SentenceTransformer
from llama_cpp import Llama
from cachetools import TTLCache
from asyncio import Queue
from typing import Tuple, Optional, Dict
from datetime import datetime

os.environ["SENTENCE_TRANSFORMERS_LOAD_ONNX"] = "0"
os.environ["TRANSFORMERS_OFFLINE"] = "0"
os.environ["OMP_NUM_THREADS"] = "1"
os.environ["MKL_NUM_THREADS"] = "1"
torch.set_num_threads(1)

app = FastAPI()
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_methods=["*"], allow_headers=["*"])

LARAVEL_API = "http://easywms.io.vn/api/chatbot"
llm_cache = TTLCache(maxsize=1000, ttl=3600)
query_cache = TTLCache(maxsize=3000, ttl=1200)
SESSION_HISTORY = TTLCache(maxsize=1000, ttl=3600)
LLM_QUEUE = Queue(maxsize=1)

class ChatQuery(BaseModel):
    text: str
    session_id: str = "default"

SYNONYMS = {
    "nckd": "nồi chiên không dầu",
    "nồi chiên": "nồi chiên không dầu",
    "bếp điện": "bếp từ",
    "tivi": "tv",
    "ti vi": "tv",
    "ấm đun": "ấm siêu tốc",
    "lò nướng": "lò vi sóng",
    "điều hòa": "quạt điều hòa"
}

def normalize_query(text: str) -> str:
    t = text.lower().strip()
    t = re.sub(r'[^\w\s]', '', t)
    for k, v in SYNONYMS.items():
        t = t.replace(k, v)
    return t

def get_clean_search_query(text: str) -> str:
    noise = ["tháng này", "hôm nay", "xuất nhanh hay chậm", "tốc độ bán", "thế còn", "vậy còn", "bao nhiêu", "số lượng", "ở đâu", "kệ nào"]
    clean = text.lower()
    for w in noise: clean = clean.replace(w, " ")
    return clean.strip()

print("⏳ Loading Embedding...")
try:
    path = snapshot_download(repo_id="sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2", ignore_patterns=["*.onnx","*.safetensors"])
    embedder = SentenceTransformer(path)
except:
    embedder = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')

index = faiss.IndexFlatIP(embedder.get_embedding_dimension())
PRODUCT_CATALOG = []

def init_db():
    global PRODUCT_CATALOG
    try:
        headers = {"User-Agent": "EasyWMS-Internal-Bot"}
        r = requests.get(f"{LARAVEL_API}/products/all", headers=headers, timeout=10)
        if r.status_code == 200:
            PRODUCT_CATALOG = r.json()
            texts = [normalize_query(f"{p['name']} {p['sku']} {p.get('category_name', '')}") for p in PRODUCT_CATALOG]
            emb = embedder.encode(texts, convert_to_numpy=True)
            faiss.normalize_L2(emb)
            index.add(emb)
            print(f"✅ Deep RAG ready with {len(PRODUCT_CATALOG)} products")
    except Exception as e: 
        print(f"❌ DB Error: {e}")

init_db()

def search_product(text):
    clean = get_clean_search_query(text)
    if not clean: return None
    q_norm = normalize_query(clean)
    
    vec = embedder.encode([q_norm], convert_to_numpy=True)
    faiss.normalize_L2(vec)
    
    scores, idxs = index.search(vec, 3)
    if scores[0][0] > 0.38:
        return PRODUCT_CATALOG[idxs[0][0]]
    
    for p in PRODUCT_CATALOG:
        if p['sku'].lower() in q_norm: return p
    return None

INTENT_PATTERNS = {
    "product_velocity": ["nhanh hay chậm", "tốc độ", "bán chạy"],
    "expiry":           ["hạn sử dụng", "hết hạn", "hsd"],
    "lowstock":         ["sắp hết", "cạn kho", "cảnh báo"],
    "inventory":        ["tồn", "bao nhiêu", "kiểm"],
    "location":         ["ở đâu", "vị trí", "kệ nào"],
    "summary":          ["tổng quan", "dashboard"],
    "import":           ["nhập", "hàng về"],
    "export":           ["xuất", "giao hàng"],
    "history":          ["lịch sử", "biến động"],
    "dead_stock":       ["tồn lâu", "dead stock", "không xuất"]
}

def detect_intent(text):
    t = text.lower()
    
    has_date_regex = bool(re.search(r'(?<!tháng\s)(?<!tháng)\b(\d{1,2})[-/](\d{1,2})(?:[-/](\d{2,4}))?\b', t))
    is_today = "hôm nay" in t or " nay " in t or t.startswith("nay ") or t.endswith(" nay")
    
    if is_today or has_date_regex:
        if "nhập" in t: return "today_imported"
        if "xuất" in t: return "today_exported"
        
    for intent, kws in INTENT_PATTERNS.items():
        if any(k in t for k in kws): return intent
    return "rag"

def build_template_response(intent, product, data):
    name = product['name'] if product else ""
    
    if intent == "inventory" and product:
        return f"Dạ, **{name}** hiện còn tồn **{data.get('available_qty', 0)}** cái."
    
    if intent == "location" and product:
        locs = data.get("locations", [])
        return f"Dạ, **{name}** đang ở kệ: **{', '.join(locs)}**." if locs else f"Dạ, mình chưa thấy vị trí của {name}."

    if intent == "product_velocity":
        if not data or data.get("found") == False: 
            return f"Dạ chưa có số liệu tốc độ xuất cho {name if name else 'sản phẩm này'} ạ."
        cat = str(data.get("category", data.get("velocity_category", ""))).upper()
        label = "XUẤT NHANH " if "FAST" in cat else "XUẤT CHẬM " if "SLOW" in cat else "TRUNG BÌNH "
        score = data.get('score', data.get('velocity_score', 0))
        return f"Dạ, mặt hàng **{name}** thuộc nhóm **{label}**. (Điểm hiệu quả: {score})"

    if intent in ["import", "export", "lowstock", "today_imported", "today_exported"]:
        items = data.get("items", [])
        if not items: return "Dạ không có dữ liệu giao dịch hoặc cảnh báo nào."
        
        api_date = data.get('date', 'Hôm nay')
        api_month = data.get('month', 'tháng này')
        
        if intent == "import": title = f"Hàng nhập ({api_month})"
        elif intent == "today_imported": title = f"Hàng nhập (Ngày {api_date})"
        elif intent == "export": title = f"Hàng xuất ({api_month})"
        elif intent == "today_exported": title = f"Hàng xuất (Ngày {api_date})"
        else: title = "Hàng sắp hết"
        
        col = "Đã nhập" if "import" in intent else "Đã xuất" if "export" in intent else "Tồn"
        key = "total_in" if "import" in intent else "total_out" if "export" in intent else "qty"
        
        msg = f"**Bảng kê {title}:**\n\n| Sản phẩm | SKU | {col} |\n|---|---|---|\n"
        for i in items[:8]: msg += f"| {i['name']} | {i['sku']} | **{i[key]}** |\n"
        return msg
    return None

llm = Llama(model_path="./qwen2.5-1.5b-instruct-q3_k_m.gguf", n_ctx=1024, n_threads=1, verbose=False)

async def ask_llm(prompt, cache_key):
    if cache_key in llm_cache: return llm_cache[cache_key]
    await LLM_QUEUE.put(1)
    try:
        loop = asyncio.get_event_loop()
        out = await loop.run_in_executor(None, lambda: llm(prompt, max_tokens=256, temperature=0.1))
        res = out['choices'][0]['text'].strip()
        llm_cache[cache_key] = res
        return res
    finally: LLM_QUEUE.get_nowait()

@app.post("/chat", response_class=PlainTextResponse)
async def chat(q: ChatQuery):
    text = q.text.strip()
    session_id = q.session_id
    
    ctx = SESSION_HISTORY.get(session_id, {})
    intent = detect_intent(text)
    if any(text.lower().startswith(x) for x in ["còn", "thế"]) and ctx.get("intent"):
        intent = ctx["intent"]

    product = search_product(text)
    if not product and ctx.get("sku"): 
        product = {"sku": ctx["sku"], "name": ctx["name"]}
    
    sku = product["sku"] if product else ""
    if product: SESSION_HISTORY[session_id] = {"intent": intent, "sku": sku, "name": product["name"]}

    target_date, target_month, target_year = "", "", ""
    if "tháng trước" in text.lower():
        now = datetime.now()
        target_month = str(now.month - 1 if now.month > 1 else 12).zfill(2)
        target_year = str(now.year if now.month > 1 else now.year - 1)
        
    match_month = re.search(r'tháng\s+(\d{1,2})(?:[-/](\d{4}))?', text.lower())
    if match_month and "tháng trước" not in text.lower():
        m, y = match_month.groups()
        target_month = m.zfill(2)
        target_year = y if y else str(datetime.now().year)
        
    if not target_month:
        match_date = re.search(r'\b(\d{1,2})[-/](\d{1,2})(?:[-/](\d{2,4}))?\b', text)
        if match_date:
            day, month, year = match_date.groups()
            if not year: 
                year = str(datetime.now().year) 
            elif len(year) == 2: 
                year = "20" + year
            target_date = f"{year}-{month.zfill(2)}-{day.zfill(2)}"
        
    endpoints = {
        "inventory": "/inventory/by-product", 
        "location": "/product/location",        
        "lowstock": "/inventory/low-stock", 
        "summary": "/summary", 
        "import": "/movement/imported",          
        "export": "/movement/exported",        
        "today_imported": "/movement/today-imported",
        "today_exported": "/movement/today-exported",
        "product_velocity": "/velocity/by-product",
        "expiry": "/inventory/expiring",
        "dead_stock": "/movement/dead-stock",
        "history": "/product/history"
    }
    
    data = {}
    if endpoints.get(intent):
        params = {}
        if sku: params["sku"] = sku
        if target_date and intent in ["today_imported", "today_exported", "history"]:
            params["date"] = target_date
        if target_month: params["month"] = target_month
        if target_year: params["year"] = target_year

        try:
            headers = {"User-Agent": "EasyWMS-Internal-Bot"}
            res = requests.get(f"{LARAVEL_API}{endpoints[intent]}", params=params, headers=headers, timeout=5)
            data = res.json()
        except Exception as e: 
            print(f"API Call Error: {e}") 
            pass

    if not data and intent in ["product_velocity", "history", "inventory", "location"]:
        return f"Dạ mình không tìm thấy dữ liệu của mặt hàng này trong hệ thống. Bạn đọc lại đúng tên giúp mình nhé!"

    tr = build_template_response(intent, product, data)
    if tr: return tr

    data_hash = hashlib.md5(json.dumps(data).encode()).hexdigest()
    prompt = f"<|im_start|>system\nAI EasyWMS. Trả lời xưng mình, gọi bạn. Sử dụng số liệu: {json.dumps(data, ensure_ascii=False)}. Tuyệt đối không bịa.<|im_end|>\n<|im_start|>user\n{text}<|im_end|>\n<|im_start|>assistant\n"
    return await ask_llm(prompt, f"{text}_{data_hash}")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8001, workers=1)