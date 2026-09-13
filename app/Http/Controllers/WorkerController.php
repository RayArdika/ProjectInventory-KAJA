<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Payroll;
use App\Models\Production;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $workers = User::query()
            ->where('role', 'worker')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $workers->getCollection()->each(function (User $worker) {
            $worker->has_history = Production::where('worker_name', $worker->name)->exists()
                || Payroll::where('worker_name', $worker->name)->exists();
        });

        return view('workers', compact('workers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $worker = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'worker',
            'is_active' => true,
        ]);

        $this->log("Created worker account: {$worker->name}");

        return back()->with('success', 'Worker account created successfully.');
    }

    public function update(Request $request, User $worker): RedirectResponse
    {
        $this->ensureWorker($worker);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($worker->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($worker->id),
            ],
        ]);

        $oldName = $worker->name;

        DB::transaction(function () use ($worker, $validated, $oldName) {
            $worker->update($validated);

            if ($oldName !== $validated['name']) {
                Production::where('worker_name', $oldName)
                    ->update(['worker_name' => $validated['name']]);
                Payroll::where('worker_name', $oldName)
                    ->update(['worker_name' => $validated['name']]);
            }
        });

        $this->log("Updated worker account: {$oldName} to {$worker->fresh()->name}");

        return back()->with('success', 'Worker account updated successfully.');
    }

    public function resetPassword(Request $request, User $worker): RedirectResponse
    {
        $this->ensureWorker($worker);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $worker->update(['password' => Hash::make($validated['password'])]);
        $this->removeWorkerSessions($worker);
        $this->log("Reset password for worker: {$worker->name}");

        return back()->with('success', 'Worker password reset successfully.');
    }

    public function toggleStatus(User $worker): RedirectResponse
    {
        $this->ensureWorker($worker);

        $worker->update(['is_active' => ! $worker->is_active]);

        if (! $worker->is_active) {
            $this->removeWorkerSessions($worker);
        }

        $status = $worker->is_active ? 'activated' : 'deactivated';
        $this->log(ucfirst($status) . " worker account: {$worker->name}");

        return back()->with('success', "Worker account {$status} successfully.");
    }

    public function destroy(User $worker): RedirectResponse
    {
        $this->ensureWorker($worker);

        $hasHistory = Production::where('worker_name', $worker->name)->exists()
            || Payroll::where('worker_name', $worker->name)->exists();

        if ($hasHistory) {
            return back()->withErrors([
                'worker' => 'This worker cannot be deleted because production or payroll history exists. Deactivate the account instead.',
            ]);
        }

        $workerName = $worker->name;
        $this->removeWorkerSessions($worker);
        $worker->delete();
        $this->log("Deleted worker account: {$workerName}");

        return back()->with('success', 'Worker account deleted successfully.');
    }

    private function ensureWorker(User $worker): void
    {
        abort_unless($worker->role === 'worker', 404);
    }

    private function removeWorkerSessions(User $worker): void
    {
        DB::table('sessions')->where('user_id', $worker->id)->delete();
    }

    private function log(string $activity): void
    {
        ActivityLog::create([
            'user_name' => auth()->user()->name,
            'activity' => $activity,
        ]);
    }
}
