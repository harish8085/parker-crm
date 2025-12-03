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
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->whereDoesntHave('views', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get(['id', 'title', 'message']);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $announcements,
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


