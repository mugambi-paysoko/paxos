<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationUnreadSummaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request): JsonResponse
    {
        $notifications = $request->user()->unreadNotifications()->latest()->take(8)->get();

        return response()->json([
            'notifications' => $notifications->map(function ($n): array {
                $data = $n->data ?? [];

                return [
                    'id' => $n->id,
                    'title' => (string) ($data['title'] ?? 'Update'),
                    'message' => (string) ($data['message'] ?? ''),
                    'action_url' => isset($data['action_url']) ? (string) $data['action_url'] : null,
                    'action_label' => (string) ($data['action_label'] ?? 'View'),
                ];
            })->values(),
        ]);
    }
}
