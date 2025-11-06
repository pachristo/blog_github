<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator', 'title', 'slug', 'category', 'content', 
        'display_image', 'status', 'date', 'likes', 'other', 
        'meta_keywords', 'meta_description','lang'
    ];

    protected $casts = [
        'date' => 'date',
        'other' => 'array',
        'likes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'creator');
    }

    public static function validateInput($request, $blog = null)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('blogs')->ignore($blog?->id)
            ],
            'category' => 'required|string|max:100',
            'content' => 'required|string',
            'display_image' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,archived',
            'date' => 'required|date',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:300',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
            
            if (empty($blog->date)) {
                $blog->date = now();
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title') && empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }

    public function getExcerpt($length = 150)
    {
        return Str::limit(strip_tags($this->content), $length);
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }
}
