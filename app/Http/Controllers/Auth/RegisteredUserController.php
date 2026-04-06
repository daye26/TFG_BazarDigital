<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedCountryCode = preg_replace('/\s+/', '', (string) $request->input('phone_country_code'));
        $normalizedPhoneNumber = preg_replace('/\s+/', '', (string) $request->input('phone_number'));

        $request->merge([
            'phone_country_code' => $normalizedCountryCode,
            'phone_number' => $normalizedPhoneNumber,
            'phone' => $normalizedCountryCode.$normalizedPhoneNumber,
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_country_code' => ['required', 'string', 'regex:/^\+[1-9]\d{0,3}$/'],
            'phone_number' => ['required', 'string', 'regex:/^\d{6,14}$/'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route($user->redirectRouteName(), absolute: false));
    }
}
