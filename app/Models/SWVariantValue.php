<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWVariantValue extends Model
{
    use HasFactory;

    protected $table = 'swVariantValue';
    protected $primaryKey = 'id';

    protected $fillable = [
        'swProduct_id',
        'value',
        'value_en',
        'pos',
    ];
}
