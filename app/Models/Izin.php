<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = ['nama', 'keterangan'];
}
