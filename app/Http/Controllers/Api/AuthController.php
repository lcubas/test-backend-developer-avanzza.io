<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login The User
     *
     * @param App\Http\Requests\Auth\LoginRequest $request
     *
     * @return User
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            throw new ApiException('Bad credentials', Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $credentials['email'])->first();

        return response()->json([
            'message' => 'Successful login',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ]);
    }
}
