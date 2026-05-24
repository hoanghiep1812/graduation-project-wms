<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderItemAllocation;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WmsOutboundTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SalesOrderService();
    }

    public function test_reserve_stock_success()
    {
        // 1. Setup DB
        $product = Product::create(['sku' => 'TEST-001', 'name' => 'Test Product']);
        $warehouse = Warehouse::create(['code' => 'WHM-01', 'name' => 'Main Warehouse']);
        $bin1 = BinLocation::create(['warehouse_id' => $warehouse->id, 'code' => 'B-01']);
        $bin2 = BinLocation::create(['warehouse_id' => $warehouse->id, 'code' => 'B-02']);

        // Stock in bin 1 (20) and bin 2 (30) -> total 50
        Inventory::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'bin_location_id' => $bin1->id, 'on_hand_quantity' => 20, 'reserved_quantity' => 0]);
        Inventory::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'bin_location_id' => $bin2->id, 'on_hand_quantity' => 30, 'reserved_quantity' => 0]);

        // 2. Create Order
        $so = SalesOrder::create(['so_number' => 'SO-001', 'customer_name' => 'Cust', 'status' => 'draft']);
        SalesOrderItem::create(['sales_order_id' => $so->id, 'product_id' => $product->id, 'quantity' => 35]);

        // 3. Action
        $this->service->reserveStock($so->fresh(), 1);

        // 4. Assertions
        $this->assertEquals('confirmed', $so->fresh()->status);
        $this->assertEquals(20, Inventory::where('bin_location_id', $bin1->id)->first()->reserved_quantity);
        $this->assertEquals(15, Inventory::where('bin_location_id', $bin2->id)->first()->reserved_quantity);
        $this->assertEquals(2, SalesOrderItemAllocation::count());
    }

    public function test_release_stock_success()
    {
        // Setup DB
        $product = Product::create(['sku' => 'TEST-002', 'name' => 'Test Product']);
        $warehouse = Warehouse::create(['code' => 'WHM-01', 'name' => 'Main']);
        $bin1 = BinLocation::create(['warehouse_id' => $warehouse->id, 'code' => 'B-01']);
        $inv = Inventory::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'bin_location_id' => $bin1->id, 'on_hand_quantity' => 10, 'reserved_quantity' => 0]);

        $so = SalesOrder::create(['so_number' => 'SO-002', 'customer_name' => 'Cust', 'status' => 'draft']);
        $item = SalesOrderItem::create(['sales_order_id' => $so->id, 'product_id' => $product->id, 'quantity' => 5]);

        $this->service->reserveStock($so->fresh(), 1);

        // Precondition
        $this->assertEquals(5, $inv->fresh()->reserved_quantity);
        $this->assertEquals(1, SalesOrderItemAllocation::count());

        // Action
        $this->service->releaseStock($so->fresh(), 1);

        // Assertions
        $this->assertEquals('cancelled', $so->fresh()->status);
        $this->assertEquals(0, $inv->fresh()->reserved_quantity);
        $this->assertEquals(0, SalesOrderItemAllocation::count());
    }

    public function test_ship_order_success()
    {
        // Setup DB
        $product = Product::create(['sku' => 'TEST-003', 'name' => 'Test Product']);
        $warehouse = Warehouse::create(['code' => 'WHM-01', 'name' => 'Main']);
        $bin1 = BinLocation::create(['warehouse_id' => $warehouse->id, 'code' => 'B-01']);
        $inv = Inventory::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'bin_location_id' => $bin1->id, 'on_hand_quantity' => 20, 'reserved_quantity' => 0]);

        $so = SalesOrder::create(['so_number' => 'SO-003', 'customer_name' => 'Cust', 'status' => 'draft']);
        $item = SalesOrderItem::create(['sales_order_id' => $so->id, 'product_id' => $product->id, 'quantity' => 15]);

        $this->service->reserveStock($so->fresh(), 1);

        // Action
        $this->service->shipOrder($so->fresh(), 1);

        // Assertions
        $this->assertEquals('shipped', $so->fresh()->status);
        $this->assertEquals(5, $inv->fresh()->on_hand_quantity); // Deducted
        $this->assertEquals(0, $inv->fresh()->reserved_quantity); // Deducted/Cleared
        $this->assertEquals(15, $item->fresh()->shipped_quantity);
    }
}
