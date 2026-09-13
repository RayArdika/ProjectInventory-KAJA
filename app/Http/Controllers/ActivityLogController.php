<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->when($request->filled('activity'), function ($query) use ($request) {
                $activity = trim($request->activity);

                $query->where(function ($query) use ($activity) {
                    $query->where('activity', 'like', "%{$activity}%")
                        ->orWhere('user_name', 'like', "%{$activity}%");
                });
            })
            ->when($request->filled('date'), fn ($query) =>
                $query->whereDate('created_at', $request->date)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('activity-log', compact('logs'));
    }
}
