<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function PHPUnit\Framework\isNull;

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

    public function showtutorSessions(int $tutorId, ?int $moduleId = null) 
    {
     
        $user = User::findOrFail($tutorId);

        if ($user->role == 'tracking') {
            return response()->json([
                "{$this->msg}" => 'interdit, cet utilisateur n\'est pas un tuteur.'
            ], 403);
        }

        if (is_null($moduleId)) {
            $sessions = $user->sessions;
            
            return response()->json([
                "{$this->data}" => $sessions
            ]);
        }

        // $module = Module::findOrFail($moduleId);

        $sessions = $user->sessions()->where('module_id', $moduleId)->get();

        return response()->json([
            "{$this->data}" => $sessions
        ]);        

    }

    public function getTutorSessions(?int $moduleId = null)
    {
         /** @var \App\Models\User $tutor **/
        $tutor = auth()->user();
        
        if (is_null($moduleId)) {
            $sessions = $tutor->sessions()->with('module')->get();
            return response()->json([
                "{$this->data}" => $sessions
            ]);
        }

        $sessions = $tutor->sessions()->with('module')->where('module_id', $moduleId)->get();

        return response()->json([
            "{$this->data}" => $sessions
        ]);

    }
}
