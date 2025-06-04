<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function redirect;
use function report;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRegisterRequest $request)
    {
        $data = $request->validated();

        try{
            User::create([
               'name' => $data['name'],
               'email' => $data['email'],
               'password' => Hash::make($data['password']),
            ]);
        } catch ( \Exception $e ) {
            report($e);
        }

        return redirect()->route('login')->with([
            'message' => 'Registration Successful',
            'type' => 'success'
        ]);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with([
                'message' => 'Login successful',
                'type' => 'success'
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->intended('/')->with([
            'message' => 'Logout successful',
            'type' => 'success'
        ]);    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
