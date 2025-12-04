<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    registros: Object,
    filtros: Object,
    acciones: Array,
    tablas: Array,
    usuarios: Array,
});

const search = ref(props.filtros?.search || '');
const selectedAccion = ref(props.filtros?.accion || '');
const selectedTabla = ref(props.filtros?.tabla || '');
const selectedUsuario = ref(props.filtros?.usuario_id || '');
const fechaInicio = ref(props.filtros?.fecha_inicio || '');
const fechaFin = ref(props.filtros?.fecha_fin || '');

let timeout = null;

const applyFilters = () => {
    router.get('/admin/auditoria', {
        search: search.value,
        accion: selectedAccion.value,
        tabla: selectedTabla.value,
        usuario_id: selectedUsuario.value,
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

watch([selectedAccion, selectedTabla, selectedUsuario, fechaInicio, fechaFin], () => {
    applyFilters();
});

const clearFilters = () => {
    search.value = '';
    selectedAccion.value = '';
    selectedTabla.value = '';
    selectedUsuario.value = '';
    fechaInicio.value = '';
    fechaFin.value = '';
};

const hasActiveFilters = () => {
    return search.value || selectedAccion.value || selectedTabla.value || selectedUsuario.value || fechaInicio.value || fechaFin.value;
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

const getAccionColor = (accion) => {
    const colores = {
        'CREAR': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'ACTUALIZAR': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'ELIMINAR': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'LOGIN': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'LOGOUT': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };

    for (const [key, color] of Object.entries(colores)) {
        if (accion.includes(key)) return color;
    }

    return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};
</script>

<template>
    <Head title="Auditoría del Sistema" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                📋 Auditoría del Sistema
            </h2>
        </template>

        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Registros de Auditoría</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Histórico de acciones realizadas en el sistema
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
                            placeholder="Buscar por acción, tabla, registro o usuario..."
                        />
                    </div>

                    <!-- Acción -->
                    <div class="relative">
                        <select
                            v-model="selectedAccion"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las acciones</option>
                            <option v-for="accion in acciones" :key="accion" :value="accion">
                                {{ accion }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="relative">
                        <select
                            v-model="selectedTabla"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las tablas</option>
                            <option v-for="tabla in tablas" :key="tabla" :value="tabla">
                                {{ tabla }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Usuario -->
                    <div class="relative">
                        <select
                            v-model="selectedUsuario"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todos los usuarios</option>
                            <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                {{ usuario.nombre }} {{ usuario.apellido }}
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

            <!-- Tabla de registros -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                <div v-if="registros.data.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <span class="text-5xl mb-3 block">🔍</span>
                    <h3 class="font-medium text-lg">No se encontraron registros</h3>
                    <p class="text-sm mt-1">
                        Intenta ajustar los filtros de búsqueda.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Acción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Tabla / Registro
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    IP Origen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Fecha
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="registro in registros.data" :key="registro.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ registro.usuario?.nombre }} {{ registro.usuario?.apellido }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        ID: {{ registro.usuario_id }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getAccionColor(registro.accion)" class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-lg">
                                        {{ registro.accion }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ registro.tabla_afectada }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Reg: {{ registro.registro_id || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono">
                                    {{ registro.ip_origen || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(registro.creado_en) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="registros.links.length > 3" class="flex justify-center">
                <div class="flex gap-1.5 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        v-for="(link, key) in registros.links"
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
