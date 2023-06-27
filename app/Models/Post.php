<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Post extends Model
{
    use HasFactory;
    protected   $table        = 'posts';
    protected   $primaryKey   = 'id';
    protected   $guarded      = [];
    public      $cols = [
        'id', 'parent_id', 'title', 'type', 'writer', 'created_at', 'updated_at'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format("Y-m-d H:i:s");
    }

    // 자식 답변들
    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_id')
            ->with('replies')
            ->select($this->cols);
    }
}
