<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CylinderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = $this->getUserStats($user);
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($user);
        
        return view('profile.index', compact('user', 'stats', 'recentActivity'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_image' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $validated['profile_image'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Show security settings
     */
    public function security()
    {
        $user = Auth::user();
        $sessions = $this->getActiveSessions();
        
        return view('profile.security', compact('user', 'sessions'));
    }

    /**
     * Delete user account (with confirmation)
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        // Delete profile image
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Account deleted successfully!');
    }

    /**
     * Get user statistics
     */
    private function getUserStats($user)
    {
        $stats = [
            'total_sales' => $user->sales()->count(),
            'total_purchases' => $user->purchases()->count(),
            'total_customers_created' => $user->customers()->count(),
            'total_suppliers_created' => $user->suppliers()->count(),
            'total_cylinder_transactions' => $user->cylinderTransactions()->count(),
            'total_journal_entries' => $user->journalEntries()->count(),
        ];

        // Calculate total sales amount
        $stats['total_sales_amount'] = $user->sales()
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        // Calculate total purchase amount
        $stats['total_purchase_amount'] = $user->purchases()
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        // Member since
        $stats['member_since'] = $user->created_at->format('F Y');

        return (object) $stats;
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($user)
    {
        $activities = collect();

        // Recent sales
        $sales = $user->sales()
            ->with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return (object) [
                    'type' => 'sale',
                    'title' => 'Created Sale Invoice',
                    'description' => $sale->invoice_no . ' - ' . ($sale->customer->name ?? 'N/A'),
                    'amount' => $sale->grand_total,
                    'date' => $sale->created_at,
                    'url' => route('sales.show', $sale)
                ];
            });

        // Recent purchases
        $purchases = $user->purchases()
            ->with('supplier')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($purchase) {
                return (object) [
                    'type' => 'purchase',
                    'title' => 'Created Purchase Invoice',
                    'description' => $purchase->purchase_invoice_no . ' - ' . ($purchase->supplier->name ?? 'N/A'),
                    'amount' => $purchase->grand_total,
                    'date' => $purchase->created_at,
                    'url' => route('purchases.show', $purchase)
                ];
            });

        // Recent cylinder transactions
        $cylinderTransactions = $user->cylinderTransactions()
            ->with(['cylinder', 'customer'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return (object) [
                    'type' => 'cylinder',
                    'title' => 'Cylinder ' . ucfirst($transaction->transaction_type),
                    'description' => $transaction->cylinder->cylinder_number . ' - ' . ($transaction->customer->name ?? 'N/A'),
                    'amount' => $transaction->damage_charge ?? 0,
                    'date' => $transaction->created_at,
                    'url' => null
                ];
            });

        // Merge and sort all activities
        $activities = $sales->concat($purchases)->concat($cylinderTransactions)
            ->sortByDesc('date')
            ->take(10);

        return $activities;
    }

    /**
     * Get active sessions
     */
    private function getActiveSessions()
    {
        if (!config('session.driver') === 'database') {
            return collect();
        }

        return \DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return (object) [
                    'device' => $this->getDeviceInfo($session->user_agent),
                    'ip_address' => $session->ip_address,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity),
                    'is_current' => $session->id === session()->getId()
                ];
            });
    }

    /**
     * Get device info from user agent
     */
    private function getDeviceInfo($userAgent)
    {
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
}