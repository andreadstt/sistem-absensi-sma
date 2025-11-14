<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    schedules: {
        type: Array,
        default: () => []
    },
    myClasses: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    today: {
        type: String,
        default: ''
    },
    teacherName: {
        type: String,
        default: ''
    },
    message: {
        type: String,
        default: ''
    }
});

// Real-time clock
const currentDateTime = ref('');
let intervalId = null;

const updateDateTime = () => {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric'
    };
    const dateStr = now.toLocaleDateString('en-US', options);
    const timeStr = now.toLocaleTimeString('en-US', { hour12: false });
    currentDateTime.value = `${dateStr} - ${timeStr}`;
};

onMounted(() => {
    updateDateTime();
    intervalId = setInterval(updateDateTime, 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

// `time_slot` is now stored as a string (e.g. "07:00-08:00"). We'll display it directly in the template.
</script>

<template>
    <Head title="Guru Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Guru</h2>
        </template>

        <div class="page-container">
            <div class="content-wrapper">
                <!-- Welcome Section -->
                <div class="guru-welcome-card mb-6 p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="text-white">
                            <h3 class="text-2xl font-bold">Welcome, {{ teacherName }}</h3>
                            <p class="text-white/90 font-medium mt-1">{{ currentDateTime }}</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Today's Classes -->
                    <div class="stat-card p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="stat-card-icon bg-blue-100 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-sm font-semibold mb-1">Today's Classes</div>
                        <div class="text-3xl font-bold text-gray-900">{{ stats.totalSchedulesToday }}</div>
                        <div class="text-gray-500 text-xs font-medium mt-1">Scheduled for today</div>
                    </div>

                    <!-- Total Classes -->
                    <div class="stat-card p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="stat-card-icon bg-purple-100 text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-sm font-semibold mb-1">My Classes</div>
                        <div class="text-3xl font-bold text-gray-900">{{ stats.totalClasses }}</div>
                        <div class="text-gray-500 text-xs font-medium mt-1">Classes you teach</div>
                    </div>

                    <!-- Total Students -->
                    <div class="stat-card p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="stat-card-icon bg-green-100 text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-sm font-semibold mb-1">Total Students</div>
                        <div class="text-3xl font-bold text-gray-900">{{ stats.totalStudents }}</div>
                        <div class="text-gray-500 text-xs font-medium mt-1">Across all classes</div>
                    </div>

                    <!-- Subjects -->
                    <div class="stat-card p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="stat-card-icon bg-orange-100 text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-sm font-semibold mb-1">Subjects</div>
                        <div class="text-3xl font-bold text-gray-900">{{ stats.totalSubjects }}</div>
                        <div class="text-gray-500 text-xs font-medium mt-1">Different subjects</div>
                    </div>
                </div>

                <!-- Message if no teacher profile -->
                <div v-if="message" class="alert alert-warning shadow-xl mb-8 border-l-4 border-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-7 w-7" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-bold text-lg">{{ message }}</span>
                </div>

                <!-- Today's Schedule -->
                <div class="mb-10">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-4xl font-black flex items-center gap-3 text-gray-900">
                            <div class="p-3 bg-gradient-to-br from-primary to-primary/80 rounded-2xl shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">Today's Schedule</span>
                        </h4>
                        <div class="badge badge-primary badge-lg font-bold text-sm px-6 py-4 shadow-lg">
                            {{ schedules.length }} {{ schedules.length === 1 ? 'Class' : 'Classes' }}
                        </div>
                    </div>

                    <!-- No schedules message -->
                    <div v-if="schedules.length === 0 && !message" class="relative overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl shadow-lg p-8">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-blue-200/20 rounded-full blur-2xl"></div>
                        <div class="relative flex items-center gap-6">
                            <div class="p-6 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-16 h-16 stroke-white stroke-[2]">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="text-2xl font-black text-gray-900 mb-2">No Classes Today! 🎉</h5>
                                <p class="text-lg font-semibold text-gray-600">Enjoy your day off and recharge for tomorrow!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule cards grid -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="schedule in schedules"
                            :key="schedule.id"
                            :href="`/guru/absensi/${schedule.class_room_id}/${schedule.subject_id}/${new Date().toISOString().split('T')[0]}`"
                            class="group relative overflow-hidden bg-gradient-to-br from-white to-indigo-50/30 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 cursor-pointer border-2 border-indigo-100/50 hover:border-indigo-400 hover:-translate-y-3 hover:scale-105"
                        >
                            <!-- Gradient overlay on hover -->
                            <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            
                            <!-- Top accent line -->
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-purple-500 to-pink-500"></div>
                            
                            <div class="relative p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-3 bg-gradient-to-br from-primary to-primary/80 rounded-xl shadow-md group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Time</div>
                                            <div class="text-lg text-gray-900 font-black">
                                                {{ schedule.time_slot }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="badge badge-primary badge-lg shadow-md font-bold text-xs px-4 py-3 group-hover:scale-110 transition-transform duration-300">
                                        TODAY
                                    </div>
                                </div>
                                
                                <h2 class="text-2xl font-black text-gray-900 group-hover:text-primary transition-colors duration-300 mb-4 leading-tight">
                                    {{ schedule.subject_name }}
                                </h2>
                                
                                <div class="flex items-center gap-3 mb-6 p-3 bg-white/80 rounded-xl border border-gray-100">
                                    <div class="p-2 bg-gradient-to-br from-secondary to-purple-500 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="font-black text-gray-900 text-base">
                                        {{ schedule.class_name }}
                                    </span>
                                </div>

                                <button class="w-full btn btn-primary gap-2 group-hover:shadow-xl font-bold text-base py-3 rounded-xl group/button">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Take Attendance
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/button:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- My Classes Section -->
                <div v-if="myClasses && myClasses.length > 0" class="mb-10">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-4xl font-black flex items-center gap-3 text-gray-900">
                            <div class="p-3 bg-gradient-to-br from-secondary to-purple-600 rounded-2xl shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="bg-gradient-to-r from-secondary to-pink-600 bg-clip-text text-transparent">My Classes</span>
                        </h4>
                        <div class="badge badge-secondary badge-lg font-bold text-sm px-6 py-4 shadow-lg">
                            {{ myClasses.length }} {{ myClasses.length === 1 ? 'Class' : 'Classes' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="classData in myClasses"
                            :key="classData.class_room_id"
                            :href="route('guru.kelas.show', classData.class_room_id)"
                            class="group relative overflow-hidden bg-gradient-to-br from-white to-purple-50/30 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-3 hover:scale-105 transition-all duration-500 cursor-pointer border-2 border-purple-100/50 hover:border-purple-400"
                        >
                            <!-- Gradient overlay on hover -->
                            <div class="absolute inset-0 bg-gradient-to-br from-secondary/10 via-pink-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            
                            <!-- Top accent line -->
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-secondary via-purple-500 to-pink-500"></div>
                            
                            <div class="relative p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="p-3 bg-gradient-to-br from-secondary to-purple-600 rounded-xl shadow-md group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div class="badge badge-secondary badge-lg shadow-md font-bold text-xs px-4 py-3 group-hover:scale-110 transition-transform duration-300">
                                        CLASS
                                    </div>
                                </div>
                                
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-secondary transition-colors duration-300 mb-4">
                                    {{ classData.class_name }}
                                </h3>
                                
                                <div class="flex items-center gap-3 mb-6 p-3 bg-white/80 rounded-xl border border-gray-100">
                                    <div class="p-2 bg-gradient-to-br from-accent to-green-500 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-2xl font-black text-accent">{{ classData.student_count }}</span>
                                        <span class="ml-2 font-bold text-gray-700 text-base">{{ classData.student_count === 1 ? 'Student' : 'Students' }}</span>
                                    </div>
                                </div>

                                <div class="mb-5">
                                    <p class="text-sm text-gray-600 mb-3 font-bold uppercase tracking-wider flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Subjects:
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="subject in classData.subjects"
                                            :key="subject.id"
                                            class="badge badge-info hover:badge-info hover:scale-105 transition-all text-xs font-bold py-3 px-4 shadow-sm"
                                        >
                                            {{ subject.name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Schedules Section -->
                                <div v-if="classData.schedules && classData.schedules.length > 0" class="mb-5 border-t-2 border-gray-100 pt-5">
                                    <p class="text-sm text-gray-600 mb-3 font-bold uppercase tracking-wider flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Schedule:
                                    </p>
                                    <div class="space-y-2 max-h-36 overflow-y-auto custom-scrollbar">
                                        <div
                                            v-for="(schedule, idx) in classData.schedules"
                                            :key="idx"
                                            class="flex items-center justify-between text-sm bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-3 border border-indigo-100 hover:shadow-md transition-shadow"
                                        >
                                            <div class="flex items-center gap-2">
                                                <span class="badge badge-primary font-bold text-xs px-3 py-2">{{ schedule.day }}</span>
                                                <span class="font-black text-gray-900 text-xs">{{ schedule.time_slot }}</span>
                                            </div>
                                            <span class="text-xs font-bold text-gray-700 bg-white px-3 py-1 rounded-lg">{{ schedule.subject_name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <button class="w-full btn btn-secondary gap-2 group-hover:shadow-xl font-bold text-base py-3 rounded-xl group/button">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View Details
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/button:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar for schedule list */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #8b5cf6, #a855f7);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #7c3aed, #9333ea);
}

/* Pulse animation for decorative elements */
@keyframes pulse {
    0%, 100% {
        opacity: 0.6;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

.animate-pulse {
    animation: pulse 3s ease-in-out infinite;
}
</style>
