<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'customer')->latest()->paginate(10);

        return view('pages.admin.user.index', compact('users'));
    }
}
