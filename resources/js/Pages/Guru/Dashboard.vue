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
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">Dashboard Guru</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="mb-6">
                    <div class="relative bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 overflow-hidden shadow-2xl sm:rounded-2xl">
                        <!-- Glassmorphism overlay -->
                        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent backdrop-blur-sm"></div>
                        
                        <div class="relative p-8 text-white">
                            <h3 class="text-5xl font-bold mb-3 drop-shadow-lg">
                                Welcome, {{ teacherName }}! 👋
                            </h3>
                            <p class="text-white text-xl font-semibold drop-shadow-md">{{ currentDateTime }}</p>
                        </div>
                        
                        <!-- Decorative circles -->
                        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="absolute -left-8 -bottom-8 w-40 h-40 bg-purple-300/10 rounded-full blur-3xl"></div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Today's Classes -->
                    <div class="stats shadow-lg hover:shadow-2xl transition-all duration-300 bg-base-100 border border-base-300/50">
                        <div class="stat">
                            <div class="stat-figure text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-10 h-10 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="stat-title text-gray-700 font-semibold text-base">Today's Classes</div>
                            <div class="stat-value text-primary text-4xl">{{ stats.totalSchedulesToday }}</div>
                            <div class="stat-desc text-gray-600 font-medium text-sm">Scheduled for today</div>
                        </div>
                    </div>

                    <!-- Total Classes -->
                    <div class="stats shadow-lg hover:shadow-2xl transition-all duration-300 bg-base-100 border border-base-300/50">
                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-10 h-10 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="stat-title text-gray-700 font-semibold text-base">My Classes</div>
                            <div class="stat-value text-secondary text-4xl">{{ stats.totalClasses }}</div>
                            <div class="stat-desc text-gray-600 font-medium text-sm">Classes you teach</div>
                        </div>
                    </div>

                    <!-- Total Students -->
                    <div class="stats shadow-lg hover:shadow-2xl transition-all duration-300 bg-base-100 border border-base-300/50">
                        <div class="stat">
                            <div class="stat-figure text-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-10 h-10 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="stat-title text-gray-700 font-semibold text-base">Total Students</div>
                            <div class="stat-value text-accent text-4xl">{{ stats.totalStudents }}</div>
                            <div class="stat-desc text-gray-600 font-medium text-sm">Across all classes</div>
                        </div>
                    </div>

                    <!-- Subjects -->
                    <div class="stats shadow-lg hover:shadow-2xl transition-all duration-300 bg-base-100 border border-base-300/50">
                        <div class="stat">
                            <div class="stat-figure text-info">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-10 h-10 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div class="stat-title text-gray-700 font-semibold text-base">Subjects</div>
                            <div class="stat-value text-info text-4xl">{{ stats.totalSubjects }}</div>
                            <div class="stat-desc text-gray-600 font-medium text-sm">Different subjects</div>
                        </div>
                    </div>
                </div>

                <!-- Message if no teacher profile -->
                <div v-if="message" class="alert alert-warning mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ message }}</span>
                </div>

                <!-- Today's Schedule -->
                <div class="mb-8">
                    <h4 class="text-3xl font-bold mb-5 flex items-center gap-3 text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Today's Schedule
                    </h4>

                    <!-- No schedules message -->
                    <div v-if="schedules.length === 0 && !message" class="alert alert-info shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-lg font-medium">No classes scheduled for today. Enjoy your day off! 🎉</span>
                    </div>

                    <!-- Schedule cards grid -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="schedule in schedules"
                            :key="schedule.id"
                            :href="`/guru/absensi/${schedule.class_room_id}/${schedule.subject_id}/${new Date().toISOString().split('T')[0]}`"
                            class="group card bg-gradient-to-br from-base-100 to-base-200 shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer border-2 border-base-300/50 hover:border-primary/70 hover:-translate-y-2"
                        >
                            <div class="card-body">
                                <div class="flex items-start justify-between">
                                    <div class="badge badge-primary badge-lg shadow-lg font-bold text-sm py-3 px-4">
                                        Jam
                                    </div>
                                    <div class="text-base text-gray-700 font-bold">
                                        {{ schedule.time_slot }}
                                    </div>
                                </div>
                                
                                <h2 class="card-title mt-3 text-2xl font-bold text-gray-900 group-hover:text-primary transition-colors">
                                    {{ schedule.subject_name }}
                                </h2>
                                
                                <div class="flex items-center gap-2 mt-3 text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="font-bold text-base">
                                        {{ schedule.class_name }}
                                    </span>
                                </div>

                                <div class="card-actions justify-end mt-4">
                                    <button class="btn btn-primary gap-2 group-hover:btn-primary group-hover:shadow-lg font-bold text-base">
                                        Take Attendance
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- My Classes Section -->
                <div v-if="myClasses && myClasses.length > 0" class="mb-8">
                    <h4 class="text-3xl font-bold mb-5 flex items-center gap-3 text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        My Classes
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="classData in myClasses"
                            :key="classData.class_room_id"
                            :href="route('guru.kelas.show', classData.class_room_id)"
                            class="group card bg-gradient-to-br from-base-100 to-base-200 shadow-lg border-2 border-base-300/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 cursor-pointer hover:border-secondary/70"
                        >
                            <div class="card-body">
                                <h3 class="card-title text-2xl font-bold text-gray-900 group-hover:text-secondary transition-colors">
                                    {{ classData.class_name }}
                                </h3>
                                
                                <div class="flex items-center gap-2 mt-3 text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span class="font-bold text-primary text-lg">{{ classData.student_count }}</span>
                                    <span class="font-semibold text-base">{{ classData.student_count === 1 ? 'Student' : 'Students' }}</span>
                                </div>

                                <div class="mt-4">
                                    <p class="text-base text-gray-700 mb-2 font-bold">Subjects:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="subject in classData.subjects"
                                            :key="subject.id"
                                            class="badge badge-info badge-outline hover:badge-info transition-all text-sm font-semibold py-3 px-3"
                                        >
                                            {{ subject.name }}
                                        </span>
                                    </div>
                                </div>

                                <div class="card-actions justify-end mt-4">
                                    <button class="btn btn-secondary gap-2 group-hover:shadow-lg font-bold text-base">
                                        Lihat Detail
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
