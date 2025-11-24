<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    auth: Object,
});

const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

const menuItems = [
    { name: 'Dashboard', icon: '📊', href: '/admin', current: true },
    { name: 'Usuarios', icon: '👥', href: '/admin/usuarios', current: false },
    { name: 'Denuncias', icon: '📝', href: '/admin/denuncias', current: false },
    { name: 'Reportes', icon: '📈', href: '/admin/reportes', current: false },
    { name: 'Configuración', icon: '⚙️', href: '/admin/configuracion', current: false },
];

const stats = [
    { name: 'Total Usuarios', value: '0', icon: '👥', color: 'blue', change: '+0%' },
    { name: 'Denuncias Activas', value: '0', icon: '📝', color: 'yellow', change: '+0%' },
    { name: 'Atendidas Hoy', value: '0', icon: '✅', color: 'green', change: '+0%' },
    { name: 'Pendientes', value: '0', icon: '⏳', color: 'red', change: '-0%' },
];
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <Head title="Panel de Administración" />

        <!-- Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex flex-col flex-grow bg-gradient-to-b from-gray-900 to-gray-800 border-r border-gray-700 overflow-y-auto">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-6 py-5 border-b border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-xl font-bold text-white">A</span>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-white">Admin Panel</h1>
                            <p class="text-xs text-gray-400">Alerta-Lima</p>
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
                                ? 'bg-red-600/20 text-red-400 border-l-4 border-red-500'
                                : 'text-gray-300 hover:bg-gray-700/50 border-l-4 border-transparent',
                            'group flex items-center px-4 py-3 text-sm font-medium rounded-r-xl transition-all duration-200'
                        ]"
                    >
                        <span class="text-2xl mr-3">{{ item.icon }}</span>
                        {{ item.name }}
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="flex-shrink-0 border-t border-gray-700 p-4">
                    <div class="flex items-center gap-3 px-3 py-3 bg-gray-800/50 rounded-xl">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                {{ $page.props.auth.user.nombre.charAt(0) }}{{ $page.props.auth.user.apellido.charAt(0) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">
                                {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                            </p>
                            <p class="text-xs text-gray-400 truncate">Administrador</p>
                        </div>
                    </div>
                    <button
                        @click="logout"
                        class="mt-3 w-full flex items-center justify-center px-4 py-2.5 text-sm font-medium text-red-400 hover:bg-red-900/20 rounded-xl transition-all duration-200"
                    >
                        <span class="mr-2">🚪</span>
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 flex h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="px-4 text-gray-500 focus:outline-none lg:hidden"
                >
                    <span class="text-2xl">☰</span>
                </button>
                <div class="flex-1 flex items-center justify-between px-6">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Panel de Administración</h1>
                    <div class="flex items-center gap-4">
                        <button class="relative p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            <span class="text-2xl">🔔</span>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="p-6 lg:p-8">
                <!-- Welcome Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Bienvenido, {{ $page.props.auth.user.nombre }} 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Resumen general del sistema de gestión de denuncias
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div 
                        v-for="stat in stats" 
                        :key="stat.name"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div :class="`w-12 h-12 bg-${stat.color}-100 dark:bg-${stat.color}-900/30 rounded-xl flex items-center justify-center`">
                                <span class="text-2xl">{{ stat.icon }}</span>
                            </div>
                            <span :class="`text-xs font-medium px-3 py-1 rounded-full bg-${stat.color}-100 dark:bg-${stat.color}-900/30 text-${stat.color}-700 dark:text-${stat.color}-400`">
                                {{ stat.change }}
                            </span>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ stat.value }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ stat.name }}</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Actividad Reciente</h3>
                        <div class="space-y-4">
                            <div class="text-center py-8">
                                <span class="text-4xl mb-2 block">📊</span>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">No hay actividad reciente</p>
                            </div>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Estado del Sistema</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Base de Datos</span>
                                </div>
                                <span class="text-xs font-medium text-green-700 dark:text-green-400">Operativo</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Servidor</span>
                                </div>
                                <span class="text-xs font-medium text-green-700 dark:text-green-400">Operativo</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Notificaciones</span>
                                </div>
                                <span class="text-xs font-medium text-green-700 dark:text-green-400">Operativo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Management Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones de Gestión</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button class="flex items-center gap-3 p-4 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                            <span class="text-2xl">👥</span>
                            <span class="font-medium">Gestionar Usuarios</span>
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
            </main>
        </div>
    </div>
</template>
