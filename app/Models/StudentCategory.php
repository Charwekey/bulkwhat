<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StudentCategory extends Model
{
    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'slug',
        'type',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(StudentCategory::class, 'parent_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'recipient_student_category');
    }

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get all category IDs including self and all subcategories.
     */
    public function getAllCategoryIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllCategoryIds());
        }
        return array_values(array_unique($ids));
    }

    /**
     * Get query for all valid recipients in this category and all its subcategories.
     */
    public function getAllValidRecipientsQuery()
    {
        $categoryIds = $this->getAllCategoryIds();

        return Recipient::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('student_categories.id', $categoryIds);
        })->where('is_valid', true);
    }
}
