<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'import_id',
    'phone_number',
    'data',
    'is_valid',
    'validation_errors'
])]
class Recipient extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_valid' => 'boolean',
            'validation_errors' => 'array',
        ];
    }

    /**
     * Get the import that owns the recipient.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Get the messages for the recipient.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function categories()
    {
        return $this->belongsToMany(StudentCategory::class, 'recipient_student_category');
    }

    /**
     * Get a specific field from the data JSON by key name (Accessor).
     */
    public function getFieldAttribute(string $name): ?string
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Retrieves a value from the data JSON by column name.
     */
    public function getField(string $key): ?string
    {
        return $this->data[$key] ?? null;
    }
}
