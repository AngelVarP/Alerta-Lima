<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categorias: Array,
    distritos: Array,
});

const form = useForm({
    titulo: '',
    categoria_id: '',
    descripcion: '',
    direccion: '',
    distrito_id: '',
    referencia: '',
    es_anonima: false,
    adjuntos: [],
});

// Estado para preview de archivos
const filePreview = ref([]);

// Manejar selección de archivos
const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    
    // Validar tipos de archivo
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'video/mp4'];
    const validFiles = files.filter(file => validTypes.includes(file.type));
    
    if (validFiles.length !== files.length) {
        alert('Solo se permiten archivos JPG, PNG y MP4');
    }
    
    // Agregar archivos al formulario
    form.adjuntos = [...form.adjuntos, ...validFiles];
    
    // Crear previews
    validFiles.forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                filePreview.value.push({
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    url: e.target.result,
                    file: file
                });
            };
            reader.readAsDataURL(file);
        } else {
            filePreview.value.push({
                name: file.name,
                size: file.size,
                type: file.type,
                url: null,
                file: file
            });
        }
    });
};

// Eliminar archivo
const removeFile = (index) => {
    form.adjuntos.splice(index, 1);
    filePreview.value.splice(index, 1);
};

// Formatear tamaño de archivo
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

// Calcular progreso del formulario
const formProgress = computed(() => {
    let filled = 0;
    const required = 5; // título, categoría, descripción, dirección, distrito
    
    if (form.titulo) filled++;
    if (form.categoria_id) filled++;
    if (form.descripcion && form.descripcion.length >= 20) filled++;
    if (form.direccion) filled++;
    if (form.distrito_id) filled++;
    
    return Math.round((filled / required) * 100);
});

