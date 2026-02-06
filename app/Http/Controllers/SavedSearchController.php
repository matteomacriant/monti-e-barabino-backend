<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SavedSearch;

class SavedSearchController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->savedSearches;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'criteria' => 'required|array',
        ]);

        $search = $request->user()->savedSearches()->create($validated);
        return response()->json($search, 201);
    }

    public function destroy(Request $request, $id)
    {
        $search = $request->user()->savedSearches()->findOrFail($id);
        $search->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
