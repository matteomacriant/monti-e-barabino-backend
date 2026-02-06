<?php

namespace App\Services;

use App\Models\Practice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PracticeService
{
    public function create(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // Use provided year/month or default to current
            $year = $data['year'] ?? Carbon::now()->year;
            $month = $data['month'] ?? Carbon::now()->month;

            // Generate Code: YYYYMM + PROG based on selected year
            $count = Practice::where('year', $year)->count();
            $progressive = $count + 1;

            $code = $year . sprintf('%02d', $month) . $progressive;

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
            ]);

            if (isset($data['related_practices']) && is_array($data['related_practices'])) {
                $practice->relatedPractices()->sync($data['related_practices']);
            }

            return $practice;
        });
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
