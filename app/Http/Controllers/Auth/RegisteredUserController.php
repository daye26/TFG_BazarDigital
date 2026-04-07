<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\ValidPhoneNumber;
use App\Services\PhoneNumberService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
    public function store(Request $request, PhoneNumberService $phoneNumberService): RedirectResponse
    {
        $normalizedCountryCode = preg_replace('/\s+/', '', (string) $request->input('phone_country_code'));
        $normalizedPhoneNumber = preg_replace('/\s+/', '', (string) $request->input('phone_number'));

        $request->merge([
            'phone_country_code' => $normalizedCountryCode,
            'phone_number' => $normalizedPhoneNumber,
            'phone' => $phoneNumberService->normalizeOrConcatenate($normalizedCountryCode, $normalizedPhoneNumber),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone_country_code' => ['bail', 'required', 'string', 'regex:/^\+[1-9]\d{0,2}$/'],
            'phone_number' => ['bail', 'required', 'string', 'regex:/^\d{2,14}$/'],
            'phone' => ['bail', 'required', 'string', new ValidPhoneNumber, Rule::unique(User::class, 'phone')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route($user->redirectRouteName(), absolute: false));
    }
}
