<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    schedules: { type: Array, default: () => [] },
    myClasses: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    today: { type: String, default: '' },
    teacherName: { type: String, default: '' },
    message: { type: String, default: '' }
});

const currentDateTime = ref('');
let intervalId = null;

const updateDateTime = () => {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
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

                <!-- Warning Message -->
                <div v-if="message" class="alert-message alert-error mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ message }}</span>
                </div>

                <!-- Today's Schedule -->
                <div class="mb-8">
                    <div class="section-header">
                        <div class="section-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="section-title">Today's Schedule</h4>
                        <span class="card-header-badge ml-auto">{{ schedules.length }} {{ schedules.length === 1 ? 'Class' : 'Classes' }}</span>
                    </div>

                    <div v-if="schedules.length === 0 && !message" class="empty-state">
                        <div class="empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h5 class="text-xl font-bold text-gray-900 mb-2">No Classes Today</h5>
                        <p class="text-gray-600">Enjoy your day off and recharge for tomorrow!</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link
                            v-for="schedule in schedules"
                            :key="schedule.id"
                            :href="`/guru/absensi/${schedule.class_room_id}/${schedule.subject_id}/${new Date().toISOString().split('T')[0]}`"
                            class="schedule-card p-5"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span class="time-badge">{{ schedule.time_slot }}</span>
                                <span class="card-header-badge">TODAY</span>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-3">{{ schedule.subject_name }}</h2>
                            <div class="flex items-center gap-2 text-gray-700 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="font-semibold">{{ schedule.class_name }}</span>
                            </div>
                            <button class="btn-formal w-full justify-center">
                                Take Attendance
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </Link>
                    </div>
                </div>

                <!-- My Classes -->
                <div v-if="myClasses && myClasses.length > 0">
                    <div class="section-header">
                        <div class="section-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h4 class="section-title">My Classes</h4>
                        <span class="card-header-badge ml-auto">{{ myClasses.length }} {{ myClasses.length === 1 ? 'Class' : 'Classes' }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Link
                            v-for="classData in myClasses"
                            :key="classData.class_room_id"
                            :href="route('guru.kelas.show', classData.class_room_id)"
                            class="class-card p-5"
                        >
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ classData.class_name }}</h3>
                            
                            <div class="flex items-center gap-2 mb-4">
                                <div class="avatar-circle">{{ classData.class_name.charAt(0) }}</div>
                                <div>
                                    <span class="text-2xl font-bold text-gray-900">{{ classData.student_count }}</span>
                                    <span class="text-gray-600 font-medium ml-1">{{ classData.student_count === 1 ? 'Student' : 'Students' }}</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600 font-semibold mb-2">Subjects:</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="subject in classData.subjects" :key="subject.id" class="subject-tag">
                                        {{ subject.name }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="classData.schedules && classData.schedules.length > 0" class="mb-4 pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600 font-semibold mb-2">Schedule:</p>
                                <div class="space-y-2 max-h-32 overflow-y-auto custom-scrollbar">
                                    <div v-for="(schedule, idx) in classData.schedules" :key="idx" class="schedule-item">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="badge-outline text-blue-600 border-blue-600">{{ schedule.day }}</span>
                                                <span class="font-bold text-gray-900">{{ schedule.time_slot }}</span>
                                            </div>
                                            <span class="font-medium text-gray-700">{{ schedule.subject_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-center gap-2 mt-4 pt-4 border-t border-gray-200 text-indigo-600 font-semibold text-sm group-hover:text-indigo-700">
                                <span>Lihat Detail</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
