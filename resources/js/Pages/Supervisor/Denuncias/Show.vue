<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    denuncia: Object,
    funcionariosArea: Array,
    estados: Array,
    prioridades: Array,
});

// Modales
const showAsignarModal = ref(false);
const showReasignarModal = ref(false);
const showCambiarPrioridadModal = ref(false);

// Forms
const formAsignar = useForm({
    funcionario_id: '',
    motivo: ''
});

const formReasignar = useForm({
    funcionario_id: '',
    motivo: ''
});

const formCambiarPrioridad = useForm({
    prioridad_id: '',
    motivo: ''
});

// Funciones
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

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-PE', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const asignarDenuncia = () => {
    formAsignar.post(`/supervisor/denuncias/${props.denuncia.id}/asignar`, {
        onSuccess: () => {
            showAsignarModal.value = false;
            formAsignar.reset();
        }
    });
};

const reasignarDenuncia = () => {
    formReasignar.post(`/supervisor/denuncias/${props.denuncia.id}/reasignar`, {
        onSuccess: () => {
            showReasignarModal.value = false;
            formReasignar.reset();
        }
    });
};

const cambiarPrioridad = () => {
    formCambiarPrioridad.post(`/supervisor/denuncias/${props.denuncia.id}/cambiar-prioridad`, {
        onSuccess: () => {
            showCambiarPrioridadModal.value = false;
            formCambiarPrioridad.reset();
        }
    });
};
</script>

