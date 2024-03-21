<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWProduct extends Model
{
    use HasFactory;

    protected $table = 'swProduct';
    protected $primaryKey = 'id';

    protected $fillable = [
        'swProductClass_id',
        'articlenumber',
        'serie',
        'price',
        'weight',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sw_id',
        'sw_edited',
        'sw_deleted'
    ];

    public function variantValues()
    {
        return $this->hasMany(SWVariantValue::class, 'swProduct_id', 'id')->orderBy('pos');
    }
}
