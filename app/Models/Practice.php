<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Practice extends Model
{
    /** @use HasFactory<\Database\Factories\PracticeFactory> */
    protected $fillable = [
        'code',
        'title',
        'status', // Added by migration
        'year',
        'month',
        'user_id',
        'note',
        'notes', // Added by migration
        'client',
        'client_id', // Added by migration
        'supplier',
        'order_number',
        'supplier_order_number',
        'ddt_number',
        'invoice_number',
        'invoice_year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'practice_user');
    }

    /*
     * Relations with other practices
     */
    public function relatedPractices()
    {
        return $this->belongsToMany(Practice::class, 'practice_relations', 'source_practice_id', 'related_practice_id')
            ->withPivot('relation_type')
            ->withTimestamps();
    }

    public function parentPractices()
    {
        return $this->belongsToMany(Practice::class, 'practice_relations', 'related_practice_id', 'source_practice_id')
            ->withPivot('relation_type')
            ->withTimestamps();
    }
}
