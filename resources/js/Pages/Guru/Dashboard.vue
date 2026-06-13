<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useCurrentDateTime, getTodayDayNumber, getDayName, getTodayDate } from '@/composables/useDateTime';

defineProps({
    schedules: { type: Array, default: () => [] },
    myClasses: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    today: { type: String, default: '' },
    teacherName: { type: String, default: '' },
    message: { type: String, default: '' }
});

const { currentDateTime } = useCurrentDateTime();
const currentDayNum = ref(getTodayDayNumber());
</script>

<template>
    <Head title="Guru Dashboard" />
    
    <GuruLayout title="Dashboard Guru">
        <div class="page-container">
            <div class="content-wrapper">
                <!-- Welcome Section -->
                <div class="guru-welcome-card mb-4 sm:mb-5 md:mb-6 p-3 sm:p-4 md:p-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-lg flex-shrink-0 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="text-white">
                            <h3 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold">Welcome, {{ teacherName }}</h3>
                            <p class="text-white/90 font-medium mt-1 text-xs sm:text-sm md:text-base">{{ currentDateTime }}</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-3 md:gap-4 mb-6 md:mb-8">
                    
                    <div class="stat-card p-3 sm:p-3.5 md:p-5">
                        <div class="flex items-start justify-between mb-2 md:mb-3">
                            <div class="stat-card-icon bg-purple-100 text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-xs sm:text-xs md:text-sm lg:text-sm font-semibold mb-1">Total Kelas</div>
                        <div class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-bold text-gray-900">{{ stats.totalClasses }}</div>
                        <div class="text-gray-500 text-xs sm:text-xs md:text-xs lg:text-sm font-medium mt-1">Kelas aktif yang diampu</div>
                    </div>
                    <div class="stat-card p-3 sm:p-3.5 md:p-5">
                        <div class="flex items-start justify-between mb-2 md:mb-3">
                            <div class="stat-card-icon bg-blue-100 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-xs sm:text-xs md:text-sm lg:text-sm font-semibold mb-1">Kelas Hari ini</div>
                        <div class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-bold text-gray-900">{{ stats.totalSchedulesToday }}</div>
                        <div class="text-gray-500 text-xs sm:text-xs md:text-xs lg:text-sm font-medium mt-1">{{ stats.totalSchedulesToday }} Kelas dijadwalkan hari ini</div>
                    </div>

                    <div class="stat-card p-3 sm:p-3.5 md:p-5">
                        <div class="flex items-start justify-between mb-2 md:mb-3">
                            <div class="stat-card-icon bg-green-100 text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-xs sm:text-xs md:text-sm lg:text-sm font-semibold mb-1">Total Siswa</div>
                        <div class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-bold text-gray-900">{{ stats.totalStudents }}</div>
                        <div class="text-gray-500 text-xs sm:text-xs md:text-xs lg:text-sm font-medium mt-1">Total siswa dari semua kelas yang diajar</div>
                    </div>

                    <div class="stat-card p-3 sm:p-3.5 md:p-5">
                        <div class="flex items-start justify-between mb-2 md:mb-3">
                            <div class="stat-card-icon bg-orange-100 text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-gray-600 text-xs sm:text-xs md:text-sm lg:text-sm font-semibold mb-1">Mata Pelajaran</div>
                        <div class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-bold text-gray-900">{{ stats.totalSubjects }}</div>
                        <div class="text-gray-500 text-xs sm:text-xs md:text-xs lg:text-sm font-medium mt-1">Total Mata pelajaran diampu</div>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h4 class="section-title">Jadwal Saya</h4>
                        <span class="card-header-badge ml-auto">{{ myClasses.length }} {{ myClasses.length === 1 ? 'Kelas' : 'Kelas' }}</span>
                    </div>

                    <div v-if="myClasses.length === 0 && !message" class="empty-state">
                        <div class="empty-state-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h5 class="text-lg sm:text-xl md:text-2xl lg:text-2xl font-bold text-gray-900 mb-2">Anda Belum Memiliki Kelas</h5>
                        <p class="text-sm sm:text-base md:text-lg lg:text-lg text-gray-600">Hubungi administrator untuk ditugaskan ke kelas</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                        <div
                            v-for="classData in myClasses"
                            :key="classData.class_room_id"
                            class="class-card p-3 sm:p-4 md:p-6"
                        >
                            <h3 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold text-gray-900 mb-3 sm:mb-3 md:mb-4">{{ classData.class_name }}</h3>

                            <div class="mb-4 sm:mb-4 md:mb-5">
                                <p class="text-xs sm:text-sm md:text-base lg:text-base text-gray-700 font-bold mb-2 sm:mb-2 md:mb-3">Mata Pelajaran:</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="subject in classData.subjects" :key="subject.id" class="subject-tag">
                                        {{ subject.name }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="classData.schedules && classData.schedules.length > 0" class="mb-3 sm:mb-4 md:mb-5 pt-3 sm:pt-4 md:pt-5 border-t border-gray-200">
                                <p class="text-xs sm:text-sm md:text-base lg:text-base text-gray-700 font-bold mb-2 sm:mb-3 md:mb-4">Jadwal:</p>
                                <div class="space-y-2 sm:space-y-2.5 md:space-y-3 max-h-36 sm:max-h-40 md:max-h-48 overflow-y-auto custom-scrollbar">
                                    <div v-for="(schedule, idx) in classData.schedules" :key="idx" class="schedule-item">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-3 md:gap-4">
                                            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                <span class="badge-outline text-blue-600 border-blue-600 flex-shrink-0 text-xs sm:text-xs md:text-sm lg:text-sm font-semibold">{{ schedule.day }}</span>
                                                <span class="font-bold text-gray-900 flex-shrink-0 text-xs sm:text-sm md:text-base lg:text-base">{{ schedule.time_slot }}</span>
                                            </div>
                                            <!-- Button next to schedule -->
                                            <Link
                                                v-if="schedule.day === getDayName(currentDayNum)"
                                                :href="`/guru/absensi/${classData.class_room_id}/${schedule.subject_id}/${getTodayDate()}`"
                                                class="flex items-center justify-center gap-1 sm:gap-1 md:gap-2 px-2 sm:px-3 md:px-4 py-1.5 md:py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded text-xs sm:text-xs md:text-sm lg:text-sm whitespace-nowrap flex-shrink-0 w-full sm:w-auto transition"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-4 sm:w-4 md:h-5 md:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <span>Masuk</span>
                                            </Link>
                                            <button
                                                v-else
                                                disabled
                                                class="flex items-center justify-center gap-1 sm:gap-1 md:gap-2 px-2 sm:px-3 md:px-4 py-1.5 md:py-2 bg-gray-300 text-gray-600 font-bold rounded text-xs sm:text-xs md:text-sm lg:text-sm whitespace-nowrap flex-shrink-0 w-full sm:w-auto cursor-not-allowed"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-4 sm:w-4 md:h-5 md:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <span>Masuk</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuruLayout>
</template>

<style scoped>
.page-container {
    min-height: calc(100vh - 200px);
}

.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}

