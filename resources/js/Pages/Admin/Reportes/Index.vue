<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    estadisticas: Object,
    areas: Array,
    filtros: Object,
});

const fechaInicio = ref(props.filtros?.fecha_inicio || '');
const fechaFin = ref(props.filtros?.fecha_fin || '');
const selectedArea = ref(props.filtros?.area_id || '');

const applyFilters = () => {
    router.get('/admin/reportes', {
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
        area_id: selectedArea.value,
    }, {
        preserveState: true,
        replace: true
    });
};

watch([fechaInicio, fechaFin, selectedArea], () => {
    if (fechaInicio.value && fechaFin.value) {
        applyFilters();
    }
});

const exportarCSV = () => {
    window.location.href = `/admin/reportes/exportar-csv?fecha_inicio=${fechaInicio.value}&fecha_fin=${fechaFin.value}&area_id=${selectedArea.value}`;
};

const exportarPDF = () => {
    window.location.href = `/admin/reportes/exportar-pdf?fecha_inicio=${fechaInicio.value}&fecha_fin=${fechaFin.value}&area_id=${selectedArea.value}`;
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

const formatPercentage = (value, total) => {
    if (total === 0) return '0%';
    return `${Math.round((value / total) * 100)}%`;
};
</script>

<template>
    <Head title="Reportes y Métricas" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                📈 Reportes y Métricas
            </h2>
        </template>

        <div class="space-y-6">

            <!-- Header con botones de exportación -->
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Reportes</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Análisis y métricas del sistema de denuncias
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="exportarCSV"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <span class="text-lg">📥</span>
                        Exportar CSV
                    </button>
                    <button
                        @click="exportarPDF"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        <span class="text-lg">📄</span>
                        Exportar PDF
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Filtros de Rango</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha Inicio
                        </label>
                        <input
                            v-model="fechaInicio"
                            type="date"
                            class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha Fin
                        </label>
                        <input
                            v-model="fechaFin"
                            type="date"
                            class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        />
                    </div>
                    <div v-if="areas">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Área
                        </label>
                        <select
                            v-model="selectedArea"
                            class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las áreas</option>
                            <option v-for="area in areas" :key="area.id" :value="area.id">
                                {{ area.nombre }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">📊</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                        {{ estadisticas.total }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total de Denuncias</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">✅</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                        {{ estadisticas.cerradas }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Denuncias Cerradas</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                        {{ formatPercentage(estadisticas.cerradas, estadisticas.total) }} del total
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                            <span class="text-2xl">⏳</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                        {{ estadisticas.en_proceso }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">En Proceso</p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                        {{ formatPercentage(estadisticas.en_proceso, estadisticas.total) }} del total
                    </p>
                </div>
            </div>

            <!-- Distribución por Estado -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Distribución por Estado</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div
                        v-for="estado in estadisticas.por_estado"
                        :key="estado.id"
                        class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-all"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span :class="getEstadoColor(estado.codigo)" class="px-2 py-1 rounded-lg text-xs font-medium">
                                {{ estado.codigo }}
                            </span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ estado.denuncias_count }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ estado.nombre }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ formatPercentage(estado.denuncias_count, estadisticas.total) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución por Categoría -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Distribución por Categoría</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Categoría
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Cantidad
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Porcentaje
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Barra
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="categoria in estadisticas.por_categoria" :key="categoria.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ categoria.nombre }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white font-semibold">
                                    {{ categoria.denuncias_count }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ formatPercentage(categoria.denuncias_count, estadisticas.total) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div
                                            class="bg-gradient-to-r from-red-500 to-red-600 h-2 rounded-full transition-all duration-300"
                                            :style="{ width: formatPercentage(categoria.denuncias_count, estadisticas.total) }"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Distribución por Área (solo si no filtró por área) -->
            <div v-if="estadisticas.por_area" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Distribución por Área</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="area in estadisticas.por_area"
                        :key="area.id"
                        class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-red-300 dark:hover:border-red-700 transition-all"
                    >
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-500 rounded-lg flex items-center justify-center text-white font-bold">
                                {{ area.nombre.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ area.nombre }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ area.denuncias_count }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    denuncias
                                </div>
                            </div>
                            <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ formatPercentage(area.denuncias_count, estadisticas.total) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
