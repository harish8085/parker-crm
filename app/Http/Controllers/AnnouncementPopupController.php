<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementView;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnnouncementPopupController extends Controller
{
    public function active(): JsonResponse
    {
        $user = Auth::user();

        $now = Carbon::now();

        $announcements = Announcement::where('is_active', true)
             
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->whereDoesntHave('views', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('created_by', '!=', $user->id)
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'message', 'message_attachment']);

        // Format announcements with attachments
        $formattedAnnouncements = $announcements->map(function ($announcement) {
            $formatted = [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'message' => $announcement->message,
                'attachments' => $announcement->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'path' => $attachment->attachment,
                        'name' => basename($attachment->attachment),
                        'url' => asset($attachment->attachment),
                    ];
                }),
            ];

            // Add message_attachment if exists
            if ($announcement->message_attachment) {
                $formatted['message_attachment'] = [
                    'path' => $announcement->message_attachment,
                    'name' => basename($announcement->message_attachment),
                    'url' => asset($announcement->message_attachment),
                ];
            }

            return $formatted;
        });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $formattedAnnouncements->values()->toArray(),
        ]);
    }

    public function acknowledge(int $id): JsonResponse
    {
        $user = Auth::user();

        $announcement = Announcement::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        AnnouncementView::firstOrCreate(
            [
                'announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ],
            [
                'viewed_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Acknowledged',
        ]);
    }
}