.guru-welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
    padding: 0.75rem;
}

@media (min-width: 640px) {
    .stat-card {
        padding: 0.875rem;
    }
}

@media (min-width: 768px) {
    .stat-card {
        padding: 1.25rem;
    }
}

.stat-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.stat-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
}

@media (min-width: 768px) {
    .stat-card-icon {
        width: 48px;
        height: 48px;
    }
}

.alert-message {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    border-radius: 8px;
}

@media (min-width: 768px) {
    .alert-message {
        gap: 12px;
        padding: 16px;
    }
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

@media (min-width: 640px) {
    .section-header {
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 14px;
    }
}

@media (min-width: 768px) {
    .section-header {
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
    }
}

.section-header-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #3b82f6, #1e40af);
    border-radius: 8px;
    color: white;
    flex-shrink: 0;
}

@media (min-width: 640px) {
    .section-header-icon {
        width: 36px;
        height: 36px;
    }
}

@media (min-width: 768px) {
    .section-header-icon {
        width: 40px;
        height: 40px;
    }
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

@media (min-width: 640px) {
    .section-title {
        font-size: 1.2rem;
    }
}

@media (min-width: 768px) {
    .section-title {
        font-size: 1.5rem;
    }
}

.card-header-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}

@media (min-width: 640px) {
    .card-header-badge {
        padding: 5px 14px;
        font-size: 0.85rem;
    }
}

@media (min-width: 768px) {
    .card-header-badge {
        padding: 6px 16px;
        font-size: 0.95rem;
    }
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
    background: #f9fafb;
    border: 2px dashed #e5e7eb;
    border-radius: 12px;
}

.empty-state-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    background: #f3f4f6;
    border-radius: 50%;
    margin-bottom: 16px;
}

.empty-state-icon svg {
    width: 32px;
    height: 32px;
    color: #9ca3af;
}

.class-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.class-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}


.subject-tag {
    display: inline-block;
    padding: 4px 8px;
    background: #f0f4ff;
    color: #3730a3;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
}

@media (min-width: 640px) {
    .subject-tag {
        padding: 6px 11px;
        font-size: 0.8rem;
    }
}

@media (min-width: 768px) {
    .subject-tag {
        padding: 8px 14px;
        font-size: 0.95rem;
    }
}

.schedule-item {
    padding: 6px;
    background: #f9fafb;
    border-radius: 6px;
}

@media (min-width: 640px) {
    .schedule-item {
        padding: 9px;
    }
}

@media (min-width: 768px) {
    .schedule-item {
        padding: 12px;
    }
}

.badge-outline {
    display: inline-block;
    padding: 3px 6px;
    border: 1px solid;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
}

@media (min-width: 640px) {
    .badge-outline {
        padding: 4px 9px;
        border: 1.5px solid;
        font-size: 0.8rem;
    }
}

@media (min-width: 768px) {
    .badge-outline {
        padding: 6px 12px;
        border: 2px solid;
        font-size: 0.9rem;
    }
}

.time-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #fef3c7;
    color: #b45309;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
}

.schedule-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.schedule-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.btn-formal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-formal:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
