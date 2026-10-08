<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminRepositoryInterface $admins)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users'   => $this->admins->paginate(20, $request->only(['search', 'role'])),
            'roles'   => AdminRole::options(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new Admin(['is_active' => true]), 'roles' => AdminRole::options()]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $this->admins->create([
            ...$request->safe()->except('password_confirmation'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Team member added.');
    }

    public function edit(Admin $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => AdminRole::options()]);
    }

    public function update(StoreAdminUserRequest $request, Admin $user): RedirectResponse
    {
        $data = collect($request->safe()->except('password_confirmation'))
            ->reject(fn ($value, $key) => $key === 'password' && blank($value))
            ->all();

        $user->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.users.index')->with('status', 'Team member updated.');
    }

    public function destroy(Request $request, Admin $user): RedirectResponse
    {
        if ($user->id === $request->user('admin')->id) {
            return back()->withErrors(['user' => 'You cannot remove your own account.']);
        }

        $user->delete();

        return back()->with('status', 'Team member removed.');
    }
}
