<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
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

    public function index() 
    {
        return response()->json([
            "{$this->data}" => auth()->user(),
        ]);
        
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(),[
            'firstname' => ['required', 'string', 'min:2'],
            'lastname' => ['required', 'string', 'min:2'],
            'phone_number' => ['required', 'numeric', 'min:9', 'unique:App\Models\User,phone_number'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->phone_number = $request->phone_number;

        $user->save();

        return response()->json([
            "{$this->msg}" => 'Mise à jour du profil réussie.'
        ]);

    }

    public function updatePassword(Request $request) {
        $validator = Validator::make($request->all(),[
            'current_password' => ['required', 'string', 'min:2'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                "{$this->validation_errors}" => ['Le mot de passe actuel est incorrect.'],
            ], 422);
        }

        // Mettre à jour le mot de passe avec la version hachée du nouveau mot de passe
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            "{$this->data}" => 'Mot de passe mis à jour avec succès.',
        ], 422);

    }
}
