<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Reponse;
use App\Models\User;
use Inertia\Inertia;

class EvaluationController extends Controller
{
    public function index()
{
return Inertia::render('Admin/Evaluation',[
'questions'=>Question::all(),
'etudiants'=>User::where('role','etudiant')->get()
]);
}

public function store(Request $request)
{
    

$request->validate([
        'question' => 'required'
    ]);

    Question::create([
        'question' => $request->question
    ]);

    return back();

}
}

