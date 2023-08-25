<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public static function rev()
    {
        $post = static::all();

        return $post->reverse();
    }

    public static function lastPost()
    {
        $post = static::all();

        $lastPost = $post->reverse()->take(3);

        return $lastPost;
    }

    use HasFactory;

    // protected $fillable = ['thumbnail', 'date', 'title', 'excerpt', 'body'];
    protected $guarded = ['id'];
}
