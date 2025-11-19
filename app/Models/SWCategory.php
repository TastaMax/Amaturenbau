<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWCategory extends Model
{
    use HasFactory;

    protected $table = 'swCategory';
    protected $primaryKey = 'id';

    protected $fillable = [
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

    public function subcategories()
    {
        return $this->hasMany(SWSubCategory::class, 'swCategory_id', 'id');
    }
}
