class DashboardSummary {
  // KPI
  final int pendingOutbound;
  final int pendingInbound;
  final int pendingReslotting;

  // Space
  final int spaceUsed;
  final int spaceFree;
  final double spacePercentage;

  // ABC
  final int abcFast;
  final int abcMedium;
  final int abcSlow;

  // Alerts
  final int lowStockCount;
  final String asOf;

  DashboardSummary({
    required this.pendingOutbound,
    required this.pendingInbound,
    required this.pendingReslotting,
    required this.spaceUsed,
    required this.spaceFree,
    required this.spacePercentage,
    required this.abcFast,
    required this.abcMedium,
    required this.abcSlow,
    required this.lowStockCount,
    required this.asOf,
  });

  factory DashboardSummary.fromJson(Map<String, dynamic> json) {
    return DashboardSummary(
      pendingOutbound: json['kpi']['pending_outbound'] ?? 0,
      pendingInbound: json['kpi']['pending_inbound'] ?? 0,
      pendingReslotting: json['kpi']['pending_reslotting'] ?? 0,

      spaceUsed: json['space']['total_used'] ?? 0,
      spaceFree: json['space']['total_free'] ?? 0,
      spacePercentage: (json['space']['percentage'] ?? 0).toDouble(),

      abcFast: json['abc']['fast'] ?? 0,
      abcMedium: json['abc']['medium'] ?? 0,
      abcSlow: json['abc']['slow'] ?? 0,

      lowStockCount: json['alerts']['low_stock_count'] ?? 0,
      asOf: json['as_of'] ?? '',
    );
  }
}