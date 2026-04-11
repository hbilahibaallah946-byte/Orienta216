<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\University;

class UniversityController extends Controller
{
    public function index()
    {
        return response()->json(University::all());
    }
}
