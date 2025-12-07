<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adjunto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'adjuntos';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'denuncia_id',
        'subido_por_id',
        'nombre_original',
        'nombre_almacenado',
        'ruta_almacenamiento',
        'tipo_mime',
        'tamano_bytes',
        'cifrado',
        'hash_archivo',
    ];

    protected function casts(): array
    {
        return [
            'tamano_bytes' => 'integer',
            'cifrado' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class, 'denuncia_id');
    }

    public function subidoPor()
    {
        return $this->belongsTo(Usuario::class, 'subido_por_id');
    }

    // Helpers
    public function getTamanoFormateadoAttribute(): string
    {
        $bytes = $this->tamano_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }

    public function esImagen(): bool
    {
        return str_starts_with($this->tipo_mime, 'image/');
    }

    public function esVideo(): bool
    {
        return str_starts_with($this->tipo_mime, 'video/');
    }

    public function esPdf(): bool
    {
        return $this->tipo_mime === 'application/pdf';
    }
}
