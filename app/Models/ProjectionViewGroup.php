<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectionViewGroup extends Model
{
    protected $table = 'projection_view_groups';

    protected $fillable = [
        'projection_view_id',
        'group_id',
        'division_id',
    ];

    public function projectionView(): BelongsTo
    {
        return $this->belongsTo(ProjectionView::class, 'projection_view_id', 'id');
    }
}
