<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Mail\WelcomeUserMail;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($data['search'] ?? '');
        $staffMembers = User::query()->when($search, fn ($query) => $query
            ->where(fn ($inner) => $inner->where('username', 'like', '%'.$search.'%')
                ->orWhere('full_name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')))
            ->orderBy('id')->paginate(10)->withQueryString();
        $searchSuggestions = User::query()->orderBy('full_name')->limit(100)->get(['full_name', 'username', 'email'])
            ->flatMap(fn (User $user) => [$user->full_name, $user->username, $user->email])->unique()->values();

        return view('staff.index', compact('staffMembers', 'search', 'searchSuggestions'));
    }

    public function create(): View
    {
        return view('staff.create');
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['username', 'email', 'full_name', 'role', 'password']);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->images->store($request->file('avatar'), 'avatars', 600, 600);
        }
        try {
            $staff = DB::transaction(function () use ($request, $data) {
                $staff = User::create($data);
                ActivityLog::recordCreation($request->user(), 'staff', $staff->public_id, $staff->full_name, $this->auditValues($staff));

                return $staff;
            });
        } catch (\Throwable $exception) {
            $this->images->delete($data['avatar'] ?? null);
            throw $exception;
        }

        try {
            Mail::to($staff->email)->send(new WelcomeUserMail($staff));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('staff.show', $staff)
                ->with('success', 'Staff account created successfully.')
                ->with('warning', 'The welcome email could not be sent. Check the mail settings and try the password-reset email if needed.');
        }

        return redirect()->route('staff.show', $staff)->with('success', 'Staff account created and the welcome email was sent.');
    }

    public function show(User $staff): View
    {
        $sales = $staff->sales()->with(['product', 'customer'])->latest('created_at')->paginate(5);

        return view('staff.show', compact('staff', 'sales'));
    }

    public function edit(User $staff): View
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(StaffRequest $request, User $staff): RedirectResponse
    {
        $newAvatar = $request->hasFile('avatar') ? $this->images->store($request->file('avatar'), 'avatars', 600, 600) : null;
        $before = $this->auditValues($staff);
        $data = $request->safe()->only(['username', 'email', 'full_name', 'role']);
        $passwordChanged = filled($request->validated('password'));
        if ($passwordChanged) {
            $data['password'] = $request->validated('password');
        }
        if ($newAvatar) {
            $data['avatar'] = $newAvatar;
        }
        $oldAvatar = $staff->avatar;
        try {
            DB::transaction(function () use ($request, $staff, $data, $before, $passwordChanged) {
                $staffMembers = User::query()->lockForUpdate()->get(['id', 'role']);
                /** @var User $locked */
                $locked = $staffMembers->firstWhere('id', $staff->id) ?? abort(404);
                if ($locked->role === 'admin' && $data['role'] !== 'admin' && $staffMembers->where('role', 'admin')->count() <= 1) {
                    throw ValidationException::withMessages(['role' => 'At least one administrator account must remain.']);
                }
                $locked->update($data);
                $locked->refresh();
                $after = $this->auditValues($locked);

                ActivityLog::recordEdit(
                    $request->user(),
                    'staff',
                    $locked->public_id,
                    $locked->full_name,
                    $before,
                    $after,
                    $passwordChanged ? ['password' => ['from' => 'Protected', 'to' => 'Updated securely']] : [],
                );
            });
        } catch (\Throwable $exception) {
            $this->images->delete($newAvatar);
            throw $exception;
        }
        if ($newAvatar) {
            $this->images->delete($oldAvatar);
        }

        return redirect()->route('staff.show', $staff)->with('success', 'Staff account updated successfully.');
    }

    public function destroy(Request $request, User $staff): RedirectResponse
    {
        if ($request->user()->is($staff)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            $avatar = DB::transaction(function () use ($request, $staff) {
                User::query()->lockForUpdate()->get(['id']);
                if (User::count() <= 1) {
                    throw new \LogicException('The last staff account cannot be deleted.');
                }
                if ($staff->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
                    throw new \LogicException('The last administrator account cannot be deleted.');
                }
                if ($staff->sales()->exists()) {
                    throw new \DomainException('This staff account is used in sales history and cannot be deleted.');
                }
                $avatar = $staff->avatar;
                $publicId = $staff->public_id;
                $name = $staff->full_name;
                $staff->delete();
                ActivityLog::recordDeletion($request->user(), 'staff', $publicId, $name);

                return $avatar;
            });
            $this->images->delete($avatar);
        } catch (\LogicException|\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (QueryException) {
            return back()->with('error', 'This staff account became referenced by a sale and cannot be deleted.');
        }

        return redirect()->route('staff.index')->with('success', 'Staff account deleted successfully.');
    }

    private function auditValues(User $staff): array
    {
        return [
            'username' => $staff->username,
            'email' => $staff->email,
            'full_name' => $staff->full_name,
            'role' => $staff->role,
            'avatar' => $staff->avatar ? 'Custom avatar' : 'Default avatar',
        ];
    }
}