<template>
    <Head :title="`Supervisión ${denuncia.codigo}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link href="/supervisor/denuncias" class="text-sm text-purple-600 dark:text-purple-400 hover:underline mb-2 inline-block">
                        ← Volver a la lista
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                        {{ denuncia.codigo }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header con información principal y acciones -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3 flex-wrap">
                            <span :class="getEstadoColor(denuncia.estado?.codigo)" class="px-3 py-1.5 rounded-lg text-sm font-medium">
                                {{ denuncia.estado?.nombre }}
                            </span>
                            <span :class="getPrioridadColor(denuncia.prioridad?.codigo)" class="px-3 py-1.5 rounded-lg text-sm font-medium">
                                Prioridad: {{ denuncia.prioridad?.nombre }}
                            </span>
                            <span v-if="!denuncia.asignado_a_id" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                ⚠️ Sin asignar
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            {{ denuncia.titulo }}
                        </h1>
                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                            <span>📅 {{ formatDate(denuncia.creado_en) }}</span>
                            <span>•</span>
                            <span>🏷️ {{ denuncia.categoria?.nombre }}</span>
                            <span>•</span>
                            <span>📍 {{ denuncia.distrito?.nombre }}</span>
                        </div>
                    </div>

                    <!-- Botones de acción del supervisor -->
                    <div class="flex flex-col gap-3">
                        <button
                            v-if="!denuncia.asignado_a_id"
                            @click="showAsignarModal = true"
                            class="px-4 py-2.5 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-medium rounded-xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 transition-all duration-200"
                        >
                            📥 Asignar a Funcionario
                        </button>
                        <button
                            v-else
                            @click="showReasignarModal = true"
                            class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium rounded-xl shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-200"
                        >
                            🔄 Reasignar
                        </button>
                        <button
                            @click="showCambiarPrioridadModal = true"
                            class="px-4 py-2.5 bg-gradient-to-r from-yellow-600 to-yellow-700 hover:from-yellow-700 hover:to-yellow-800 text-white font-medium rounded-xl shadow-lg shadow-yellow-500/30 hover:shadow-yellow-500/50 transition-all duration-200"
                        >
                            ⚡ Cambiar Prioridad
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Contenido Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Descripción -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Descripción</h3>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ denuncia.descripcion }}</p>
                    </div>

                    <!-- Ubicación -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📍 Ubicación</h3>
                        <div class="space-y-2 text-sm">
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Dirección:</span> {{ denuncia.direccion }}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Distrito:</span> {{ denuncia.distrito?.nombre }}
                            </p>
                            <p v-if="denuncia.referencia" class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Referencia:</span> {{ denuncia.referencia }}
                            </p>
                        </div>
                    </div>

                    <!-- Adjuntos -->
                    <div v-if="denuncia.adjuntos && denuncia.adjuntos.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📎 Adjuntos ({{ denuncia.adjuntos.length }})</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <a
                                v-for="adjunto in denuncia.adjuntos"
                                :key="adjunto.id"
                                :href="`/storage/${adjunto.ruta_archivo}`"
                                target="_blank"
                                class="block p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ adjunto.nombre_archivo }}</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Comentarios -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">💬 Comentarios</h3>
                        <div v-if="!denuncia.comentarios || denuncia.comentarios.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <span class="text-4xl mb-2 block">💭</span>
                            <p>No hay comentarios aún</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="comentario in denuncia.comentarios"
                                :key="comentario.id"
                                :class="[
                                    'p-4 rounded-xl border',
                                    comentario.es_interno
                                        ? 'bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-900/30'
                                        : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-900/30'
                                ]"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-bold">
                                            {{ comentario.usuario?.nombre?.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ comentario.usuario?.nombre }} {{ comentario.usuario?.apellido }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDate(comentario.creado_en) }}
                                            </p>
                                        </div>
                                    </div>
                                    <span v-if="comentario.es_interno" class="text-xs px-2 py-1 bg-yellow-200 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400 rounded-full">
                                        Interno
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ comentario.contenido }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Información del Ciudadano -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">👤 Ciudadano</h3>
                        <div v-if="!denuncia.es_anonima && denuncia.ciudadano" class="space-y-2 text-sm">
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Nombre:</span> {{ denuncia.ciudadano.nombre }} {{ denuncia.ciudadano.apellido }}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Email:</span> {{ denuncia.ciudadano.email }}
                            </p>
                            <p v-if="denuncia.ciudadano.telefono" class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Teléfono:</span> {{ denuncia.ciudadano.telefono }}
                            </p>
                        </div>
                        <p v-else class="text-gray-500 dark:text-gray-400 text-sm">
                            Denuncia anónima
                        </p>
                    </div>

                    <!-- Asignación -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">👷 Asignación</h3>
                        <div v-if="denuncia.asignado_a" class="space-y-2 text-sm">
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Asignado a:</span> {{ denuncia.asignado_a.nombre }} {{ denuncia.asignado_a.apellido }}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Área:</span> {{ denuncia.area?.nombre }}
                            </p>
                        </div>
                        <p v-else class="text-orange-600 dark:text-orange-400 text-sm font-medium">
                            ⚠️ Sin asignar - Requiere atención
                        </p>
                    </div>

                    <!-- Historial de Asignaciones -->
                    <div v-if="denuncia.historial_asignaciones && denuncia.historial_asignaciones.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📜 Historial Asignaciones</h3>
                        <div class="space-y-3">
                            <div
                                v-for="historial in denuncia.historial_asignaciones"
                                :key="historial.id"
                                class="border-l-4 border-purple-500 pl-3 py-2"
                            >
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ historial.asignado_a?.nombre }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    por {{ historial.asignado_por?.nombre }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ formatDate(historial.creado_en) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Asignar -->
        <div v-if="showAsignarModal" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4" @click="showAsignarModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">📥 Asignar Denuncia</h3>
                <form @submit.prevent="asignarDenuncia" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Funcionario</label>
                        <select
                            v-model="formAsignar.funcionario_id"
                            class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500"
                            required
                        >
                            <option value="">Selecciona un funcionario</option>
                            <option v-for="funcionario in funcionariosArea" :key="funcionario.id" :value="funcionario.id">
                                {{ funcionario.nombre }} {{ funcionario.apellido }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Motivo (opcional)</label>
                        <textarea
                            v-model="formAsignar.motivo"
                            rows="3"
                            class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500"
                            placeholder="Motivo de la asignación..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="showAsignarModal = false"
                            class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="formAsignar.processing"
                            class="flex-1 px-4 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors disabled:opacity-50"
                        >
                            Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Reasignar -->
        <div v-if="showReasignarModal" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4" @click="showReasignarModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">🔄 Reasignar Denuncia</h3>
                <form @submit.prevent="reasignarDenuncia" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nuevo Funcionario</label>
                        <select
                            v-model="formReasignar.funcionario_id"
                            class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500"
                            required
                        >
                            <option value="">Selecciona un funcionario</option>
                            <option v-for="funcionario in funcionariosArea" :key="funcionario.id" :value="funcionario.id">
                                {{ funcionario.nombre }} {{ funcionario.apellido }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Motivo (requerido)</label>
                        <textarea
                            v-model="formReasignar.motivo"
                            rows="3"
                            class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500"
                            placeholder="Motivo de la reasignación..."
                            required
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="showReasignarModal = false"
                            class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="formReasignar.processing"
                            class="flex-1 px-4 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors disabled:opacity-50"
                        >
                            Reasignar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Cambiar Prioridad -->
        <div v-if="showCambiarPrioridadModal" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 p-4" @click="showCambiarPrioridadModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">⚡ Cambiar Prioridad</h3>
                <form @submit.prevent="cambiarPrioridad" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nueva Prioridad</label>
                        <select
                            v-model="formCambiarPrioridad.prioridad_id"
                            class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-yellow-500"
                            required
                        >
                            <option value="">Selecciona una prioridad</option>
                            <option v-for="prioridad in prioridades" :key="prioridad.id" :value="prioridad.id">
                                {{ prioridad.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Motivo (opcional)</label>
                        <textarea
                            v-model="formCambiarPrioridad.motivo"
                            rows="2"
                            class="block w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-yellow-500"
                            placeholder="Motivo del cambio de prioridad..."
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="showCambiarPrioridadModal = false"
                            class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="formCambiarPrioridad.processing"
                            class="flex-1 px-4 py-2.5 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-colors disabled:opacity-50"
                        >
                            Cambiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
