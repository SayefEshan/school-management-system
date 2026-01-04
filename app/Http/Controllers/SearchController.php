<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $results = [];
        if ($request->user()->can('View User')) {
            $results['users'] = User::filter($request->all())->take(5)->get();
        }
        return response()->json($results);
    }
}
