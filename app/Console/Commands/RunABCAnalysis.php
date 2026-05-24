<?php

namespace App\Console\Commands;

use App\Services\DemandAnalysisService;
use Illuminate\Console\Command;

class RunABCAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wms:run-abc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chạy thuật toán phân tích ABC để phân hạng sản phẩm (Hot/Medium/Slow)';

    /**
     * Execute the console command.
     */
    public function handle(DemandAnalysisService $demandService)
    {
        $this->info('Đang thu thập và phân tích dữ liệu xuất kho (Pareto 80/20)...');

        try {
            $demandService->calculateVelocityForAllProducts();

            $this->info("Hoàn tất! Đã học (train) và cập nhật nhãn AI cho toàn bộ sản phẩm trong kho.");
        } catch (\Exception $e) {
            $this->error('Lỗi thuật toán: ' . $e->getMessage());
        }
    }
}
