<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'student_category_id',
    'original_filename',
    'stored_path',
    'columns',
    'phone_column',
    'total_records',
    'valid_records',
    'invalid_records',
    'status'
])]
class Import extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'columns' => 'array',
            'total_records' => 'integer',
            'valid_records' => 'integer',
            'invalid_records' => 'integer',
        ];
    }

    /**
     * Get the user that owns the import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studentCategory(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'student_category_id');
    }

    /**
     * Get the recipients for the import.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    /**
     * Get the campaigns for the import.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the valid recipients count attribute.
     */
    public function getValidRecipientsCountAttribute(): int
    {
        return $this->recipients()->where('is_valid', true)->count();
    }

    /**
     * Alias accessor for file_name -> original_filename.
     */
    public function getFileNameAttribute(): string
    {
        return $this->original_filename ?? '';
    }
}
