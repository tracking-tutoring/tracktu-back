<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\generateSessions;
use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Group;
use App\Models\Module;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\table;

class AffectationController extends Controller
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
    

    public function assignTutor(Request $request)
    {
        $response = Gate::inspect('create');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'module_id' => ['required', 'numeric'],
            'tutor_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module_query = Module::where('id', $request->module_id);

        $module_existence = $module_query->exists();
        $tutor_existence = User::where('id', $request->tutor_id)->exists();

        if (!$module_existence) {
            return response()->json([
                "{$this->msg}" => "pas de Module correspondant",
            ], 404);
        }

        if (!$tutor_existence) {
            return response()->json([
                "{$this->msg}" => "pas de Tuteur correspondant",
            ], 404);
        }

        $exists = DB::table('module_tutor')
            ->where('tutor_id', $request->tutor_id)
            ->where('module_id', $request->module_id)
            ->exists();

        if ($exists) {
            return response()->json([
                "{$this->msg}" => "une assignation entre ce module et ce tuteur existe déjà",
            ], 400);
        }

        $module_query->first()->tutors()->attach($request->tutor_id, [
            'assigned_by' => $request->user()->id,
        ]);

        $generate_session_instance = new generateSessions($request->tutor_id, $module_query->first());
        $generate_session_instance->generateSessionsForModule();

        return response()->json([
            "{$this->msg}" => 'Affectation réussie.',
        ]);
    }

    public function deleteTutorAssignment(Request $request)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'tutor_id' => ['required', 'numeric'],
            'module_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        DB::table('module_tutor')
            ->where('tutor_id', $request->tutor_id)
            ->where('module_id', $request->module_id)
            ->delete();

        Session::where('tutor_id', $request->tutor_id)
            ->where('module_id', $request->module_id)
            ->delete();

        return response()->json([
            "{$this->msg}" => 'Affectation supprimée.'
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
            'module_id' => ['required', 'numeric'],
            'tutor_id' => ['required', 'numeric'],
            'groups' => ['array'],
            'groups.*' => ['numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module_query = Module::where('id', $request->module_id);

        $module_existence = $module_query->exists();
        $tutor_existence = User::where('id', $request->tutor_id)->exists();


        if (!$module_existence) {
            return response()->json([
                "{$this->msg}" => "pas de Module correspondant",
            ], 404);
        }

        if (!$tutor_existence) {
            return response()->json([
                "{$this->msg}" => "pas de Tuteur correspondant",
            ], 404);
        }



        foreach ($request->groups as  $key => $group_id) {
            $affectation = new Affectation();
            $affectation->group_id = $group_id;
            $affectation->module_id = $request->module_id;
            $affectation->tutor_id = $request->tutor_id;
            $affectation->assigned_by = $request->user()->id;
            $affectation->save();
        }


        return response()->json([
            "{$this->msg}" => 'Affectation réussie.',
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'affectations' => ['required', 'array'],
            'affectations.*' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        foreach ($request->affectations as $affectation) {
            DB::table('affectations')
                ->where('id', $affectation)
                ->delete();
        }

        return response()->json([
            "{$this->msg}" => 'Affectation supprimée.'
        ]);
    }
}
