<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    denuncia: Object,
});

// Función para obtener el color del badge según el estado
const getEstadoBadgeColor = (codigo) => {
    const colors = {
        'REG': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'ASI': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'PRO': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'RES': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'CER': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
        'REC': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[codigo] || 'bg-gray-100 text-gray-800';
};

// Función para formatear fecha
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Denuncia ${denuncia.codigo}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 dark:text-white leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ denuncia.codigo }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Detalles de tu denuncia
                </p>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            
            <!-- Botón volver -->
            <div>
                <Link href="/mis-denuncias" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a mis denuncias
                </Link>
            </div>

            <!-- Header con estado y código -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ denuncia.titulo }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Registrada el {{ formatDate(denuncia.creado_en) }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <span :class="getEstadoBadgeColor(denuncia.estado.codigo)" class="px-4 py-2 rounded-xl font-semibold text-sm text-center">
                            {{ denuncia.estado.nombre }}
                        </span>
                        <span v-if="denuncia.es_anonima" class="px-4 py-2 rounded-xl bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 font-medium text-sm text-center">
                            🔒 Anónima
                        </span>
                    </div>
                </div>
            </div>

            <!-- Información principal -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Columna principal -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Descripción -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-8 py-5 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 ml-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Descripción
                            </h3>
                        </div>
                        <div class="p-8">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                {{ denuncia.descripcion }}
                            </p>
                        </div>
                    </div>

                    <!-- Evidencias -->
                    <div v-if="denuncia.adjuntos && denuncia.adjuntos.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 px-8 py-5 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 ml-2">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Evidencias ({{ denuncia.adjuntos.length }})
                            </h3>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div
                                    v-for="adjunto in denuncia.adjuntos"
                                    :key="adjunto.id"
                                    class="group bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-200"
                                >
                                    <!-- Imagen o video -->
                                    <a
                                        :href="`/storage/${adjunto.ruta_archivo}`"
                                        target="_blank"
                                        class="block relative"
                                    >
                                        <div v-if="adjunto.tipo_mime.startsWith('image/')" class="aspect-video bg-gray-100 dark:bg-gray-800">
                                            <img :src="`/storage/${adjunto.ruta_archivo}`" :alt="adjunto.nombre_archivo" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="aspect-video bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <!-- Overlay al hover -->
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                            </svg>
                                        </div>
                                    </a>
                                    
                                    <!-- Info del archivo -->
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800/50">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" :title="adjunto.nombre_archivo">
                                            {{ adjunto.nombre_archivo }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ (adjunto.tamano / 1024).toFixed(1) }} KB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna lateral -->
                <div class="space-y-6">
                    
                    <!-- Información de categoría -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Categoría</h4>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ denuncia.categoria.nombre }}
                        </p>
                    </div>

                    <!-- Ubicación -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Ubicación</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Distrito</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ denuncia.distrito.nombre }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Dirección</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ denuncia.direccion }}
                                </p>
                            </div>
                            <div v-if="denuncia.referencia">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Referencia</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ denuncia.referencia }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Prioridad -->
                    <div v-if="denuncia.prioridad" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
                        <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Prioridad</h4>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ denuncia.prioridad.nombre }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
