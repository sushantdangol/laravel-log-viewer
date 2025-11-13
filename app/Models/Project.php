<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'tbl_projects';
    protected $primaryKey = 'project_id';

    protected $fillable = [
        'project_parent_id',
        'project_name',
        'project_file_path',
        'project_type'
    ];
}
