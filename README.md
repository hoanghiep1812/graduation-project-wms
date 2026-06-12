# EasyWMS - HỆ THỐNG QUẢN LÝ KHO GIA DỤNG: ĐẢM BẢO TOÀN VẸN DỮ LIỆU VÀ TỐI ƯU VỊ TRÍ LẤY HÀNG KHI XUẤT KHO

Hệ thống quản lý kho gia dụng, tối ưu hóa không gian lưu trữ, đảm bảo tính toàn vẹn dữ liệu và tích hợp trợ lý ảo AI tra cứu bảo mật nội bộ.

---

## 🏗️ Kiến Trúc Tổng Thể Hệ Thống (Tech Stack)

Hệ thống được phát triển theo mô hình ứng dụng phân tán, đảm bảo tính độc lập và bảo mật cao:
* **Main Backend & Web Admin:** Laravel (PHP) & MySQL – Đóng vai trò bộ não điều hành, xử lý các nghiệp vụ cốt lõi (Nhập, Xuất, Tồn kho) và quản lý giao dịch (Database Transactions / Pessimistic Locking).
* **AI Engine Server:** FastAPI (Python) & FAISS Vector Database – Máy chủ độc lập xử lý kỹ thuật RAG (Retrieval-Augmented Generation) và chạy mô hình Local LLM.
* **Mobile App (Dành cho Admin):** Flutter (Dart) – Ứng dụng di động đa nền tảng giúp ban quản lý giám sát hệ thống, nhận thông báo đề xuất dời kệ và tích hợp Chatbot AI từ xa.

---

## 🌟 Tính Năng Cốt Lõi 

1.  **Quản Lý Vận Hành Tập Trung (Web Admin):**
    * Số hóa cấu trúc kho bãi theo vị trí vật lý cụ thể (Khu vực, Kệ hàng, Ô chứa).
    * Quản lý danh mục Sản phẩm, Đối tác, Nhà cung cấp.
    * Khởi tạo và quản lý chứng từ Nhập kho – Xuất kho thời gian thực.
    * Tự động tính toán sức chứa và gợi ý kệ trống tối ưu cho hàng hóa khi Nhập kho.
    * Lọc hàng hóa xuất kho tự động theo tiêu chuẩn FEFO/FIFO và vạch lộ trình lấy hàng ngắn nhất.

2.  **Đảm Bảo Toàn Vẹn Dữ Liệu:**
    * Áp dụng Cơ chế khóa bi quan (`Pessimistic Locking`) kết hợp `Database Transactions` để đóng băng dòng dữ liệu tồn kho tức thời khi phát sinh giao dịch, triệt tiêu hoàn toàn lỗi tranh chấp hoặc "âm kho".

3.  **Giám Sát Bỏ Túi & Điều Hành Từ Xa (Mobile App):**
    * Dành riêng cho tài khoản Quản trị viên (Admin) để theo dõi biến động số liệu kho mọi lúc mọi nơi.
    * Nhận thông báo tức thời khi hệ thống đề xuất điều chuyển, dời kệ hàng hóa (chuyển hàng bán chạy ra gần cửa xuất).
    * Tích hợp Chatbot AI đồng bộ trực tiếp như phiên bản Web.

4.  **Trợ Lý Ảo Chatbot AI Bảo Mật Nội Bộ (RAG & Local LLM):**
    * Sử dụng kiến trúc RAG để tự động gọi API lấy số liệu thực tế từ cơ sở dữ liệu làm ngữ cảnh (Context) trước khi đưa vào Prompt.
    * Sử dụng mô hình Local LLM chạy 100% trên hạ tầng nội bộ để sinh câu trả lời chính xác, loại bỏ hoàn toàn rủi ro AI bịa đặt thông tin.
    * Đảm bảo an toàn tuyệt đối cho bí mật kinh doanh, không gửi bất kỳ byte dữ liệu nào ra Cloud API bên ngoài (nhũ ChatGPT/Gemini).

---

## ⚙️ Hướng Dẫn Cài Đặt & Vận Hành

### 1. Cấu Hình Phân Hệ Web & Backend (Laravel)
Yêu cầu: PHP >= 8.x, Composer, MySQL.

```bash
# Di chuyển vào thư mục gốc của dự án Web
cd graduation-project-wms

# Cài đặt các thư viện phụ thuộc
composer install

# Tạo file môi trường và cấu hình Database trong file .env
cp .env.example .env
php artisan key:generate

# Chạy Migration để khởi tạo cấu trúc bảng dữ liệu và dữ liệu mẫu
php artisan migrate --seed

# Khởi chạy server Web nội bộ
php artisan serve
```
### 2. Cấu Hình Phân Hệ AI Server
Yêu cầu: Python >= 3.9, pip.

```bash
# Di chuyển vào thư mục chứa mã nguồn AI
cd python_chatbot

# Cài đặt các thư viện cần thiết
pip install -r requirements.txt

# Khởi chạy máy chủ AI cục bộ
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```
### Cấu Hình Ứng Dụng Di Động (Flutter Mobile App)
Yêu cầu: Flutter SDK đã cấu hình trên máy cá nhân, Android Studio / Xcode.

```bash
# Di chuyển vào thư mục ứng dụng di động
cd app_mobile

# Lấy các gói thư viện Flutter về máy
flutter pub get

# Cấu hình IP Endpoint trỏ về Laravel Backend Server trong file cấu hình (ví dụ: lib/config.dart)
# Thay đổi URL thành IP cục bộ của bạn: http://<YOUR_LOCAL_IP>:8000/api

# Chạy ứng dụng trên thiết bị giả lập hoặc thiết bị thật (Chế độ Debug)
flutter run
```
---

## 🔒 Bản Quyền & Giấy Phép

> 🎓 **ĐỒ ÁN TỐT NGHIỆP NGÀNH KỸ THUẬT PHẦN MỀM — NĂM 2026**
>
> Dự án này được nghiên cứu và phát triển trong khuôn khổ Đồ án tốt nghiệp hệ chính quy tại **Trường Đại học Công nghệ thông tin và Truyền thông - Đại học Thái Nguyên (ICTU)**.
>
> * **Tác giả:** Hoàng Thế Hiệp



