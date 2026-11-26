<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($data['search'] ?? '');
        $staffMembers = User::query()->when($search, fn ($query) => $query
            ->where(fn ($inner) => $inner->where('username', 'like', '%'.$search.'%')->orWhere('full_name', 'like', '%'.$search.'%')))
            ->orderBy('id')->paginate(10)->withQueryString();

        return view('staff.index', compact('staffMembers', 'search'));
    }

    public function create(): View
    {
        return view('staff.create');
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['username', 'full_name', 'password']);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->images->store($request->file('avatar'), 'avatars', 600, 600);
        }
        $staff = User::create($data);

        return redirect()->route('staff.show', $staff)->with('success', 'Staff account created successfully.');
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
        $data = $request->safe()->only(['username', 'full_name']);
        if (filled($request->validated('password'))) {
            $data['password'] = $request->validated('password');
        }
        if ($newAvatar) {
            $data['avatar'] = $newAvatar;
        }
        $oldAvatar = $staff->avatar;
        try {
            $staff->update($data);
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
            $avatar = DB::transaction(function () use ($staff) {
                User::query()->lockForUpdate()->get(['id']);
                if (User::count() <= 1) {
                    throw new \LogicException('The last staff account cannot be deleted.');
                }
                if ($staff->sales()->exists()) {
                    throw new \DomainException('This staff account is used in sales history and cannot be deleted.');
                }
                $avatar = $staff->avatar;
                $staff->delete();

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
}
