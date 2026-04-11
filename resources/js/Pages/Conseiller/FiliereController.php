<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FiliereController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'filieres' => Filiere::all()
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Données reçues:', $request->all());
        
        try {
            // Validation simple
            $request->validate([
                'specialite' => 'required|string'
            ]);
            
            // Création
            $filiere = Filiere::create([
                'specialite' => $request->specialite,
                'code' => $request->code,
                'universite' => $request->universite,
                'type_bac' => $request->type_bac,
                'formule' => $request->formule,
                'annee' => $request->annee
            ]);
            
            Log::info('Filière créée:', $filiere->toArray());
            
            return redirect()->back()->with('success', 'Filière ajoutée avec succès.');
            
        } catch (\Exception $e) {
            Log::error('Erreur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $filiere = Filiere::findOrFail($id);
            
            $request->validate([
                'specialite' => 'required|string'
            ]);
            
            $filiere->update([
                'specialite' => $request->specialite,
                'code' => $request->code,
                'universite' => $request->universite,
                'type_bac' => $request->type_bac,
                'formule' => $request->formule,
                'annee' => $request->annee
            ]);
            
            return redirect()->back()->with('success', 'Filière modifiée avec succès.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $filiere = Filiere::findOrFail($id);
            $filiere->delete();
            
            return redirect()->back()->with('success', 'Filière supprimée avec succès.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Filière introuvable.');
        }
    }
}