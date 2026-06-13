<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Guest', email: '' });
const teacher = computed(() => {
    // Get teacher data from props if available
    return page.props.teacher || { name: '', nip: '', avatar: null };
});

// Get initials from teacher name for placeholder
const initials = computed(() => {
    if (teacher.value?.name) {
        return teacher.value.name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    }
    return 'GU';
});

// Avatar URL or placeholder
const avatarUrl = computed(() => {
    if (teacher.value?.avatar) {
        return teacher.value.avatar;
    }
    return '/images/avatar-placeholder.svg';
});

// Navigation menu items
const navItems = [
    {
        label: 'Dashboard',
        route: 'guru.dashboard',
        icon: 'dashboard',
    },
    {
        label: 'Rekap Absen',
        route: 'guru.rekap.absen',
        icon: 'recap',
    },
    {
        label: 'Kehadiran Saya',
        route: 'guru.kehadiran.index',
        icon: 'kehadiran',
    },
    {
        label: 'Ruang Wali Kelas',
        route: 'guru.wali-kelas.index',
        icon: 'wali-kelas',
    },
    {
        label: 'Profile',
        route: 'guru.profile.show',
        icon: 'profile',
    },
];

const isActive = (routeName) => {
    return route().current(routeName);
};
</script>

<template>
    <!-- Sidebar -->
    <aside
        class="fixed left-0 top-0 h-screen w-64 bg-gradient-to-b from-slate-800 to-slate-900 text-white shadow-lg z-30 transform transition-transform duration-300 ease-in-out lg:transform-none"
        :class="{ 'translate-x-0': open, '-translate-x-full': !open }"
    >
        <div class="flex flex-col h-full">
            <!-- Logo Section -->
            <div class="px-6 py-6 border-b border-slate-700">
                <Link :href="route('guru.dashboard')" class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="SMAN 10 Logo" class="w-12 h-12 rounded-lg shadow-lg" />
                    <div>
                        <p class="font-bold text-sm leading-tight">SISTEM ABSENSI</p>
                        <p class="text-xs text-blue-200 font-semibold">SMAN 10 KOTA BOGOR</p>
                    </div>
                </Link>
            </div>

            <!-- Profile Section -->
            <div class="px-6 py-6 border-b border-slate-700">
                <div class="flex flex-col items-center gap-4">
                    <!-- Avatar -->
                    <div class="relative">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-lg">
                            <img
                                v-if="teacher?.avatar"
                                :src="avatarUrl"
                                :alt="teacher?.name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else class="text-2xl font-bold text-white">
                                {{ initials }}
                            </span>
                        </div>
                        <div class="absolute bottom-0 right-0 w-5 h-5 bg-green-500 rounded-full border-2 border-slate-800"></div>
                    </div>

                    <!-- Profile Info -->
                    <div class="text-center w-full">
                        <p class="font-semibold text-base truncate">{{ teacher?.name || user.name }}</p>
                        <p class="text-xs text-slate-200 truncate">{{ user.email }}</p>
                        <p v-if="teacher?.nip" class="text-xs text-slate-200 mt-1">NIP: {{ teacher.nip }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <div class="mb-4">
                    <p class="px-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Menu</p>
                </div>

                <template v-for="item in navItems" :key="item.route">
                    <Link
                        :href="route(item.route)"
                        class="block px-4 py-3 rounded-lg transition duration-200 ease-in-out"
                        :class="[
                            isActive(item.route)
                                ? 'bg-blue-600 text-white font-semibold shadow-md'
                                : 'text-slate-300 hover:bg-slate-700'
                        ]"
                        @click="$emit('close')"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Icons -->
                            <svg v-if="item.icon === 'dashboard'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m2 3l2-3m2 3l2-3m2-4l2 3m-2-3V7a2 2 0 012-2h4.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2h-4.414a1 1 0 01-.707-.293l-3.414-3.414A1 1 0 0110 19V9z" />
                            </svg>
                            <svg v-else-if="item.icon === 'recap'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <svg v-else-if="item.icon === 'kehadiran'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-10 4h10m-10 4h10M3 8h18v13a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                            </svg>
                            <svg v-else-if="item.icon === 'wali-kelas'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <svg v-else-if="item.icon === 'profile'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="font-medium">{{ item.label }}</span>
                        </div>
                    </Link>
                </template>
            </nav>

            <!-- Logout Button -->
            <div class="px-4 py-6 border-t border-slate-700">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center gap-2"
                    @click="$emit('close')"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </Link>
            </div>

            <!-- Version Footer -->
            <div class="px-4 py-3 border-t border-slate-700 text-center text-xs text-slate-200">
                <p>v1.0 SMAN 10</p>
            </div>
        </div>
    </aside>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.5);
}
</style>
