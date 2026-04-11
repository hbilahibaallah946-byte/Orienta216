<?php

namespace App\Http\Controllers;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Question;
use App\Models\User;



class StudentController extends Controller
{
    public function destroy($id)
{
    $etudiant = User::findOrFail($id);
    $etudiant->delete();
    return back();
}

    public function questionnaire()
   {
       return Inertia::render('Etudiant/Questionnaire',[
       'questions'=>Question::all()
    ]);
    }

    public function chooseSpeciality()
    {
        $specialities = ['Math', 'Sciences', 'Lettres', 'Sport', 'Info', 'Technique', 'Economie et Gestion'];

        return Inertia::render('ChooseSpeciality', [
            'specialities' => $specialities
        ]);
    }

    public function storeSpeciality(Request $request)
    {
        $request->validate([
            'speciality' => 'required|string',
        ]);

        session(['speciality' => $request->speciality]);

        return redirect()->route('dashboard');
    }

    public function fillGrades()
    {
        $speciality = session('speciality');
        $subjects = [];

        switch ($speciality) {
            case 'Sciences':
                $subjects = ['Math', 'Physique', 'Sc', 'Arabe', 'Francais', 'Anglais', 'Philosophie', 'Info', 'Option', 'Sport'];
                break;

            case 'Math':
                $subjects = ['Math', 'Physique', 'Info', 'Arabe', 'Francais', 'Anglais', 'Option', 'Sport'];
                break;

            case 'Lettres':
                $subjects = ['Arabe', 'Francais', 'Anglais', 'Histoire', 'Philosophie', 'Sport'];
                break;

            case 'Sport':
                $subjects = ['Sport', 'Santé', 'Nutrition', 'Physique', 'Math'];
                break;

            case 'Info':
                $subjects = ['Programmation', 'Algorithmique', 'Math', 'Anglais', 'Info', 'Physique'];
                break;

            case 'Technique':
                $subjects = ['Technique', 'Math', 'Physique', 'Info', 'Anglais', 'Option'];
                break;

            case 'Economie et Gestion':
                $subjects = ['Economie', 'Gestion', 'Math', 'Francais', 'Anglais', 'Info'];
                break;
        }

        return Inertia::render('FillGrades', [
            'subjects' => $subjects,
            'speciality' => $speciality
        ]);
    }
   


public function dashboard() 
{
    $filieres = Filiere::all();
    $questions = Question::all();

    return Inertia::render('Etudiant/Dashboard', [
        'filieres' => $filieres,
        'questions' => $questions
    ]);
}



}
