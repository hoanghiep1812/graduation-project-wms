import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/wms_provider.dart';
import '../models/dashboard_summary.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<WmsProvider>(context, listen: false).fetchDashboard();
    });
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final int kpiColumns = isLandscape || screenWidth > 600 ? 4 : 2;

    return Scaffold(
      backgroundColor: Colors.blue[50], // Nền xanh dương nhạt mát mắt
      body: Consumer<WmsProvider>(
        builder: (context, provider, child) {
          if (provider.state == AppState.loading && provider.summary == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.state == AppState.error) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 60, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.errorMessage, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => provider.fetchDashboard(),
                    child: const Text('Thử lại'),
                  ),
                ],
              ),
            );
          }

          final data = provider.summary;
          if (data == null) {
            return const Center(child: Text('Không có dữ liệu'));
          }

          return RefreshIndicator(
            onRefresh: () => provider.fetchDashboard(),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Thanh hiển thị thời gian cập nhật
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Tổng quan kho hàng',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.blueGrey),
                      ),
                      Text(
                        'Cập nhật: ${data.asOf}',
                        style: const TextStyle(
                          color: Colors.grey,
                          fontStyle: FontStyle.italic,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Lớp thẻ 1: Sức chứa kho
                  _buildSpaceCard(data),
                  const SizedBox(height: 14),

                  // Lớp thẻ 2: Biến động xuất nhập trong ngày
                  _buildTodayStats(provider.todayIn, provider.todayOut),
                  const SizedBox(height: 24),

                  // Lớp thẻ 3: Nhiệm vụ Grid tiêu chuẩn
                  const Text(
                    'ĐƠN HÀNG & NHIỆM VỤ',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black54, letterSpacing: 0.5),
                  ),
                  const SizedBox(height: 10),
                  GridView(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: kpiColumns,
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      mainAxisExtent: 90,
                    ),
                    children: [
                      _buildStatCard(
                        'Đơn chờ Xuất',
                        data.pendingOutbound.toString(),
                        Colors.blue.shade700,
                        Icons.local_shipping_rounded,
                        onTap: () {},
                      ),
                      _buildStatCard(
                        'Đơn chờ Nhập',
                        data.pendingInbound.toString(),
                        Colors.teal.shade700,
                        Icons.call_received_rounded,
                        onTap: () {},
                      ),
                      _buildStatCard(
                        'Chờ Dời kệ',
                        data.pendingReslotting.toString(),
                        Colors.orange.shade700,
                        Icons.layers_rounded,
                        onTap: () {},
                      ),
                      _buildStatCard(
                        'Cạn & Tồn Thấp',
                        data.lowStockCount.toString(),
                        Colors.red.shade700,
                        Icons.warning_amber_rounded,
                        onTap: () {},
                      ),
                    ],
                  ),

                  const SizedBox(height: 24),

                  // Lớp thẻ 4: Đã lột lác từ ô lưới thành Thẻ Đồ Thị ABC cao cấp
                  const Text(
                    'HIỆU SUẤT LƯU CHUYỂN HÀNG HÓA',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.black54, letterSpacing: 0.5),
                  ),
                  const SizedBox(height: 10),
                  _buildABCCard(data),

                  const SizedBox(height: 20),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // --- THIẾT KẾ MỚI: THẺ PHÂN LOẠI ABC THEO THANH TỶ LỆ ---
  Widget _buildABCCard(DashboardSummary data) {
    int total = data.abcFast + data.abcMedium + data.abcSlow;
    // Tránh lỗi chia cho 0 nếu kho chưa có hàng
    double fastRatio = total > 0 ? data.abcFast / total : 0.0;
    double mediumRatio = total > 0 ? data.abcMedium / total : 0.0;
    double slowRatio = total > 0 ? data.abcSlow / total : 0.0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.blue.withOpacity(0.04), blurRadius: 12, offset: const Offset(0, 4)),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.analytics_rounded, color: Colors.blue, size: 20),
              SizedBox(width: 8),
              Text('Phân Tích Hàng Tồn Kho', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            ],
          ),
          const SizedBox(height: 16),

          // Thanh biểu đồ dạng đường ống (Pipeline Bar)
          if (total > 0)
            ClipRRect(
              borderRadius: BorderRadius.circular(6),
              child: SizedBox(
                height: 12,
                child: Row(
                  children: [
                    if (data.abcFast > 0) Expanded(flex: data.abcFast, child: Container(color: Colors.green)),
                    if (data.abcMedium > 0) Expanded(flex: data.abcMedium, child: Container(color: Colors.orange)),
                    if (data.abcSlow > 0) Expanded(flex: data.abcSlow, child: Container(color: Colors.grey)),
                  ],
                ),
              ),
            )
          else
            Container(height: 12, decoration: BoxDecoration(color: Colors.grey[200], borderRadius: BorderRadius.circular(6))),

          const SizedBox(height: 16),

          // Chú thích thông số bên dưới (Nhìn cực thoáng)
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildABCLegendItem('Nhóm A (Nhanh)', data.abcFast, Colors.green, '${(fastRatio * 100).toStringAsFixed(0)}%'),
              _buildABCLegendItem('Nhóm B (Vừa)', data.abcMedium, Colors.orange, '${(mediumRatio * 100).toStringAsFixed(0)}%'),
              _buildABCLegendItem('Nhóm C (Chậm)', data.abcSlow, Colors.grey.shade600, '${(slowRatio * 100).toStringAsFixed(0)}%'),
            ],
          )
        ],
      ),
    );
  }

  Widget _buildABCLegendItem(String label, int value, Color color, String percent) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
            const SizedBox(width: 6),
            Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.w500)),
          ],
        ),
        const SizedBox(height: 4),
        Row(
          crossAxisAlignment: CrossAxisAlignment.baseline,
          textBaseline: TextBaseline.alphabetic,
          children: [
            Text('$value', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(width: 2),
            Text(' mã ($percent)', style: TextStyle(fontSize: 10, color: Colors.grey[500])),
          ],
        )
      ],
    );
  }

  Widget _buildSpaceCard(DashboardSummary data) {
    final isOverloaded = data.spacePercentage >= 90;
    final progressColor = isOverloaded ? Colors.red : Colors.blue.shade700;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.blue.withOpacity(0.04), blurRadius: 12, offset: const Offset(0, 4)),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.pie_chart_rounded, color: Colors.indigo, size: 20),
                  SizedBox(width: 8),
                  Text('Sức Chứa Kho Toàn Cục', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.black87)),
                ],
              ),
              Text('${data.spacePercentage}%', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: progressColor)),
            ],
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: data.spacePercentage / 100,
              minHeight: 10,
              backgroundColor: Colors.blue[50],
              color: progressColor,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Đã dùng: ${data.spaceUsed}', style: TextStyle(color: Colors.grey[600], fontSize: 12, fontWeight: FontWeight.w500)),
              Text('Còn trống: ${data.spaceFree}', style: TextStyle(color: Colors.grey[600], fontSize: 12, fontWeight: FontWeight.w500)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String value, Color color, IconData icon, {VoidCallback? onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(color: Colors.blue.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 4)),
          ],
        ),
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    title,
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey[500], height: 1.1),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Icon(icon, size: 16, color: color.withOpacity(0.6)),
              ],
            ),
            Text(
              value,
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: color, letterSpacing: -0.5),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTodayStats(int incoming, int outgoing) {
    return Row(
      children: [
        Expanded(child: _buildSimpleTile('Nhập hôm nay', incoming.toString(), Colors.green.shade600, Icons.arrow_downward_rounded)),
        const SizedBox(width: 12),
        Expanded(child: _buildSimpleTile('Xuất hôm nay', outgoing.toString(), Colors.red.shade600, Icons.arrow_upward_rounded)),
      ],
    );
  }

  Widget _buildSimpleTile(String label, String value, Color color, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(color: Colors.blue.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 4)),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(color: color.withOpacity(0.1), shape: BoxShape.circle),
            child: Icon(icon, color: color, size: 16),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: color, letterSpacing: -0.5)),
                Text(label, style: const TextStyle(fontSize: 11, color: Colors.black54, fontWeight: FontWeight.w500)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}