<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

#[Fillable([
    'user_id',
    'import_id',
    'student_category_id',
    'name',
    'message_template',
    'total_recipients',
    'sent_count',
    'failed_count',
    'status',
    'started_at',
    'completed_at'
])]
class Campaign extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the import that owns the campaign.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function studentCategory(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'student_category_id');
    }

    public function getTargetNameAttribute(): string
    {
        if ($this->studentCategory) {
            return $this->studentCategory->name;
        }

        return $this->import ? $this->import->original_filename : 'No target selected';
    }

    /**
     * Get the messages for the campaign.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the progress percentage attribute.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0.0;
        }

        return round((($this->sent_count + $this->failed_count) / $this->total_recipients) * 100, 2);
    }

    /**
     * Get the success rate attribute.
     */
    public function getSuccessRateAttribute(): float
    {
        $totalProcessed = $this->sent_count + $this->failed_count;
        
        if ($totalProcessed === 0) {
            return 0.0;
        }

        return round(($this->sent_count / $totalProcessed) * 100, 2);
    }

    /**
     * Scope a query to only include campaigns of a given user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
