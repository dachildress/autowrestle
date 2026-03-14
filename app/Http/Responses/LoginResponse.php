<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect scorers to mat dashboard; others to intended or default home.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->isScorer()) {
            return redirect()->intended(route('mat.dashboard'));
        }

        return redirect()->intended(config('fortify.home', '/'));
    }
}
