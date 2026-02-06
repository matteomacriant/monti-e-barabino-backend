<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Practice;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'saved_searches' => $user->savedSearches()->take(5)->get(),
            'favorite_practices' => $user->favoritePractices()->take(5)->get(),
            'recent_practices' => Practice::orderBy('created_at', 'desc')->take(5)->get(), // Logic for "recent" might need a visit log, but for now just latest created
        ]);
    }
}
