<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserController extends Controller
{

    protected $msg;
    protected $data;
    protected $validation_errors;

    public function __construct()
    {
        $this->msg = config('utilities.httpKeyResponse.message');
        $this->data = config('utilities.httpKeyResponse.data');
        $this->validation_errors = config('utilities.httpKeyResponse.validation_errors');
    }

    public function getUsers(string $userRole)
    {
        $availableUserRoles = ['tracking', 'tutor',];

        if (!in_array($userRole, $availableUserRoles)) {
            throw new InvalidArgumentException('Invalid user type(role) provided');
        }


        return response()->json([
            "{$this->data}" => User::where('role', $userRole)->paginate(),
        ]);

    }

    public function getUser(string $userRole, int $userId)
    {
        $availableUserRoles = ['tracking', 'tutor',];

        if (!in_array($userRole, $availableUserRoles)) {
            throw new InvalidArgumentException('Invalid user type(role) provided');
        }

        $user_query = User::where('id', $userId);

        if (!$user_query->exists()) {
            return response()->json([
                "{$this->msg}" => "pas d'utilisateur correspondant",
            ], 404);
        }

        $user = $user_query->first();

        return response()->json([
            "{$this->data}" => $user
        ]);

    }

    public function createUser(Request $request)
    {
        $response = Gate::inspect('create');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'min:4'],
            'lastname' => ['required', 'string', 'min:4'],
            'phone_number' => ['required', 'numeric', 'min:9', 'unique:App\Models\User,phone_number'],
            'email' => ['required',  'unique:' . User::class],
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

        return response()->json([
            "{$this->msg}" => 'Utilisateur créé avec succès.'
        ]);
    }

    public function deleteUser(int $userId)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $user_query = User::where('id', $userId);

        if (!$user_query->exists()) {
            return response()->json([
                "{$this->msg}" => "pas d'utilisateur correspondant",
            ], 404);
        }

        $user = $user_query->first();

        $user->delete();

        return response()->json([
            "{$this->msg}" => 'Utilisateur supprimé.'
        ]);
    }

}
