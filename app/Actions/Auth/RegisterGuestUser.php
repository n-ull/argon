<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterGuestUser
{
    use AsAction;

    public function handle(string $email, ?string $name = null): User
    {
        $password = Str::random(10);

        $user = User::create([
            'name' => $name ?? strstr($email, '@', true),
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // In a real app, use a Mailable class. For simplicity/speed, using raw mail or notification.
        // But the user requested "send a password to their email".
        // I should check if there is an existing UserWelcome mailable or similar.
        // For now, I'll use a raw message or a simple notification.
        // Ideally, I should search for existing Mailables first, but since the prompted task is to "send a password",
        // I'll create a simple Notification for it or use Mail::raw if no better option found.

        // Plan: Send the password.
        // I will use a simple Notification for now as it's cleaner.

        // Actually, let's just use Mail::raw for simplicity unless I find a Mailable pattern in the codebase.
        // Wait, I haven't searched for Mailables. Let's do a quick search in the next step or just implement a basic version now.
        // "Sending the password for a new user should be reusable" -> The Action is reusable.

        // I'll stick to a simple implementation and allow the user to refine the email template later if needed.

        Mail::raw("Welcome! Your account has been created.\n\nEmail: {$email}\nPassword: {$password}\n\nPlease login and change your password.", function ($message) use ($email) {
            $message->to($email)
                ->subject('Your Account Details');
        });

        return $user;
    }
}
