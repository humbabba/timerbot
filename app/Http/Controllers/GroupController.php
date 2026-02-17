<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->where('id', '!=', auth()->id())
        ->limit(10)
        ->get(['id', 'name', 'email']);

        return response()->json($users);
    }
}
