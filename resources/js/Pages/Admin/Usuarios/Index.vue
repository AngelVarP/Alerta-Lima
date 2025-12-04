<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    usuarios: Object,
    roles: Array,
    areas: Array,
    filtros: Object,
});

const search = ref(props.filtros?.buscar || '');
const selectedRol = ref(props.filtros?.rol || '');
const selectedArea = ref(props.filtros?.area || '');
const selectedActivo = ref(props.filtros?.activo || '');

let timeout = null;

const applyFilters = () => {
    router.get('/admin/usuarios', {
        buscar: search.value,
        rol: selectedRol.value,
        area: selectedArea.value,
        activo: selectedActivo.value,
    }, {
        preserveState: true,
        replace: true
    });
};

watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => applyFilters(), 300);
});

watch([selectedRol, selectedArea, selectedActivo], () => {
    applyFilters();
});

const clearFilters = () => {
    search.value = '';
    selectedRol.value = '';
    selectedArea.value = '';
    selectedActivo.value = '';
};

const toggleActivo = (usuario) => {
    if (confirm(`¿Está seguro de ${usuario.activo ? 'desactivar' : 'activar'} al usuario ${usuario.nombre} ${usuario.apellido}?`)) {
        router.post(`/admin/usuarios/${usuario.id}/toggle-activo`, {}, {
            preserveState: true,
            onSuccess: () => {
                // Actualización exitosa
            }
        });
    }
};

const hasActiveFilters = () => {
    return search.value || selectedRol.value || selectedArea.value || selectedActivo.value;
};

const getRolesNames = (roles) => {
    if (!roles || roles.length === 0) return 'Sin rol';
    return roles.map(r => r.nombre).join(', ');
};

const getEstadoBadge = (activo) => {
    return activo
        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                👥 Gestión de Usuarios
            </h2>
        </template>

        <div class="space-y-6">

            <!-- Header con botón crear -->
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Usuarios del Sistema</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Administra los usuarios, roles y permisos del sistema
                    </p>
                </div>
                <Link
                    href="/admin/usuarios/create"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    <span class="text-xl">➕</span>
                    Nuevo Usuario
                </Link>
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

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Buscar por nombre, email, DNI..."
                        />
                    </div>

                    <!-- Rol -->
                    <div class="relative">
                        <select
                            v-model="selectedRol"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todos los roles</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">
                                {{ rol.nombre }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Área -->
                    <div class="relative">
                        <select
                            v-model="selectedArea"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todas las áreas</option>
                            <option v-for="area in areas" :key="area.id" :value="area.id">
                                {{ area.nombre }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="relative">
                        <select
                            v-model="selectedActivo"
                            class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                        >
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-2xl border border-gray-100 dark:border-gray-700">
                <div v-if="usuarios.data.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <span class="text-5xl mb-3 block">🔍</span>
                    <h3 class="font-medium text-lg">No se encontraron usuarios</h3>
                    <p class="text-sm mt-1">
                        Intenta ajustar los filtros o crea un nuevo usuario.
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
                                    Email / DNI
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Roles
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Área
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="usuario in usuarios.data" :key="usuario.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                                {{ usuario.nombre.charAt(0) }}{{ usuario.apellido ? usuario.apellido.charAt(0) : '' }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ usuario.nombre }} {{ usuario.apellido }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                ID: {{ usuario.id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ usuario.email }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ usuario.dni || 'Sin DNI' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-lg bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                        {{ getRolesNames(usuario.roles) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ usuario.area?.nombre || 'Sin área' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getEstadoBadge(usuario.activo)" class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-lg">
                                        {{ usuario.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/usuarios/${usuario.id}/edit`"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors"
                                            title="Editar"
                                        >
                                            ✏️
                                        </Link>
                                        <button
                                            @click="toggleActivo(usuario)"
                                            :class="usuario.activo ? 'text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300' : 'text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300'"
                                            class="transition-colors"
                                            :title="usuario.activo ? 'Desactivar' : 'Activar'"
                                        >
                                            {{ usuario.activo ? '🔒' : '🔓' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="usuarios.links.length > 3" class="flex justify-center">
                <div class="flex gap-1.5 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        v-for="(link, key) in usuarios.links"
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
