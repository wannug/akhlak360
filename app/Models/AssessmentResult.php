<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_period_id',
        'employee_id',
        'amanah_score',
        'kompeten_score',
        'harmonis_score',
        'loyal_score',
        'adaptif_score',
        'kolaboratif_score',
        'self_score',
        'others_score',
        'gap_score',
        'final_score',
        'category',
        'talent_mapping_category',
    ];

    protected function casts(): array
    {
        return [
            'amanah_score' => 'decimal:2',
            'kompeten_score' => 'decimal:2',
            'harmonis_score' => 'decimal:2',
            'loyal_score' => 'decimal:2',
            'adaptif_score' => 'decimal:2',
            'kolaboratif_score' => 'decimal:2',
            'self_score' => 'decimal:2',
            'others_score' => 'decimal:2',
            'gap_score' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function assessmentPeriod(): BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeBelowThreshold(Builder $query, AssessmentPeriod $period): Builder
    {
        return $query->where('assessment_period_id', $period->id)
            ->where('final_score', '<', $period->threshold_score);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
