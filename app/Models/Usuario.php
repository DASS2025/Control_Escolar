<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'Usuarios';
    public $timestamps = false;

    // Laravel busca 'email' por defecto; sobreescribimos con nuestro campo
    protected $authPasswordName = 'password_hash';

    protected $fillable = [
        'rol_id',
        'correo_institucional',
        'password_hash',
        'consentimiento_aviso',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Sobreescribir el campo de email para autenticación
    public function getEmailForPasswordReset(): string
    {
        return $this->correo_institucional;
    }

    // La tabla Usuarios no tiene columna remember_token
    public function getRememberTokenName()
    {
        return '';
    }
    // Un Usuario pertenece a un Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Un Usuario puede ser un Alumno
    public function alumno()
    {
        return $this->hasOne(Alumno::class, 'usuario_id');
    }

    // Un Usuario puede ser un Docente
    public function docente()
    {
        return $this->hasOne(Docente::class, 'usuario_id');
    }

    // Un Usuario puede ser Personal Administrativo
    public function personalAdministrativo()
    {
        return $this->hasOne(PersonalAdministrativo::class, 'usuario_id');
    }

    // Un Usuario genera muchos registros de auditoría
    public function bitacoraAuditoria()
    {
        return $this->hasMany(BitacoraAuditoria::class, 'usuario_id');
    }
}
