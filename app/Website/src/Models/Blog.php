<?php

namespace App\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'tags',
        'user_id'
    ];

    protected $casts = [
        'tags' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->select('name', 'img_src');
    }
}
