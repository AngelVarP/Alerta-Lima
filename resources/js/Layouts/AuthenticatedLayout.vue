<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const page = usePage();

// Obtener contador de notificaciones no leídas desde props compartidas
const noLeidasCount = computed(() => page.props.noLeidasCount || 0);

// Función para verificar si una ruta está activa
const isCurrentRoute = (href) => {
    const currentUrl = page.url;
    
    if (href === '/dashboard') return currentUrl === '/dashboard';
    if (href === '/mis-denuncias') return currentUrl === '/mis-denuncias' || currentUrl.startsWith('/denuncias/');
    if (href === '/denuncias/nueva') return currentUrl === '/denuncias/nueva';
    if (href === '/notificaciones') return currentUrl === '/notificaciones';
    
    return currentUrl === href;
};

// Define el menú del sidebar con detección dinámica de ruta activa
const menuItems = computed(() => [
    { name: 'Dashboard', icon: '📊', href: '/dashboard', current: isCurrentRoute('/dashboard') },
    { name: 'Mis Denuncias', icon: '📝', href: '/mis-denuncias', current: isCurrentRoute('/mis-denuncias') },
    { name: 'Nueva Denuncia', icon: '➕', href: '/denuncias/nueva', current: isCurrentRoute('/denuncias/nueva') },
    { name: 'Notificaciones', icon: '🔔', href: '/notificaciones', current: isCurrentRoute('/notificaciones'), badge: noLeidasCount.value },
]);

const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">

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
                    <Link
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
                            'group flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200'
                        ]"
                    >
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ item.icon }}</span>
                            {{ item.name }}
                        </div>
                        <span
                            v-if="item.badge && item.badge > 0"
                            class="px-2.5 py-1 text-xs font-bold bg-red-500 text-white rounded-full"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
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
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <Link
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="item.href"
                        @click="sidebarOpen = false"
                        :class="[
                            item.current
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
                            'group flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200'
                        ]"
                    >
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ item.icon }}</span>
                            {{ item.name }}
                        </div>
                        <span
                            v-if="item.badge && item.badge > 0"
                            class="px-2.5 py-1 text-xs font-bold bg-red-500 text-white rounded-full"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Main content container -->
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
                    <slot name="header">
                         <h1 class="text-xl font-bold text-gray-900 dark:text-white">Panel</h1>
                    </slot>
                    <div class="flex items-center gap-4">
                        <ThemeToggle />
                        <Link href="/notificaciones" class="relative p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <span class="text-2xl">🔔</span>
                            <span v-if="noLeidasCount > 0" class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                {{ noLeidasCount }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>