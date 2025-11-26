<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $res = response()->json(
            Company::select('id', 'name')->orderBy('name')->get()
        );
        return $res;
    }
}
