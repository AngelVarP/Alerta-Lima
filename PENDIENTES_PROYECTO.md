# 📋 Tareas Pendientes - Alerta Lima

## ✅ **LO QUE YA ESTÁ COMPLETO (100%)**

### Backend
- ✅ 23 Modelos con relaciones completas
- ✅ 7 Controladores (Funcionario, Supervisor, Usuario, Auditoria, Reporte, Comentario, Denuncia)
- ✅ DenunciaPolicy con 12 métodos de autorización
- ✅ 2 Services (NotificacionService, SlaService)
- ✅ 4 Form Requests validados
- ✅ 3 Middlewares (CheckRole, VerificarRol, VerificarPermiso)
- ✅ 45+ rutas configuradas con protección de roles
- ✅ Sistema de autenticación con Usuario modelo custom

### Frontend
- ✅ 16 pantallas Vue3 + Inertia.js
- ✅ 2 Layouts (AuthenticatedLayout, AdminLayout)
- ✅ Dark mode completo
- ✅ Responsive design con Tailwind CSS 4
- ✅ Filtros avanzados con debounce
- ✅ Modales con validación
- ✅ Paginación integrada

---

## ❌ **LO QUE FALTA PARA PRODUCCIÓN**

### 1. **Base de Datos - CRÍTICO** 🔴

#### A. Seeders de Datos Iniciales
**Estado:** Parcial (solo CatalogSeeder, RoleSeeder)

**Faltan:**
```bash
php artisan make:seeder UsuarioSeeder        # Crear usuarios de prueba
php artisan make:seeder AreaSeeder           # Áreas municipales
php artisan make:seeder DistritoSeeder       # 43 distritos de Lima
php artisan make:seeder EstadoDenunciaSeeder # Estados del workflow
php artisan make:seeder PrioridadSeeder      # Prioridades con SLA
```

**Usuarios mínimos necesarios:**
- 1 Admin (admin@alerta-lima.pe)
- 1 Supervisor (supervisor@alerta-lima.pe)
- 2-3 Funcionarios (funcionario1@alerta-lima.pe)
- 2-3 Ciudadanos de prueba

#### B. Migración vs SQL Schema
**Problema:** El proyecto usa `schema_sgdc_v2.sql` directo, no migraciones Laravel.

**Opciones:**
1. ✅ **Usar SQL directo** (actual) - Rápido pero menos portable
2. ❌ Generar migraciones desde la BD existente - Más Laravel-friendly

**Recomendación:** Mantener SQL actual + documentar bien en README.

---

### 2. **Comandos Artisan para Tareas Programadas** 🟡

**Faltan:**

#### A. Comando de Verificación SLA
```bash
php artisan make:command VerificarSlaVencidos
```

**Función:**
- Buscar denuncias con SLA próximo a vencer (24h antes)
- Notificar a funcionarios asignados
- Marcar denuncias con SLA vencido
- Registrar en EventoSeguridad

**Programación:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Cada hora verificar SLA
    $schedule->command('sla:verificar')->hourly();

    // Limpiar notificaciones antiguas (>30 días)
    $schedule->command('notificaciones:limpiar')->daily();
}
```

#### B. Comando de Limpieza
```bash
php artisan make:command LimpiarNotificacionesAntiguas
```

**Función:**
- Eliminar notificaciones leídas mayores a 30 días
- Archivar registros de auditoría antiguos

---

### 3. **Jobs/Queues para Tareas Asíncronas** 🟡

**Faltan:**

```bash
php artisan make:job EnviarNotificacionEmail
php artisan make:job ProcesarAdjuntoDenuncia
php artisan make:job GenerarReportePDF
```

**Configurar en .env:**
```env
QUEUE_CONNECTION=database  # o redis en producción
```

**Crear tabla de jobs:**
```bash
php artisan queue:table
php artisan migrate
```

---

### 4. **Testing - IMPORTANTE** 🟠

**Estado:** Solo tests de ejemplo

**Faltan:**

#### A. Feature Tests
```bash
php artisan make:test DenunciaControllerTest
php artisan make:test FuncionarioControllerTest
php artisan make:test SupervisorControllerTest
php artisan make:test UsuarioControllerTest
php artisan make:test AuthorizationTest
```

**Tests mínimos necesarios:**
- ✅ Ciudadano puede crear denuncia
- ✅ Funcionario puede ver denuncias de su área
- ✅ Funcionario NO puede ver denuncias de otras áreas
- ✅ Supervisor puede asignar/reasignar denuncias
- ✅ Admin puede gestionar usuarios
- ✅ Transiciones de estado correctas
- ✅ SLA se calcula correctamente

#### B. Unit Tests
```bash
php artisan make:test SlaServiceTest --unit
php artisan make:test NotificacionServiceTest --unit
php artisan make:test DenunciaPolicyTest --unit
```

---

### 5. **Factories para Testing** 🟢

**Faltan:**

```bash
php artisan make:factory UsuarioFactory
php artisan make:factory DenunciaFactory
php artisan make:factory ComentarioFactory
php artisan make:factory AreaFactory
```

**Uso:**
```php
// En tests
Usuario::factory()->count(10)->create();
Denuncia::factory()->conEstado('PRO')->create();
```

---

### 6. **Configuración de Producción** 🔴

#### A. Variables de Entorno (.env)

**Actualizar .env.example con:**
```env
# APP
APP_NAME="Alerta Lima"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://alerta-lima.gob.pe

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=alerta_lima_prod
DB_USERNAME=postgres
DB_PASSWORD=

# Mail (para notificaciones)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@alerta-lima.gob.pe"
MAIL_FROM_NAME="Alerta Lima"

# Queue
QUEUE_CONNECTION=redis  # producción
# QUEUE_CONNECTION=database  # desarrollo

