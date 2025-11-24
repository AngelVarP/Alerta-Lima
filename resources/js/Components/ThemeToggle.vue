<template>
    <button 
        @click="toggleTheme" 
        class="relative p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-yellow-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        aria-label="Toggle Dark Mode"
    >
        <div class="relative w-6 h-6 overflow-hidden">
            <!-- Sun Icon -->
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                class="absolute inset-0 w-6 h-6 transform transition-transform duration-500"
                :class="isDark ? 'rotate-90 opacity-0' : 'rotate-0 opacity-100'"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            
            <!-- Moon Icon -->
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                class="absolute inset-0 w-6 h-6 transform transition-transform duration-500"
                :class="isDark ? 'rotate-0 opacity-100' : '-rotate-90 opacity-0'"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </div>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const isDark = ref(false);

const applyTheme = () => {
    const root = document.documentElement;
    if (isDark.value) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    applyTheme();
};

onMounted(() => {
    const saved = localStorage.getItem('theme');
    if (saved) {
        isDark.value = saved === 'dark';
    } else {
        // Respect OS preference on first load
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyTheme();
});
</script>
