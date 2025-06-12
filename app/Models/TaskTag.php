<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTag extends Pivot
{
    protected $table = 'task_tag';

    public $timestamps = false; // Desativa timestamps automáticos

    protected $fillable = ['task_id', 'tag_id', 'created_at'];
}
