<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\BinLocation;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Partner;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Faker\Factory as Faker;

class MockDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('vi_VN');

        // Bọc trong Transaction để chạy nhanh và tránh lỗi dữ liệu nửa vời
        DB::beginTransaction();

        try {
            // 1. TẠO ADMIN (Tránh bị mất tk sau khi migrate:fresh)
            $admin = User::firstOrCreate(
                ['username' => 'admin'],
                [
                    'name' => 'Quản trị viên', 
                    'email' => 'admin@easywms.vn', 
                    'password' => bcrypt('password'), // Pass mặc định: password
                    'role' => 'admin'
                ]
            );

            // 2. TẠO KHO VÀ KHU VỰC TẠI THÁI NGUYÊN
            $warehouse = Warehouse::firstOrCreate(
                ['code' => 'WH-TN'], 
                ['name' => 'Kho Gia Dụng Tổng Thái Nguyên']
            );

            $zones = [];
            $zoneNames = ['Khu A - Điện gia dụng', 'Khu B - Đồ bếp', 'Khu C - Hàng cồng kềnh'];
            foreach ($zoneNames as $idx => $zName) {
                $zones[] = Zone::firstOrCreate(
                    ['code' => 'Z-' . chr(65 + $idx), 'warehouse_id' => $warehouse->id], 
                    ['distance_to_packing' => $faker->numberBetween(5, 50), 'description' => $zName]
                );
            }

            // 3. TẠO 50 KỆ HÀNG (QUẢN LÝ SỨC CHỨA)
            $bins = [];
            for ($i = 1; $i <= 50; $i++) {
                $zone = $faker->randomElement($zones);
                $bins[] = BinLocation::create([
                    'warehouse_id' => $warehouse->id,
                    'zone_id' => $zone->id,
                    'code' => $zone->code . '-BIN-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'max_capacity' => $faker->numberBetween(500, 2000), // Sức chứa ngẫu nhiên từ 500-2000
                    'current_capacity' => 0,
                ]);
            }

            // 4. TẠO NHÀ CUNG CẤP & ĐỐI TÁC (TẠI THÁI NGUYÊN)
            $suppliers = [];
            $supplierBrands = ['Sunhouse', 'Kangaroo', 'Philips', 'Panasonic', 'Sharp', 'Toshiba', 'Lock&Lock'];
            foreach ($supplierBrands as $idx => $brand) {
                $suppliers[] = Supplier::create([
                    'code' => 'NCC' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'name' => 'Công ty TNHH Phân phối ' . $brand,
                    'address' => $faker->streetAddress() . ', TP. Thái Nguyên',
                    'status' => 'active',
                ]);
            }

            $partners = [];
            for ($i = 1; $i <= 20; $i++) {
                $districts = ['TP Thái Nguyên', 'Phổ Yên', 'Sông Công', 'Đại Từ', 'Đồng Hỷ'];
                $partners[] = Partner::create([
                    'code' => 'DT' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'Đại lý đồ điện ' . $faker->lastName(),
                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->streetAddress() . ', ' . $faker->randomElement($districts),
                    'status' => 'active',
                ]);
            }

            // 5. TẠO 100 SẢN PHẨM GIA DỤNG
            $products = [];
            $applianceTypes = ['Nồi cơm điện', 'Lò vi sóng', 'Máy xay sinh tố', 'Bếp từ', 'Quạt điều hòa', 'Chảo chống dính', 'Ấm siêu tốc', 'Máy hút bụi', 'Nồi chiên không dầu', 'Bàn là hơi nước', 'Máy lọc nước', 'Máy sấy tóc'];
            for ($i = 1; $i <= 100; $i++) {
                $type = $faker->randomElement($applianceTypes);
                $brand = $faker->randomElement($supplierBrands);
                
                $products[] = Product::create([
                    'sku' => 'GD' . $faker->unique()->numerify('#####'),
                    'name' => $type . ' ' . $brand . ' ' . strtoupper($faker->bothify('?-###')),
                    'unit' => $faker->randomElement(['Cái', 'Chiếc', 'Bộ']),
                    'minimum_stock' => $faker->numberBetween(10, 50),
                    'is_active' => 1,
                    'has_expiry' => $faker->boolean(5) ? 1 : 0, // Chỉ 5% hàng có HSD (vd: bộ lọc nước)
                    'expiry_duration' => 24,
                ]);
            }

            // 6. GIẢ LẬP GIAO DỊCH NHẬP KHO (PURCHASE ORDERS) -> Sinh StockMovement
            for ($i = 1; $i <= 60; $i++) {
                $supplier = $faker->randomElement($suppliers);
                $po = PurchaseOrder::create([
                    'po_number' => 'PO-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'supplier_id' => $supplier->id,
                    'warehouse_id' => $warehouse->id,
                    'supplier_name' => $supplier->name,
                    'status' => 'completed', // Đã hoàn thành nhập kho
                    'created_by' => $admin->id,
                    'expected_date' => $faker->dateTimeBetween('-60 days', '-10 days'),
                    'completed_at' => $faker->dateTimeBetween('-60 days', '-10 days'),
                ]);

                // Mỗi PO có 2-5 mặt hàng
                $numItems = $faker->numberBetween(2, 5);
                $poProducts = $faker->randomElements($products, $numItems);

                foreach ($poProducts as $prod) {
                    $qty = $faker->numberBetween(20, 100);
                    $bin = $faker->randomElement($bins);

                    // Bỏ qua nếu kệ không đủ sức chứa
                    if ($bin->current_capacity + $qty > $bin->max_capacity) continue;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $prod->id,
                        'quantity' => $qty,
                        'received_quantity' => $qty,
                    ]);

                    // Cộng tồn kho
                    $inventory = Inventory::firstOrCreate(
                        ['product_id' => $prod->id, 'warehouse_id' => $warehouse->id, 'bin_location_id' => $bin->id],
                        ['on_hand_quantity' => 0, 'reserved_quantity' => 0]
                    );
                    $balanceAfter = $inventory->on_hand_quantity + $qty;
                    $inventory->update(['on_hand_quantity' => $balanceAfter]);

                    // Tăng sức chứa kệ
                    $bin->increment('current_capacity', $qty);

                    // Ghi log nhập kho đa hình
                    StockMovement::create([
                        'inventory_id' => $inventory->id,
                        'transaction_type' => 'Nhập kho',
                        'quantity_change' => $qty,
                        'balance_after' => $balanceAfter,
                        'reference_type' => PurchaseOrder::class,
                        'reference_id' => $po->id,
                        'created_by' => $admin->id,
                        'note' => 'Nhập hàng từ NCC: ' . $supplier->name,
                        'created_at' => $po->completed_at,
                    ]);
                }
            }

            // 7. GIẢ LẬP GIAO DỊCH XUẤT KHO (SALES ORDERS) -> Sinh StockMovement
            for ($i = 1; $i <= 100; $i++) {
                $partner = $faker->randomElement($partners);
                $soDate = $faker->dateTimeBetween('-10 days', 'now');
                
                $so = SalesOrder::create([
                    'so_number' => 'SO-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'partner_id' => $partner->id,
                    'warehouse_id' => $warehouse->id,
                    'customer_name' => $partner->name,
                    'status' => 'shipped',
                    'created_by' => $admin->id,
                    'confirmed_at' => $soDate,
                    'shipped_at' => $soDate,
                ]);

                $numItems = $faker->numberBetween(1, 3);
                $soProducts = $faker->randomElements($products, $numItems);

                foreach ($soProducts as $prod) {
                    // Tìm inventory có sẵn của sản phẩm này
                    $inventory = Inventory::where('product_id', $prod->id)->where('on_hand_quantity', '>', 0)->inRandomOrder()->first();
                    
                    if (!$inventory) continue; // Nếu hết hàng thì bỏ qua sản phẩm này

                    // Lượng xuất không được vượt quá tồn kho hiện tại
                    $qty = $faker->numberBetween(1, min(15, $inventory->on_hand_quantity));
                    $bin = BinLocation::find($inventory->bin_location_id);

                    SalesOrderItem::create([
                        'sales_order_id' => $so->id,
                        'product_id' => $prod->id,
                        'quantity' => $qty,
                        'shipped_quantity' => $qty,
                    ]);

                    // Trừ tồn kho
                    $balanceAfter = $inventory->on_hand_quantity - $qty;
                    $inventory->update(['on_hand_quantity' => $balanceAfter]);

                    // Giảm sức chứa kệ
                    if ($bin) {
                        $bin->decrement('current_capacity', $qty);
                    }

                    // Ghi log xuất kho đa hình
                    StockMovement::create([
                        'inventory_id' => $inventory->id,
                        'transaction_type' => 'Xuất kho',
                        'quantity_change' => -$qty, // Xuất là số âm
                        'balance_after' => $balanceAfter,
                        'reference_type' => SalesOrder::class,
                        'reference_id' => $so->id,
                        'created_by' => $admin->id,
                        'note' => 'Xuất bán cho ĐL: ' . $partner->name,
                        'created_at' => $soDate,
                    ]);
                }
            }

            DB::commit();
            $this->command->info('Đã tạo thành công dữ liệu đồ gia dụng tại Thái Nguyên (Có liên kết PurchaseOrder/SalesOrder và Sức chứa kệ)!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Lỗi khi seed: ' . $e->getMessage());
        }
    }
}