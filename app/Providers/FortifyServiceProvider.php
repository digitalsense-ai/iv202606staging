<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse;
//use Laravel\Fortify\Contracts\LogoutResponse;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use \App\Classes\CommonClass;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        Fortify::ignoreRoutes();

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                /**
                 * @var User $user
                 */
                $user = $request->user();
                $commonClass = new CommonClass();
                $authUser = session('current_role') ? $commonClass->getAuthUser() : $commonClass->getAuthUser($user->id, true);  

                //$commonClass->addLog($authUser, 'user-logged-in');
             
                //return redirect('/');                

                // Check user roles
                $roles = $user->roles;

                if ($roles->count() > 1) {
                    // Save roles in session
                    session(['multi_roles' => $roles]);

                    // Redirect to role selection page
                    return redirect()->route('select.role');
                }
                else
                    $commonClass->addLog($authUser, 'user-logged-in');
                // Default: Single role
                session(['current_role' => $roles->first()]);

                return redirect()->intended('/');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::group([
           'namespace' => 'Laravel\Fortify\Http\Controllers',
           'domain' => config('fortify.domain', null),
           'prefix' => config('fortify.prefix'),
        ], function () {
            $this->loadRoutesFrom(base_path('routes/fortify.php'));
        }); // this closure


        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->with('dvuser')->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if ($user->dvuser->is_deleted == 0) {
                    return $user;
                }

                throw ValidationException::withMessages([
                    Fortify::username() => __('Your account is inactive. Please contact support.'),
                ]);
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
