<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    eventos: Object,
    filtros: Object,
    tiposEvento: Array,
    severidades: Array,
});

const search = ref(props.filtros?.search || '');
const selectedTipoEvento = ref(props.filtros?.tipo_evento || '');
const selectedSeveridad = ref(props.filtros?.severidad || '');
const fechaInicio = ref(props.filtros?.fecha_inicio || '');
const fechaFin = ref(props.filtros?.fecha_fin || '');

let timeout = null;

const applyFilters = () => {
    router.get('/admin/seguridad', {
        search: search.value,
        tipo_evento: selectedTipoEvento.value,
        severidad: selectedSeveridad.value,
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    }, {
        preserveState: true,
        replace: true
    });
};

watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => applyFilters(), 300);
});

watch([selectedTipoEvento, selectedSeveridad, fechaInicio, fechaFin], () => {
    applyFilters();
});

const clearFilters = () => {
    search.value = '';
    selectedTipoEvento.value = '';
    selectedSeveridad.value = '';
    fechaInicio.value = '';
    fechaFin.value = '';
};

const hasActiveFilters = () => {
    return search.value || selectedTipoEvento.value || selectedSeveridad.value || fechaInicio.value || fechaFin.value;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const getSeveridadColor = (severidad) => {
    const colores = {
        'BAJA': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'MEDIA': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'ALTA': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        'CRITICA': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return colores[severidad] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getSeveridadIcon = (severidad) => {
    const iconos = {
        'BAJA': '✅',
        'MEDIA': '⚠️',
        'ALTA': '🔴',
        'CRITICA': '🚨',
    };
    return iconos[severidad] || '⚪';
};
</script>

<template>
    <Head title="Eventos de Seguridad" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                🔒 Eventos de Seguridad
            </h2>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoreo de Seguridad</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Registro de eventos de seguridad y actividades sospechosas
                </p>
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Filtros</h3>
                    <button
                        v-if="hasActiveFilters()"
                        @click="clearFilters"
                        class="text-sm text-red-600 dark:text-red-400 hover:underline flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Limpiar filtros
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Búsqueda -->
                    <div class="relative md:col-span-3">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Buscar por tipo de evento, IP o usuario..."
                        />
                    </div>

                    <!-- Tipo de Evento -->
                    <div class="relative">
                        <select
                            v-model="selectedTipoEvento"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todos los tipos</option>
                            <option v-for="tipo in tiposEvento" :key="tipo" :value="tipo">
                                {{ tipo }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Severidad -->
                    <div class="relative md:col-span-2">
                        <select
                            v-model="selectedSeveridad"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las severidades</option>
                            <option v-for="severidad in severidades" :key="severidad" :value="severidad">
                                {{ severidad }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>
            </div>

            <!-- Tabla de eventos -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                <div v-if="eventos.data.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <span class="text-5xl mb-3 block">✅</span>
                    <h3 class="font-medium text-lg">No se encontraron eventos de seguridad</h3>
                    <p class="text-sm mt-1">
                        El sistema está funcionando normalmente sin incidentes.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Severidad
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tipo de Evento
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Usuario / IP
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Fecha
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="evento in eventos.data" :key="evento.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">{{ getSeveridadIcon(evento.severidad) }}</span>
                                        <span :class="getSeveridadColor(evento.severidad)" class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-lg">
                                            {{ evento.severidad }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ evento.tipo_evento }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white max-w-md truncate">
                                        {{ evento.descripcion }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ evento.usuario?.nombre || 'Sistema' }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                        {{ evento.ip_origen || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(evento.creado_en) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="eventos.links.length > 3" class="flex justify-center">
                <div class="flex gap-1.5 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        v-for="(link, key) in eventos.links"
                        :key="key"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                            link.active
                                ? 'bg-red-600 text-white shadow-md'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/70',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                        :disabled="!link.url"
                    />
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
