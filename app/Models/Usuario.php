<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    // CORRECCIÓN: Usar convenciones estándar de Laravel
    // Ya no necesitamos estas constantes:
    // const CREATED_AT = 'creado_en';
    // const UPDATED_AT = 'actualizado_en';
    // const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'dni',
        'telefono',
        'direccion',
        'area_id',
        'password', // CORRECCIÓN: Nombre estándar
        'activo',
    ];

    protected $hidden = [
        'password', // CORRECCIÓN: Nombre estándar
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // CORRECCIÓN: Nombre estándar
            'two_factor_confirmed_at' => 'datetime',
            'bloqueado_hasta' => 'datetime',
            'ultimo_login' => 'datetime',
            'activo' => 'boolean',
            'password' => 'hashed', // Laravel 11+ automatic hashing
        ];
    }

    // CORRECCIÓN: Ya no necesitamos getAuthPassword() porque usamos 'password'

    // Relaciones
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id')
            ->withPivot('asignado_en');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_usuario', 'usuario_id', 'permiso_id');
    }

    public function denunciasCreadas()
    {
        return $this->hasMany(Denuncia::class, 'ciudadano_id');
    }

    public function denunciasAsignadas()
    {
        return $this->hasMany(Denuncia::class, 'asignado_a_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'usuario_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    // Helpers de autorización
    public function tieneRol(string $nombreRol): bool
    {
        return $this->roles()->where('nombre', $nombreRol)->exists();
    }

    public function tienePermiso(string $nombrePermiso): bool
    {
        // Admins tienen todos los permisos
        if ($this->esAdmin()) {
            return true;
        }

        // Verificar permiso directo
        if ($this->permisos()->where('nombre', $nombrePermiso)->exists()) {
            return true;
        }

        // Verificar permiso a través de roles
        foreach ($this->roles as $rol) {
            if ($rol->permisos()->where('nombre', $nombrePermiso)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function esAdmin(): bool
    {
        return $this->tieneRol('admin');
    }

    public function esFuncionario(): bool
    {
        return $this->tieneRol('funcionario') || $this->tieneRol('supervisor');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }

    // Helper para bloquear usuario
    public function bloquear(int $minutos = 15): void
    {
        $this->update([
            'bloqueado_hasta' => now()->addMinutes($minutos),
        ]);
    }

    // Helper para verificar si está bloqueado
    public function estaBloqueado(): bool
    {
        return $this->bloqueado_hasta && $this->bloqueado_hasta->isFuture();
    }

    // Helper para incrementar intentos fallidos
    public function incrementarIntentosFallidos(): void
    {
        $this->increment('intentos_fallidos');

        if ($this->intentos_fallidos >= 5) {
            $this->bloquear(15);
        }
    }

    // Helper para resetear intentos
    public function resetearIntentosFallidos(): void
    {
        $this->update([
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'ultimo_login' => now(),
        ]);
    }
}
