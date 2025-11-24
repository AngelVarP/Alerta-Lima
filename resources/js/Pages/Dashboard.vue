<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="Dashboard" />

        <!-- Navbar -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                <span class="text-lg font-bold text-white">A</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">Alerta-Lima</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}</span>
                        </div>
                        <button
                            @click="logout"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                        >
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h2 class="text-2xl font-bold mb-4">¡Bienvenido al Dashboard!</h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            Has iniciado sesión exitosamente en el sistema de gestión de denuncias ciudadanas.
                        </p>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <h3 class="font-semibold text-blue-900 dark:text-blue-100">Mis Denuncias</h3>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">0</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                <h3 class="font-semibold text-green-900 dark:text-green-100">Atendidas</h3>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">0</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                                <h3 class="font-semibold text-yellow-900 dark:text-yellow-100">En Proceso</h3>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
