<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header-table { width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #2563eb; }
        .date-info { text-align: right; font-size: 12px; font-style: italic; color: #666; }
        .title { text-align: center; font-size: 22px; font-weight: bold; margin: 20px 0 5px 0; }
        .sub-title { text-align: center; font-size: 14px; margin-bottom: 20px; font-style: italic; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px; text-align: center; }
        .items-table td { border: 1px solid #cbd5e1; padding: 10px; }
        .text-center { text-align: center; }
        .footer-table { width: 100%; margin-top: 40px; text-align: center; }
        .signature-space { height: 90px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo">Easy<span style="color: #000;">WMS</span></td>
            <td class="date-info">Ngày in: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="title">PHIẾU XUẤT KHO</div>
    <div class="sub-title">(Lệnh Xuất Hàng & Bàn Giao Vận Chuyển)</div>

    <table class="info-table">
        <tr>
            <td width="55%">
                <strong>Khách hàng:</strong> {{ $order->customer_name }}<br>
                <strong>Mã đơn hàng (SO):</strong> {{ $order->so_number }}
            </td>
            <td width="45%">
                <strong>Ngày xuất:</strong> {{ $order->shipped_at ? \Carbon\Carbon::parse($order->shipped_at)->format('d/m/Y H:i') : \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                <strong>Người lập phiếu:</strong> {{ $order->creator->name ?? 'Admin' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="50%" style="text-align: left;">Sản phẩm</th>
                <th width="25%">Mã SKU</th>
                <th width="20%">Số lượng xuất</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->product->sku }}</td>
                    <td class="text-center"><strong>{{ number_format($item->quantity) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><i>* Ghi chú: Nhân viên vận chuyển vui lòng đối chiếu kỹ số lượng thực tế với phiếu xuất kho trước khi rời khỏi bến bãi.</i></p>

    <table class="footer-table">
        <tr>
            <td width="33%"><strong>Người lập phiếu</strong><br>(Ký, ghi rõ họ tên)</td>
            <td width="33%"><strong>Thủ kho xuất</strong><br>(Ký, ghi rõ họ tên)</td>
            <td width="34%"><strong>Nhân viên vận chuyển</strong><br>(Ký, ghi rõ họ tên)</td>
        </tr>
        <tr>
            <td class="signature-space"></td>
            <td class="signature-space"></td>
            <td class="signature-space"></td>
        </tr>
    </table>
</body>
</html>