# Session (producción)
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Cache (producción)
CACHE_DRIVER=redis

# File Storage
FILESYSTEM_DISK=public  # o s3 para producción
```

#### B. Permisos de Archivos
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### C. Optimizaciones
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
npm run build
```

---

### 7. **Sistema de Notificaciones** 🟡

**Estado:** Modelo y Service creados, falta implementación completa

**Pendiente:**

#### A. Notificaciones Email
```bash
php artisan make:notification DenunciaCreada
php artisan make:notification DenunciaAsignada
php artisan make:notification EstadoCambiado
php artisan make:notification SlaProximoVencer
```

#### B. Notificaciones en Tiempo Real (Opcional)
- Laravel Broadcasting con Pusher/Socket.io
- Notificaciones push para funcionarios

**Configurar:**
```bash
composer require pusher/pusher-php-server
```

---

### 8. **Validaciones y Seguridad** 🔴

#### A. CSRF Protection
✅ Ya configurado en Laravel

#### B. Rate Limiting
**Agregar en routes/web.php:**
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Rutas públicas
});

Route::middleware(['throttle:api'])->group(function () {
    // APIs si las hay
});
```

#### C. Validación de Archivos Adjuntos
**En DenunciaController:**
```php
$request->validate([
    'adjuntos.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
]);

// Escanear archivos con antivirus (opcional)
```

#### D. Sanitización de Inputs
✅ Laravel sanitiza automáticamente, pero revisar:
- XSS en comentarios
- SQL Injection (usar Eloquent, no raw queries)

---

### 9. **Documentación** 🟢

**Actualizar README.md con:**

#### A. Sección de Instalación Completa
```markdown
## Instalación Paso a Paso

### 1. Requisitos Previos
- Lista detallada

### 2. Clonar y Configurar
- Paso a paso con capturas

### 3. Base de Datos
- Cómo ejecutar schema_sgdc_v2.sql
- Cómo correr seeders

### 4. Usuarios de Prueba
- Listado de usuarios creados por seeders
- Credenciales de acceso

### 5. Troubleshooting
- Errores comunes y soluciones
```

#### B. Documentación de API (si aplica)
```bash
composer require darkaonline/l5-swagger
```

#### C. Manual de Usuario
- Guía para ciudadanos
- Guía para funcionarios
- Guía para supervisores
- Guía para administradores

---

### 10. **Mejoras Opcionales (No Críticas)** 🟢

#### A. Reportes PDF
```bash
composer require barryvdh/laravel-dompdf
```

**Actualizar ReporteController::exportarPDF()** - Actualmente retorna JSON.

#### B. Mapa Interactivo
- Integrar Google Maps/OpenStreetMap
- Marcar denuncias por ubicación GPS
- Filtrar por zona geográfica

#### C. Dashboard con Gráficos
- Chart.js o ApexCharts
- Gráficos de tendencias
- Métricas en tiempo real

#### D. Export Excel (además de CSV)
```bash
composer require maatwebsite/excel
```

#### E. Log Viewer para Admin
```bash
composer require rap2hpoutre/laravel-log-viewer
```

---

## 📊 **PRIORIDADES RECOMENDADAS**

### 🔴 **CRÍTICO (Hacer YA):**
1. Seeders de datos iniciales (usuarios, áreas, estados)
2. Configuración .env de producción
3. Testing básico (al menos 10 tests principales)
4. Documentación de instalación actualizada

### 🟠 **IMPORTANTE (Esta Semana):**
5. Comando SLA vencidos
6. Jobs para emails asíncronos
7. Rate limiting
8. Optimizaciones de producción

### 🟡 **DESEABLE (Próximas 2 Semanas):**
9. Notificaciones email completas
10. Factories completos
11. Suite completa de tests (30+ tests)
12. Manual de usuario

### 🟢 **OPCIONAL (Mejoras Futuras):**
13. Reportes PDF mejorados
14. Mapa interactivo
15. Gráficos avanzados
16. Export Excel

---

## ✅ **CHECKLIST DE DESPLIEGUE**

Antes de subir a producción:

```
[ ] .env configurado correctamente
[ ] php artisan config:cache ejecutado
[ ] php artisan route:cache ejecutado
[ ] php artisan view:cache ejecutado
[ ] npm run build ejecutado
[ ] Seeders ejecutados (usuarios, roles, áreas, estados)
[ ] Al menos 10 tests pasando
[ ] Logs configurados
[ ] Backups automáticos configurados
[ ] SSL/HTTPS configurado
[ ] Firewall configurado
[ ] Monitoreo configurado (Sentry, New Relic, etc.)
[ ] Variables secretas en .env no commiteadas
[ ] README.md actualizado
[ ] CHANGELOG.md actualizado
```

---

## 🎯 **RESUMEN EJECUTIVO**

### Para que el proyecto esté "TERMINADO" y funcional:

**Mínimo Viable (1-2 días):**
- ✅ Seeders de datos iniciales
- ✅ 5-10 tests básicos
- ✅ README actualizado
- ✅ Configuración .env ejemplo completa

**Para Producción (1 semana):**
- ✅ Todo lo anterior +
- ✅ Comando SLA vencidos
- ✅ Jobs asíncronos básicos
- ✅ Rate limiting
- ✅ 20+ tests
- ✅ Validaciones de seguridad

**Proyecto Completo (2-3 semanas):**
- ✅ Todo lo anterior +
- ✅ Suite completa de tests (50+ tests)
- ✅ Notificaciones email funcionando
- ✅ Reportes PDF
- ✅ Manual de usuario
- ✅ Dashboard con gráficos

---

**Fecha de documento:** 2025-12-04
**Versión:** 1.0
**Autor:** Claude Code
