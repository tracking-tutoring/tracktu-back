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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'group_id' => ['required', 'numeric'],
            'module_id' => ['required', 'numeric'],
            'tutor_id' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module_query = Module::where('id', $request->module_id);

        $group_existence = Group::where('id', $request->group_id)->exists();
        $module_existence = $module_query->exists();
        $tutor_existence = User::where('id', $request->tutor_id)->exists();

        if (!$group_existence) {
            return response()->json([
                "{$this->msg}" => "pas de Groupe correspondant",
            ], 404);
        }

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

        $module_query->first()->tutors()->attach($request->tutor_id, [
            'assigned_by' => $request->user()->id,
        ]);

        $affectation = new Affectation();
        $affectation->group_id = $request->group_id;
        $affectation->module_id = $request->module_id;
        $affectation->tutor_id = $request->tutor_id;
        $affectation->assigned_by = $request->user()->id;

        $affectation->save();

        $generate_session_instance = new generateSessions($affectation, $module_query->first());
        $generate_session_instance->generateSessionsForModule();

        return response()->json([
            "{$this->msg}" => 'Affectation réussie.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Affectation $affectation)
    {
        $response = Gate::inspect('update');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'numeric'],
            'module_id' => ['required', 'numeric'],
            'tutor_id' => ['required', 'numeric'],
            'old_tutor_id' => ['numeric', 'nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module_query = Module::where('id', $request->module_id);

        $group_existence = Group::where('id', $request->group_id)->exists();
        $module_existence = $module_query->exists();
        $tutor_existence = User::where('id', $request->tutor_id)->exists();

        if (!$group_existence) {
            return response()->json([
                "{$this->msg}" => "pas de Groupe correspondant",
            ], 404);
        }

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

        $module = $module_query->first();

        $affectation->group_id = $request->group_id;
        $affectation->module_id = $request->module_id;
        $affectation->tutor_id = $request->tutor_id;
        $affectation->assigned_by = $request->user()->id;

        if ($affectation->isDirty()) {
            $affectation->save();
        }

        /*  si le "old_tutor_id" est différent de "tutor_id", 
            alors on désire changer de remplacer l'ancien tuteur par le nouveau tuteur Pour le module
        */
        if ($request->has('old_tutor_id') && !is_null($request->old_tutor_id) && $request->old_tutor_id !== $request->tutor_id) {
            $module->tutors()->detach($request->old_tutor_id);

            $module->tutors()->attach($request->tutor_id, [
                'assigned_by' => $request->user()->id,
            ]);

            // récupérer les séances de l'ancien tuteur
            $sessions = Session::where('group_id', $request->group_id)
                ->where('module_id', $request->module_id)
                ->where('tutor_id', $request->old_tutor_id)
                ->get();

            // chaque séance de l'ancien tuteur est remplacé par celui du nouveau tuteur
            foreach ($sessions as $session) {
                $session->group_id = $request->group_id;
                $session->module_id = $request->module_id;
                $session->tutor_id = $request->tutor_id;
                $session->save();
            }
        }

        // récupérer les séances
        $sessions = Session::where('group_id', $request->group_id)
            ->where('module_id', $request->module_id)
            ->where('tutor_id', $request->tutor_id)
            ->get();

        // mettre à jour chaque séance
        foreach ($sessions as $session) {
            $session->group_id = $request->group_id;
            $session->module_id = $request->module_id;
            $session->tutor_id = $request->tutor_id;
            $session->save();
        }



        return response()->json([
            "{$this->msg}" => 'Affectation réussie.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
