<?php

namespace App\Services;

use App\Models\Practice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PracticeService
{
    protected function generateCode($year, $month)
    {
        // "anno-mese + progressivo annuale" -> YYYY-MM-XXXX (resetting at year start)
        // Check strict locking if concurrency is high, but for now simple count + 1

        // Count practices in this YEAR
        $count = Practice::where('year', $year)->count();
        $progressive = $count + 1;

        return sprintf('%04d-%02d-%04d', $year, $month, $progressive);
    }

    public function create(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Use provided year/month or default to current
            $year = $data['year'] ?? Carbon::now()->year;
            $month = $data['month'] ?? Carbon::now()->month;

            $code = $this->generateCode($year, $month);

            $practice = Practice::create([
                'code' => $code,
                'title' => $data['title'] ?? 'Untitled',
                'year' => $year,
                'month' => $month,
                'user_id' => $user->id,
                'note' => $data['note'] ?? null,
                'notes' => $data['notes'] ?? null,
                'client' => $data['client'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'supplier' => $data['supplier'] ?? null,
                'order_number' => $data['order_number'] ?? null,
                'supplier_order_number' => $data['supplier_order_number'] ?? null,
                'ddt_number' => $data['ddt_number'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'invoice_year' => $data['invoice_year'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            if (isset($data['related_practices']) && is_array($data['related_practices'])) {
                $practice->relatedPractices()->sync($data['related_practices']);
            }

            return $practice;
        });
    }

    public function clone(Practice $original, $user, array $options)
    {
        return DB::transaction(function () use ($original, $user, $options) {
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;

            $newCode = $this->generateCode($year, $month);

            // Replicate basic data
            $clone = $original->replicate(['code', 'created_at', 'updated_at', 'id']);
            $clone->code = $newCode;
            $clone->title = 'Copia di ' . $original->title;
            $clone->user_id = $user->id;
            $clone->year = $year;
            $clone->month = $month;
            $clone->save();

            // Options
            if ($options['link_to_parent'] ?? false) {
                $clone->relatedPractices()->attach($original->id, ['relation_type' => 'parent']);
            }

            if ($options['with_attachments'] ?? false) {
                foreach ($original->attachments as $attachment) {
                    $originalPath = $attachment->filepath;
                    $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                    // Keep original filename as requested
                    $newFilename = $attachment->filename;
                    $newPath = 'attachments/' . md5(uniqid()) . '.' . $extension;

                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($originalPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->copy($originalPath, $newPath);

                        $clone->attachments()->create([
                            'filename' => $newFilename,
                            'filepath' => $newPath,
                            'expiry_date' => $attachment->expiry_date
                        ]);
                    }
                }
            }

            return $clone;
        });
    }

    public function getLineage(Practice $practice)
    {
        // 1. Find Root
        $root = $practice;
        // Traverse UP to find the absolute root
        while ($parent = $root->relatedPractices()->wherePivot('relation_type', 'parent')->first()) {
            $root = $parent;
        }

        // 2. Traverse DOWN from Root to build the tree/list
        $lineage = [];
        $this->traverseChildren($root, $lineage);

        return $lineage;
    }

    protected function traverseChildren(Practice $node, array &$list, $level = 0)
    {
        // Add current node
        $node->load('attachments'); // Optional: load data needed for display
        $node->level = $level; // Add level helper if needed for UI indentation
        $list[] = $node;

        // Find children (Inverse of Parent relation)
        // Parent relation: Child -> Parent (source -> related, type='parent')
        // So Children are: Parent -> Child (related -> source, type='parent')
        // Definition in Model: parentPractices() uses 'related', 'source'.
        $children = $node->parentPractices()->wherePivot('relation_type', 'parent')->orderBy('created_at', 'asc')->get();

        foreach ($children as $child) {
            $this->traverseChildren($child, $list, $level + 1);
        }
    }

    public function update(Practice $practice, array $data)
    {
        return DB::transaction(function () use ($practice, $data) {
            $practice->update($data);

            if (isset($data['related_practices']) && is_array($data['related_practices'])) {
                $practice->relatedPractices()->sync($data['related_practices']);
            }

            return $practice;
        });
    }
}
