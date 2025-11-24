<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'guard_name',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'rol_usuario', 'rol_id', 'usuario_id')
            ->withPivot('asignado_en');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }

    // Helpers
    public function asignarPermiso(Permiso $permiso): void
    {
        $this->permisos()->syncWithoutDetaching($permiso->id);
    }

    public function quitarPermiso(Permiso $permiso): void
    {
        $this->permisos()->detach($permiso->id);
    }
}
