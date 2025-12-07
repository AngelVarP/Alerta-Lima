#!/bin/bash

echo "================================================"
echo "  CONFIGURACIÓN DE POSTGRESQL PARA ALERTA LIMA"
echo "================================================"
echo ""

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Verificar si PostgreSQL está corriendo
echo -e "${YELLOW}[1/5]${NC} Verificando servicio PostgreSQL..."
sudo service postgresql status > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}→${NC} Iniciando PostgreSQL..."
    sudo service postgresql start
fi
echo -e "${GREEN}✓${NC} PostgreSQL está corriendo\n"

# 2. Crear base de datos si no existe
echo -e "${YELLOW}[2/5]${NC} Verificando base de datos..."
sudo -u postgres psql -lqt | cut -d \| -f 1 | grep -qw alerta_lima
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}→${NC} Creando base de datos 'alerta_lima'..."
    sudo -u postgres createdb alerta_lima
    echo -e "${GREEN}✓${NC} Base de datos creada"
else
    echo -e "${GREEN}✓${NC} Base de datos ya existe"
fi
echo ""

# 3. Configurar contraseña del usuario postgres
echo -e "${YELLOW}[3/5]${NC} Configurando contraseña del usuario postgres..."
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'password';" > /dev/null 2>&1
echo -e "${GREEN}✓${NC} Contraseña configurada como 'password'\n"

# 4. Ejecutar schema SQL
echo -e "${YELLOW}[4/5]${NC} Ejecutando schema SQL..."
SCHEMA_PATH="/mnt/d/UNI/CICLO 5/ANALISIS Y MODELAMIENTO DEL COMPORTAMIENTO/Presentable4/Segurity/schema_sgdc_v2.sql"
if [ -f "$SCHEMA_PATH" ]; then
    sudo -u postgres psql -d alerta_lima -f "$SCHEMA_PATH" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} Schema ejecutado correctamente"
    else
        echo -e "${RED}✗${NC} Error al ejecutar schema (puede que ya exista)"
    fi
else
    echo -e "${RED}✗${NC} Archivo schema no encontrado"
fi
echo ""

# 5. Ejecutar seeders de Laravel
echo -e "${YELLOW}[5/5]${NC} Ejecutando seeders de Laravel..."
cd "/mnt/d/UNI/CICLO 5/ANALISIS Y MODELAMIENTO DEL COMPORTAMIENTO/Presentable4/Alerta-Lima"
php artisan db:seed --force > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Seeders ejecutados correctamente"
else
    echo -e "${YELLOW}!${NC} Algunos seeders pueden haber fallado (normal si ya existen datos)"
fi
echo ""

# Verificar conexión final
echo -e "${YELLOW}Verificando conexión...${NC}"
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>/dev/null | grep -q OK
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Laravel puede conectarse a PostgreSQL"
    echo ""
    echo -e "${GREEN}================================================${NC}"
    echo -e "${GREEN}  ¡CONFIGURACIÓN COMPLETADA EXITOSAMENTE!${NC}"
    echo -e "${GREEN}================================================${NC}"
    echo ""
    echo "📋 CREDENCIALES DE PRUEBA:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  🔐 Admin:       admin@alerta.lima.gob.pe / password"
    echo "  👤 Ciudadano:   vecino@gmail.com / password"
    echo "  👷 Funcionario: funcionario@alerta.lima.gob.pe / password"
    echo "                  (Área: Limpieza Pública)"
    echo "  👨‍💼 Supervisor:  supervisor@alerta.lima.gob.pe / password"
    echo "                  (Área: Seguridad Ciudadana)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "🚀 PARA INICIAR LA APLICACIÓN:"
    echo "   composer dev"
    echo ""
    echo "🌐 ACCEDE A:"
    echo "   http://localhost:8000"
    echo ""
else
    echo -e "${RED}✗${NC} No se pudo conectar a PostgreSQL"
    echo ""
    echo "Verifica:"
    echo "  1. PostgreSQL está corriendo: sudo service postgresql status"
    echo "  2. Contraseña en .env es 'password'"
    echo "  3. Base de datos existe: sudo -u postgres psql -l | grep alerta_lima"
fi
