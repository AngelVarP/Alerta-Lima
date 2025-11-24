<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    login: '',
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
            <Link href="/register" class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 transition-colors">
                Regístrate gratis
            </Link>
        </template>

        <Head title="Iniciar Sesión" />

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-5">
                <div class="group">
                    <InputLabel for="login" value="DNI o Correo Electrónico" class="mb-2" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-lg">🆔</span>
                        </div>
                        <TextInput 
                            id="login" 
                            v-model="form.login"
                            type="text" 
                            required 
                            placeholder="12345678 o tu@email.com"
                            class="pl-12 w-full"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.login" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Puedes ingresar con tu DNI (8 dígitos) o correo electrónico
                    </p>
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
                            autocomplete="current-password" 
                            required 
                            placeholder="••••••••"
                            class="pl-12 w-full"
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center group cursor-pointer">
                    <input 
                        id="remember-me" 
                        v-model="form.remember"
                        type="checkbox" 
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 transition-all cursor-pointer"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                        Recordarme
                    </span>
                </label>

                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <div class="space-y-3">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="w-full justify-center py-3 text-base font-semibold bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Iniciando sesión...
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <span>Iniciar Sesión</span>
                        <span>→</span>
                    </span>
                </PrimaryButton>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                            o continúa con
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    class="w-full flex items-center justify-center gap-3 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 font-medium"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuar con Google
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
