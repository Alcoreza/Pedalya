<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->loadCount('bookings')->load('googleCalendarConnection');

        return view('customer.profile', [
            'user' => $user,
            'calendarConnection' => $user->googleCalendarConnection,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile_number' => ['required', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return redirect()
            ->route('customer.profile')
            ->with('status', 'Your profile changes have been saved.');
    }
}
