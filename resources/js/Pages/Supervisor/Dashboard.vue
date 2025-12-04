<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    denunciasSinAsignar: {
        type: Array,
        default: () => []
    },
    rendimientoEquipo: {
        type: Array,
        default: () => []
    },
    denunciasSlaCritico: {
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
    <Head title="Dashboard Supervisor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                Dashboard Supervisor
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-6 shadow-lg text-white">
                <h1 class="text-2xl font-bold mb-2">
                    ¡Bienvenido, {{ $page.props.auth.user.nombre }}! 👨‍💼
                </h1>
                <p class="opacity-90">
                    Panel de supervisión y control del área
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Total del Área -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.total_area }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total del Área</p>
                </div>

                <!-- Sin Asignar -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-orange-200 dark:border-orange-900/30 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">📥</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400 mb-1">{{ stats.sin_asignar }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sin Asignar</p>
                </div>

                <!-- En Proceso -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">⚙️</span>
                        </div>
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
                    </div>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-1">{{ stats.sla_vencido }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">SLA Vencido</p>
                </div>

                <!-- Cerradas Este Mes -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-green-200 dark:border-green-900/30 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">✅</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mb-1">{{ stats.cerradas_mes }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Cerradas (Mes)</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Link href="/supervisor/denuncias?asignado_a=sin_asignar" class="flex items-center gap-3 p-4 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">📥</span>
                        <span class="font-medium">Asignar Denuncias</span>
                    </Link>
                    <Link href="/supervisor/denuncias" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">📋</span>
                        <span class="font-medium">Ver Denuncias</span>
                    </Link>
                    <Link href="/supervisor/reportes" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">📊</span>
                        <span class="font-medium">Ver Reportes</span>
                    </Link>
                    <Link href="/supervisor/denuncias?sla_vencido=1" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">⏰</span>
                        <span class="font-medium">SLA Crítico</span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Denuncias Sin Asignar -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-orange-200 dark:border-orange-900/30">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">📥 Denuncias Sin Asignar</h2>
                        <Link href="/supervisor/denuncias?asignado_a=sin_asignar" class="text-sm text-orange-600 dark:text-orange-400 hover:underline">Ver todas</Link>
                    </div>

                    <div v-if="denunciasSinAsignar.length === 0" class="text-center py-12">
                        <span class="text-6xl mb-4 block">✅</span>
                        <p class="text-gray-500 dark:text-gray-400">¡Todo asignado!</p>
                    </div>

                    <div v-else class="space-y-3">
                        <Link
                            v-for="denuncia in denunciasSinAsignar.slice(0, 5)"
                            :key="denuncia.id"
                            :href="`/supervisor/denuncias/${denuncia.id}`"
                            class="block p-4 bg-orange-50 dark:bg-orange-900/10 rounded-xl hover:bg-orange-100 dark:hover:bg-orange-900/20 transition-colors border border-orange-200 dark:border-orange-900/30"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-mono font-semibold text-orange-600 dark:text-orange-400">
                                    {{ denuncia.codigo }}
                                </span>
                                <span :class="getPrioridadColor(denuncia.prioridad?.codigo)" class="px-2 py-1 rounded-lg text-xs font-medium">
                                    {{ denuncia.prioridad?.nombre }}
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
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">⚠️ SLA Crítico</h2>
                        <Link href="/supervisor/denuncias?sla_vencido=1" class="text-sm text-red-600 dark:text-red-400 hover:underline">Ver todas</Link>
                    </div>

                    <div v-if="denunciasSlaCritico.length === 0" class="text-center py-12">
                        <span class="text-6xl mb-4 block">🎉</span>
                        <p class="text-gray-500 dark:text-gray-400">¡Sin SLA vencido!</p>
                    </div>

                    <div v-else class="space-y-3">
                        <Link
                            v-for="denuncia in denunciasSlaCritico"
                            :key="denuncia.id"
                            :href="`/supervisor/denuncias/${denuncia.id}`"
                            class="block p-4 bg-red-50 dark:bg-red-900/10 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/20 transition-colors border border-red-200 dark:border-red-900/30"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-mono font-semibold text-red-600 dark:text-red-400">
                                    {{ denuncia.codigo }}
                                </span>
                                <span class="px-2 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    Vencido
                                </span>
                            </div>
                            <h3 class="font-medium text-gray-900 dark:text-white text-sm mb-1 truncate">
                                {{ denuncia.titulo }}
                            </h3>
                            <div class="text-xs text-red-600 dark:text-red-400">
                                Asignado a: {{ denuncia.asignado_a?.nombre || 'Sin asignar' }}
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Rendimiento del Equipo -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">👥 Rendimiento del Equipo</h2>

                <div v-if="rendimientoEquipo.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Sin datos de rendimiento
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white">Funcionario</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white">Activas</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white">Cerradas (Mes)</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-900 dark:text-white">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="funcionario in rendimientoEquipo" :key="funcionario.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold">
                                            {{ funcionario.nombre?.charAt(0) }}{{ funcionario.apellido?.charAt(0) }}
                                        </div>
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ funcionario.nombre }} {{ funcionario.apellido }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center py-3 px-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ funcionario.asignadas_activas || 0 }}
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                        {{ funcionario.cerradas_mes || 0 }}
                                    </span>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <span :class="[
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        (funcionario.asignadas_activas || 0) > 15
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                            : (funcionario.asignadas_activas || 0) > 8
                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                                                : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                    ]">
                                        {{ (funcionario.asignadas_activas || 0) > 15 ? 'Sobrecargado' : (funcionario.asignadas_activas || 0) > 8 ? 'Alto' : 'Normal' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
