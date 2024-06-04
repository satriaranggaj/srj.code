<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{   
    public static function rev()
    {
        $post = static::all();

        return $post->reverse();
    }

    public static function lastProject()
    {
        $project = static::all();

        $lastProject = $project->reverse()->take(3);

        return $lastProject;
    }

    use HasFactory;

    protected $fillable = [
        'title', 
        'link',
    ];
}
