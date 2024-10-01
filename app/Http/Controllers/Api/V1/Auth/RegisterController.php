<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;


class RegisterController extends Controller
{
    protected $validation_errors;

    public function __construct()
    {
        $this->validation_errors = config('utilities.httpKeyResponse.validation_errors');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'min:2'],
            'lastname' => ['required', 'string', 'min:2'],
            'phone_number' => ['required', 'numeric', 'min:9', 'unique:App\Models\User,phone_number'],
            'email' => ['required', 'email:rfc,dns', 'unique:'. User::class],
            'role' => ['required', Rule::in(['tracking', 'tutor'])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $user = new User();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make($request->password);

        $user->save();

        $token = $user->createToken($request->email)->plainTextToken;

        return response(compact('user', 'token'), 200);
    }
}
