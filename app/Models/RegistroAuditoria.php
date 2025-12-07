<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAuditoria extends Model
{
    use HasFactory;

    protected $table = 'registros_auditoria';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'accion',
        'tipo_entidad',
        'id_entidad',
        'valores_anteriores',
        'valores_nuevos',
        'ip_origen',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'creado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Helper para registrar auditoría
    public static function registrar(
        string $accion,
        ?Usuario $usuario = null,
        ?string $tipoEntidad = null,
        ?int $idEntidad = null,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null
    ): self {
        return self::create([
            'usuario_id' => $usuario?->id,
            'accion' => $accion,
            'tipo_entidad' => $tipoEntidad,
            'id_entidad' => $idEntidad,
            'valores_anteriores' => $valoresAnteriores,
            'valores_nuevos' => $valoresNuevos,
            'ip_origen' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
