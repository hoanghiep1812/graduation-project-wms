import 'package:do_an1/core/api_client.dart';
import 'package:flutter/material.dart';
import 'package:flutter_markdown/flutter_markdown.dart';

class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final TextEditingController _controller = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  final List<Map<String, dynamic>> _messages = [
    {
      "isBot": true,
      "text":
      "Xin chào! Tôi là trợ lý AI của EasyWMS. Sếp cần kiểm tra kho hay tra cứu mã hàng nào không ạ?",
      "time": DateTime.now(),
    },
  ];

  bool _isLoading = false;

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _handleSendMessage({String? textOverride}) async {
    String text = textOverride ?? _controller.text.trim();
    if (text.isEmpty) return;

    // Chỉ clear ô nhập liệu nếu người dùng tự gõ
    if (textOverride == null) {
      _controller.clear();
    }

    setState(() {
      _messages.add({"isBot": false, "text": text, "time": DateTime.now()});
      _isLoading = true;
    });
    _scrollToBottom();

    try {
      final apiClient = ApiClient();

      final response = await apiClient.dio.post(
        'http://103.75.185.216:8001/chat',
        data: {"text": text, "session_id": "user_trial_1"},
      );

      String botReply = response.data.toString();

      if (botReply.trim().isEmpty) {
        botReply = "Dạ, Bot đã nhận tín hiệu nhưng chưa biết trả lời sao ạ.";
      }

      setState(() {
        _isLoading = false;
        _messages.add({
          "isBot": true,
          "text": botReply,
          "time": DateTime.now(),
        });
      });
      _scrollToBottom();
    } catch (e) {
      print("Lỗi Chatbot: $e");
      setState(() {
        _isLoading = false;
        _messages.add({
          "isBot": true,
          "text": "Lỗi kết nối bộ não AI rồi sếp ơi! Sếp xem lại mạng hoặc server nhé.",
          "time": DateTime.now(),
        });
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                return _buildChatBubble(msg['isBot'], msg['text']);
              },
            ),
          ),

          if (_isLoading)
            Padding(
              padding: const EdgeInsets.only(left: 16, bottom: 8),
              child: Row(
                children: [
                  Text(
                    "AI đang suy nghĩ...",
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontSize: 12,
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ],
              ),
            ),

          _buildSuggestions(),
          const SizedBox(height: 8),
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildChatBubble(bool isBot, String text) {
    return Align(
      alignment: isBot ? Alignment.centerLeft : Alignment.centerRight,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: isBot ? Colors.grey[200] : Colors.blue[800],
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(12),
            topRight: const Radius.circular(12),
            bottomLeft: Radius.circular(isBot ? 0 : 12),
            bottomRight: Radius.circular(isBot ? 12 : 0),
          ),
        ),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.85,
        ),
        child: isBot
            ? MarkdownBody(
          data: text,
          styleSheet: MarkdownStyleSheet(
            p: const TextStyle(color: Colors.black87, fontSize: 15),
            strong: const TextStyle(fontWeight: FontWeight.bold),
            tableBody: const TextStyle(color: Colors.black87, fontSize: 14),
            tableHead: const TextStyle(color: Colors.black, fontSize: 14, fontWeight: FontWeight.bold),
            tableBorder: TableBorder.all(color: Colors.grey.shade400, width: 1),
            tableCellsPadding: const EdgeInsets.all(8),
          ),
        )
            : Text(
          text,
          style: const TextStyle(color: Colors.white, fontSize: 15),
        ),
      ),
    );
  }

  Widget _buildSuggestions() {
    final suggestions = [
      "Hôm nay nhập sản phẩm nào?",
      "Hôm nay xuất sản phẩm nào?",
      "Tháng này nhập sản phẩm nào?",
    ];

    return SizedBox(
      height: 40,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: suggestions.length,
        itemBuilder: (context, index) {
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ActionChip(
              backgroundColor: Colors.blue[50],
              side: BorderSide(color: Colors.blue.shade200),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
              ),
              labelStyle: TextStyle(
                  color: Colors.blue[800],
                  fontSize: 13,
                  fontWeight: FontWeight.w500
              ),
              label: Text(suggestions[index]),
              onPressed: () {
                _handleSendMessage(textOverride: suggestions[index]);
              },
            ),
          );
        },
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(
          top: BorderSide(color: Color(0xFFEEEEEE), width: 1),
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: Colors.grey[100],
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: Colors.grey.shade300),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
              child: TextField(
                controller: _controller,
                decoration: const InputDecoration(
                  hintText: "Nhắn tin cho Trợ lý AI...",
                  border: InputBorder.none,
                  hintStyle: TextStyle(color: Colors.grey, fontSize: 15),
                ),
                onSubmitted: (_) => _handleSendMessage(),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Container(
            decoration: BoxDecoration(
              color: Colors.blue[800],
              shape: BoxShape.circle,
            ),
            child: IconButton(
              icon: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
              onPressed: () => _handleSendMessage(),
            ),
          ),
        ],
      ),
    );
  }
}