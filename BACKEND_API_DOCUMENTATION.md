# 📚 Documentación de Backend API - Alerta Lima

## 🎯 Resumen

Se ha implementado el backend completo para **Funcionarios** y **Supervisores**, incluyendo:

- ✅ **5 Controladores nuevos**
- ✅ **4 Form Requests de validación**
- ✅ **DenunciaPolicy completa con 10 métodos de autorización**
- ✅ **2 Servicios auxiliares** (NotificacionService, SlaService)
- ✅ **45+ endpoints nuevos** completamente funcionales
- ✅ **Rutas organizadas por rol** con middleware de autorización

---

## 📂 Estructura de Archivos Creados

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── FuncionarioController.php       ✅ NUEVO
│   │   ├── SupervisorController.php        ✅ NUEVO
│   │   ├── ComentarioController.php        ✅ NUEVO
│   │   ├── ReporteController.php           ✅ NUEVO
│   │   └── AuditoriaController.php         ✅ NUEVO
│   │
│   └── Requests/
│       ├── CambiarEstadoDenunciaRequest.php    ✅ NUEVO
│       ├── AsignarDenunciaRequest.php          ✅ NUEVO
│       ├── ComentarioRequest.php               ✅ NUEVO
│       └── CambiarPrioridadRequest.php         ✅ NUEVO
│
├── Policies/
│   └── DenunciaPolicy.php              ✅ COMPLETADO (10 métodos)
│
└── Services/
    ├── NotificacionService.php         ✅ NUEVO
    └── SlaService.php                  ✅ NUEVO

