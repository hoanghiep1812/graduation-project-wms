import 'package:do_an1/screens/chat_screen.dart';
import 'package:do_an1/screens/login_screen.dart';
import 'package:do_an1/services/auth_service.dart';
import 'package:flutter/material.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;

import 'dashboard_screen.dart';
import 'search_screen.dart';
import 'alert_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;
  final AuthService _authService = AuthService();

  final List<String> _appBarTitles = [
    'Tổng quan EasyWMS',
    'Trợ lý Kho Thông minh',
    'Tra cứu Hệ thống',
    'Cảnh báo Tồn kho',
    'Cài đặt',
  ];

  @override
  void initState() {
    super.initState();
    _setupFCMToken();
    _listenToForegroundMessages();
  }

  Future<void> _setupFCMToken() async {
    FirebaseMessaging messaging = FirebaseMessaging.instance;

    await messaging.requestPermission(alert: true, badge: true, sound: true);

    String? token = await messaging.getToken();
    print("============== BẮT ĐƯỢC FCM TOKEN ==============");
    print(token);

    if (token != null) {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? userId = prefs.getString('userId');

      if (userId != null) {
        try {
          var response = await http.post(
            Uri.parse('https://easywms.io.vn/api/update-fcm-token'),
            body: {'user_id': userId, 'fcm_token': token},
          );
          print("Phản hồi từ Laravel FCM: ${response.body}");
        } catch (e) {
          print("Lỗi khi gửi Token lên Laravel: $e");
        }
      }
    }
  }

  void _listenToForegroundMessages() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) async {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      bool isNotiEnabled = prefs.getBool('notifications_enabled') ?? true;

      if (!isNotiEnabled) return;

      if (message.notification != null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    "🔔 ${message.notification?.title}",
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  const SizedBox(height: 4),
                  Text(message.notification?.body ?? ''),
                ],
              ),
              backgroundColor: Colors.blue[800],
              duration: const Duration(seconds: 5),
              behavior: SnackBarBehavior.floating,
              margin: const EdgeInsets.only(bottom: 16, left: 16, right: 16),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          );
        }
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      const DashboardScreen(),
      const ChatScreen(),
      const SearchScreen(),
      const AlertScreen(),
      SettingsTab(authService: _authService),
    ];

    return Scaffold(
      appBar: AppBar(
        title: Text(
          _appBarTitles[_currentIndex],
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.blue[800],
        foregroundColor: Colors.white,
        elevation: 0,
      ),

      body: IndexedStack(index: _currentIndex, children: screens),

      bottomNavigationBar: NavigationBarTheme(
        data: NavigationBarThemeData(
          indicatorColor: Colors.blue[100],
        ),
        child: NavigationBar(
          selectedIndex: _currentIndex,
          onDestinationSelected: (index) => setState(() => _currentIndex = index),
          labelBehavior: NavigationDestinationLabelBehavior.alwaysHide,
          backgroundColor: Colors.white,
          elevation: 10,
          destinations: [
            NavigationDestination(
              icon: Icon(Icons.dashboard_outlined, color: Colors.grey[600]),
              selectedIcon: Icon(Icons.dashboard, color: Colors.blue[900]),
              label: 'Tổng quan',
            ),
            NavigationDestination(
              icon: Icon(Icons.smart_toy_outlined, color: Colors.grey[600]),
              selectedIcon: Icon(Icons.smart_toy, color: Colors.blue[900]),
              label: 'AI',
            ),
            NavigationDestination(
              icon: Icon(Icons.search_outlined, color: Colors.grey[600]),
              selectedIcon: Icon(Icons.search, color: Colors.blue[900]),
              label: 'Tra cứu',
            ),
            NavigationDestination(
              icon: Icon(Icons.notifications_none_outlined, color: Colors.grey[600]),
              selectedIcon: Icon(Icons.notifications_active, color: Colors.blue[900]),
              label: 'Cảnh báo',
            ),
            NavigationDestination(
              icon: Icon(Icons.settings_outlined, color: Colors.grey[600]),
              selectedIcon: Icon(Icons.settings, color: Colors.blue[900]),
              label: 'Cài đặt',
            ),
          ],
        ),
      ),
    );
  }
}

class SettingsTab extends StatefulWidget {
  final AuthService authService;
  const SettingsTab({super.key, required this.authService});

  @override
  State<SettingsTab> createState() => _SettingsTabState();
}

class _SettingsTabState extends State<SettingsTab> {
  bool _isBioOn = false;
  bool _isNotiOn = true;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    bool bioState = await widget.authService.isBiometricEnabled();

    SharedPreferences prefs = await SharedPreferences.getInstance();
    bool notiState = prefs.getBool('notifications_enabled') ?? true;

    if (mounted) {
      setState(() {
        _isBioOn = bioState;
        _isNotiOn = notiState;
      });
    }
  }

  Future<void> _toggleNotification(bool value) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setBool('notifications_enabled', value);
    setState(() {
      _isNotiOn = value;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.blue[50],
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 8, bottom: 8, top: 8),
            child: Text(
              "Bảo mật & Đăng nhập",
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey),
            ),
          ),
          _buildSettingCard(
            child: SwitchListTile(
              title: const Text("Bật đăng nhập bằng vân tay", style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              secondary: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: Colors.blue[50], borderRadius: BorderRadius.circular(8)),
                child: Icon(Icons.fingerprint, color: Colors.blue[800]),
              ),
              value: _isBioOn,
              activeColor: Colors.blue[800],
              onChanged: (bool value) async {
                await widget.authService.setBiometricEnabled(value);
                setState(() => _isBioOn = value);
              },
            ),
          ),

          const SizedBox(height: 20),

          const Padding(
            padding: EdgeInsets.only(left: 8, bottom: 8),
            child: Text(
              "Hệ thống",
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey),
            ),
          ),
          _buildSettingCard(
            child: SwitchListTile(
              title: const Text("Nhận thông báo ", style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
              subtitle: const Text("Đề xuất dời kệ", style: TextStyle(fontSize: 13)),
              secondary: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: Colors.orange[50], borderRadius: BorderRadius.circular(8)),
                child: Icon(Icons.notifications_active, color: Colors.orange[800]),
              ),
              value: _isNotiOn,
              activeColor: Colors.blue[800],
              onChanged: _toggleNotification,
            ),
          ),

          const Spacer(),

          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blue,
                foregroundColor: Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                  side: const BorderSide(color: Colors.blue),
                ),
              ),
              icon: const Icon(Icons.logout),
              label: const Text(
                "ĐĂNG XUẤT",
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              onPressed: () async {
                await widget.authService.logout();
                if (!mounted) return;
                Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
                  MaterialPageRoute(
                    builder: (context) => const LoginScreen(),
                  ),
                      (Route<dynamic> route) => false,
                );
              },
            ),
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }

  Widget _buildSettingCard({required Widget child}) {
    return Container(
      decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.blue.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            )
          ]
      ),
      child: child,
    );
  }
}