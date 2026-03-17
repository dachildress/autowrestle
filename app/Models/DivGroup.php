<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivGroup extends Model
{
    protected $table = 'divgroups';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'Name',
        'gender',
        'MinAge',
        'MaxAge',
        'MinGrade',
        'MaxGrade',
        'MaxWeightDiff',
        'BracketType',
        'MaxPwrDiff',
        'bracketed',
        'bouted',
        'MaxExpDiff',
        'Tournament_Id',
        'Division_id',
    ];

    protected $primaryKey = ['id', 'Tournament_Id', 'Division_id'];

    public $timestamps = true;

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'Tournament_Id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'Division_id', 'id');
    }

    /** Single-letter prefix for gender: B = Boys, G = Girls, C = Co-ed */
    public static function genderPrefix(?string $gender): string
    {
        return match ((string) ($gender ?? 'boys')) {
            'girls' => 'G',
            'coed' => 'C',
            default => 'B',
        };
    }

    /** Group name with leading gender prefix (e.g. "B Grades P - 1"). */
    public function getDisplayNameAttribute(): string
    {
        return self::genderPrefix($this->gender) . ' ' . ($this->Name ?? 'Group ' . $this->id);
    }

    /** Grade for display: -1 → "P" (Pre-K), 0 → "K" (Kindergarten), otherwise the number. */
    public static function gradeForDisplay(?int $grade): string
    {
        return match ((int) ($grade ?? 0)) {
            -1 => 'P',
            0 => 'K',
            default => (string) $grade,
        };
    }

    /** Minimum grade for display (e.g. "P" instead of -1). */
    public function getDisplayMinGradeAttribute(): string
    {
        return self::gradeForDisplay((int) $this->MinGrade);
    }

    /** Maximum grade for display (e.g. "P" instead of -1). */
    public function getDisplayMaxGradeAttribute(): string
    {
        return self::gradeForDisplay((int) $this->MaxGrade);
    }
}
