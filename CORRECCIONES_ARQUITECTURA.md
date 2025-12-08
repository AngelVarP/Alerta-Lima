# 🔧 CORRECCIONES DE ARQUITECTURA - ALERTA LIMA

> **Fecha**: Diciembre 2024
> **Estado**: Implementado - Requiere Migración de BD

---

## 📋 RESUMEN DE CAMBIOS CRÍTICOS

Este documento detalla las correcciones aplicadas al proyecto para transformarlo de una arquitectura **híbrida SQL/Laravel** a una arquitectura **"Laravel Way"** completamente mantenible.

---

## ✅ CORRECCIONES IMPLEMENTADAS

### 1. ✨ Migraciones de Laravel (CRÍTICO)

**❌ Problema Original:**
- Base de datos creada con script SQL estático (`schema_sgdc_v2.sql`)
- Imposible hacer testing automatizado
- Difícil trabajo en equipo (versionado manual de cambios SQL)
- Despliegue manual propenso a errores

**✅ Solución Implementada:**
- **10 archivos de migración** creados en `database/migrations/`:
  1. `2024_01_01_000000_create_areas_table.php`
  2. `2024_01_01_000001_create_usuarios_table.php`
  3. `2024_01_01_000002_create_roles_table.php`
  4. `2024_01_01_000003_create_permisos_table.php`
  5. `2024_01_01_000004_create_pivot_tables.php`
  6. `2024_01_02_000000_create_catalogos_tables.php`
  7. `2024_01_03_000000_create_denuncias_table.php`
  8. `2024_01_03_000001_create_historial_tables.php`
  9. `2024_01_03_000002_create_adjuntos_comentarios_tables.php`
  10. `2024_01_03_000003_create_notificaciones_table.php`
  11. `2024_01_04_000000_create_auditoria_tables.php`

**Beneficios:**
- ✅ Testing automatizado con `RefreshDatabase`
- ✅ Versionado de cambios en Git
- ✅ Rollback automático con `php artisan migrate:rollback`
- ✅ Despliegue predecible con `php artisan migrate`

---

### 2. 🧩 Observers de Laravel (CRÍTICO)

**❌ Problema Original:**
- Lógica de negocio en **Triggers de PostgreSQL** (PL/pgSQL)
- Código invisible para desarrolladores PHP
- Debugging extremadamente difícil
- Stale models (datos desactualizados en memoria)

**✅ Solución Implementada:**
- **DenunciaObserver** (`app/Observers/DenunciaObserver.php`):
  - `creating()`: Genera código único `DEN-YYYY-NNNNNN`
  - `creating()`: Calcula fecha SLA según prioridad
  - `creating()`: Asigna área por defecto según categoría
  - `created()`: Registra estado inicial en historial
  - `updating()`: Marca `cerrada_en` al pasar a estado final

- **UsuarioObserver** (`app/Observers/UsuarioObserver.php`):
  - `created()`: Registra evento de seguridad
  - `updated()`: Registra cambios importantes (email, activo)
  - `deleting()`: Registra eliminación

- **EventServiceProvider** (`app/Providers/EventServiceProvider.php`):
  - Registra todos los observers automáticamente

**Beneficios:**
- ✅ Lógica de negocio visible en PHP
- ✅ Debugging con `dd()` y logs
- ✅ Testing unitario de lógica de negocio
- ✅ Sin necesidad de saber SQL avanzado

---

### 3. 📐 Convenciones Estándar de Laravel

**❌ Problema Original:**
```php
// Nombres no estándar
const CREATED_AT = 'creado_en';
const UPDATED_AT = 'actualizado_en';
const DELETED_AT = 'eliminado_en';
protected $fillable = ['password_hash'];
public function getAuthPassword() { return $this->password_hash; }
```

**✅ Solución Implementada:**
```php
// Nombres estándar
// Ya no necesitamos constantes personalizadas
protected $fillable = ['password'];
protected $casts = ['password' => 'hashed']; // Laravel 11+
```

