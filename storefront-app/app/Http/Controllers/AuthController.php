<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateEmailException;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

# customer login/register || session based, no Eloquent/Auth guard
class AuthController extends Controller
{
    public function __construct(protected UserRepository $users){}

    public function show_login():View{
        return view('auth.login');
    }

    public function login(Request $request):RedirectResponse{
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        # look up by email, then check the hash (never query by hash)
        $user = $this->users->find_user_by_email($data['email']);
        if (!$user || !Hash::check($data['password'], $user->password_hash)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => (int) $user->user_id,
            'user_type' => 'CUSTOMER',
            'user_name' => $user->first_name
        ]);
        return redirect()->route('products.index');
    }

    public function show_register():View{
        return view('auth.register');
    }

    public function register(Request $request):RedirectResponse{
        $data = $request->validate([
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed'
        ]);
        # hash here so the repository never sees the plain password
        try {
            $user_id = $this->users->insert_user($data['email'], Hash::make($data['password']),
                $data['first_name'], $data['last_name']);
        } catch (DuplicateEmailException $e) {
            return back()->withErrors(['email' => 'That email is already registered.'])->withInput();
        }
        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $user_id,
            'user_type' => 'CUSTOMER',
            'user_name' => $data['first_name']
        ]);
        return redirect()->route('products.index');
    }

    public function logout(Request $request):RedirectResponse{
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('products.index');
    }
}
