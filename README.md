# Alerta Lima - Sistema de Gestión de Denuncias Ciudadanas (SGDC)

Sistema web para la gestión de denuncias ciudadanas de la Municipalidad de Lima, desarrollado con Laravel 12 y PostgreSQL.

## Requisitos

- PHP 8.2+
- Composer
- PostgreSQL 16+
- Node.js 18+
- Laragon (recomendado para Windows)

## Instalación

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd Alerta-Lima
```

### 2. Instalar dependencias
```bash
composer install
npm install
```

### 3. Configurar el entorno
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con las credenciales de PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=alerta_lima
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 4. Crear la base de datos
Ejecutar el schema SQL en PostgreSQL:
```
Segurity/schema_sgdc_v2.sql
```

### 5. Iniciar el servidor
```bash
php artisan serve
```

Acceder a: http://localhost:8000

## Estructura del Proyecto

### Modelos (app/Models/)
| Modelo | Descripción |
|--------|-------------|
| `Usuario` | Usuarios del sistema (ciudadanos, funcionarios, admin) |
| `Denuncia` | Denuncias ciudadanas |
| `Area` | Áreas municipales (Limpieza, Seguridad, etc.) |
| `Rol`, `Permiso` | Sistema de roles y permisos |
| `CategoriaDenuncia` | Categorías (Basura, Alumbrado, Baches, etc.) |
| `EstadoDenuncia` | Estados (Registrada, En Proceso, Atendida, etc.) |
| `PrioridadDenuncia` | Prioridades con SLA |
| `Distrito` | Distritos de Lima |
| `Comentario`, `Adjunto` | Comentarios y archivos adjuntos |
| `Notificacion` | Sistema de notificaciones |
| `HistorialEstadoDenuncia`, `HistorialAsignacion` | Trazabilidad |
| `RegistroAuditoria`, `EventoSeguridad` | Logs de seguridad |

### Controladores (app/Http/Controllers/)
- `DashboardController` - Panel principal con estadísticas
- `DenunciaController` - CRUD de denuncias
- `UsuarioController` - Gestión de usuarios (admin)

### Rutas Principales
| Ruta | Descripción | Acceso |
|------|-------------|--------|
| `/` | Página de inicio | Público |
| `/login`, `/register` | Autenticación | Público |
| `/dashboard` | Panel principal | Autenticado |
| `/denuncias` | Listado de denuncias | Autenticado |
| `/denuncias/crear` | Nueva denuncia | Autenticado |
| `/admin/usuarios` | Gestión de usuarios | Admin/Supervisor |
| `/admin/auditoria` | Logs de auditoría | Admin |

### Roles del Sistema
- **ciudadano** - Puede crear y dar seguimiento a sus denuncias
- **funcionario** - Gestiona denuncias de su área
- **supervisor** - Puede reasignar y ver reportes
- **admin** - Acceso total al sistema

### Middlewares de Autorización
```php
// Verificar rol
Route::middleware(['rol:admin,supervisor'])->group(...);

// Verificar permiso
Route::middleware(['permiso:crear_denuncia'])->group(...);
```

## Stack Tecnológico

- **Backend**: Laravel 12, PHP 8.2+
- **Base de Datos**: PostgreSQL 16
- **Autenticación**: Laravel Fortify (con soporte 2FA)
- **Frontend**: Tailwind CSS 4, Vite 7
- **Testing**: PHPUnit 11

## Comandos Útiles

```bash
# Servidor de desarrollo
php artisan serve

# Compilar assets (desarrollo)
npm run dev

# Compilar assets (producción)
npm run build

# Ejecutar tests
php artisan test

# Formatear código
./vendor/bin/pint

# Ver rutas
php artisan route:list

# Limpiar caché
php artisan optimize:clear
```

## Licencia

MIT