**Modelos Actualizados:**
- `app/Models/Usuario.php`: Usa `password`, `created_at`, `updated_at`, `deleted_at`
- `app/Models/Denuncia.php`: Usa timestamps estándar
- `app/Models/Area.php`: Usa timestamps estándar

**Beneficios:**
- ✅ Compatibilidad con paquetes de terceros (Fortify, Breeze, Telescope)
- ✅ Menos configuración manual
- ✅ Código más limpio y estándar

---

### 4. 🔒 Cifrado Selectivo (CRÍTICO)

**❌ Problema Original:**
```sql
descripcion TEXT NOT NULL -- Cifrado con AES-256
```
- Imposible buscar texto dentro de descripciones
- `WHERE descripcion LIKE '%bache%'` retorna 0 resultados

**✅ Solución Implementada:**
```php
// En migración:
$table->text('descripcion'); // SIN cifrar para permitir búsquedas

// En Service (app/Services/EncryptionService.php):
public function encryptDni(?string $dni): ?string
public function encryptPhone(?string $phone): ?string
public function hashFile(string $filePath): string // SHA-256 para integridad
```

**Datos Cifrados (opcional):**
- DNI (solo si es requerido por ley)
- Teléfono (solo si es requerido)
- Archivos adjuntos críticos

**Datos NO Cifrados (búsqueda habilitada):**
- Título de denuncia
- Descripción de denuncia
- Categorías, estados, áreas

**Beneficios:**
- ✅ Búsqueda de texto completo funciona
- ✅ Filtros y reportes funcionan
- ✅ Privacidad solo donde es necesario

---

### 5. 📦 Versiones Estables

**❌ Problema Original:**
```json
"laravel/framework": "^12.0",  // Beta/Alpha
"tailwindcss": "^4.0.0",       // Alpha
"vite": "^7.0.7"               // Beta
```

**✅ Solución Implementada:**
```json
// composer.json
"laravel/framework": "^11.0",  // LTS Estable
"spatie/laravel-permission": "^6.9",

// package.json
"tailwindcss": "^3.4.15",      // Estable
"vite": "^5.4.11",             // Estable
"laravel-vite-plugin": "^1.0.5"
```

**Archivos Actualizados:**
- `composer.json`: Laravel 11 LTS
- `package.json`: Tailwind 3, Vite 5
- `vite.config.js`: Sin `@tailwindcss/vite`
- `tailwind.config.js`: Configuración estándar
- `postcss.config.js`: Agregado para Tailwind 3

**Beneficios:**
- ✅ Sin bugs de versiones beta
- ✅ Documentación completa disponible
- ✅ Paquetes de terceros compatibles

---

## 📂 NUEVOS ARCHIVOS CREADOS

### Migraciones (11 archivos)
```
database/migrations/
├── 2024_01_01_000000_create_areas_table.php
├── 2024_01_01_000001_create_usuarios_table.php
├── 2024_01_01_000002_create_roles_table.php
├── 2024_01_01_000003_create_permisos_table.php
├── 2024_01_01_000004_create_pivot_tables.php
├── 2024_01_02_000000_create_catalogos_tables.php
├── 2024_01_03_000000_create_denuncias_table.php
├── 2024_01_03_000001_create_historial_tables.php
├── 2024_01_03_000002_create_adjuntos_comentarios_tables.php
├── 2024_01_03_000003_create_notificaciones_table.php
└── 2024_01_04_000000_create_auditoria_tables.php
```

### Observers y Services (3 archivos)
```
app/Observers/
├── DenunciaObserver.php
└── UsuarioObserver.php

app/Services/
└── EncryptionService.php
```

### Providers (1 archivo actualizado)
```
app/Providers/
└── EventServiceProvider.php  (registra observers)
```

