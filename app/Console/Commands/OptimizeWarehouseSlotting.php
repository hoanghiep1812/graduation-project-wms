<?php

namespace App\Console\Commands;

use App\Services\ReslottingService;
use Illuminate\Console\Command;


class OptimizeWarehouseSlotting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wms:optimize-slotting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chạy Hệ thống quét toàn bộ kho và tạo đề xuất dời kệ (Reslotting)';

    /**
     * Execute the console command.
     */

    public function handle(ReslottingService $reslottingService)
    {
        $this->info('Bắt đầu quét kho...');

        try {
            $warehouseId = 1; 
            $count = $reslottingService->generateRecommendations($warehouseId);

            $this->info("Quét hoàn tất! Đã tạo {$count} đề xuất mới.");
        } catch (\Exception $e) {
            $this->error('Lỗi: ' . $e->getMessage());
        }
    }
}
