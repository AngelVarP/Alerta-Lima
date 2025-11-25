<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    auth: Object,
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            resueltas: 0,
            en_proceso: 0,
            notificaciones: 0
        })
    },
    activities: {
        type: Array,
        default: () => []
    }
});

const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

const menuItems = [
    { name: 'Dashboard', icon: '📊', href: '/dashboard', current: true },
    { name: 'Mis Denuncias', icon: '📝', href: '/mis-denuncias', current: false },
    { name: 'Nueva Denuncia', icon: '➕', href: '/denuncias/nueva', current: false },
    { name: 'Notificaciones', icon: '🔔', href: '/notificaciones', current: false },
];

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-PE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'pendiente': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
        case 'en proceso': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        case 'atendido': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'rechazado': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    ¡Bienvenido, {{ $page.props.auth.user.nombre }}! 👋
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Aquí está el resumen de tus denuncias y actividad reciente
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 dark:border-gray-700 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <span class="text-2xl">📝</span>
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">Total</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.total }}</h3>
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
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.resueltas }}</h3>
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
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.en_proceso }}</h3>
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
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stats.notificaciones }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Notificaciones</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link href="/denuncias/nueva" class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">➕</span>
                        <span class="font-medium">Nueva Denuncia</span>
                    </Link>
                    <Link href="/mis-denuncias" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">📊</span>
                        <span class="font-medium">Ver Mis Denuncias</span>
                    </Link>
                    <Link href="/perfil" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-xl transition-all duration-300 group">
                        <span class="text-2xl group-hover:scale-110 transition-transform">⚙️</span>
                        <span class="font-medium">Configuración</span>
                    </Link>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Actividad Reciente</h2>
                    <Link href="/mis-denuncias" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Ver todas</Link>
                </div>
                
                <div v-if="activities.length === 0" class="text-center py-12">
                    <span class="text-6xl mb-4 block">📭</span>
                    <p class="text-gray-500 dark:text-gray-400">No hay actividad reciente</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Crea tu primera denuncia para comenzar</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="activity in activities" :key="activity.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="getStatusColor(activity.estado?.nombre)">
                                <span class="text-lg">📄</span>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ activity.titulo }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.categoria?.nombre }} • {{ formatDate(activity.created_at) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="getStatusColor(activity.estado?.nombre)">
                                {{ activity.estado?.nombre }}
                            </span>
                            <Link :href="`/denuncias/${activity.id}`" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400">
                                ➡️
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
