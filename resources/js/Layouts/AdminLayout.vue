<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const page = usePage();
const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

// Función para verificar si una ruta está activa
const isCurrentRoute = (href) => {
    const currentUrl = page.url;

    if (href === '/admin' || href === '/admin/dashboard') {
        return currentUrl === '/admin' || currentUrl === '/admin/dashboard';
    }
    if (href.startsWith('/admin/usuarios')) {
        return currentUrl.startsWith('/admin/usuarios');
    }
    if (href.startsWith('/admin/auditoria')) {
        return currentUrl.startsWith('/admin/auditoria');
    }
    if (href.startsWith('/admin/seguridad')) {
        return currentUrl.startsWith('/admin/seguridad');
    }
    if (href.startsWith('/admin/reportes')) {
        return currentUrl.startsWith('/admin/reportes');
    }

    return currentUrl === href;
};

const menuItems = computed(() => [
    { name: 'Dashboard', icon: '📊', href: '/admin/dashboard', current: isCurrentRoute('/admin/dashboard') },
    { name: 'Usuarios', icon: '👥', href: '/admin/usuarios', current: isCurrentRoute('/admin/usuarios') },
    { name: 'Auditoría', icon: '📋', href: '/admin/auditoria', current: isCurrentRoute('/admin/auditoria') },
    { name: 'Seguridad', icon: '🔒', href: '/admin/seguridad', current: isCurrentRoute('/admin/seguridad') },
    { name: 'Reportes', icon: '📈', href: '/admin/reportes', current: isCurrentRoute('/admin/reportes') },
]);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">

        <!-- Sidebar para desktop -->
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
                    <Link
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
                    </Link>
                </nav>

                <!-- User Profile -->
                <div class="flex-shrink-0 border-t border-gray-700 p-4">
                    <div class="px-3 py-3 bg-gray-800/50 rounded-xl">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                    {{ $page.props.auth.user.nombre[0].toUpperCase() }}{{ $page.props.auth.user.apellido[0].toUpperCase() }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-white truncate">
                                    {{ $page.props.auth.user.nombre }} {{ $page.props.auth.user.apellido }}
                                </p>
                                <p class="text-xs text-gray-400 truncate">Administrador</p>
                            </div>
                        </div>
                        <button
                            @click="logout"
                            class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-red-400 hover:bg-red-900/20 rounded-lg transition-all duration-200 border border-red-800"
                        >
                            <span class="mr-2">🚪</span>
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="lg:hidden fixed inset-0 z-50 bg-gray-900/50" @click="sidebarOpen = false">
            <div class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-900 to-gray-800" @click.stop>
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
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <Link
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="item.href"
                        @click="sidebarOpen = false"
                        :class="[
                            item.current
                                ? 'bg-red-600/20 text-red-400 border-l-4 border-red-500'
                                : 'text-gray-300 hover:bg-gray-700/50 border-l-4 border-transparent',
                            'group flex items-center px-4 py-3 text-sm font-medium rounded-r-xl transition-all duration-200'
                        ]"
                    >
                        <span class="text-2xl mr-3">{{ item.icon }}</span>
                        {{ item.name }}
                    </Link>
                </nav>
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
                <div class="flex-1 flex items-center justify-between px-4 lg:px-6">
                    <slot name="header">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Panel de Administración</h1>
                    </slot>
                    <div class="flex items-center gap-4">
                        <ThemeToggle />
                        <button class="relative p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            <span class="text-2xl">🔔</span>
                        </button>
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
