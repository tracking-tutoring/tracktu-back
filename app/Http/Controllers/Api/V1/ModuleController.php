<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\generateSessions;
use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Module;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
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


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            "{$this->data}" => Module::paginate()
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
            'weeks_duration' => ['required', 'numeric',],
            'group_id' => ['numeric',],
            'tutor_id' => ['numeric',],

        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module = new Module();
        $module->name = $request->name;
        $module->weeks_duration = $request->weeks_duration;
        $module->user_id = $request->user()->id;

        $module->save();

        // $module->tutors()->attach($request->tutor_id, [
        //     'assigned_by' => $request->user()->id,
        // ]);


        // $affectation = new Affectation();
        // $affectation->group_id = $request->group_id;
        // $affectation->module_id = $module->id;
        // $affectation->tutor_id = $request->tutor_id;
        // $affectation->assigned_by = $request->user()->id;

        // $affectation->save();

        // $generate_session_instance = new generateSessions($affectation, $module);
        // $generate_session_instance->generateSessionsForModule();

        return response()->json([
            "{$this->msg}" => 'Module enregistré.',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $module = Module::findOrFail($id);

        return response()->json([
            "{$this->data}" => $module
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Module $module)
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

        $module->name = $request->name;

        $module->save();

        return response()->json([
            "{$this->msg}" => 'Module mis à jour avec succès.'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $module->delete();

        return response()->json([
            "{$this->msg}" => 'Module supprimé.'
        ]);
    }
}
