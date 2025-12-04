<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    denunciasRecientes: {
        type: Array,
        default: () => []
    },
    denunciasSlaPendiente: {
        type: Array,
        default: () => []
    },
    porEstado: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-PE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const getEstadoColor = (codigo) => {
    const colores = {
        'REG': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'PRO': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'PEN': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'ATE': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'REC': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'CER': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return colores[codigo] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getPrioridadColor = (codigo) => {
    const colores = {
        'ALT': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'MED': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'BAJ': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    };
    return colores[codigo] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};
</script>

<template>
    <Head title="Dashboard Funcionario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                Dashboard Funcionario
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    ¡Bienvenido, {{ $page.props.auth.user.nombre }}! 👷
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Panel de control para la gestión de denuncias de tu área
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total del Área -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">📋</span>
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">Total</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.total }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Denuncias del Área</p>
                </div>

                <!-- Asignadas a Mí -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">👤</span>
                        </div>
                        <span class="text-xs font-medium text-purple-700 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30 px-3 py-1 rounded-full">Asignadas</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.asignadas_a_mi }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Asignadas a Mí</p>
                </div>

                <!-- En Proceso -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">⚙️</span>
                        </div>
                        <span class="text-xs font-medium text-yellow-700 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30 px-3 py-1 rounded-full">Activas</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.en_proceso }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">En Proceso</p>
                </div>

                <!-- SLA Vencido -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-red-200 dark:border-red-900/30 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <span class="text-xs font-medium text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/30 px-3 py-1 rounded-full">Urgente</span>
                    </div>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-1">{{ stats.sla_vencido }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">SLA Vencido</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link href="/funcionario/denuncias?asignado_a=sin_asignar" class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">📥</span>
                        <span class="font-medium">Denuncias Sin Asignar</span>
                    </Link>
                    <Link href="/funcionario/denuncias?asignado_a=mis_denuncias" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">📊</span>
                        <span class="font-medium">Mis Denuncias</span>
                    </Link>
                    <Link href="/funcionario/denuncias?sla_vencido=1" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">⏰</span>
                        <span class="font-medium">SLA Crítico</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Denuncias Recientes -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Denuncias Recientes</h2>
                        <Link href="/funcionario/denuncias" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todas</Link>
                    </div>

                    <div v-if="denunciasRecientes.length === 0" class="text-center py-12">
                        <span class="text-6xl mb-4 block">📭</span>
                        <p class="text-gray-500 dark:text-gray-400">No hay denuncias recientes</p>
                    </div>

                    <div v-else class="space-y-3">
                        <Link
                            v-for="denuncia in denunciasRecientes.slice(0, 5)"
                            :key="denuncia.id"
                            :href="`/funcionario/denuncias/${denuncia.id}`"
                            class="block p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400">
                                    {{ denuncia.codigo }}
                                </span>
                                <span :class="getEstadoColor(denuncia.estado?.codigo)" class="px-2 py-1 rounded-lg text-xs font-medium">
                                    {{ denuncia.estado?.nombre }}
                                </span>
                            </div>
                            <h3 class="font-medium text-gray-900 dark:text-white text-sm mb-1 truncate">
                                {{ denuncia.titulo }}
                            </h3>
                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ denuncia.categoria?.nombre }}</span>
                                <span>•</span>
                                <span>{{ formatDate(denuncia.creado_en) }}</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- SLA Crítico -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-red-200 dark:border-red-900/30">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">⚠️ SLA Vencido</h2>
                        <Link href="/funcionario/denuncias?sla_vencido=1" class="text-sm text-red-600 dark:text-red-400 hover:underline">Ver todas</Link>
                    </div>

                    <div v-if="denunciasSlaPendiente.length === 0" class="text-center py-12">
                        <span class="text-6xl mb-4 block">✅</span>
                        <p class="text-gray-500 dark:text-gray-400">¡Excelente! No hay SLA vencido</p>
                    </div>

                    <div v-else class="space-y-3">
                        <Link
                            v-for="denuncia in denunciasSlaPendiente"
                            :key="denuncia.id"
                            :href="`/funcionario/denuncias/${denuncia.id}`"
                            class="block p-4 bg-red-50 dark:bg-red-900/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/20 transition-colors border border-red-200 dark:border-red-900/30"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-mono font-semibold text-red-600 dark:text-red-400">
                                    {{ denuncia.codigo }}
                                </span>
                                <span :class="getPrioridadColor(denuncia.prioridad?.codigo)" class="px-2 py-1 rounded-lg text-xs font-medium">
                                    {{ denuncia.prioridad?.nombre }}
                                </span>
                            </div>
                            <h3 class="font-medium text-gray-900 dark:text-white text-sm mb-1 truncate">
                                {{ denuncia.titulo }}
                            </h3>
                            <p class="text-xs text-red-600 dark:text-red-400">
                                SLA vencido • {{ denuncia.categoria?.nombre }}
                            </p>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Estadísticas por Estado -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Distribución por Estado</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div
                        v-for="estado in porEstado"
                        :key="estado.id"
                        class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-center"
                    >
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ estado.denuncias_count || 0 }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ estado.nombre }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
