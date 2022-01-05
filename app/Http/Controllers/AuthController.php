<?php

namespace App\Http\Controllers;

use App\Events\NewlyCreatedUserEvent;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    use ThrottlesLogins;

    public $maxAttempts = 5;
    public $decayMinutes = 5;

    public function register(RegisterRequest $request)
    {
        // validating request fields inside RegisterRequest
        $request->validated();

        // checking if email is already exist
        $isAlreadyTaken = User::where('email', $request->email)->count();

        if ($isAlreadyTaken) {
            return response()->json([
                'message'  => 'Email already taken']
            , 400);
        }

        $user = User::create([
            'email'     =>  $request->email,
            'password'  =>  Hash::make($request->password)
        ]);
        
        // sending a welcome email when user is created
        event(new NewlyCreatedUserEvent($user));

        return response()->json(
            ['message' => 'User successfully registered.']
        , 201);
    }

    public function login(LoginRequest $request)
    {
        $request->validated();

        $user = User::where('email', $request->email)->first();

        $isInvalidCredentials = !$user || !Hash::check($request->password, $user->password);

        if($this->hasTooManyLoginAttempts($request))
        {
            return $this->sendLockoutResponse($request);
        }

        if($isInvalidCredentials) {
            $this->incrementLoginAttempts($request);

            return response()->json([
                'message'   =>  'Invalid Credentials'
            ], 401);
        }

        $this->clearLoginAttempts($request);
        
        $access_token = $user->createToken($request->email)->plainTextToken;

        return response()->json([
            'access_token'  =>  $access_token
        ], 201);
    }

    public function username() 
    {
        return 'email';
    }
}
