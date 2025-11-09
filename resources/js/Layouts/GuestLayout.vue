<script setup>
import { onMounted } from 'vue';

// Load theme from localStorage on mount
onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 px-4 py-8"
    >
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-3">
                PORTAL GURU SMAN 10
            </h1>
            <div class="flex justify-center mb-4">
                <img 
                    src="/images/logo.png" 
                    alt="Logo SMAN 10" 
                    class="h-24 w-auto object-contain"
                />
            </div>
        </div>

        <div
            class="w-full overflow-hidden bg-white dark:bg-gray-800 px-8 py-8 shadow-xl sm:max-w-md sm:rounded-2xl border border-gray-200 dark:border-gray-700"
        >
            <slot />
        </div>
    </div>
</template>
