<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    auth: Object,
});

const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

const menuItems = [
    { name: 'Dashboard', icon: '📊', href: '/dashboard', current: true },
    { name: 'Mis Denuncias', icon: '📝', href: '/mis-denuncias', current: false },
    { name: 'Nueva Denuncia', icon: '➕', href: '#', current: false },
    { name: 'Notificaciones', icon: '🔔', href: '#', current: false },
];
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Dashboard" />

        <!-- Sidebar para desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex flex-col flex-grow bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-xl font-bold text-white">A</span>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Alerta-Lima</h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sistema de Denuncias</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <a
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
                            'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200'
                        ]"
                    >
                        <span class="text-2xl mr-3">{{ item.icon }}</span>
                        {{ item.name }}
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center gap-3 px-3 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                {{ $page.props.auth.user.nombre.charAt(0) }}{{ $page.props.auth.user.apellido.charAt(0) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="logout"
                        class="mt-3 w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all duration-200"
                    >
                        <span class="mr-2">🚪</span>
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="lg:hidden fixed inset-0 z-50 bg-gray-900/50" @click="sidebarOpen = false">
            <div class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800" @click.stop>
                <!-- Same content as desktop sidebar -->
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top bar mobile -->
            <div class="lg:hidden sticky top-0 z-40 flex h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="px-4 text-gray-500 focus:outline-none lg:hidden"
                >
                    <span class="text-2xl">☰</span>
                </button>
                <div class="flex-1 flex items-center justify-between px-4">
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">Dashboard</h1>
                    <ThemeToggle />
                </div>
            </div>

            <!-- Page content -->
            <main class="p-6 lg:p-8">
                <!-- Welcome Header -->
                <div class="mb-8 animate-fade-in flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            ¡Bienvenido, {{ $page.props.auth.user.nombre }}! 👋
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Aquí está el resumen de tus denuncias y actividad reciente
                        </p>
                    </div>
                    <div class="hidden lg:block">
                        <ThemeToggle />
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Card 1 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl">📝</span>
                            </div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">Total</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Mis Denuncias</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl">✅</span>
                            </div>
                            <span class="text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-3 py-1 rounded-full">Resueltas</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Atendidas</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl">⏳</span>
                            </div>
                            <span class="text-xs font-medium text-yellow-700 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30 px-3 py-1 rounded-full">Activas</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">En Proceso</p>
                    </div>

                    <!-- Card 4 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <span class="text-2xl">🔔</span>
                            </div>
                            <span class="text-xs font-medium text-purple-700 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/30 px-3 py-1 rounded-full">Nuevas</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Notificaciones</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 mb-8">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                            <span class="text-2xl">➕</span>
                            <span class="font-medium">Nueva Denuncia</span>
                        </button>
                        <button class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300">
                            <span class="text-2xl">📊</span>
                            <span class="font-medium">Ver Reportes</span>
                        </button>
                        <button class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300">
                            <span class="text-2xl">⚙️</span>
                            <span class="font-medium">Configuración</span>
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Actividad Reciente</h2>
                    <div class="text-center py-12">
                        <span class="text-6xl mb-4 block">📭</span>
                        <p class="text-gray-500 dark:text-gray-400">No hay actividad reciente</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Crea tu primera denuncia para comenzar</p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out;
}
</style>
