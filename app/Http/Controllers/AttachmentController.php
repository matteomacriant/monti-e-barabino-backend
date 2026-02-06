<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Attachment::with('practice');

        // Filter by filename
        if ($request->has('filename') && $request->filename) {
            $query->where('filename', 'like', '%' . $request->filename . '%');
        }

        // Filter by related practice attributes
        $query->whereHas('practice', function ($q) use ($request) {
            if ($request->has('code') && $request->code) {
                $q->where('code', 'like', '%' . $request->code . '%');
            }
            if ($request->has('year') && $request->year) {
                $q->where('year', $request->year);
            }
            if ($request->has('month') && $request->month) {
                $q->where('month', $request->month);
            }
            // Text filters on practice
            foreach (['title', 'note', 'notes', 'client', 'supplier', 'client_id'] as $field) {
                if ($request->has($field) && $request->$field) {
                    $q->where($field, 'like', '%' . $request->$field . '%');
                }
            }
            // Numbers
            foreach (['order_number', 'supplier_order_number', 'ddt_number', 'invoice_number'] as $field) {
                if ($request->has($field) && $request->$field) {
                    $q->where($field, $request->$field);
                }
            }
        });

        // Date ranges (created_at of attachment or practice? Let's assume Attachment creation)
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
        $request->validate([
            'practice_id' => 'required|exists:practices,id',
            'file' => 'required|file|max:102400', // 100MB
            'expiry_date' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public'); // or s3

        $attachment = Attachment::create([
            'practice_id' => $request->practice_id,
            'filename' => $file->getClientOriginalName(),
            'filepath' => $path,
            'expiry_date' => $request->expiry_date,
        ]);

        return response()->json($attachment, 201);
    }

    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);
        Storage::disk('public')->delete($attachment->filepath);
        $attachment->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
