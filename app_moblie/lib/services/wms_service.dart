import 'package:dio/dio.dart';
import '../core/api_client.dart';
import '../models/dashboard_summary.dart';

class WmsService {
  final ApiClient _apiClient = ApiClient();

  Future<DashboardSummary> getDashboardSummary() async {
    try {

      final response = await _apiClient.dio.get('/chatbot/summary');
      return DashboardSummary.fromJson(response.data);
    } on DioException catch (e) {
      if (e.response != null) {
        throw Exception('Lỗi Server: ${e.response?.statusCode}');
      } else {
        throw Exception('Không thể kết nối đến máy chủ Laravel. Hãy kiểm tra IP!');
      }
    } catch (e) {
      throw Exception('Lỗi phân tích dữ liệu: $e');
    }
  }

  Future<List<dynamic>> searchProduct(String keyword) async {
    try {
      final response = await _apiClient.dio.get(
          '/chatbot/product/search',
          queryParameters: {'name': keyword}
      );
      if (response.data['found'] == true) {
        return response.data['products'];
      }
      return [];
    } catch (e) {
      throw Exception('Lỗi tìm kiếm: $e');
    }
  }

  Future<Map<String, dynamic>> getProductDetail(String sku) async {
    try {
      final invRes = await _apiClient.dio.get('/chatbot/inventory/by-product', queryParameters: {'sku': sku});
      final locRes = await _apiClient.dio.get('/chatbot/product/location', queryParameters: {'sku': sku});

      return {
        'qty': invRes.data['available_qty'] ?? 0,
        'locations': locRes.data['locations'] ?? [],
      };
    } catch (e) {
      throw Exception('Lỗi lấy chi tiết SP: $e');
    }
  }

  Future<List<dynamic>> getLowStockItems() async {
    try {
      final response = await _apiClient.dio.get('/chatbot/inventory/low-stock');
      return response.data['items'] ?? [];
    } catch (e) {
      throw Exception('Lỗi tải danh sách tồn thấp: $e');
    }
  }

  Future<List<dynamic>> getExpiringItems() async {
    try {
      final response = await _apiClient.dio.get('/chatbot/inventory/expiring');
      return response.data['items'] ?? [];
    } catch (e) {
      throw Exception('Lỗi tải danh sách hết hạn: $e');
    }
  }
  Future<List<dynamic>> getDeadStock() async {
    try {
      final response = await _apiClient.dio.get('/chatbot/movement/dead-stock');
      return response.data['items'] ?? [];
    } catch (e) {
      return [];
    }
  }

  Future<Map<String, int>> getTodayStats() async {
    try {
      final imp = await _apiClient.dio.get('/chatbot/movement/today-imported');
      final exp = await _apiClient.dio.get('/chatbot/movement/today-exported');

      int totalIn = (imp.data['items'] as List).fold(0, (sum, item) => sum + (item['total_in'] as int));
      int totalOut = (exp.data['items'] as List).fold(0, (sum, item) => sum + (item['total_out'] as int));

      return {'in': totalIn, 'out': totalOut};
    } catch (e) {
      return {'in': 0, 'out': 0};
    }
  }
}