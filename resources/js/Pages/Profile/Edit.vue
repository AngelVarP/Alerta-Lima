<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { route } from '../../../../vendor/tightenco/ziggy';
import { Ziggy } from '../../ziggy';

const user = usePage().props.auth.user;

const form = useForm({
    nombre: user.nombre,
    apellido: user.apellido,
    email: user.email,
    dni: user.dni || '', // Handle potential nulls
    telefono: user.telefono || '',
    direccion: user.direccion || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    form.patch(route('profile.update', undefined, false, Ziggy), {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Profile update errors:', errors);
        },
    });
};

const updatePassword = () => {
    passwordForm.put(route('password.update', undefined, false, Ziggy), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <Head title="Mi Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                Mi Perfil
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ user.nombre.charAt(0) }}{{ user.apellido?.charAt(0) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ user.nombre }} {{ user.apellido }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Administra tu información personal y seguridad
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Profile Information -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <section>
                        <header class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <span class="text-xl">👤</span>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Información Personal</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Actualiza tus datos de contacto y dirección.
                                </p>
                            </div>
                        </header>

                        <form @submit.prevent="updateProfile" class="space-y-5">
                            <!-- Error Summary -->
                            <div v-if="form.hasErrors" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                                <p class="font-medium text-red-800 dark:text-red-300 mb-1">No se pudieron guardar los cambios:</p>
                                <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                </ul>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <InputLabel for="nombre" value="Nombre" />
                                    <TextInput
                                        id="nombre"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                        v-model="form.nombre"
                                        required
                                        autofocus
                                        autocomplete="name"
                                    />
                                    <InputError class="mt-2" :message="form.errors.nombre" />
                                </div>

                                <div>
                                    <InputLabel for="apellido" value="Apellido" />
                                    <TextInput
                                        id="apellido"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                        v-model="form.apellido"
                                        required
                                        autocomplete="family-name"
                                    />
                                    <InputError class="mt-2" :message="form.errors.apellido" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="email" value="Correo Electrónico" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    v-model="form.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <InputLabel for="dni" value="DNI" />
                                    <TextInput
                                        id="dni"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 cursor-not-allowed opacity-75"
                                        v-model="form.dni"
                                        maxlength="8"
                                        disabled
                                    />
                                    <InputError class="mt-2" :message="form.errors.dni" />
                                </div>

                                <div>
                                    <InputLabel for="telefono" value="Teléfono" />
                                    <TextInput
                                        id="telefono"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                        v-model="form.telefono"
                                    />
                                    <InputError class="mt-2" :message="form.errors.telefono" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="direccion" value="Dirección" />
                                <TextInput
                                    id="direccion"
                                    type="text"
                                    class="mt-1 block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    v-model="form.direccion"
                                />
                                <InputError class="mt-2" :message="form.errors.direccion" />
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton 
                                    :disabled="form.processing"
                                    class="rounded-xl bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800"
                                >
                                    Guardar Cambios
                                </PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 font-medium flex items-center gap-1">
                                        <span>✓</span> Guardado
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Update Password -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <section>
                        <header class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400">
                                <span class="text-xl">🔒</span>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Seguridad</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Actualiza tu contraseña para mantener tu cuenta segura.
                                </p>
                            </div>
                        </header>

                        <form @submit.prevent="updatePassword" class="space-y-5">
                            <div>
                                <InputLabel for="current_password" value="Contraseña Actual" />
                                <TextInput
                                    id="current_password"
                                    ref="currentPasswordInput"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="mt-1 block w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    autocomplete="current-password"
                                />
                                <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Nueva Contraseña" />
                                <TextInput
                                    id="password"
                                    ref="passwordInput"
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="mt-1 block w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    autocomplete="new-password"
                                />
                                <InputError :message="passwordForm.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                    autocomplete="new-password"
                                />
                                <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4 pt-2">
                                <PrimaryButton 
                                    :disabled="passwordForm.processing"
                                    class="rounded-xl bg-purple-600 hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800"
                                >
                                    Actualizar Contraseña
                                </PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 font-medium flex items-center gap-1">
                                        <span>✓</span> Guardado
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