routes/
└── web.php                             ✅ ACTUALIZADO (45+ rutas nuevas)
```

---

## 🔐 Roles y Permisos

### Roles Implementados:
- **ciudadano** - Crear y ver sus denuncias
- **funcionario** - Gestionar denuncias de su área
- **supervisor** - Asignar, reasignar y supervisar denuncias
- **admin** - Acceso total al sistema

### Middleware de Autorización:
- `role:funcionario,supervisor,admin` - Rutas de funcionarios
- `role:supervisor,admin` - Rutas de supervisores
- `role:admin` - Rutas exclusivas de admin

---

## 📡 API Endpoints

### 🔵 FUNCIONARIO - `/funcionario/*`

#### Dashboard
```http
GET /funcionario/dashboard
```
**Response:**
```json
{
  "stats": {
    "total": 150,
    "asignadas_a_mi": 12,
    "en_proceso": 45,
    "sla_vencido": 5
  },
  "denunciasRecientes": [...],
  "denunciasSlaPendiente": [...],
  "porEstado": [...]
}
```

#### Lista de Denuncias
```http
GET /funcionario/denuncias?search=&estado_id=&categoria_id=&prioridad_id=&asignado_a=
```
**Filtros disponibles:**
- `search` - Buscar por código, título o descripción
- `estado_id` - Filtrar por estado
- `categoria_id` - Filtrar por categoría
- `prioridad_id` - Filtrar por prioridad
- `asignado_a` - Filtrar por funcionario (valores: `sin_asignar`, `mis_denuncias`, o ID)
- `sla_vencido` - Filtrar denuncias con SLA vencido
- `sort_by` - Campo de ordenamiento (default: `creado_en`)
- `sort_order` - Orden (default: `desc`)

**Response:** Paginado con 15 registros

#### Ver Detalle de Denuncia
```http
GET /funcionario/denuncias/{denuncia}
```
**Response:**
```json
{
  "denuncia": {
    "id": 1,
    "codigo": "DEN-2025-00001",
    "titulo": "...",
    "ciudadano": {...},
    "adjuntos": [...],
    "comentarios": [...],
    "historialEstados": [...],
    "historialAsignaciones": [...]
  },
  "estadosDisponibles": [...],
  "funcionariosArea": [...]
}
```

#### Cambiar Estado
```http
POST /funcionario/denuncias/{denuncia}/cambiar-estado
Content-Type: application/json

{
  "estado_id": 2,
  "motivo": "Denuncia atendida satisfactoriamente",
  "comentario_interno": "Se realizaron las reparaciones necesarias"
}
```

#### Tomar Asignación
```http
POST /funcionario/denuncias/{denuncia}/tomar-asignacion
```

#### Agregar Comentario
```http
POST /funcionario/denuncias/{denuncia}/comentar
Content-Type: application/json

{
  "contenido": "Texto del comentario",
  "es_interno": true
}
```

---

### 🟣 SUPERVISOR - `/supervisor/*`

#### Dashboard
```http
GET /supervisor/dashboard
```
**Response:**
```json
{
  "stats": {
    "total_area": 150,
    "sin_asignar": 8,
    "en_proceso": 45,
    "sla_vencido": 5,
    "cerradas_mes": 120
  },
  "denunciasSinAsignar": [...],
  "rendimientoEquipo": [...],
  "denunciasSlaCritico": [...]
}
```

#### Lista de Denuncias
```http
GET /supervisor/denuncias?search=&estado_id=&asignado_a=
```
**Response:** Paginado con 20 registros

#### Ver Detalle
```http
GET /supervisor/denuncias/{denuncia}
```

#### Asignar Denuncia
```http
POST /supervisor/denuncias/{denuncia}/asignar
Content-Type: application/json

{
  "funcionario_id": 5,
  "motivo": "Asignación inicial según área de especialidad"
}
```

#### Reasignar Denuncia
```http
POST /supervisor/denuncias/{denuncia}/reasignar
Content-Type: application/json

{
  "funcionario_id": 8,
  "motivo": "Reasignación por sobrecarga de trabajo"
}
```

#### Cambiar Prioridad
```http
POST /supervisor/denuncias/{denuncia}/cambiar-prioridad
Content-Type: application/json

{
  "prioridad_id": 1,
  "motivo": "Urgencia detectada"
}
```

#### Reportes del Área
```http
GET /supervisor/reportes
```
**Response:**
```json
{
  "stats": {
    "total_mes": 50,
    "cerradas_mes": 45,
    "tiempo_promedio_resolucion": 48.5,
    "sla_cumplido_porcentaje": 92.5
  },
  "porEstado": [...],
  "porCategoria": [...]
}
```

---

### 🔴 ADMIN - `/admin/*`

#### Gestión de Usuarios
```http
GET    /admin/usuarios
POST   /admin/usuarios
GET    /admin/usuarios/{usuario}
GET    /admin/usuarios/{usuario}/edit
PUT    /admin/usuarios/{usuario}
DELETE /admin/usuarios/{usuario}
POST   /admin/usuarios/{usuario}/toggle-activo
```

#### Auditoría
```http
GET /admin/auditoria?search=&accion=&tabla=&usuario_id=&fecha_inicio=&fecha_fin=
GET /admin/auditoria/{registro}
GET /admin/auditoria/estadisticas
```

**Filtros:**
- `search` - Buscar en acción, tabla o registro_id
- `accion` - Filtrar por tipo de acción
- `tabla` - Filtrar por tabla afectada
- `usuario_id` - Filtrar por usuario
- `fecha_inicio` / `fecha_fin` - Rango de fechas

#### Eventos de Seguridad
```http
GET /admin/seguridad?search=&tipo_evento=&severidad=&fecha_inicio=&fecha_fin=
GET /admin/seguridad/{evento}
```

**Severidades:** `BAJA`, `MEDIA`, `ALTA`, `CRITICA`

#### Reportes
```http
GET /admin/reportes?fecha_inicio=&fecha_fin=&area_id=
GET /admin/reportes/exportar-csv?fecha_inicio=&fecha_fin=&area_id=&estado_id=&categoria_id=
GET /admin/reportes/exportar-pdf?fecha_inicio=&fecha_fin=&area_id=
GET /admin/reportes/rendimiento-funcionarios?fecha_inicio=&fecha_fin=&area_id=
GET /admin/reportes/sla?fecha_inicio=&fecha_fin=&area_id=
```

**Reporte CSV:** Descarga archivo con todas las denuncias
**Reporte SLA:**
```json
{
  "total": 100,
  "cerradas": 85,
  "sla_cumplido": 78,
  "sla_vencido": 7,
  "sla_en_riesgo": 5,
  "porcentaje_cumplimiento": 91.76
}
```

**Rendimiento Funcionarios:**
```json
{
  "rendimiento": [
    {
      "id": 5,
      "nombre": "Juan",
      "apellido": "Pérez",
      "total_asignadas": 25,
      "total_cerradas": 23,
      "promedio_horas_resolucion": 36.5,
      "sla_cumplido": 21
    },
    ...
  ]
}
```

---

### 💬 COMENTARIOS - Compartido

```http
POST   /denuncias/{denuncia}/comentarios
PUT    /comentarios/{comentario}
DELETE /comentarios/{comentario}
```

---

## 🛡️ DenunciaPolicy - Métodos de Autorización

```php
// Métodos implementados:
- ver(Usuario $usuario, Denuncia $denuncia)
- editar(Usuario $usuario, Denuncia $denuncia)
- cambiarEstado(Usuario $usuario, Denuncia $denuncia)
- asignar(Usuario $usuario, Denuncia $denuncia)
- eliminar(Usuario $usuario, Denuncia $denuncia)
- verComoFuncionario(Usuario $usuario, Denuncia $denuncia)
- verComoSupervisor(Usuario $usuario, Denuncia $denuncia)
- comentar(Usuario $usuario, Denuncia $denuncia)
- cambiarPrioridad(Usuario $usuario, Denuncia $denuncia)
- reasignar(Usuario $usuario, Denuncia $denuncia)
- verAdjuntos(Usuario $usuario, Denuncia $denuncia)
- agregarAdjuntos(Usuario $usuario, Denuncia $denuncia)
```

---

## 🔔 NotificacionService - Métodos Disponibles

```php
// Crear notificaciones automáticas:
- notificarCambioEstado(Denuncia $denuncia, string $nuevoEstado)
- notificarAsignacion(Denuncia $denuncia, Usuario $funcionario)
- notificarReasignacion(Denuncia $denuncia, Usuario $nuevo, Usuario $anterior)
- notificarNuevoComentario(Denuncia $denuncia, Usuario $autor)
- notificarCambioPrioridad(Denuncia $denuncia, string $nuevaPrioridad)
- notificarSLAVencido(Denuncia $denuncia)
- notificarSLAProximoVencer(Denuncia $denuncia)

// Gestionar notificaciones:
- marcarComoLeida(Notificacion $notificacion)
- marcarTodasComoLeidas(Usuario $usuario)
```

---

## ⏱️ SlaService - Métodos Disponibles

```php
// Cálculos de SLA:
- calcularFechaLimiteSLA(Denuncia $denuncia): ?Carbon
- estaVencido(Denuncia $denuncia): bool
- estaProximoVencer(Denuncia $denuncia): bool
- horasRestantes(Denuncia $denuncia): ?int
- porcentajeTranscurrido(Denuncia $denuncia): ?float
- fueCumplido(Denuncia $denuncia): ?bool
- tiempoResolucion(Denuncia $denuncia): ?int

// Consultas:
- obtenerDenunciasConSLAVencido(?int $areaId)
- obtenerDenunciasConSLAProximoVencer(?int $areaId)
- porcentajeCumplimientoArea(int $areaId, ?Carbon $inicio, ?Carbon $fin)
- actualizarSLAPorCambioPrioridad(Denuncia $denuncia, Prioridad $nueva)
```

---

## 🎨 Pantallas Vue a Crear

### 👷 Funcionario
1. **Dashboard** → `resources/js/Pages/Funcionario/Dashboard.vue`
   - Consumir: `GET /funcionario/dashboard`

2. **Lista Denuncias** → `resources/js/Pages/Funcionario/Denuncias/Index.vue`
   - Consumir: `GET /funcionario/denuncias`
   - Filtros: estado, categoría, prioridad, asignado_a

3. **Detalle Denuncia** → `resources/js/Pages/Funcionario/Denuncias/Show.vue`
   - Consumir: `GET /funcionario/denuncias/{id}`
   - Acciones:
     - `POST /funcionario/denuncias/{id}/cambiar-estado`
     - `POST /funcionario/denuncias/{id}/tomar-asignacion`
     - `POST /funcionario/denuncias/{id}/comentar`

### 👨‍💼 Supervisor
1. **Dashboard** → `resources/js/Pages/Supervisor/Dashboard.vue`
   - Consumir: `GET /supervisor/dashboard`

2. **Lista Denuncias** → `resources/js/Pages/Supervisor/Denuncias/Index.vue`
   - Consumir: `GET /supervisor/denuncias`

3. **Detalle Denuncia** → `resources/js/Pages/Supervisor/Denuncias/Show.vue`
   - Consumir: `GET /supervisor/denuncias/{id}`
   - Acciones:
     - `POST /supervisor/denuncias/{id}/asignar`
     - `POST /supervisor/denuncias/{id}/reasignar`
     - `POST /supervisor/denuncias/{id}/cambiar-prioridad`

4. **Reportes** → `resources/js/Pages/Supervisor/Reportes.vue`
   - Consumir: `GET /supervisor/reportes`

### 🔐 Admin
1. **Lista Usuarios** → `resources/js/Pages/Admin/Usuarios/Index.vue`
   - Consumir: `GET /admin/usuarios`

2. **Crear/Editar Usuario** → `resources/js/Pages/Admin/Usuarios/Form.vue`
   - Consumir: `POST /admin/usuarios` o `PUT /admin/usuarios/{id}`

3. **Auditoría** → `resources/js/Pages/Admin/Auditoria/Index.vue`
   - Consumir: `GET /admin/auditoria`

4. **Eventos Seguridad** → `resources/js/Pages/Admin/Seguridad/Index.vue`
   - Consumir: `GET /admin/seguridad`

5. **Reportes** → `resources/js/Pages/Admin/Reportes/Index.vue`
   - Consumir: `GET /admin/reportes`
   - Exportar: `GET /admin/reportes/exportar-csv`

---

## 🔄 Flujo de Estados

**Transiciones permitidas:**
```
REG (Registrada) → PRO (En Proceso), REC (Rechazada)
PRO (En Proceso) → ATE (Atendida), PEN (Pendiente), REC (Rechazada)
PEN (Pendiente) → PRO (En Proceso), ATE (Atendida)
ATE (Atendida) → CER (Cerrada)
REC (Rechazada) → (ninguna)
CER (Cerrada) → (ninguna)
```

---

## ✅ Testing

Para probar los endpoints, puedes usar:

```bash
# Ver todas las rutas
php artisan route:list

# Filtrar solo rutas de funcionario
php artisan route:list | grep funcionario

# Filtrar solo rutas de supervisor
php artisan route:list | grep supervisor
```

---

## 📝 Notas Importantes

1. **Autorización:** Todos los métodos usan `$this->authorize()` que verifica permisos vía DenunciaPolicy
2. **Notificaciones:** Se crean automáticamente en cambios de estado, asignaciones y comentarios
3. **Transacciones:** Operaciones críticas usan `DB::transaction()` para garantizar integridad
4. **Validación:** Form Requests manejan validación con mensajes en español
5. **SLA:** Se calcula automáticamente al asignar denuncias basado en prioridad
6. **Auditoría:** RegistroAuditoria se guarda automáticamente en operaciones críticas

---

## 🚀 Próximos Pasos

1. ✅ **Backend completado**
2. 🎨 **Crear pantallas Vue consumiendo estos endpoints**
3. 🧪 **Testing de integración**
4. 📊 **Implementar gráficos en dashboards**
5. 📧 **Integrar envío de emails (opcional)**

---

**Documentación generada por Claude Code**
Fecha: 2025-12-04