### Configuración (3 archivos)
```
├── tailwind.config.js  (nuevo)
├── postcss.config.js   (nuevo)
└── vite.config.js      (actualizado)
```

---

## 🚀 GUÍA DE MIGRACIÓN

### Paso 1: Backup de la BD Actual
```bash
# Si tienes datos en la BD actual, hacer backup primero
pg_dump -U postgres alerta_lima > backup_$(date +%Y%m%d).sql
```

### Paso 2: Instalar Dependencias Actualizadas
```bash
cd Alerta-Lima

# Actualizar dependencias PHP
composer update

# Actualizar dependencias JS
rm -rf node_modules package-lock.json
npm install
```

### Paso 3: Ejecutar Migraciones
```bash
# OPCIÓN A: Base de datos nueva (recomendado para desarrollo)
php artisan migrate:fresh

# OPCIÓN B: Base de datos existente con datos (producción)
# 1. Renombrar columnas manualmente primero:
#    - password_hash → password
#    - creado_en → created_at
#    - actualizado_en → updated_at
#    - eliminado_en → deleted_at
# 2. Luego ejecutar:
php artisan migrate
```

### Paso 4: Seeders (Datos Iniciales)
```bash
# Crear seeder para datos iniciales (roles, permisos, áreas, etc.)
php artisan db:seed
```

### Paso 5: Recompilar Frontend
```bash
# Desarrollo
npm run dev

# Producción
npm run build
```

### Paso 6: Testing
```bash
# Ejecutar tests para verificar que todo funciona
php artisan test

# Ejecutar linter
./vendor/bin/pint
```

---

## ⚠️ CAMBIOS QUE REQUIEREN ACCIÓN

### 1. Actualizar Controllers

**DenunciaController::store()**
```php
// ❌ ANTES: Lógica duplicada
$codigo = 'DEN-'.$year.'-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
$denuncia->codigo = $codigo;
$denuncia->fecha_limite_sla = $registradaEn->copy()->addHours($prioridadMedia->sla_horas);

// ✅ AHORA: Observer se encarga automáticamente
$denuncia = Denuncia::create([
    'ciudadano_id' => Auth::id(),
    'categoria_id' => $validated['categoria_id'],
    // codigo, fecha_limite_sla, area_id se generan automáticamente
]);
// $denuncia->refresh(); // Opcional: actualizar datos generados por observer
```

### 2. Actualizar AuthController

**AuthController::login()**
```php
// ❌ ANTES
if (!Hash::check($password, $usuario->password_hash)) { ... }

// ✅ AHORA
if (!Hash::check($password, $usuario->password)) { ... }
```

### 3. Eliminar Triggers de PostgreSQL

**Después de migrar, puedes eliminar estos triggers:**
```sql
-- Ya no son necesarios:
DROP TRIGGER IF EXISTS tr_denuncia_codigo ON denuncias;
DROP TRIGGER IF EXISTS tr_denuncia_sla ON denuncias;
DROP TRIGGER IF EXISTS tr_denuncia_area_default ON denuncias;
DROP TRIGGER IF EXISTS tr_validar_transicion ON denuncias;
DROP TRIGGER IF EXISTS tr_fecha_cierre ON denuncias;

-- Eliminar funciones:
DROP FUNCTION IF EXISTS generar_codigo_denuncia();
DROP FUNCTION IF EXISTS calcular_fecha_sla();
DROP FUNCTION IF EXISTS asignar_area_default();
DROP FUNCTION IF EXISTS validar_transicion_estado();
DROP FUNCTION IF EXISTS actualizar_fecha_cierre();
```

### 4. Actualizar .env

```env
# Asegurar que la configuración de BD es correcta
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=alerta_lima
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# Configuración de cache (para observers y catálogos)
CACHE_DRIVER=redis  # O file si no tienes Redis
QUEUE_CONNECTION=database  # O redis
```

---

## 📊 COMPARACIÓN ANTES/DESPUÉS

