@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')

<style>
    .activity-filter {
        display: grid;
        grid-template-columns: minmax(240px, 360px) 190px auto;
        align-items: end;
        gap: 12px;
        max-width: 780px;
        margin-bottom: 24px;
    }

    .activity-filter .filter-actions {
        display: flex;
        gap: 8px;
    }

    .activity-filter .filter-actions .btn-green {
        width: auto;
        min-height: 52px;
        white-space: nowrap;
    }

    .activity-filter .reset-button {
        min-height: 52px;
        padding: 0 20px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
    }

    @media (max-width: 760px) {
        .activity-filter {
            grid-template-columns: 1fr;
            max-width: none;
        }

        .activity-filter .filter-actions .btn-green {
            flex: 1;
        }
    }
</style>

<div class="card">

    <div class="mb-8">

        <h2 class="text-3xl font-bold">
            Activity Log
        </h2>

        <p class="text-gray-500 mt-2">Monitor user activity in real time</p>

    </div>

    <form method="GET" action="/activity-log" class="activity-filter">
        <label class="block">
            <span class="block mb-2 text-sm font-semibold text-gray-600">Activity or User</span>
            <input
                type="search"
                name="activity"
                value="{{ request('activity') }}"
                placeholder="Search..."
                class="w-full">
        </label>

        <label class="block">
            <span class="block mb-2 text-sm font-semibold text-gray-600">Date</span>
            <input
                type="date"
                name="date"
                value="{{ request('date') }}"
                class="w-full">
        </label>

        <div class="filter-actions">
            <button type="submit" class="btn-green" title="Apply filter">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>Search
            </button>
            @if(request()->hasAny(['activity', 'date']))
                <a href="/activity-log" class="reset-button bg-gray-200 hover:bg-gray-300 font-semibold" title="Reset filter">
                    <i class="fa-solid fa-rotate-left"></i>Reset
                </a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="border-b text-left text-gray-500">

                    <th class="pb-4">User</th>

                    <th class="pb-4">Activity</th>

                    <th class="pb-4">Date</th>

                    <th class="pb-4">Time</th>

                </tr>

            </thead>

            <tbody>

                @forelse($logs as $log)

                <tr class="border-b">

                    <td class="py-5">

                        {{ $log->user_name }}

                    </td>

                    <td>

                        {{ $log->activity }}

                    </td>

                    <td>

                        {{ $log->created_at->timezone(config('app.timezone'))->format('Y-m-d') }}

                    </td>

                    <td>

                        {{ $log->created_at->timezone(config('app.timezone'))->format('H:i') }}

                    </td>

                </tr>

                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-gray-500">No activity log found.</td>
                    </tr>
                @endforelse

            </tbody>

        </table>

        <div class="mt-6">

            {{ $logs->links() }}

        </div>

    </div>

</div>

@endsection
