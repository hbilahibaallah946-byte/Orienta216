<?php
// app/Http/Controllers/StudentProfileController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\Auth;

class StudentProfileController extends Controller
{
    public function save(Request $request)
    {
        $data = $request->validate([
            'specialite' => 'required|string',
            'matieres' => 'required|array',
            'session' => 'required|string',
        ]);

        $profile = StudentProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        return response()->json(['success' => true, 'profile' => $profile]);
    }

    public function load()
    {
        $profile = StudentProfile::where('user_id', Auth::id())->first();
        return response()->json(['profile' => $profile]);
    }
}
