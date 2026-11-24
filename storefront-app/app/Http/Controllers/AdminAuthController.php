<?php

namespace App\Http\Controllers;

use App\Repositories\AdminRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

# admin login || same as AuthController but checks app_admin
class AdminAuthController extends Controller
{
    public function __construct(protected AdminRepository $admins){}

    public function show_login():View{
        return view('admin.login');
    }

    public function login(Request $request):RedirectResponse{
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $admin = $this->admins->find_user_by_email($data['email']);
        if (!$admin || !Hash::check($data['password'], $admin->password_hash)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => (int) $admin->user_id,
            'user_type' => 'ADMIN',
            'user_name' => $admin->first_name
        ]);
        return redirect()->route('admin.products.index');
    }

    public function logout(Request $request):RedirectResponse{
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
