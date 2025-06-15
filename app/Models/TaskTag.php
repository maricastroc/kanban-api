<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTag extends Pivot
{
    protected $table = 'task_tag';

    public $timestamps = false;

    protected $fillable = ['task_id', 'tag_id', 'created_at'];
}
