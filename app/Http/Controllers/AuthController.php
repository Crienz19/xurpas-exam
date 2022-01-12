<?php

namespace App\Http\Controllers;

use App\Events\NewlyCreatedUserEvent;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            // response message with status code 400 or BAD REQUEST
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

        // response message with status code 201 or CREATED
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
            
            // response message with status code 401 or UNAUTHORIZED
            return response()->json([
                'message'   =>  'Invalid Credentials'
            ], 401);
        }

        $this->clearLoginAttempts($request);
        
        $access_token = $user->createToken($request->email)->plainTextToken;
        
        // response access token with status code 201 or CREATED
        return response()->json([
            'access_token'  =>  $access_token
        ], 201);
    }

    public function username() 
    {
        return 'email';
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return response()->json([
            'message'   =>  'Account locked! Wait for ' . ceil($seconds / 60) . ' mins'
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }
}
