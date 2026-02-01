<?php

namespace App\Modules\Ordering\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Domain\Ordering\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutAuthController extends Controller
{
    public function register(Order $order, Request $request)
    {
        // 1. Validation
        if ($order->user_id) {
            return back()->with('message', flash_info('Already Registered', 'This order is already associated with a user.'));
        }

        if (Auth::check()) {
            $user = Auth::user();

            $order->update(['user_id' => $user->id]);
            return back()->with('message', flash_success('Linked', 'Order linked to your account.'));
        }

        // Validate Email input
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        // 2. Check if email already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return redirect()->route('login', ['return_url' => route('orders.checkout', $order)])
                ->with('message', flash_info('Account Exists', 'Please log in to continue with this email.'));
        }

        // 3. Auto-Register
        $newUser = \App\Actions\Auth\RegisterGuestUser::run($email);

        // 4. Link Order
        $order->update(['user_id' => $newUser->id]);

        // 5. Login
        Auth::login($newUser);

        return back()->with('message', flash_success('Account Created', 'Your account has been created and you are now logged in.'));
    }
}
