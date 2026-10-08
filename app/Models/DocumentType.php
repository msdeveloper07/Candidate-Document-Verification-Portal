<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'instructions',
        'allowed_extensions', 'max_size_kb',
        'is_required_by_default', 'is_active', 'sort_order', 'icon',
    ];

    protected function casts(): array
    {
        return [
            'allowed_extensions'     => 'array',
            'is_required_by_default' => 'boolean',
            'is_active'              => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $type) {
            $type->slug ??= Str::slug($type->name);
        });
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CandidateDocument::class);
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_requirements')
            ->withPivot(['is_required', 'instructions'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getExtensionListAttribute(): string
    {
        return collect($this->allowed_extensions)->map(fn ($e) => mb_strtoupper($e))->implode(', ');
    }

    public function getMaxSizeLabelAttribute(): string
    {
        return $this->max_size_kb >= 1024
            ? round($this->max_size_kb / 1024, 1).' MB'
            : $this->max_size_kb.' KB';
    }

    /** Laravel validation rule fragment, e.g. "mimes:pdf,jpg|max:5120". */
    public function validationRules(): array
    {
        return [
            'file',
            'mimes:'.implode(',', $this->allowed_extensions),
            'max:'.$this->max_size_kb,
        ];
    }
}
