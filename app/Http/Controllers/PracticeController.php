<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Practice;

class PracticeController extends Controller
{
    protected $practiceService;

    public function __construct(\App\Services\PracticeService $practiceService)
    {
        $this->practiceService = $practiceService;
    }

    public function index(Request $request)
    {
        $query = Practice::with('user')
            ->withExists([
                'favoritedBy as is_favorite' => function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }
            ]);

        // Status Filter (Default to 'active' if not specified? Or 'all'? Wireframe says tabs. Let's default to all or handle in frontend)
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('code') && $request->code) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        if ($request->has('year') && $request->year) {
            $query->where('year', $request->year);
        }
        if ($request->has('month') && $request->month) {
            $query->where('month', $request->month);
        }
        // Text filters
        foreach (['title', 'note', 'notes', 'client', 'supplier', 'client_id'] as $field) {
            if ($request->has($field) && $request->$field) {
                $query->where($field, 'like', '%' . $request->$field . '%');
            }
        }
        // Exact match filters for numbers
        foreach (['order_number', 'supplier_order_number', 'ddt_number', 'invoice_number'] as $field) {
            if ($request->has($field) && $request->$field) {
                $query->where($field, $request->$field);
            }
        }
        // Date ranges (created_at)
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|string|in:active,archived',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
            'note' => 'nullable|string',
            'notes' => 'nullable|string',
            'client' => 'nullable|string',
            'client_id' => 'nullable|string',
            'supplier' => 'nullable|string',
            'related_practices' => 'nullable|array',
            'related_practices.*' => 'exists:practices,id',
            // Add other fields validation as needed
        ]);

        $practice = $this->practiceService->create($validated, $request->user());

        return response()->json($practice->load('relatedPractices'), 201);
    }

    public function show($id)
    {
        $practice = Practice::with(['attachments', 'user', 'relatedPractices'])->findOrFail($id);

        $lineage = $this->practiceService->getLineage($practice);
        // Map lineage to simple array to avoid recursion and reduce payload
        $practice->lineage = collect($lineage)->map(function ($p) {
            return $p->only(['id', 'code', 'title', 'status', 'created_at']);
        });

        return response()->json($practice);
    }

    public function update(Request $request, $id)
    {
        $practice = Practice::findOrFail($id);

        // 24h check for non-admins
        $user = $request->user();
        if ($user->role !== 'admin') {
            if ($practice->created_at->diffInHours(now()) > 24) {
                // Allow status update (archiving) even after 24h? Maybe.
                // Rule: "Non possono modificare/eliminare pratiche dopo 24h".
                // Archiving is a modification. So strictly, no.
                return response()->json(['message' => 'Cannot edit after 24 hours'], 403);
            }
            if ($practice->user_id !== $user->id) { // Only owner can edit? Directive implies "utente" created it.
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:active,archived',
            'note' => 'nullable|string',
            'notes' => 'nullable|string',
            'client' => 'nullable|string',
            'client_id' => 'nullable|string',
            'supplier' => 'nullable|string',
            'related_practices' => 'nullable|array',
            'related_practices.*' => 'exists:practices,id',
            // Add other fields validation
        ]);

        $practice = $this->practiceService->update($practice, $validated); // Use service for update
        return response()->json($practice->load('relatedPractices'));
    }

    public function toggleFavorite(Request $request, $id)
    {
        $practice = Practice::findOrFail($id);
        $user = $request->user();

        $user->favoritePractices()->toggle($practice->id);

        return response()->json(['message' => 'Toggled favorite']);
    }

    public function destroy(Request $request, $id)
    {
        $practice = Practice::findOrFail($id);

        $user = $request->user();
        if ($user->role !== 'admin') {
            if ($practice->created_at->diffInHours(now()) > 24) {
                return response()->json(['message' => 'Cannot delete after 24 hours'], 403);
            }
            if ($practice->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $practice->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function clone(Request $request, $id)
    {
        $validated = $request->validate([
            'with_attachments' => 'boolean',
            'link_to_parent' => 'boolean',
        ]);

        $original = Practice::findOrFail($id);

        $clone = $this->practiceService->clone($original, $request->user(), $validated);

        return response()->json($clone, 201);
    }


}
