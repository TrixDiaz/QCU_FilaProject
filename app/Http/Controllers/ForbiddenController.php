<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Mail\AccountActivation;
use Illuminate\Support\Facades\Mail;

class ForbiddenController extends Controller
{
    /**
     * Logout user and redirect to home
     */
    public function logoutAndRedirectHome()
    {
        Auth::logout();

        // Clear session data
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Send activation email to a random technician
     */
    public function sendTechnicianActivation()
    {
        // Find a random technician using Filament Shield/Spatie role relationship
        $technician = User::role('technician')->inRandomOrder()->first();

        if (!$technician) {
            return back()->with('error', 'No technicians found in the system.');
        }

        // Get current authenticated user
        $authUser = Auth::user();

        // Send activation email
        Mail::to($technician->email)->send(new AccountActivation($authUser));

        return back()->with('success', 'Activation email sent to technician successfully.');
    }
}