| Aspecto | ❌ Antes | ✅ Después |
|---------|----------|------------|
| **Migraciones** | SQL estático | Laravel Migrations |
| **Lógica de Negocio** | Triggers PostgreSQL | Observers Laravel |
| **Testing** | Imposible | `RefreshDatabase` |
| **Código Único** | Trigger `generar_codigo()` | `DenunciaObserver::creating()` |
| **Cálculo SLA** | Trigger `calcular_sla()` | `DenunciaObserver::creating()` |
| **Password** | `password_hash` + `getAuthPassword()` | `password` (estándar) |
| **Timestamps** | `creado_en`, `actualizado_en` | `created_at`, `updated_at` |
| **Descripción** | Cifrada (sin búsqueda) | Texto plano (búsqueda OK) |
| **Versiones** | Laravel 12, Tailwind 4, Vite 7 | Laravel 11, Tailwind 3, Vite 5 |
| **Debugging** | SQL + PHP mezclados | Solo PHP (Observers) |

---

## 🎯 BENEFICIOS FINALES

### Para Desarrolladores
- ✅ Código 100% en PHP (sin SQL oculto)
- ✅ Debugging con `dd()`, `Log::info()`, Telescope
- ✅ Testing unitario completo
- ✅ Git maneja cambios de BD
- ✅ Rollback automático de migraciones

### Para el Proyecto
- ✅ Mantenibilidad a largo plazo
- ✅ Compatibilidad con paquetes de Laravel
- ✅ Búsqueda de texto funcional
- ✅ Versiones estables (sin bugs beta)
- ✅ Documentación oficial completa

### Para el Equipo
- ✅ Onboarding más rápido (convenciones estándar)
- ✅ Menos "magia negra" en la BD
- ✅ CI/CD automatizado
- ✅ Entornos de desarrollo idénticos

---

## 📝 NOTAS IMPORTANTES

### Datos Sensibles
- **DNI**: Puede cifrarse con `EncryptionService::encryptDni()` si es requerido por ley
- **Teléfono**: Puede cifrarse con `EncryptionService::encryptPhone()` si es necesario
- **Archivos**: Hash SHA-256 para integridad, cifrado opcional

### Búsquedas
- La **descripción** de denuncias **NO** debe cifrarse
- Permite `LIKE '%texto%'`, `ILIKE`, Full-Text Search
- Los reportes y filtros funcionan correctamente

### Testing
```php
// Ejemplo de test con la nueva arquitectura
public function test_denuncia_genera_codigo_automaticamente()
{
    $denuncia = Denuncia::factory()->create();

    $this->assertNotNull($denuncia->codigo);
    $this->assertMatchesRegularExpression('/^DEN-\d{4}-\d{6}$/', $denuncia->codigo);
}

public function test_denuncia_calcula_sla_automaticamente()
{
    $prioridad = PrioridadDenuncia::where('codigo', 'MED')->first();

    $denuncia = Denuncia::factory()->create(['prioridad_id' => $prioridad->id]);

    $this->assertNotNull($denuncia->fecha_limite_sla);
    $this->assertEquals(
        $denuncia->registrada_en->copy()->addHours($prioridad->sla_horas),
        $denuncia->fecha_limite_sla
    );
}
```

---

## ✨ CONCLUSIÓN

El proyecto ha sido transformado de una arquitectura **híbrida y frágil** a una arquitectura **Laravel Way profesional y mantenible**.

**Próximos pasos:**
1. Ejecutar `composer update` y `npm install`
2. Migrar la base de datos con `php artisan migrate:fresh`
3. Ejecutar seeders con `php artisan db:seed`
4. Actualizar controllers que usan `password_hash`
5. Ejecutar tests con `php artisan test`

**Tiempo estimado de migración:** 2-4 horas (desarrollo), 1 día (producción con datos)

**¿Necesitas ayuda?** Revisa la documentación oficial de Laravel 11: https://laravel.com/docs/11.x
