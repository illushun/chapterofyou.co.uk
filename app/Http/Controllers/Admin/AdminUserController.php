<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'created_at', 'is_admin')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return Inertia::render('admin/user/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Display the specified user and their details (e.g., addresses, orders).
     */
    public function show(User $user)
    {
        $user->load(['addresses', 'orders' => function($query) {
            $query->select('id', 'grand_total', 'status', 'created_at')->limit(5);
        }]);

        return Inertia::render('admin/user/Show', [
            'user' => $user,
        ]);
    }
}
