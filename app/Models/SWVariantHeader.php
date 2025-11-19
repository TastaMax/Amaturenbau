<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWVariantHeader extends Model
{
    use HasFactory;

    protected $table = 'swVariantHeader';
    protected $primaryKey = 'id';

    protected $fillable = [
        'swProductClass_id',
        'title',
        'title_en',
        'pos',
    ];

    public function swProductClass()
    {
        return $this->belongsTo(SWProductClass::class, 'swProductClass_id', 'id');
    }
}
