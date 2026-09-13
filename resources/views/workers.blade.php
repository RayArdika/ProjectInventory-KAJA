@extends('layouts.app')

@section('title', 'Manage Workers')

@section('content')
<style>
    .worker-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .worker-field label{display:block;margin-bottom:8px;font-size:13px;font-weight:700;color:#334155}
    .worker-field input{width:100%;min-height:50px;border:0;border-radius:14px;background:#f1f5f9;padding:12px 14px}
    .worker-actions{display:flex;min-width:290px;flex-wrap:wrap;justify-content:flex-end;gap:8px}
    .worker-action{display:inline-flex;min-height:40px;align-items:center;justify-content:center;border:0;border-radius:10px;padding:0 12px;color:#fff;font-size:12px;font-weight:700;white-space:nowrap;transition:transform .2s ease,opacity .2s ease}
    .worker-action-reset{background:#d97706}
    .worker-action-reset:hover{background:#b45309}
    .worker-action:hover{transform:translateY(-1px)}
    .worker-action:disabled{cursor:not-allowed;opacity:.35;transform:none}
    .worker-modal{position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;padding:18px;background:rgba(15,23,42,.55)}
    .worker-modal.hidden{display:none}
    .worker-modal-panel{width:min(100%,520px);border-radius:18px;background:#fff;padding:24px;box-shadow:0 24px 70px rgba(15,23,42,.25)}
    .worker-modal-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}
    .worker-modal-close{display:inline-flex;width:40px;height:40px;align-items:center;justify-content:center;border:0;border-radius:10px;background:#f1f5f9;color:#334155}
    .worker-search-form{display:flex;width:100%;gap:10px}
    .worker-search-input{width:100%;min-width:0}
    .worker-search-button{flex:0 0 auto}
    @media(max-width:700px){
        .worker-form-grid{grid-template-columns:minmax(0,1fr)}
        .worker-search-form{display:grid;grid-template-columns:minmax(0,1fr);gap:12px}
        .worker-search-input{width:100% !important;min-height:58px}
        .worker-search-button{width:100%;min-height:58px}
    }
</style>

<div class="card mb-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Add Worker</h2>
        <p class="mt-1 text-sm text-gray-500">Create an account that can access Production and personal Payroll.</p>
    </div>

    <form method="POST" action="{{ route('workers.store') }}" class="worker-form-grid">
        @csrf
        <div class="worker-field">
            <label for="worker-name">Full Name</label>
            <input id="worker-name" type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="worker-field">
            <label for="worker-email">Email</label>
            <input id="worker-email" type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="worker-field">
            <label for="worker-password">Password</label>
            <input id="worker-password" type="password" name="password" required>
        </div>
        <div class="worker-field">
            <label for="worker-password-confirmation">Confirm Password</label>
            <input id="worker-password-confirmation" type="password" name="password_confirmation" required>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-green w-full">
                Add Worker
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Worker Accounts</h2>
            <p class="mt-1 text-sm text-gray-500">Edit access, reset passwords, or deactivate accounts without removing history.</p>
        </div>
        <form method="GET" action="{{ route('workers.index') }}" class="worker-search-form md:w-auto">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search worker..." class="worker-search-input md:w-64">
            <button type="submit" class="btn-green px-5 worker-search-button" title="Search workers">
                Search
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px]">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workers as $worker)
                    <tr>
                        <td class="font-semibold">{{ $worker->name }}</td>
                        <td>{{ $worker->email }}</td>
                        <td>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $worker->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $worker->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $worker->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="worker-actions">
                                <button type="button" class="worker-action bg-blue-500 js-edit-worker"
                                    data-id="{{ $worker->id }}" data-name="{{ $worker->name }}" data-email="{{ $worker->email }}"
                                    title="Edit account" aria-label="Edit {{ $worker->name }}">
                                    Edit
                                </button>
                                <button type="button" class="worker-action worker-action-reset js-reset-worker"
                                    data-id="{{ $worker->id }}" data-name="{{ $worker->name }}"
                                    title="Reset password" aria-label="Reset password for {{ $worker->name }}">
                                    Reset
                                </button>
                                <form method="POST" action="{{ route('workers.toggle-status', $worker) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="worker-action {{ $worker->is_active ? 'bg-gray-500' : 'bg-green-600' }}"
                                        title="{{ $worker->is_active ? 'Deactivate account' : 'Activate account' }}"
                                        aria-label="{{ $worker->is_active ? 'Deactivate' : 'Activate' }} {{ $worker->name }}">
                                        {{ $worker->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('workers.destroy', $worker) }}" onsubmit="return confirm('Delete this worker account permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="worker-action bg-red-500" @disabled($worker->has_history)
                                        title="{{ $worker->has_history ? 'Cannot delete: worker history exists' : 'Delete account' }}"
                                        aria-label="Delete {{ $worker->name }}">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-gray-500">No worker accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $workers->links() }}</div>
</div>

<div id="editWorkerModal" class="worker-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editWorkerTitle">
    <div class="worker-modal-panel">
        <div class="worker-modal-header">
            <div><h3 id="editWorkerTitle" class="text-xl font-bold">Edit Worker</h3><p class="text-sm text-gray-500">Update the worker's account details.</p></div>
            <button type="button" class="worker-modal-close js-close-modal" title="Close" aria-label="Close">X</button>
        </div>
        <form id="editWorkerForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="worker-field"><label for="edit-worker-name">Full Name</label><input id="edit-worker-name" type="text" name="name" required></div>
            <div class="worker-field"><label for="edit-worker-email">Email</label><input id="edit-worker-email" type="email" name="email" required></div>
            <button type="submit" class="btn-green w-full">Save Changes</button>
        </form>
    </div>
</div>

<div id="resetWorkerModal" class="worker-modal hidden" role="dialog" aria-modal="true" aria-labelledby="resetWorkerTitle">
    <div class="worker-modal-panel">
        <div class="worker-modal-header">
            <div><h3 id="resetWorkerTitle" class="text-xl font-bold">Reset Password</h3><p id="resetWorkerDescription" class="text-sm text-gray-500"></p></div>
            <button type="button" class="worker-modal-close js-close-modal" title="Close" aria-label="Close">X</button>
        </div>
        <form id="resetWorkerForm" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div class="worker-field"><label for="reset-worker-password">New Temporary Password</label><input id="reset-worker-password" type="password" name="password" required></div>
            <div class="worker-field"><label for="reset-worker-password-confirmation">Confirm Password</label><input id="reset-worker-password-confirmation" type="password" name="password_confirmation" required></div>
            <button type="submit" class="btn-green w-full">Reset Password</button>
        </form>
    </div>
</div>

<script>
const workerBaseUrl = @json(url('/workers'));
const editModal = document.getElementById('editWorkerModal');
const resetModal = document.getElementById('resetWorkerModal');

function openWorkerModal(modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    modal.querySelector('input')?.focus();
}

function closeWorkerModals() {
    document.querySelectorAll('.worker-modal').forEach(modal => modal.classList.add('hidden'));
    document.body.style.overflow = '';
}

document.querySelectorAll('.js-edit-worker').forEach(button => button.addEventListener('click', () => {
    document.getElementById('editWorkerForm').action = `${workerBaseUrl}/${button.dataset.id}`;
    document.getElementById('edit-worker-name').value = button.dataset.name;
    document.getElementById('edit-worker-email').value = button.dataset.email;
    openWorkerModal(editModal);
}));

document.querySelectorAll('.js-reset-worker').forEach(button => button.addEventListener('click', () => {
    document.getElementById('resetWorkerForm').action = `${workerBaseUrl}/${button.dataset.id}/reset-password`;
    document.getElementById('resetWorkerDescription').textContent = `Set a temporary password for ${button.dataset.name}.`;
    document.getElementById('resetWorkerForm').reset();
    openWorkerModal(resetModal);
}));

document.querySelectorAll('.js-close-modal').forEach(button => button.addEventListener('click', closeWorkerModals));
document.querySelectorAll('.worker-modal').forEach(modal => modal.addEventListener('click', event => {
    if (event.target === modal) closeWorkerModals();
}));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeWorkerModals();
});
</script>
@endsection
