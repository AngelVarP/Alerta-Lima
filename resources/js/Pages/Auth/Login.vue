<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <AuthLayout title="Bienvenido de nuevo">
        <template #subtitle>
            ¿No tienes una cuenta? 
            <Link href="/register" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                Regístrate gratis
            </Link>
        </template>

        <Head title="Iniciar Sesión" />

        <form @submit.prevent="submit" class="mt-8 space-y-6">
            <div class="space-y-4">
                <div>
                    <InputLabel for="email" value="Correo Electrónico" />
                    <div class="mt-1">
                        <TextInput 
                            id="email" 
                            v-model="form.email"
                            type="email" 
                            autocomplete="email" 
                            required 
                            placeholder="nombre@ejemplo.com"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                </div>

                <div>
                    <InputLabel for="password" value="Contraseña" />
                    <div class="mt-1">
                        <TextInput 
                            id="password" 
                            v-model="form.password"
                            type="password" 
                            autocomplete="current-password" 
                            required 
                            placeholder="••••••••"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input 
                        id="remember-me" 
                        v-model="form.remember"
                        type="checkbox" 
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700"
                    />
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        Recordarme
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>

            <div>
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">Iniciando sesión...</span>
                    <span v-else>Iniciar Sesión</span>
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
