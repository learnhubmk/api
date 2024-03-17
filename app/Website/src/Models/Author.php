<?php

namespace App\Website\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'img_src',
        'bio',
        'website_url',
        'linkedin_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