const submit = () => {
    form.post('/denuncias', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nueva Denuncia" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 dark:text-white leading-tight">
                    ➕ Nueva Denuncia Ciudadana
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                     Completa el formulario para registrar tu denuncia
                </p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto">
            <!-- Barra de progreso -->
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progreso del formulario</span>
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ formProgress }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 h-2.5 rounded-full transition-all duration-500" :style="{ width: formProgress + '%' }"></div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Información Básica -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-8 py-5 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 ml-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Información Básica
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 ml-2">Describe brevemente tu denuncia</p>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <!-- Título -->
                        <div>
                            <label for="titulo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                    Título de la denuncia
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <input
                                id="titulo"
                                v-model="form.titulo"
                                type="text"
                                class="block w-full px-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                                :class="{ 'border-red-500 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900': form.errors.titulo }"
                                placeholder="Ej: Basura acumulada en la esquina de mi calle"
                            />
                            <p v-if="form.errors.titulo" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.titulo }}
                            </p>
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label for="categoria_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Categoría
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <select
                                id="categoria_id"
                                v-model="form.categoria_id"
                                class="block w-full px-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 cursor-pointer shadow-sm"
                                :class="{ 'border-red-500 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900': form.errors.categoria_id }"
                            >
                                <option value="">Selecciona una categoría</option>
                                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                    {{ categoria.nombre }}
                                </option>
                            </select>
                            <p v-if="form.errors.categoria_id" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.categoria_id }}
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Descripción detallada
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="6"
                                class="block w-full px-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none shadow-sm"
                                :class="{ 'border-red-500 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900': form.errors.descripcion }"
                                placeholder="Describe el problema con el mayor detalle posible. Incluye información como: ¿Qué sucedió? ¿Cuándo ocurrió? ¿Hay algún riesgo? (mínimo 20 caracteres)"
                            ></textarea>
                            <div class="flex justify-between items-center mt-2">
                                <p v-if="form.errors.descripcion" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.descripcion }}
                                </p>
                                <p class="text-sm ml-auto" :class="form.descripcion.length >= 20 ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                                    {{ form.descripcion.length }} / 20 caracteres
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 px-8 py-5 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-3 ml-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Ubicación del Problema
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 ml-2">Indica dónde ocurrió el problema</p>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Dirección -->
                            <div>
                                <label for="direccion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Dirección
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <input
                                    id="direccion"
                                    v-model="form.direccion"
                                    type="text"
                                    class="block w-full px-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                                    :class="{ 'border-red-500 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900': form.errors.direccion }"
                                    placeholder="Ej: Av. Arequipa 1234"
                                />
                                <p v-if="form.errors.direccion" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.direccion }}
                                </p>
                            </div>

                            <!-- Distrito -->
                            <div>
                                <label for="distrito_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        Distrito
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select
                                    id="distrito_id"
                                    v-model="form.distrito_id"
                                    class="block w-full px-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 cursor-pointer shadow-sm"
                                    :class="{ 'border-red-500 dark:border-red-500 ring-2 ring-red-200 dark:ring-red-900': form.errors.distrito_id }"
                                >
                                    <option value="">Selecciona un distrito</option>
                                    <option v-for="distrito in distritos" :key="distrito.id" :value="distrito.id">
                                        {{ distrito.nombre }}
                                    </option>
                                </select>
                                <p v-if="form.errors.distrito_id" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.distrito_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Referencia -->
                        <div>
                            <label for="referencia" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Referencia adicional (opcional)
                                </span>
                            </label>
                            <input
                                id="referencia"
                                v-model="form.referencia"
                                type="text"
                                class="block w-full px-4 py-3.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                                placeholder="Ej: Frente al parque, cerca de la bodega 'El Tío'"
                            />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Ayúdanos a ubicar mejor el problema con puntos de referencia conocidos
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Evidencias (Adjuntos) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 px-8 py-5 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 ml-2">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Evidencias (Opcional)
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 ml-2">Adjunta fotos o videos que respalden tu denuncia</p>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <!-- Input de archivos -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Subir archivos
                                </span>
                            </label>
                            
                            <div class="relative">
                                <input
                                    type="file"
                                    @change="handleFileChange"
                                    accept="image/jpeg,image/jpg,image/png,video/mp4"
                                    multiple
                                    class="hidden"
                                    id="file-upload"
                                />
                                <label
                                    for="file-upload"
                                    class="flex flex-col items-center justify-center w-full px-6 py-8 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                                >
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Haz clic para seleccionar archivos
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        JPG, PNG o MP4 (máx. 10MB por archivo)
                                    </p>
                                </label>
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                💡 Las evidencias ayudan a validar y dar seguimiento más rápido a tu denuncia
                            </p>
                        </div>

                        <!-- Preview de archivos -->
                        <div v-if="filePreview.length > 0" class="space-y-3">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Archivos seleccionados ({{ filePreview.length }})
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div
                                    v-for="(file, index) in filePreview"
                                    :key="index"
                                    class="relative group bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-200"
                                >
                                    <!-- Preview de imagen -->
                                    <div v-if="file.url" class="aspect-video bg-gray-100 dark:bg-gray-800">
                                        <img :src="file.url" :alt="file.name" class="w-full h-full object-cover" />
                                    </div>
                                    
                                    <!-- Preview de video -->
                                    <div v-else class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Info del archivo -->
                                    <div class="p-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ file.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ formatFileSize(file.size) }}
                                        </p>
                                    </div>
                                    
                                    <!-- Botón eliminar -->
                                    <button
                                        type="button"
                                        @click="removeFile(index)"
                                        class="absolute top-2 right-2 p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                        title="Eliminar archivo"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opciones Adicionales -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-start gap-4 p-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-100 dark:border-purple-800/50">
                            <input
                                id="es_anonima"
                                v-model="form.es_anonima"
                                type="checkbox"
                                class="mt-1 w-5 h-5 text-purple-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 cursor-pointer"
                            />
                            <label for="es_anonima" class="flex-1 cursor-pointer">
                                <div class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Realizar denuncia de forma anónima
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1.5 leading-relaxed">
                                    Tu identidad no será visible públicamente. Solo las autoridades tendrán acceso a tus datos para el seguimiento del caso.
                                </p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="text-red-500">*</span> Campos obligatorios
                        </p>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <Link
                                href="/mis-denuncias"
                                class="flex-1 sm:flex-none px-6 py-3.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 text-center"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 sm:flex-none px-8 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                            >
                                <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Guardando...
                                </span>
                                <span v-else>Registrar Denuncia</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
