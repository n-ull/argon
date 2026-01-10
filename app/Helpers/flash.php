<?php

/**
 * Flash Message Helpers
 *
 * These helpers provide a consistent way to create flash messages across the application.
 *
 * Usage Examples:
 *
 * // Success message
 * return redirect()->route('home')->with('message', flash_success(
 *     'Order created!',
 *     'Your order has been successfully created.'
 * ));
 *
 * // Error message
 * return back()->with('message', flash_error(
 *     'Validation failed',
 *     'Please check your input and try again.'
 * ));
 *
 * // Warning message
 * return redirect()->route('dashboard')->with('message', flash_warning(
 *     'Session expiring soon',
 *     'Your session will expire in 5 minutes.'
 * ));
 *
 * // Info message
 * return redirect()->route('profile')->with('message', flash_info(
 *     'Profile updated',
 *     'Your profile information has been updated.'
 * ));
 *
 * // Custom type
 * return back()->with('message', flash_message(
 *     'Custom message',
 *     'This is a custom message.',
 *     'info'
 * ));
 */
if (! function_exists('flash_message')) {
    /**
     * Flash a message to the session
     *
     * @param  'success'|'error'|'warning'|'info'  $type
     */
    function flash_message(string $summary, string $detail, string $type = 'info'): array
    {
        return [
            'summary' => $summary,
            'detail' => $detail,
            'type' => $type,
        ];
    }
}

if (! function_exists('flash_success')) {
    /**
     * Flash a success message
     */
    function flash_success(string $summary, string $detail): array
    {
        return flash_message($summary, $detail, 'success');
    }
}

if (! function_exists('flash_error')) {
    /**
     * Flash an error message
     */
    function flash_error(string $summary, string $detail): array
    {
        return flash_message($summary, $detail, 'error');
    }
}

if (! function_exists('flash_warning')) {
    /**
     * Flash a warning message
     */
    function flash_warning(string $summary, string $detail): array
    {
        return flash_message($summary, $detail, 'warning');
    }
}

if (! function_exists('flash_info')) {
    /**
     * Flash an info message
     */
    function flash_info(string $summary, string $detail): array
    {
        return flash_message($summary, $detail, 'info');
    }
}
