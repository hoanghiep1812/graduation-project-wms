import 'package:flutter/material.dart';
import '../models/dashboard_summary.dart';
import '../services/wms_service.dart';


enum AppState { initial, loading, loaded, error }

class WmsProvider with ChangeNotifier {
  final WmsService _service = WmsService();

  AppState state = AppState.initial;
  DashboardSummary? summary;
  String errorMessage = '';
  List<dynamic> deadStockItems = [];
  int todayIn = 0;
  int todayOut = 0;

  Future<void> fetchDashboard() async {
    state = AppState.loading;
    notifyListeners();

    try {
      summary = await _service.getDashboardSummary();

      deadStockItems = await _service.getDeadStock();
      final stats = await _service.getTodayStats();
      todayIn = stats['in']!;
      todayOut = stats['out']!;
      state = AppState.loaded;
    } catch (e) {
      errorMessage = e.toString();
      state = AppState.error;
    }

    notifyListeners();
  }
}