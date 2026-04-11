<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialite;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class OrientationController extends Controller
{
    


    public function saveSpecialite(Request $request)
    {
        $request->validate([
            'specialite_id' => 'required|exists:specialites,id'
        ]);

        session(['specialite_id' => $request->specialite_id]);

        return response()->json(['message' => 'Spécialité enregistrée']);
    }

    public function saveNotes(Request $request)
{
    $notes = $request->all();

    $values = [
        $notes['math'],
        $notes['physique'],
        $notes['science'],
        $notes['arabe'],
        $notes['francais'],
        $notes['anglais'],
        $notes['philosophie'],
        $notes['informatique'],
        $notes['option'],
        $notes['sport']
    ];

    $validNotes = array_filter($values, function($v){
        return $v !== null;
    });

    $moyenne = array_sum($validNotes) / count($validNotes);

    $note = Note::create([
        'user_id' => Auth::id(),
        'specialite_id' => session('specialite_id'),
        'math' => $notes['math'],
        'physique' => $notes['physique'],
        'science' => $notes['science'],
        'arabe' => $notes['arabe'],
        'francais' => $notes['francais'],
        'anglais' => $notes['anglais'],
        'philosophie' => $notes['philosophie'],
        'informatique' => $notes['informatique'],
        'option' => $notes['option'],
        'sport' => $notes['sport'],
        'moyenne' => $moyenne
    ]);

    return response()->json([
        'moyenne' => $moyenne
    ]);
}
}


