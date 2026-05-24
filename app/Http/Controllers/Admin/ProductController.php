<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
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
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku' => 'required|max:50|unique:products,sku,' . $id,
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'minimum_stock' => 'nullable|integer|min:0',
            'expiry_duration' => 'nullable|integer|min:1',
        ]);

        $product->update([
            'sku' => $request->sku,
            'name' => $request->name,
            'unit' => $request->unit,
            'minimum_stock' => $request->minimum_stock ?? 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'has_expiry' => $request->has('has_expiry') ? 1 : 0,
            'expiry_duration' => $request->has('has_expiry') ? $request->expiry_duration : null,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Đã cập nhật sản phẩm thành công!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        try {
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Đã xóa sản phẩm thành công!');
        } catch (\Exception $e) {

            return redirect()->route('admin.products.index')->with('error', 'Không thể xóa vì sản phẩm này đã có dữ liệu Nhập/Xuất kho! Bạn chỉ có thể Tắt trạng thái kinh doanh.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:products,sku|max:50',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'minimum_stock' => 'nullable|integer|min:0',
            'expiry_duration' => 'nullable|integer|min:1',
        ]);

        Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'unit' => $request->unit,
            'minimum_stock' => $request->minimum_stock ?? 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'has_expiry' => $request->has('has_expiry') ? 1 : 0,
            'expiry_duration' => $request->has('has_expiry') ? $request->expiry_duration : null,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Đã thêm sản phẩm mới thành công!');
    }
}
