<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FiliereController extends Controller
{
    public function index()
    {
        $filieres = Filiere::orderBy('specialite')->get();

        return Inertia::render('Admin/Filieres/Index', [
            'filieres' => $filieres,
        ]);
    }
// Dans app/Http/Controllers/Admin/FiliereController.php

public function store(Request $request)
{
    $request->validate([
        'specialite' => 'required|string|max:255',
        'code' => 'nullable|string|max:50',
        'universite' => 'nullable|string|max:255',
        'type_bac' => 'nullable|string|max:100',
        'formule' => 'nullable|string|max:255',
        'annee' => 'nullable|integer',
        'criteres' => 'nullable|string',
        'description' => 'nullable|string',
    ]);

    Filiere::create([
        'specialite' => $request->specialite,
        'code' => $request->code,
        'universite' => $request->universite,
        'type_bac' => $request->type_bac,
        'formule' => $request->formule,
        'annee' => $request->annee,
        'description' => $request->description,
        'criteres' => $request->criteres
            ? array_values(array_filter(array_map('trim', explode(',', $request->criteres))))
            : null,
    ]);

    return redirect()->back()->with('success', 'Filière ajoutée avec succès.');
}

    public function update(Request $request, Filiere $filiere)
    {
        $request->validate([
            'specialite'  => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'universite'  => 'nullable|string|max:255',
            'type_bac'    => 'nullable|string|max:100',
            'formule'     => 'nullable|string|max:255',
            'annee'       => 'nullable|integer',
            'criteres'    => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $filiere->update([
            'specialite'  => $request->specialite,
            'code'        => $request->code,
            'universite'  => $request->universite,
            'type_bac'    => $request->type_bac,
            'formule'     => $request->formule,
            'annee'       => $request->annee,
            'description' => $request->description,
            'criteres'    => $request->criteres
                ? array_values(array_filter(array_map(
                    fn($s) => strtolower(trim($s)),
                    explode(',', $request->criteres)
                  )))
                : null,
        ]);

        return redirect()->back()->with('success', 'Filière mise à jour.');
    }

    public function destroy($id)
    {
        Filiere::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Filière supprimée.');
    }
}