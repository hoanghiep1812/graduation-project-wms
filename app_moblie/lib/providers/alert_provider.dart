import 'package:flutter/material.dart';
import '../services/wms_service.dart';

class AlertProvider with ChangeNotifier {
  final WmsService _service = WmsService();

  bool isLoading = false;
  List<dynamic> lowStockItems = [];
  List<dynamic> expiringItems = [];
  List<dynamic> deadStockItems = [];

  Future<void> fetchAlerts() async {
    isLoading = true;
    notifyListeners();

    try {
      final results = await Future.wait([
        _service.getLowStockItems(),
        _service.getExpiringItems(),
        _service.getDeadStock(),
      ]);

      lowStockItems = results[0];
      expiringItems = results[1];
      deadStockItems = results[2];
    } catch (e) {
    }

    isLoading = false;
    notifyListeners();
  }
}