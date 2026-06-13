<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '@/Components/Guru/Sidebar.vue';

defineProps({
    title: {
        type: String,
        default: 'Guru Portal',
    },
});

const sidebarOpen = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <Sidebar :open="sidebarOpen" @close="closeSidebar" />

        <!-- Main Content -->
        <div class="lg:ml-64">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
                <div class="px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <!-- Mobile Menu Button -->
                        <button
                            @click="toggleSidebar"
                            class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                        >
                            <svg
                                class="h-6 w-6"
                                :class="{ 'hidden': sidebarOpen, 'block': !sidebarOpen }"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <h1 class="text-xl font-semibold text-gray-900">{{ title }}</h1>

                        <!-- Spacer -->
                        <div class="w-10"></div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-6 px-4 sm:px-6 lg:px-8">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-500">
                    <p>&copy; 2026 Sistem Absensi SMAN 10. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
:deep(.sidebar-enter-active) {
    transition: all 0.3s ease;
}

:deep(.sidebar-leave-active) {
    transition: all 0.3s ease;
}
</style>
