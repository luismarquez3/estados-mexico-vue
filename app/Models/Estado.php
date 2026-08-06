<?php

namespace App\Models;

use Database\Factories\EstadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    /** @use HasFactory<EstadoFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cve_ent',
        'nomgeo',
        'pob_total',
    ];

    protected function casts(): array
    {
        return [
            'pob_total' => 'integer',
        ];
    }
}
