<?php

namespace App\Http\Controllers\Report\Concerns;

use App\Models\ChannelUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait InteractsWithReportData
{
    protected function authenticatedUser(): User
    {
        return Auth::user();
    }

    protected function hasAnyRole(User $user, array $roleNames): bool
    {
        return $user->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Return null for full-access users, otherwise return visible user IDs.
     */
    protected function visibleUserIds(User $user): ?array
    {
        if ($this->hasAnyRole($user, ['Admin', 'Maker', 'Checker'])) {
            return null;
        }

        if ($this->hasAnyRole($user, ['Channel'])) {
            $associateIds = ChannelUser::where('channel_id', $user->id)
                ->pluck('associate_channel_id')
                ->toArray();

            return collect([$user->id])
                ->merge($associateIds)
                ->unique()
                ->values()
                ->all();
        }

        return [$user->id];
    }

    protected function applyUserScope(Builder $query, string $column, ?array $visibleUserIds): Builder
    {
        if (is_array($visibleUserIds)) {
            $query->whereIn($column, $visibleUserIds);
        }

        return $query;
    }

    protected function applyDateRangeFilter(Builder $query, string $column, ?string $fromDate, ?string $toDate): Builder
    {
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

        if ($from) {
            $query->where($column, '>=', $from);
        }

        if ($to) {
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    protected function reportUsersForFilter(?array $visibleUserIds)
    {
        $query = User::query()->select('id', 'first_name', 'last_name', 'Emp_Id');

        if (is_array($visibleUserIds)) {
            $query->whereIn('id', $visibleUserIds);
        }

        return $query->orderBy('first_name')->get();
    }

    protected function csvDownloadResponse(string $fileName, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
