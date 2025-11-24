<script setup>
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    title: String,
});
</script>

<template>
    <div class="min-h-screen flex">
        <!-- Left side - Image with overlay -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-purple-800">
            <!-- Animated background -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
            </div>

            <!-- Background image -->
            <div 
                class="absolute inset-0 bg-cover bg-center"
                style="background-image: url('/images/fondo_lima.jpeg'); opacity: 0.30;"
            ></div>

            <!-- Content overlay -->
            <div class="relative z-10 flex flex-col justify-center px-12 text-white">
                <div class="animate-fade-in-up">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-2xl">
                            <span class="text-3xl font-bold">A</span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">Alerta-Lima</h1>
                            <p class="text-blue-100">Sistema de Gestión de Denuncias</p>
                        </div>
                    </div>

                    <h2 class="text-4xl font-bold mb-6 leading-tight">
                        Tu voz importa para<br />mejorar nuestra ciudad
                    </h2>
                    
                    <p class="text-lg text-blue-100 mb-8 leading-relaxed">
                        Reporta problemas, da seguimiento a tus denuncias y contribuye<br />
                        a hacer de Lima una ciudad mejor para todos.
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3 animate-fade-in-up animation-delay-200">
                            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <span class="text-2xl">✅</span>
                            </div>
                            <div>
                                <p class="font-semibold">Reporta fácilmente</p>
                                <p class="text-sm text-blue-100">Crea denuncias en segundos</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 animate-fade-in-up animation-delay-400">
                            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <span class="text-2xl">📊</span>
                            </div>
                            <div>
                                <p class="font-semibold">Seguimiento en tiempo real</p>
                                <p class="text-sm text-blue-100">Monitorea el estado de tus reportes</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 animate-fade-in-up animation-delay-600">
                            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <span class="text-2xl">🔔</span>
                            </div>
                            <div>
                                <p class="font-semibold">Notificaciones instantáneas</p>
                                <p class="text-sm text-blue-100">Recibe actualizaciones al instante</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900">
            <div class="absolute top-4 right-4">
                <ThemeToggle />
            </div>
            <div class="w-full max-w-md animate-fade-in">
                <!-- Logo mobile -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-xl font-bold text-white">A</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alerta-Lima</h1>
                    </div>
                </div>

                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ title }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        <slot name="subtitle" />
                    </p>
                </div>

                <!-- Form content -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                    <slot />
                </div>

                <!-- Footer -->
                <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                    © 2025 Municipalidad de Lima. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out;
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-200 {
    animation-delay: 0.2s;
}

.animation-delay-400 {
    animation-delay: 0.4s;
}

.animation-delay-600 {
    animation-delay: 0.6s;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
