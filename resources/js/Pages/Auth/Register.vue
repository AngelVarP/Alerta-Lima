<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <AuthLayout title="Crear una cuenta">
        <template #subtitle>
            ¿Ya tienes una cuenta? 
            <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                Inicia sesión aquí
            </Link>
        </template>

        <Head title="Registrarse" />

        <form @submit.prevent="submit" class="mt-8 space-y-6">
            <div class="space-y-4">
                <div>
                    <InputLabel for="name" value="Nombre Completo" />
                    <div class="mt-1">
                        <TextInput 
                            id="name" 
                            v-model="form.name"
                            type="text" 
                            autocomplete="name" 
                            required 
                            placeholder="Juan Pérez"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>
                </div>

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
                            autocomplete="new-password" 
                            required 
                            placeholder="••••••••"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                    <div class="mt-1">
                        <TextInput 
                            id="password_confirmation" 
                            v-model="form.password_confirmation"
                            type="password" 
                            autocomplete="new-password" 
                            required 
                            placeholder="••••••••"
                        />
                    </div>
                </div>
            </div>

            <div>
                <PrimaryButton :disabled="form.processing">
                    <span v-if="form.processing">Creando cuenta...</span>
                    <span v-else>Registrarse</span>
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
