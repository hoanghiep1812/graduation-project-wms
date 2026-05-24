<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @return void
     */
    protected function checkAdmin()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if (!$currentUser || !$currentUser->isAdmin()) {
            abort(403, 'Hành động bị từ chối. Cấp quyền Quản trị viên không hợp lệ.');
        }
    }

    public function index()
	{
	    $this->checkAdmin();
	
	    $users = User::orderBy('created_at', 'desc')->paginate(10);
	
	    return view('admin.users.index', compact('users'));
	}

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff',
        ], [
            'username.unique' => 'Tên đăng nhập / Mã NV đã tồn tại.',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->back()->with('success', 'Đã cấp tài khoản thành công.');
    }

    public function resetPassword(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|min:6',
            'admin_security_code' => 'required'
        ]);

        if (!Hash::check($request->admin_security_code, auth()->user()->password)) {
            return back()->withErrors(['admin_security_code' => 'Mã bảo mật Admin không chính xác. Hành động bị từ chối!']);
        }

        $targetUser = User::findOrFail($request->user_id);
        if ($targetUser->isAdmin()) {
            return back()->withErrors(['admin_security_code' => 'Hành động không hợp lệ: Không can thiệp tài khoản Quản trị viên khác.']);
        }

        $targetUser->password = Hash::make($request->new_password);
        $targetUser->save();

        return back()->with('success', 'Đã cấp lại mật khẩu cho ' . $targetUser->name);
    }

    public function update(Request $request, User $user)
    {
        $this->checkAdmin();

        if ($user->isAdmin() || $user->id === auth()->id()) {
            return back()->withErrors(['Hành động không hợp lệ: Không can thiệp tài khoản Quản trị viên.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:staff',
        ]);

        $user->update($validated);
        return back()->with('success', 'Cập nhật thông tin thành công.');
    }

    public function destroy(User $user)
	{
	    $this->checkAdmin();
	
	    if ($user->isAdmin() || $user->id === auth()->id()) {
	        return back()->withErrors(['Không thể thao tác tài khoản này.']);
	    }
	
	    if (!$user->is_active) {
	        return back()->withErrors(['Tài khoản đã bị vô hiệu hóa trước đó.']);
	    }
	
	    $user->update(['is_active' => false]);
	
	    return back()->with('success', 'Đã vô hiệu hóa tài khoản.');
	}
}
