<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomeLog extends Model
{
    use HasFactory;

    protected $table = 'logs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'system',
        'importance',
        'message',
        'debug',
    ];
}
