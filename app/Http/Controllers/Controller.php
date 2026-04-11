<?php

namespace App\Http\Controllers;

abstract class Controller
{
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
        // Ajouter les autres spécialités ici
    }

    return view('fill-grades', compact('subjects'));
}


}
