<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationReadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_type === User::class, 403);
        abort_unless((string) $notification->notifiable_id === (string) $request->user()->id, 403);

        $notification->markAsRead();

        return back();
    }
}
