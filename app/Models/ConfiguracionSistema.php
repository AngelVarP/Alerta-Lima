<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfiguracionSistema extends Model
{
    use HasFactory;

    protected $table = 'configuracion_sistema';
    protected $primaryKey = 'clave';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'categoria',
        'descripcion',
        'es_sensible',
        'actualizado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'es_sensible' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    // Relaciones
    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por_id');
    }

    // Helpers estáticos para obtener configuración
    public static function obtener(string $clave, $default = null)
    {
        return Cache::remember("config.{$clave}", 3600, function () use ($clave, $default) {
            $config = self::find($clave);

            if (!$config) {
                return $default;
            }

            return self::convertirValor($config->valor, $config->tipo);
        });
    }

    public static function establecer(string $clave, $valor, ?Usuario $usuario = null): void
    {
        $config = self::find($clave);

        if ($config) {
            $config->update([
                'valor' => is_array($valor) ? json_encode($valor) : (string) $valor,
                'actualizado_por_id' => $usuario?->id,
            ]);
        }

        Cache::forget("config.{$clave}");
    }

    protected static function convertirValor(string $valor, string $tipo)
    {
        return match ($tipo) {
            'integer' => (int) $valor,
            'boolean' => filter_var($valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($valor, true),
            default => $valor,
        };
    }

    // Scopes
    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }
}
