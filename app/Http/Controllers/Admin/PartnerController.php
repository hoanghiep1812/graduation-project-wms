<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
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
        $query = Partner::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('tax_code', 'like', "%{$keyword}%");
            });
        }

        $partners = $query->orderBy('created_at', 'desc')->paginate(10);
        $partners->appends($request->all());

        return view('admin.partners.index', compact('partners'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $validatedData = $request->validate([
		    'code' => ['required','string','max:50', Rule::unique('partners')->whereNull('deleted_at')],
		
		    'name' => ['required','string','max:255', Rule::unique('partners')->whereNull('deleted_at')],
		
		    'phone' => ['required','string','max:20', Rule::unique('partners')->whereNull('deleted_at')],
		
		    'tax_code' => 'nullable|string|max:50',
		    'address' => 'nullable|string',
		    'status' => 'nullable|in:active,inactive'
		], [
		    'code.unique' => 'Mã Đối tác đã tồn tại!',
		    'name.unique' => 'Tên Đối tác đã tồn tại!',
		    'phone.unique' => 'Số điện thoại đã tồn tại!',
		]);

        if (!isset($validatedData['status'])) {
            $validatedData['status'] = 'active';
        }

        Partner::create($validatedData);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Đã thêm Đối Tác thành công!');
    }

    public function show(Partner $partner)
    {
        $partner->load(['salesOrders' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('admin.partners.show', compact('partner'));
    }

    public function edit(Partner $partner) {}

    public function update(Request $request, Partner $partner)
    {
        $validatedData = $request->validate([
		    'code' => [
		        'required',
		        'string',
		        'max:50',
		        Rule::unique('partners')
		            ->ignore($partner->id)
		            ->whereNull('deleted_at')
		    ],
		
		    'name' => [
		        'required',
		        'string',
		        'max:255',
		        Rule::unique('partners')
		            ->ignore($partner->id)
		            ->whereNull('deleted_at')
		    ],
		
		    'phone' => [
		        'required',
		        'string',
		        'max:20',
		        Rule::unique('partners')
		            ->ignore($partner->id)
		            ->whereNull('deleted_at')
		    ],
		
		    'tax_code' => 'nullable|string|max:50',
		    'address' => 'nullable|string',
		    'status' => 'required|in:active,inactive'
		]);

        $partner->update($validatedData);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Đã cập nhật thông tin Đối Tác!');
    }

    public function destroy(Partner $partner)
    {
        if (method_exists($partner, 'salesOrders') && $partner->salesOrders()->count() > 0) {
            $partner->update(['status' => 'inactive']);
            return redirect()->route('admin.partners.index')
                ->with('success', 'Khách hàng này đã có giao dịch nên chỉ được chuyển sang trạng thái Ngừng HĐ!');
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Đã xóa Đối Tác ra khỏi hệ thống!');
    }
}
