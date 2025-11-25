<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    notificaciones: Object,
    noLeidasCount: Number,
    filtros: Object,
});

const filtroSeleccionado = ref(props.filtros.filtro || '');
const tipoSeleccionado = ref(props.filtros.tipo || '');

const aplicarFiltros = () => {
    router.get('/notificaciones', {
        filtro: filtroSeleccionado.value,
        tipo: tipoSeleccionado.value,
    }, { preserveState: true, replace: true });
};

const marcarTodasComoLeidas = () => {
    router.post('/notificaciones/marcar-todas-leidas', {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['notificaciones', 'noLeidasCount'] });
        }
    });
};

const marcarComoLeida = (id) => {
    router.post(`/notificaciones/${id}/marcar-leida`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['notificaciones', 'noLeidasCount'] });
        }
    });
};

const eliminarNotificacion = (id) => {
    if (confirm('¿Estás seguro de eliminar esta notificación?')) {
        router.delete(`/notificaciones/${id}`, {
            preserveScroll: true,
        });
    }
};

const getTipoIcon = (tipo) => {
    const icons = {
        'cambio_estado': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'comentario': 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'prioridad': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'asignacion': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'resolucion': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    };
    return icons[tipo] || icons['cambio_estado'];
};

const getTipoColor = (tipo) => {
    const colors = {
        'cambio_estado': 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30',
        'comentario': 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30',
        'prioridad': 'text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30',
        'asignacion': 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30',
        'resolucion': 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30',
    };
    return colors[tipo] || colors['cambio_estado'];
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
</script>

<template>
    <Head title="Notificaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-2xl text-gray-900 dark:text-white leading-tight flex items-center gap-2">
                        <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Notificaciones
                        <span v-if="noLeidasCount > 0" class="px-3 py-1 text-sm font-semibold bg-red-500 text-white rounded-full">
                            {{ noLeidasCount }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Mantente informado sobre tus denuncias
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            
            <!-- Filtros y acciones -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <!-- Filtro de estado -->
                    <select
                        v-model="filtroSeleccionado"
                        @change="aplicarFiltros"
                        class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 cursor-pointer"
                    >
                        <option value="">Todas las notificaciones</option>
                        <option value="no_leidas">No leídas</option>
                        <option value="leidas">Leídas</option>
                    </select>

                    <!-- Filtro por tipo -->
                    <select
                        v-model="tipoSeleccionado"
                        @change="aplicarFiltros"
                        class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 cursor-pointer"
                    >
                        <option value="">Todos los tipos</option>
                        <option value="cambio_estado">Cambios de estado</option>
                        <option value="comentario">Comentarios</option>
                        <option value="prioridad">Prioridad</option>
                        <option value="asignacion">Asignaciones</option>
                        <option value="resolucion">Resoluciones</option>
                    </select>
                </div>

                <!-- Botón marcar todas como leídas -->
                <button
                    v-if="noLeidasCount > 0"
                    @click="marcarTodasComoLeidas"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all duration-200 transform hover:scale-[1.02]"
                >
                    Marcar todas como leídas
                </button>
            </div>

            <!-- Lista de notificaciones -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <div v-if="notificaciones.data.length === 0" class="p-12 text-center text-gray-500 dark:text-gray-400">
                    <span class="text-5xl mb-3 block">🔔</span>
                    <h3 class="font-medium text-lg">No tienes notificaciones</h3>
                    <p class="text-sm mt-1">
                        Cuando haya actualizaciones en tus denuncias, aparecerán aquí.
                    </p>
                </div>

                <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li
                        v-for="notificacion in notificaciones.data"
                        :key="notificacion.id"
                        :class="[
                            'p-6 transition-all duration-200',
                            !notificacion.leida_en ? 'bg-blue-50/50 dark:bg-blue-900/10' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
                        ]"
                    >
                        <div class="flex gap-4">
                            <!-- Icono del tipo -->
                            <div :class="['flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center', getTipoColor(notificacion.tipo)]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTipoIcon(notificacion.tipo)" />
                                </svg>
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                            {{ notificacion.asunto }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                            {{ notificacion.mensaje }}
                                        </p>
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ formatDate(notificacion.creado_en) }}
                                            </span>
                                            <Link
                                                v-if="notificacion.denuncia"
                                                :href="`/denuncias/${notificacion.denuncia.id}`"
                                                class="text-blue-600 dark:text-blue-400 hover:underline font-medium"
                                            >
                                                Ver denuncia {{ notificacion.denuncia.codigo }}
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="flex items-center gap-2">
                                        <button
                                            v-if="!notificacion.leida_en"
                                            @click="marcarComoLeida(notificacion.id)"
                                            class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                            title="Marcar como leída"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="eliminarNotificacion(notificacion.id)"
                                            class="p-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                            title="Eliminar"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Paginación -->
            <div v-if="notificaciones.links.length > 3" class="flex justify-center">
                <div class="flex gap-1.5 p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <Link
                        v-for="(link, key) in notificaciones.links"
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
