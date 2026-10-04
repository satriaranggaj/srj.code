<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Retained for backwards compatibility.
 *
 * Portfolio V2 has no blog: there are no post routes, no admin post CRUD and the
 * blog views were removed as dead code. The model, the `posts`/`categories`
 * tables and their historical migrations are all kept so that existing rows are
 * never destroyed and can be migrated away deliberately if they are ever needed.
 *
 * The read helpers the public site used to rely on (rev(), lastPost()) were
 * removed because they loaded every row into memory just to reverse and slice it.
 */
class Post extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
