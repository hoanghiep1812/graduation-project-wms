<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BinLocation;
use App\Models\Zone;
use Illuminate\Http\Request;

class BinLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = BinLocation::with('zone');

        if ($request->has('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $bins = $query->orderBy('zone_id')->orderBy('code')->paginate(15);
        return view('admin.bins.index', compact('bins'));
    }

    public function create()
    {
        $zones = Zone::orderBy('code')->get();
        return view('admin.bins.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:bin_locations,code|max:50',
            'zone_id' => 'required|exists:zones,id',
            'max_capacity' => 'required|integer|min:1'
        ]);

        BinLocation::create([
            'code' => strtoupper($request->code),
            'zone_id' => $request->zone_id,
            'warehouse_id' => 4,
            'max_capacity' => $request->max_capacity,
            'current_capacity' => 0
        ]);

        return redirect()->route('admin.bins.index')->with('success', 'Đã thêm Kệ hàng thành công!');
    }

    public function edit($id)
    {
        $bin = BinLocation::findOrFail($id);
        $zones = Zone::orderBy('code')->get();
        return view('admin.bins.edit', compact('bin', 'zones'));
    }

    public function update(Request $request, $id)
    {
        $bin = BinLocation::findOrFail($id);
        $request->validate([
            'code' => 'required|max:50|unique:bin_locations,code,' . $id,
            'zone_id' => 'required|exists:zones,id',
            'max_capacity' => 'required|integer|min:1'
        ]);

        $bin->update([
            'code' => strtoupper($request->code),
            'zone_id' => $request->zone_id,
            'max_capacity' => $request->max_capacity
        ]);

        return redirect()->route('admin.bins.index')->with('success', 'Đã cập nhật Kệ hàng!');
    }

    public function destroy($id)
    {
        $bin = BinLocation::findOrFail($id);
        try {
            $bin->delete();
            return redirect()->route('admin.bins.index')->with('success', 'Đã xóa Kệ hàng!');
        } catch (\Exception $e) {
            return redirect()->route('admin.bins.index')->with('error', 'Không thể xóa vì trên Kệ này đang có Tồn kho!');
        }
    }
}
