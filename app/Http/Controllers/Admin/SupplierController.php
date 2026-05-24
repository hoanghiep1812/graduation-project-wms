<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
	protected function checkAdmin()
    {
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Hành động bị từ chối. Cấp quyền Quản trị viên không hợp lệ.');
        }
    }
    
    public function index(Request $request)
    {
    	$this->checkAdmin();
        $query = Supplier::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('tax_code', 'like', "%{$keyword}%");
            });
        }
        $suppliers = $query->orderBy('created_at', 'desc')->paginate(10);
        $suppliers->appends($request->all());

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $validatedData = $request->validate([
		    'code' => ['required','string','max:50', Rule::unique('suppliers')->whereNull('deleted_at')],
		
		    'name' => ['required','string','max:255', Rule::unique('suppliers')->whereNull('deleted_at')],
		
		    'phone' => ['required','string','max:20', Rule::unique('suppliers')->whereNull('deleted_at')],
		
		    'tax_code' => 'nullable|string|max:50',
		    'address' => 'nullable|string',
		    'status' => 'nullable|in:active,inactive'
		], [
		    'code.unique' => 'Mã NCC đã tồn tại!',
		    'name.unique' => 'Tên NCC đã tồn tại!',
		    'phone.unique' => 'Số điện thoại đã tồn tại!',
		]);
        if (!isset($validatedData['status'])) {
            $validatedData['status'] = 'active';
        }

        Supplier::create($validatedData);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Đã thêm Nhà Cung Cấp thành công!');
    }

    public function show(Supplier $supplier)
    {

        $supplier->load(['inbounds' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'inbounds.creator']);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier) {}

    public function update(Request $request, Supplier $supplier)
    {
        $validatedData = $request->validate([
		    'code' => [
		        'required',
		        'string',
		        'max:50',
		        Rule::unique('suppliers')
		            ->ignore($supplier->id)
		            ->whereNull('deleted_at')
		    ],
		
		    'name' => [
		        'required',
		        'string',
		        'max:255',
		        Rule::unique('suppliers')
		            ->ignore($supplier->id)
		            ->whereNull('deleted_at')
		    ],
		
		    'phone' => [
		        'required',
		        'string',
		        'max:20',
		        Rule::unique('suppliers')
		            ->ignore($supplier->id)
		            ->whereNull('deleted_at')
		    ],
		
		    'tax_code' => 'nullable|string|max:50',
		    'address' => 'nullable|string',
		    'status' => 'required|in:active,inactive'
		]);
        $supplier->update($validatedData);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Đã cập nhật thông tin Nhà Cung Cấp!');
    }

    public function destroy(Supplier $supplier)
	{
	    if ($supplier->inbounds()->exists()) {
	
	        $supplier->update([
	            'status' => 'inactive'
	        ]);
	
	        return redirect()
	            ->route('admin.suppliers.index')
	            ->with(
	                'success',
	                'Nhà cung cấp đã phát sinh giao dịch nên chỉ được chuyển sang trạng thái Ngừng giao dịch!'
	            );
	    }
	
	    $supplier->delete();
	
	    return redirect()
	        ->route('admin.suppliers.index')
	        ->with(
	            'success',
	            'Đã xóa nhà cung cấp khỏi hệ thống!'
	        );
	}
}
