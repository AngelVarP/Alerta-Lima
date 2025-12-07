#!/bin/bash

# Set environment variables explicitly
export DB_CONNECTION=pgsql
export DB_HOST=127.0.0.1
export DB_PORT=5432
export DB_DATABASE=alerta_lima
export DB_USERNAME=postgres
export DB_PASSWORD=password

echo "================================================"
echo "  INICIANDO ALERTA LIMA EN MODO DESARROLLO"
echo "================================================"
echo ""

cd "/mnt/d/UNI/CICLO 5/ANALISIS Y MODELAMIENTO DEL COMPORTAMIENTO/Presentable4/Alerta-Lima"

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
echo "🚀 Iniciando servicios..."
echo ""

# Run composer dev (which starts server, queue, pail, and vite)
composer dev
