<?php

namespace App\Http\Controllers\Admin;

use App\Events\AiCompletedEvent;
use App\Helpers\FCMHelper;
use App\Http\Controllers\Controller;
use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\SlottingRecommendation;
use App\Services\ReslottingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReslottingController extends Controller
{
    public function index()
    {
        $recommendations = SlottingRecommendation::with(['product', 'currentBin.zone', 'recommendedBin.zone'])
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.reslotting.index', compact('recommendations'));
    }

    private function notifyStaff($title, $content)
    {
        $staffs = \App\Models\User::where('role', 'staff')->get();
        foreach ($staffs as $staff) {
            $noti = \App\Models\Notification::create([
                'user_id' => $staff->id,
                'title' => $title,
                'content' => $content,
                'type' => 'success'
            ]);
        }
    }

    public function approve($id, \App\Services\StockMovementService $stockMovementService)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user->isAdmin()) {
            abort(403, 'Bạn không có thẩm quyền duyệt dời kệ!');
        }
        $recommendation = SlottingRecommendation::findOrFail($id);

        try {
            DB::transaction(function () use ($recommendation, $stockMovementService) {
                $inventory = Inventory::select('inventories.*')
                    ->join('batches', 'inventories.batch_id', '=', 'batches.id')
                    ->where('inventories.product_id', $recommendation->product_id)
                    ->where('inventories.bin_location_id', $recommendation->current_bin_id)
                    ->where('inventories.on_hand_quantity', '>', 0)
                    ->orderByRaw('batches.expiry_date IS NULL ASC')
                    ->orderBy('batches.expiry_date', 'asc')
                    ->orderBy('inventories.on_hand_quantity', 'asc')
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) throw new \Exception('Không tìm thấy lô hàng khả dụng nào để dời trên Kệ này!');

                if ($inventory->reserved_quantity > 0) {
                    throw new \Exception('Không thể dời kệ vì lô hàng này đang bị giữ chỗ (reserved). Chờ xuất xong mới được dời!');
                }

                $oldBin = BinLocation::lockForUpdate()->find($recommendation->current_bin_id);
                $newBin = BinLocation::lockForUpdate()->find($recommendation->recommended_bin_id);

                $aiReason = json_decode($recommendation->reason, true);
                $aiSuggestedQty = (isset($aiReason['qty_to_move'])) ? (int) $aiReason['qty_to_move'] : (int) $inventory->on_hand_quantity;

                $maxCap = (int) $newBin->max_capacity;
                $currentCap = (int) $newBin->current_capacity;
                $onHand = (int) $inventory->on_hand_quantity;

                $availableSpace = $maxCap - $currentCap;

                $qtyToMove = min($aiSuggestedQty, $onHand, $availableSpace);

                if ($qtyToMove <= 0) {
                    throw new \Exception("Hủy thao tác! Đề xuất dời: {$aiSuggestedQty} | Tồn lô cũ nhất đang có: {$onHand} | Kệ đích còn trống: {$availableSpace}. Vui lòng kiểm tra lại!");
                }

                $userId = auth()->id() ?? 1;

                $newInventory = Inventory::firstOrNew([
                    'product_id'      => $recommendation->product_id,
                    'warehouse_id'    => $inventory->warehouse_id,
                    'bin_location_id' => $recommendation->recommended_bin_id,
                    'batch_id'        => $inventory->batch_id
                ]);

                $newInventory->on_hand_quantity += $qtyToMove;
                $newInventory->reserved_quantity = $newInventory->reserved_quantity ?? 0;
                $newInventory->save();

                $inventory->on_hand_quantity -= $qtyToMove;
                $inventory->save();

                $oldBin->current_capacity -= $qtyToMove;
                $oldBin->save();

                $newBin->current_capacity += $qtyToMove;
                $newBin->save();

                $stockMovementService->recordMovement($inventory, -$qtyToMove, 'transfer_out', null, $userId);
                $stockMovementService->recordMovement($newInventory, $qtyToMove, 'transfer_in', null, $userId);

                $recommendation->update(['status' => 'approved']);
                $productName = $inventory->product->name ?? 'Sản phẩm';
                $oldBin = $oldBin->code;
                $newBin = $newBin->code;
                $this->notifyStaff(
                    "Nhiệm vụ dời kệ mới!",
                    "Quản lý yêu cầu chuyển {$qtyToMove} {$productName} từ {$oldBin} sang {$newBin}. Vui lòng thực hiện."
                );
            });

            return redirect()->route('admin.reslotting.index')->with('success', 'Đã dời kệ thành công!');
        } catch (\Exception $e) {
            $recommendation->update(['status' => 'rejected']);
            return redirect()->route('admin.reslotting.index')->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $recommendation = SlottingRecommendation::findOrFail($id);
        $recommendation->update(['status' => 'rejected']);
        return redirect()->route('admin.reslotting.index')->with('success', 'Đã từ chối đề xuất dời kệ này.');
    }

    public function generate(ReslottingService $reslottingService)
    {
        try {
            $count = $reslottingService->generateRecommendations(4);

            if ($count > 0) {
                $currentUser = auth()->user();
                $triggerName = $currentUser->name;

                $admins = \App\Models\User::where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    if ($admin->id !== $currentUser->id) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'title' => "Có {$count} đề xuất dời kệ mới",
                            'content' => "{$triggerName} vừa chạy phân tích kho. Mời bạn vào kiểm tra và duyệt.",
                            'type' => 'warning'
                        ]);
                    }
                }

                if ($currentUser->role !== 'admin') {
                    FCMHelper::sendToBoss(
                        'Đề xuất Dời kệ',
                        "Vừa tìm thấy {$count} phương án dời kệ do nhân viên {$triggerName} kích hoạt. Hãy vào hệ thống duyệt ngay nhé!"
                    );
                }

                broadcast(new AiCompletedEvent(
                    "Hệ thống đã phân tích xong kho! Tìm thấy {$count} đề xuất."
                ));

                return redirect()->route('admin.reslotting.index')->with('success', "Tìm thấy {$count} đề xuất.");
            } else {
                broadcast(new AiCompletedEvent(
                    "Hệ thống vừa quét xong: Kho đang rất tối ưu."
                ));

                return redirect()->route('admin.reslotting.index')->with('success', "Kho đang rất tối ưu.");
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.reslotting.index')->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }
}
