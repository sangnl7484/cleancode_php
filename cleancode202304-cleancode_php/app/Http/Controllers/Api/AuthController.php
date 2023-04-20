<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = DB::select("select * from users where email = '$request->email' limit 1");
        if (isset($user[0])) {
            $user = User::find($user[0]->id);
            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            } else {
                $token = $user->createToken('main')->plainTextToken;
                return response([
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => (new DateTime($user->created_at))->format('Y-m-d H:i:s'),
                    ],
                    'token' => $token
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    }
    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response('', 204);
    }
    public function getUser(Request $request)
    {
        $user = $request->user();
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => (new DateTime($user->created_at))->format('Y-m-d H:i:s'),
        ];
        return response($data);
    }
}
