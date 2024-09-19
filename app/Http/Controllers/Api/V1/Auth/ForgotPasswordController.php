<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class ForgotPasswordController extends Controller
{
    protected $msg;
    protected $validation_errors;

    public function __construct()
    {
        $this->msg = config('utilities.httpKeyResponse.message');
        $this->validation_errors = config('utilities.httpKeyResponse.validation_errors');
    }


    public function forgotPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $exist1 = User::where('email', $request->email)->exists();

        if (!$exist1) {
            return response()->json([
                "{$this->validation_errors}" => ['email' => ['This email does not exist']],
            ], 422);
        }

        $exist2 =  DB::table('password_reset_tokens')->where('email', $request->email);
        
        if ($exist2->exists()) {
            $exist2->delete();
        }

        $token = mt_rand(100000, 999999);
        
        $password_reset = DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        if ($password_reset) {
            Mail::to($request->email)->send(new ResetPassword($token));

            return response()->json([
                "{$this->msg}" => "Please check your email for a verification code"
            ]);
        }

    }


    public function checkCode(Request $request) {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $token = DB::table('password_reset_tokens')->where([
            ['token', $request->token],
        ]);

        if ($token->exists()) {
            $difference = Carbon::now()->diffInSeconds($token->first()->created_at);
            if ($difference > 3600) {
                return response()->json([
                    "{$this->validation_errors}" => ['token' => ['Code Expired']],
                ], 400);
            }

            DB::table('password_reset_tokens')->where([
                ['token', $request->token],
            ])->delete();

            return response()->json([
                "{$this->msg}" => "You can now reset your password"
            ]);
        } else {
            return response()->json([
                "{$this->validation_errors}" => ['token' => ['Invalid code']],
            ], 422);
        }
        
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns', 'exists:App\Models\User,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' =>  Hash::make($request->password),
        ]);

        $token = $user->createToken($request->email)->plainTextToken;
        
        return response()->json([
            'user' => $user,
            'token' => $token,
            "{$this->msg}" => 'Your password has been reset'
        ]);

    }

}
