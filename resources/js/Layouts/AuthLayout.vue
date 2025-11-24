<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: String,
});
</script>

<template>
    <div class="min-h-screen flex bg-white dark:bg-gray-900">
        <!-- Left Side: Image & Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900">
            <img 
                src="/images/fondo_lima.jpeg" 
                alt="Lima City" 
                class="absolute inset-0 w-full h-full object-cover opacity-50"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
            
            <div class="relative z-10 w-full flex flex-col justify-between p-12 text-white">
                <div>
                    <Link href="/" class="flex items-center gap-2 text-2xl font-bold tracking-tighter">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-lg">A</span>
                        </div>
                        Alerta-Lima
                    </Link>
                </div>
                
                <div>
                    <h2 class="text-4xl font-bold leading-tight mb-4">
                        Seguridad y control<br />para nuestra ciudad.
                    </h2>
                    <p class="text-gray-300 text-lg max-w-md">
                        Gestiona alertas, monitorea incidencias y mantén segura a la comunidad con nuestra plataforma integral.
                    </p>
                </div>
                
                <div class="text-sm text-gray-400">
                    &copy; {{ new Date().getFullYear() }} Alerta-Lima. Todos los derechos reservados.
                </div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ title }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <slot name="subtitle"></slot>
                    </p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
