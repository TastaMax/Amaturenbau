<?php

namespace App\Http\Controllers\Management\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function index()
    {
        if(Auth::user())
        {
            return redirect('/')->with([]);
        }

        return view('pages/login/index', []);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Instance of App\Models\User:
            $user = Auth::user();
            return redirect('/')->with([
                'success' => "Willkommen ".$user->name."!"
            ]);
        } else {
            // Invalid credentials.
            return redirect()->back()->with('error', 'Benutzername oder Passwort falsch!');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with([
            'success' => "Sie wurden erfolgreich ausgeloggt!"
        ]);
    }

}
