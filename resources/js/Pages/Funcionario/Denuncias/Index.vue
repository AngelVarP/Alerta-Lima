<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    denuncias: Object,
    filtros: Object,
    estados: Array,
    categorias: Array,
    prioridades: Array,
    funcionarios: Array,
});

const search = ref(props.filtros.search || '');
const selectedEstado = ref(props.filtros.estado_id || '');
const selectedCategoria = ref(props.filtros.categoria_id || '');
const selectedPrioridad = ref(props.filtros.prioridad_id || '');
const selectedAsignado = ref(props.filtros.asignado_a || '');
const slaVencido = ref(props.filtros.sla_vencido || false);

let timeout = null;

const applyFilters = () => {
    router.get('/funcionario/denuncias', {
        search: search.value,
        estado_id: selectedEstado.value,
        categoria_id: selectedCategoria.value,
        prioridad_id: selectedPrioridad.value,
        asignado_a: selectedAsignado.value,
        sla_vencido: slaVencido.value ? 1 : '',
    }, {
        preserveState: true,
        replace: true
    });
};

watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => applyFilters(), 300);
});

watch([selectedEstado, selectedCategoria, selectedPrioridad, selectedAsignado, slaVencido], () => {
    applyFilters();
});

const clearFilters = () => {
    search.value = '';
    selectedEstado.value = '';
    selectedCategoria.value = '';
    selectedPrioridad.value = '';
    selectedAsignado.value = '';
    slaVencido.value = false;
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const hasActiveFilters = () => {
    return search.value || selectedEstado.value || selectedCategoria.value ||
           selectedPrioridad.value || selectedAsignado.value || slaVencido.value;
};
</script>

<template>
    <Head title="Gestión de Denuncias" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                📋 Gestión de Denuncias del Área
            </h2>
        </template>

        <div class="space-y-6">

            <!-- Filtros Avanzados -->
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Búsqueda -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Buscar por código o título..."
                        />
                    </div>

                    <!-- Estado -->
                    <div class="relative">
                        <select
                            v-model="selectedEstado"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todos los estados</option>
                            <option v-for="estado in estados" :key="estado.id" :value="estado.id">
                                {{ estado.nombre }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div class="relative">
                        <select
                            v-model="selectedCategoria"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las categorías</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nombre }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Prioridad -->
                    <div class="relative">
                        <select
                            v-model="selectedPrioridad"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las prioridades</option>
                            <option v-for="prioridad in prioridades" :key="prioridad.id" :value="prioridad.id">
                                {{ prioridad.nombre }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Asignado a -->
                    <div class="relative">
                        <select
                            v-model="selectedAsignado"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todos los funcionarios</option>
                            <option value="sin_asignar">Sin asignar</option>
                            <option value="mis_denuncias">Mis denuncias</option>
                            <option v-for="funcionario in funcionarios" :key="funcionario.id" :value="funcionario.id">
                                {{ funcionario.nombre }} {{ funcionario.apellido }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- SLA Vencido -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                v-model="slaVencido"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            />
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Solo SLA vencido</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Lista de denuncias -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                <div v-if="denuncias.data.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <span class="text-5xl mb-3 block">🔍</span>
                    <h3 class="font-medium text-lg">No se encontraron denuncias</h3>
                    <p class="text-sm mt-1">
                        Intenta ajustar los filtros o verifica que haya denuncias en tu área.
                    </p>
                </div>

                <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li v-for="denuncia in denuncias.data" :key="denuncia.id">
                        <Link
                            :href="`/funcionario/denuncias/${denuncia.id}`"
                            class="block p-5 hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition duration-150"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                                        <span class="text-sm font-mono font-semibold text-blue-600 dark:text-blue-400">
                                            {{ denuncia.codigo }}
                                        </span>
                                        <span :class="getEstadoColor(denuncia.estado?.codigo)" class="px-2.5 py-1 rounded-lg text-xs font-medium">
                                            {{ denuncia.estado?.nombre }}
                                        </span>
                                        <span :class="getPrioridadColor(denuncia.prioridad?.codigo)" class="px-2.5 py-1 rounded-lg text-xs font-medium">
                                            {{ denuncia.prioridad?.nombre }}
                                        </span>
                                        <span v-if="!denuncia.asignado_a_id" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                            Sin asignar
                                        </span>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                        {{ denuncia.titulo }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">
                                        {{ denuncia.descripcion }}
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ denuncia.ciudadano?.nombre || 'Anónimo' }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            {{ denuncia.categoria?.nombre }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(denuncia.creado_en) }}
                                        </span>
                                        <span v-if="denuncia.asignado_a" class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Asignado a: {{ denuncia.asignado_a.nombre }}
                                        </span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 ml-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Paginación -->
            <div v-if="denuncias.links.length > 3" class="flex justify-center">
                <div class="flex gap-1.5 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        v-for="(link, key) in denuncias.links"
                        :key="key"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                            link.active
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/70',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                        :disabled="!link.url"
                    />
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
