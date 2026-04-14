<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use App\Models\Payment;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('customer');
        $customer = $user->customer;

        $subscription = null;
        $totalPaid = 0;
        $activeInstances = Instance::query()->where('user_id', $user->id)->count();

        if ($customer) {
            $subscription = Subscription::query()
                ->with('plan')
                ->where('customer_id', $customer->id)
                ->latest('end_date')
                ->first();

            $totalPaid = (float) Payment::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'approved')
                ->sum('amount');
        }

        return view('profile.edit', [
            'user' => $user,
            'customer' => $customer,
            'subscription' => $subscription,
            'activeInstances' => $activeInstances,
            'totalPaid' => $totalPaid,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->customer) {
            $user->customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'country_code' => $validated['country_code'] ?? null,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function settings(Request $request): View
    {
        $user = $request->user()->load('customer');
        $settings = array_merge([
            'language' => 'en',
            'timezone' => config('app.timezone', 'UTC'),
            'email_notifications' => true,
            'whatsapp_notifications' => true,
            'marketing_notifications' => false,
        ], $user->settings ?? []);

        return view('profile.settings', [
            'user' => $user,
            'customer' => $user->customer,
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'string', 'in:en,ar'],
            'timezone' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $settings = array_merge($user->settings ?? [], [
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
            'email_notifications' => $request->boolean('email_notifications'),
            'whatsapp_notifications' => $request->boolean('whatsapp_notifications'),
            'marketing_notifications' => $request->boolean('marketing_notifications'),
        ]);

        $user->update([
            'settings' => $settings,
        ]);

        return Redirect::route('settings.edit')->with('status', 'settings-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
