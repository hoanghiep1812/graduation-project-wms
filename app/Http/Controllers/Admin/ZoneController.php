<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $query = Zone::withCount('binLocations');

        if ($request->has('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $zones = $query->orderBy('code', 'asc')->paginate(10);
        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        return view('admin.zones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:zones,code|max:50',
            'description' => 'nullable|string|max:255',
            'distance_to_packing' => 'required|integer|min:0',
        ]);

        Zone::create([
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'distance_to_packing' => $request->distance_to_packing,
            'warehouse_id' => 4,
        ]);

        return redirect()->route('admin.zones.index')->with('success', 'Đã thêm Khu Vực mới!');
    }

    public function edit($id)
    {
        $zone = Zone::findOrFail($id);
        return view('admin.zones.edit', compact('zone'));
    }

    public function update(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);
        $request->validate([
            'code' => 'required|max:50|unique:zones,code,' . $id,
            'description' => 'nullable|string|max:255',
            'distance_to_packing' => 'required|integer|min:0',
        ]);

        $zone->update([
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'distance_to_packing' => $request->distance_to_packing,
        ]);

        return redirect()->route('admin.zones.index')->with('success', 'Đã cập nhật Khu Vực!');
    }

    public function destroy($id)
    {
        $zone = Zone::findOrFail($id);
        try {
            $zone->delete();
            return redirect()->route('admin.zones.index')->with('success', 'Đã xóa Khu Vực!');
        } catch (\Exception $e) {
            return redirect()->route('admin.zones.index')->with('error', 'Không thể xóa vì Khu Vực này đang chứa Kệ hàng! Vui lòng xóa/chuyển các Kệ đi nơi khác trước.');
        }
    }
}
