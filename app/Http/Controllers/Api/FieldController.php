<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Field;

class FieldController extends Controller
{
    public function index()
    {
        return response()->json(Field::with('university')->get());
    }
}
