# 🏢 Asignar Área a Funcionario

## Problema

El usuario **funcionario@alerta.lima.gob.pe** (ID: 3) no tiene un área asignada, lo que causa errores al intentar acceder al dashboard de funcionario.

---

## Solución Rápida

### Opción 1: Usar Tinker (Recomendado)

```bash
php artisan tinker
```

Luego ejecuta:

```php
// 1. Ver áreas disponibles
$areas = DB::table('areas')->get();
print_r($areas->pluck('nombre', 'id')->toArray());

// 2. Asignar área al funcionario (reemplaza area_id con el ID del área)
$funcionario = App\Models\Usuario::where('email', 'funcionario@alerta.lima.gob.pe')->first();
$funcionario->area_id = 1; // Cambia 1 por el ID del área que quieras
$funcionario->save();

// 3. Verificar
echo "Funcionario ahora pertenece al área: " . $funcionario->area->nombre;
```

---

### Opción 2: SQL Directo (Si no tienes contraseña de PostgreSQL)

Si no puedes acceder a PostgreSQL directamente, usa esta consulta SQL a través de Tinker:

```bash
php artisan tinker
```

```php
// Ver áreas disponibles
DB::table('areas')->get()->each(function($area) {
    echo "ID: {$area->id} - {$area->nombre}\n";
});

// Asignar área (cambia 1 por el ID del área correcta)
DB::table('usuarios')->where('email', 'funcionario@alerta.lima.gob.pe')->update(['area_id' => 1]);

// Verificar
$func = DB::table('usuarios')->where('email', 'funcionario@alerta.lima.gob.pe')->first();
echo "Area ID asignada: " . $func->area_id;
```

---

### Opción 3: Crear un Área si No Existe

Si no hay áreas creadas en la base de datos:

```bash
php artisan tinker
```

```php
// Crear un área de prueba
$area = DB::table('areas')->insertGetId([
    'nombre' => 'Servicios Públicos',
    'descripcion' => 'Área encargada de servicios públicos municipales',
    'activo' => true,
    'creado_en' => now(),
    'actualizado_en' => now(),
]);

// Asignar al funcionario
DB::table('usuarios')->where('email', 'funcionario@alerta.lima.gob.pe')->update(['area_id' => $area]);

echo "Área creada con ID: $area y asignada al funcionario";
```

---

## Verificación

Después de asignar el área, verifica usando `/debug-user`:

```
http://127.0.0.1:8000/debug-user
```

Deberías ver algo como:

```json
{
  "user": {
    "id": 3,
    "nombre": "Funcionario",
    "apellido": "Municipal",
    "email": "funcionario@alerta.lima.gob.pe",
    "area_id": 1
  },
  "area": {
    "id": 1,
    "nombre": "Servicios Públicos"
  }
}
```

---

## Áreas Típicas en Municipalidades

Si necesitas crear más áreas, aquí hay ejemplos comunes:

```bash
php artisan tinker
```

```php
$areas = [
    ['nombre' => 'Servicios Públicos', 'descripcion' => 'Limpieza, alumbrado, parques'],
    ['nombre' => 'Obras Públicas', 'descripcion' => 'Infraestructura y construcción'],
    ['nombre' => 'Seguridad Ciudadana', 'descripcion' => 'Seguridad y prevención'],
    ['nombre' => 'Medio Ambiente', 'descripcion' => 'Gestión ambiental y residuos'],
    ['nombre' => 'Tránsito y Transporte', 'descripcion' => 'Vías y transporte público'],
];

foreach ($areas as $area) {
    DB::table('areas')->insertOrIgnore([
        'nombre' => $area['nombre'],
        'descripcion' => $area['descripcion'],
        'activo' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
}

echo "Áreas creadas exitosamente\n";
DB::table('areas')->get()->each(function($area) {
    echo "- ID {$area->id}: {$area->nombre}\n";
});
```

---

## Después de Asignar el Área

1. **Recarga el navegador** con `Ctrl + Shift + R`
2. **Ve al dashboard de funcionario**: `http://127.0.0.1:8000/funcionario/dashboard`
3. **Deberías ver** estadísticas y denuncias del área asignada

---

## Troubleshooting

### "No tengo acceso a tinker"

Si no puedes usar tinker, crea un archivo temporal:

```php
// routes/web.php - AGREGAR TEMPORALMENTE
Route::get('/asignar-area-temp', function () {
    $funcionario = App\Models\Usuario::where('email', 'funcionario@alerta.lima.gob.pe')->first();
    $funcionario->area_id = 1; // Cambia por el ID correcto
    $funcionario->save();

    return "Área asignada: " . $funcionario->area->nombre;
})->middleware('auth');
```

Luego ve a: `http://127.0.0.1:8000/asignar-area-temp`

**IMPORTANTE: Elimina esta ruta después de usarla**

---

### "No sé qué área asignar"

Usa el área con ID 1 (la primera que exista):

```bash
php artisan tinker
```

```php
$primeraArea = DB::table('areas')->where('activo', true)->first();
if ($primeraArea) {
    DB::table('usuarios')->where('email', 'funcionario@alerta.lima.gob.pe')->update(['area_id' => $primeraArea->id]);
    echo "Asignado al área: {$primeraArea->nombre}";
} else {
    echo "No hay áreas disponibles. Crea una primero.";
}
```

---

**Fecha:** 2025-12-07
**Usuario Afectado:** funcionario@alerta.lima.gob.pe (ID: 3)
**Problema:** area_id = NULL
**Solución:** Asignar área válida
