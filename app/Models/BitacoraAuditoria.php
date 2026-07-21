<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraAuditoria extends Model
{
    protected $table = 'bitacora_auditoria';

    // Solo tiene created_at, no updated_at; la DB lo gestiona automáticamente
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla_afectada',
        'valores_json',
    ];

    protected $casts = [
        'valores_json' => 'array',
        'created_at'   => 'datetime',
    ];

    // Cada registro de bitácora pertenece a un Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
