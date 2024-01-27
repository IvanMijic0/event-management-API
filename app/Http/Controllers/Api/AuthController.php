<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function login( Request $request ): JsonResponse
    {
        $request->validate( [
            'email' => 'required|email|string',
            'password' => 'required|string',
        ] );

        $user = User::where( 'email', $request->email )->first();

        if ( !$user || !Hash::check( $request->password, $user->password ) ) {
            throw ValidationException::withMessages( [
                'email' => 'The provided credentials are incorrect.',
            ] );
        }

        $token = $user->createToken( 'api-token' )->plainTextToken;

        return response()->json( [
            'token' => $token,
        ] );
    }

    public function logout( Request $request ): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json( [
            'message' => 'Logged out successfully.',
        ] );
    }
}
