<?php

namespace App\Website\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_name'
    ];

    public function blogPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tags', 'tag_id', 'blog_post_id');
    }
}
