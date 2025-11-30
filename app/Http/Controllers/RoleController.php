<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\Role;

class RoleController extends Controller
{
    public function selectRole()
    {
        $roles = session('multi_roles');

        if (!$roles) {
            return redirect('/');
        }

        return view('auth.select-role', compact('roles'));
    }

    public function setRole(Request $request)
    {
    	$request->validate([
	        'role_id' => 'required|exists:roles,id',
	    ]);

        $role = Role::findOrFail($request->input('role_id'));
        session(['current_role' => $role]);

        // Remove multi_roles from session as it's no longer needed
    	session()->forget('multi_roles');

        return redirect('/');
    }
}

