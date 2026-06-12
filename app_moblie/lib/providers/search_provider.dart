import 'package:flutter/material.dart';
import '../services/wms_service.dart';

class SearchProvider with ChangeNotifier {
  final WmsService _service = WmsService();

  bool isLoading = false;
  List<dynamic> products = [];

  Future<void> search(String keyword) async {
    isLoading = true;
    notifyListeners();

    try {
      products = await _service.searchProduct(keyword);
    } catch (e) {
      products = [];
    }

    isLoading = false;
    notifyListeners();
  }
}