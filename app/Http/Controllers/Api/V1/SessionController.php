<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SessionController extends Controller
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
            "{$this->data}" => Session::paginate()
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $session = Session::findOrFail($id);

        return response()->json([
            "{$this->data}" => $session
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Session $session)
    {
        $response = Gate::inspect('update');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'module_id' => ['numeric'],
            'tutor_id' => ['numeric'],
            'group_id' => ['numeric'],
            'start_time' => ['date_format:Y-m-d H:i:s'],
            'end_time' => ['date_format:Y-m-d H:i:s'],
            'marked_by' => ['numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        if ($request->has('module_id')) {
            $session->module_id = $request->module_id;
        }

        if ($request->has('tutor_id')) {
            $session->tutor_id = $request->tutor_id;
        }

        if ($request->has('group_id')) {
            $session->group_id = $request->group_id;
        }

        if ($request->has('start_time')) {
            $session->start_time = $request->start_time;
        }

        if ($request->has('end_time')) {
            $session->end_time = $request->end_time;
        }

        if ($request->has('marked_by')) {
            $session->marked_by = $request->marked_by;
        }

        if ($session->isDirty()) {
            $session->save();

            return response()->json([
                "{$this->msg}" => 'Session mise à jour avec succès.'
            ]);
        }
    }


    public function markSession(Request $request, Session $session)
    {
        $response = Gate::inspect('update');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'marked_by' => ['required', 'numeric'],
            'status' => ['required', Rule::in(['effectuee', 'non_effectuee'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $session->marked_by = $request->marked_by;
        $session->status = $request->status;

        if ($session->isDirty()) {
            $session->save();
        }


        if ($request->status === 'effectuee') {
            // ajouter 2 heures à la colonne hours_done de la table module_tutor  
            DB::table('module_tutor')
                ->where('module_id', $session->module_id)
                ->where('tutor_id', $session->tutor_id)
                ->increment('hours_done', 2);
        }

        if ($request->status === 'non_effectuee') {
            // ajouter 2 heures à la colonne hours_not_done de la table module_tutor
            DB::table('module_tutor')
                ->where('module_id', $session->module_id)
                ->where('tutor_id', $session->tutor_id)
                ->increment('hours_not_done', 2);
        }

        return response()->json([
            "{$this->msg}" => 'Séance marquée avec succès.'
        ]);
    }
}
