<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Models\AttributeTrait;

class Post extends Model
{
    use HasFactory, AttributeTrait;
    protected   $table        = 'posts';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    public      $cols = [
        'id', 'level', 'parent_id', 'title', 'type', 'writer', 'created_at', 'updated_at'
    ];

    // 자식 답변들
    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_id')
            ->with('replies')
            ->select($this->cols);
    }
}
