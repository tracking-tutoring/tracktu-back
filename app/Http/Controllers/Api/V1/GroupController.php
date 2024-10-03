<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
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

    public function getTutorgroups(?int $moduleId = null) {
        /** @var \App\Models\User $user **/
        $user = auth()->user();

        if (is_null($moduleId)) {
            $groups = $user->groups()->with('students')->get();

            return response()->json([
                "{$this->data}" => $groups,
            ]);
        }

        $groups = $user->groups()->with('students')->where('module_id', $moduleId)->get();

        return response()->json([
            "{$this->data}" => $groups,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::paginate();
        return response()->json([
            "{$this->data}" => $groups
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = Gate::inspect('create');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $group = new Group();
        $group->name = $request->name;
        $group->user_id = $request->user()->id;

        $group->save();

        return response()->json([
            "{$this->msg}" => 'Groupe créé avec succès.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $group = Group::findOrFail($id);

        return response()->json([
            "{$this->data}" => $group
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $response = Gate::inspect('update');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $group->name = $request->name;
        
        $group->save();

        return response()->json([
            "{$this->msg}" => 'Groupe mis à jour avec succès.'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $group->delete();

        return response()->json([
            "{$this->msg}" => 'Groupe supprimé.'
        ]);
    }
}
