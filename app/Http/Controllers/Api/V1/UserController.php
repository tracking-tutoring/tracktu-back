<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

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

    public function getTutors()
    {
        return response()->json([
            "{$this->data}" => User::where('role', 'tutor')->paginate(),
        ]);
    }

    public function getTutor(int $id)
    {

        $user = User::findOrFail($id);

        if ($user->role == 'tracking') {
            return response()->json([
                "{$this->msg}" => 'interdit, cet utilisateur n\'est pas un tuteur.'
            ], 403);
        }

        return response()->json([
            "{$this->data}" => $user
        ]);
    }

    public function createTutor(Request $request)
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
            'email' => ['required', 'email:rfc,dns', 'unique:' . User::class],
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
        $user->role = 'tutor';
        $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            "{$this->msg}" => 'Tuteur créé avec succès.'
        ]);
    }

    public function deleteTutor(User $user)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        if ($user->role == 'tracking') {
            return response()->json([
                "{$this->msg}" => 'interdit, cet utilisateur n\'est pas un tuteur.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            "{$this->msg}" => 'Tuteur supprimé.'
        ]);
    }
}
