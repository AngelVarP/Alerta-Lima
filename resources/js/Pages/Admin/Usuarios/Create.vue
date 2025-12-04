<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    roles: Array,
    areas: Array,
});

const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    dni: '',
    telefono: '',
    direccion: '',
    area_id: '',
    password: '',
    password_confirmation: '',
    roles: [],
});

const toggleRole = (roleId) => {
    const index = form.roles.indexOf(roleId);
    if (index === -1) {
        form.roles.push(roleId);
    } else {
        form.roles.splice(index, 1);
    }
};

const submit = () => {
    form.post('/admin/usuarios', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirigir al index tras éxito
        },
    });
};
</script>

<template>
    <Head title="Crear Usuario" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                ➕ Crear Nuevo Usuario
            </h2>
        </template>

        <div class="max-w-4xl">

            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <Link href="/admin/usuarios" class="hover:text-red-600 dark:hover:text-red-400">
                        Usuarios
                    </Link>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white">Crear</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Usuario</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Complete el formulario para crear un nuevo usuario del sistema
                </p>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

                    <!-- Información Personal -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Información Personal</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nombre <span class="text-red-600">*</span>
                                </label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Juan"
                                    required
                                />
                                <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.nombre }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Apellido
                                </label>
                                <input
                                    v-model="form.apellido"
                                    type="text"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Pérez García"
                                />
                                <p v-if="form.errors.apellido" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.apellido }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email <span class="text-red-600">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="juan.perez@ejemplo.com"
                                    required
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    DNI
                                </label>
                                <input
                                    v-model="form.dni"
                                    type="text"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="12345678"
                                    maxlength="15"
                                />
                                <p v-if="form.errors.dni" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.dni }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Teléfono
                                </label>
                                <input
                                    v-model="form.telefono"
                                    type="text"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="987654321"
                                    maxlength="20"
                                />
                                <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.telefono }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Dirección
                                </label>
                                <input
                                    v-model="form.direccion"
                                    type="text"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Av. Principal 123, Lima"
                                />
                                <p v-if="form.errors.direccion" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.direccion }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Asignación de Área -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Área de Trabajo</h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Área
                            </label>
                            <select
                                v-model="form.area_id"
                                class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent appearance-none cursor-pointer"
                            >
                                <option value="">Seleccione un área</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">
                                    {{ area.nombre }}
                                </option>
                            </select>
                            <p v-if="form.errors.area_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.area_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                            Roles <span class="text-red-600">*</span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label
                                v-for="rol in roles"
                                :key="rol.id"
                                class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all"
                                :class="form.roles.includes(rol.id)
                                    ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                            >
                                <input
                                    type="checkbox"
                                    :checked="form.roles.includes(rol.id)"
                                    @change="toggleRole(rol.id)"
                                    class="w-5 h-5 text-red-600 rounded focus:ring-red-500 focus:ring-2"
                                />
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ rol.nombre }}</span>
                                    <p v-if="rol.descripcion" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ rol.descripcion }}
                                    </p>
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.roles" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.roles }}
                        </p>
                    </div>

                    <!-- Contraseña -->
                    <div class="p-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Contraseña</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Contraseña <span class="text-red-600">*</span>
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Mínimo 8 caracteres"
                                    required
                                />
                                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Confirmar Contraseña <span class="text-red-600">*</span>
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Repita la contraseña"
                                    required
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <Link
                        href="/admin/usuarios"
                        class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium rounded-xl transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Creando...</span>
                        <span v-else>Crear Usuario</span>
                    </button>
                </div>
            </form>

        </div>
    </AdminLayout>
</template>
