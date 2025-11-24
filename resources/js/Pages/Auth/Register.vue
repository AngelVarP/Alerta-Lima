<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    dni: '',
    telefono: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <AuthLayout title="Crea tu cuenta">
        <template #subtitle>
            ¿Ya tienes una cuenta? 
            <Link href="/" class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 transition-colors">
                Inicia sesión
            </Link>
        </template>

        <Head title="Registro" />

        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="group">
                    <InputLabel for="nombre" value="Nombre" class="mb-2" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-lg">👤</span>
                        </div>
                        <TextInput 
                            id="nombre" 
                            v-model="form.nombre"
                            type="text" 
                            required 
                            placeholder="Juan"
                            class="pl-12 w-full"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.nombre" />
                </div>

                <div class="group">
                    <InputLabel for="apellido" value="Apellido" class="mb-2" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-lg">👤</span>
                        </div>
                        <TextInput 
                            id="apellido" 
                            v-model="form.apellido"
                            type="text" 
                            required 
                            placeholder="Pérez"
                            class="pl-12 w-full"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.apellido" />
                </div>
            </div>

            <div class="group">
                <InputLabel for="email" value="Correo Electrónico" class="mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">📧</span>
                    </div>
                    <TextInput 
                        id="email" 
                        v-model="form.email"
                        type="email" 
                        autocomplete="email" 
                        required 
                        placeholder="tu@email.com"
                        class="pl-12 w-full"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="group">
                <InputLabel for="dni" class="mb-2">
                    <span>DNI</span>
                    <span class="text-red-500 ml-1">*</span>
                </InputLabel>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">🆔</span>
                    </div>
                    <TextInput 
                        id="dni" 
                        v-model="form.dni"
                        type="text" 
                        required
                        placeholder="12345678"
                        maxlength="8"
                        pattern="[0-9]{8}"
                        class="pl-12 w-full"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.dni" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Ingresa tu DNI de 8 dígitos (obligatorio)
                </p>
            </div>

            <div class="group">
                <InputLabel for="telefono" value="Teléfono (Opcional)" class="mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">📱</span>
                    </div>
                    <TextInput 
                        id="telefono" 
                        v-model="form.telefono"
                        type="tel" 
                        placeholder="987654321"
                        class="pl-12 w-full"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.telefono" />
            </div>


            <div class="group">
                <InputLabel for="password" value="Contraseña" class="mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">🔒</span>
                    </div>
                    <TextInput 
                        id="password" 
                        v-model="form.password"
                        type="password" 
                        autocomplete="new-password" 
                        required 
                        placeholder="Mínimo 8 caracteres"
                        class="pl-12 w-full"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="group">
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 text-lg">🔒</span>
                    </div>
                    <TextInput 
                        id="password_confirmation" 
                        v-model="form.password_confirmation"
                        type="password" 
                        autocomplete="new-password" 
                        required 
                        placeholder="Repite tu contraseña"
                        class="pl-12 w-full"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="w-full justify-center py-3 text-base font-semibold bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creando cuenta...
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <span>Crear Cuenta</span>
                        <span>→</span>
                    </span>
                </PrimaryButton>

                <p class="mt-4 text-xs text-center text-gray-500 dark:text-gray-400">
                    Al registrarte, aceptas nuestros 
                    <a href="#" class="text-blue-600 hover:text-blue-500 dark:text-blue-400">Términos de Servicio</a>
                    y 
                    <a href="#" class="text-blue-600 hover:text-blue-500 dark:text-blue-400">Política de Privacidad</a>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
