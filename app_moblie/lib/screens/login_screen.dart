import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'main_screen.dart';
import 'package:do_an1/services/auth_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final AuthService _authService = AuthService();

  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _isBiometricEnabled = false;

  @override
  void initState() {
    super.initState();
    _loadSavedData();
  }

  Future<void> _loadSavedData() async {
    String? savedUsername = await _authService.getSavedUsername();
    bool bioEnabled = await _authService.isBiometricEnabled();

    if (savedUsername != null) {
      _usernameController.text = savedUsername;
    }
    setState(() {
      _isBiometricEnabled = bioEnabled;
    });
  }

  Future<void> _handleBiometricLogin() async {
    bool isSuccess = await _authService.authenticateWithBiometrics();

    if (isSuccess) {
      String? savedPass = await _authService.getSavedPassword();
      if (savedPass != null && _usernameController.text.isNotEmpty) {
        _performApiLogin(_usernameController.text.trim(), savedPass);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Vui lòng đăng nhập bằng mật khẩu lần đầu!'),
          ),
        );
      }
    }
  }

  void _handleLogin() {
    FocusScope.of(context).unfocus();

    if (_usernameController.text.isEmpty || _passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vui lòng nhập Tên đăng nhập và Mật khẩu!'),
        ),
      );
      return;
    }

    _performApiLogin(
      _usernameController.text.trim(),
      _passwordController.text.trim(),
    );
  }

  Future<void> _performApiLogin(String username, String password) async {
    setState(() => _isLoading = true);

    try {
      var response = await http.post(
        Uri.parse('https://easywms.io.vn/api/login'),
        body: {'username': username, 'password': password},
      );

      var responseData = json.decode(response.body);

      if (response.statusCode == 200 && responseData['success'] == true) {
        var userData = responseData['data']['user'];
        String token = responseData['data']['token'];

        if (userData['role'] != 'admin') {
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Bạn không có quyền truy cập!'),
              backgroundColor: Colors.red,
            ),
          );
          return;
        }

        await _authService.saveAuthToken(token);
        await _authService.saveCredentials(username, password);

        SharedPreferences prefs = await SharedPreferences.getInstance();
        await prefs.setString('userId', userData['id'].toString());
        await prefs.setString('userRole', userData['role'].toString());

        if (!mounted) return;
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const MainScreen()),
        );
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Đăng nhập thất bại!'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Lỗi kết nối đến máy chủ WMS.'),
          backgroundColor: Colors.orange,
        ),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = Colors.blue[800]!;

    return Scaffold(
      backgroundColor: const Color(0xFFF0F6F9),
      body: SafeArea(
        top: false,
        child: SingleChildScrollView(
          child: Column(
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 60, horizontal: 24),
                decoration: BoxDecoration(
                  color: Colors.blue[50]!,
                  borderRadius: const BorderRadius.only(
                    bottomLeft: Radius.circular(40),
                    bottomRight: Radius.circular(40),
                  ),
                ),
                child: Column(
                  children: [
                    const SizedBox(height: 10,),
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.03),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            )
                          ]
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.asset(
                          'assets/images/logo.png',
                          height: 100,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    Text(
                      'EASY WMS',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 2,
                        color: Colors.blue[900]!,
                        fontFamily: 'Roboto', // Sếp có thể thay đổi font nếu thích
                      ),
                    ),
                    const SizedBox(height: 8,),
                    Text(
                      'Hệ thống quản lý kho thông minh',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey[700]!,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),

              // --- PHẦN FORM ĐĂNG NHẬP (Lột xác) ---
              Container(
                margin: const EdgeInsets.all(24.0),
                padding: const EdgeInsets.all(24.0),
                decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.04),
                        blurRadius: 16,
                        offset: const Offset(0, 8),
                      )
                    ]
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Đăng Nhập',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.black87,
                      ),
                    ),
                    const SizedBox(height: 24),
                    TextField(
                      controller: _usernameController,
                      decoration: InputDecoration(
                        hintText: 'Tên đăng nhập',
                        prefixIcon: Icon(Icons.badge_outlined, color: primaryColor),
                        filled: true,
                        fillColor: Colors.grey[100]!,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                      ),
                    ),
                    const SizedBox(height: 20),

                    TextField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      decoration: InputDecoration(
                        hintText: 'Mật khẩu',
                        prefixIcon: Icon(Icons.lock_outline_rounded, color: primaryColor),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscurePassword
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                            color: Colors.grey[500],
                            size: 20,
                          ),
                          onPressed: () =>
                              setState(() => _obscurePassword = !_obscurePassword),
                        ),
                        filled: true,
                        fillColor: Colors.grey[100]!, // Nền ô xám nhạt
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none, // Không viền
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      height: 56,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _handleLogin,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          elevation: 1, // Giảm đổ bóng để nhìn hiện đại hơn
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(28), // Bo góc tối đa
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 0),
                        ),
                        child: _isLoading
                            ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2.5,
                          ),
                        )
                            : const Text(
                          'ĐĂNG NHẬP',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                            letterSpacing: 1.0,
                          ),
                        ),
                      ),
                    ),

                    // Nút vân tay: tách biệt ra khỏi nút chính cho bố cục hiện đại
                    if (_isBiometricEnabled) ...[
                      const SizedBox(height: 20),
                      TextButton.icon(
                        onPressed: _isLoading ? null : _handleBiometricLogin,
                        icon: Icon(
                          Icons.fingerprint_rounded,
                          color: primaryColor,
                          size: 24,
                        ),
                        label: Text(
                          'Sử dụng vân tay',
                          style: TextStyle(
                            color: primaryColor,
                            fontWeight: FontWeight.w600,
                            fontSize: 14,
                          ),
                        ),
                        style: TextButton.styleFrom(
                          backgroundColor: Colors.blue[50]!,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(28),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}