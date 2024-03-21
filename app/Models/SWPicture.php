<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SWPicture extends Model
{
    use HasFactory;

    protected $table = 'swPicture';
    protected $primaryKey = 'id';

    protected $fillable = [
        'type',
        'assignment_id',
        'path',
        'file',
        'pos',
    ];
}
