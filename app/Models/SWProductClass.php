<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWProductClass extends Model
{
    use HasFactory;

    protected $table = 'swProductClass';
    protected $primaryKey = 'id';

    protected $fillable = [
        'swSubCategory_id',
        'title',
        'title_en',
        'description',
        'description_en',
        'datasheet',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sw_id',
        'sw_edited',
        'sw_deleted'
    ];

    public function subCategory()
    {
        return $this->belongsTo(SWSubCategory::class, 'swSubCategory_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(SWProduct::class, 'swProductClass_id', 'id');
    }

    public function pictures()
    {
        return $this->hasMany(SWPicture::class, 'assignment_id', 'id')->where('type', 1)->orderBy('pos', 'asc');
    }

    public function variantHeaders()
    {
        return $this->hasMany(SWVariantHeader::class, 'swProductClass_id', 'id')->orderBy('pos');
    }
}
