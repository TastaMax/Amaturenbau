<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWSubCategory extends Model
{
    use HasFactory;

    protected $table = 'swSubCategory';
    protected $primaryKey = 'id';
    protected $fillable = [
        'swCategory_id',
        'title',
        'title_en',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sw_id',
        'sw_edited',
        'sw_deleted',
        'sw_active',
    ];

    public function category()
    {
        return $this->belongsTo(SWCategory::class, 'swCategory_id', 'id');
    }
}
