<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->with('merchantProfile')->latest()->paginate(20);
        return response()->json($users);
    }

    public function suspend($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Tidak dapat suspend admin'], 403);
        }
        $user->update(['status' => 'suspended']);
        return response()->json(['message' => "User {$user->name} telah di-suspend"]);
    }

    public function mute($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => 'muted']);
        return response()->json(['message' => "User {$user->name} telah di-mute"]);
    }

    public function activate($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => 'active']);
        return response()->json(['message' => "User {$user->name} telah diaktifkan kembali"]);
    }
}
