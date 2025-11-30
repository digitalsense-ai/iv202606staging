<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    public function destroy(Request $request)
	{
	    // Clear session role data
	    Session::forget(['current_role', 'multi_roles']);

	    // Logout user
	    Auth::guard('web')->logout();

	    // Invalidate session
	    $request->session()->invalidate();
	    $request->session()->regenerateToken();

	    return redirect('/');
	}
}
