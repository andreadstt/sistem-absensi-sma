<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    myClasses: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Rekap Absen" />
    
    <GuruLayout title="Rekap Absensi">
        <div class="page-container">
            <div class="content-wrapper">
                <!-- Header -->
                <div class="mb-4 sm:mb-6 md:mb-8">
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">Rekapitulasi Absensi</h1>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600">Kelola dan lihat ringkasan kehadiran untuk setiap kelas Anda</p>
                </div>

                <!-- My Classes -->
                <div v-if="myClasses && myClasses.length > 0">
                    <div class="section-header">
                        <div class="section-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h4 class="section-title">Daftar Kelas</h4>
                        <span class="card-header-badge ml-auto">{{ myClasses.length }} {{ myClasses.length === 1 ? 'Kelas' : 'Kelas' }}</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                        <Link
                            v-for="classData in myClasses"
                            :key="classData.class_room_id"
                            :href="route('guru.kelas.show', classData.class_room_id)"
                            class="class-card p-3 sm:p-4 md:p-5"
                        >
                            <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 mb-2 sm:mb-3">{{ classData.class_name }}</h3>
                            

                            <div class="mb-3 sm:mb-4">
                                <p class="text-xs text-gray-600 font-semibold mb-2">Mata Pelajaran:</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="subject in classData.subjects" :key="subject.id" class="subject-tag">
                                        {{ subject.name }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="classData.schedules && classData.schedules.length > 0" class="mb-3 sm:mb-4 pt-3 sm:pt-4 border-t border-gray-200">
                                <p class="text-xs text-gray-600 font-semibold mb-2">Jadwal:</p>
                                <div class="space-y-1 sm:space-y-1.5 md:space-y-2 max-h-24 sm:max-h-28 md:max-h-36 overflow-y-auto custom-scrollbar">
                                    <div v-for="(schedule, idx) in classData.schedules" :key="idx" class="schedule-item">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs sm:text-xs md:text-sm gap-1 sm:gap-2">
                                            <div class="flex items-center gap-1 sm:gap-2 min-w-0">
                                                <span class="badge-outline text-blue-600 border-blue-600 flex-shrink-0">{{ schedule.day }}</span>
                                                <span class="font-bold text-gray-900 flex-shrink-0">{{ schedule.time_slot }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-center gap-2 mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 text-indigo-600 font-semibold text-xs sm:text-sm group-hover:text-indigo-700">
                                <span>Lihat Detail</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 md:h-4 md:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-8 sm:py-10 md:py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 sm:h-14 md:h-16 w-12 sm:w-14 md:w-16 text-gray-400 mx-auto mb-3 sm:mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-gray-600 text-base sm:text-lg font-medium">Anda belum memiliki kelas</p>
                    <p class="text-gray-500 text-xs sm:text-sm">Hubungi administrator untuk ditugaskan ke kelas</p>
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
        font-size: 1.25rem;
    }
}

.card-header-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

@media (min-width: 640px) {
    .card-header-badge {
        padding: 5px 12px;
        font-size: 0.8rem;
    }
}

@media (min-width: 768px) {
    .card-header-badge {
        padding: 6px 14px;
        font-size: 0.875rem;
    }
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

.avatar-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #60a5fa, #3b82f6);
    color: white;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.875rem;
    flex-shrink: 0;
}

@media (min-width: 640px) {
    .avatar-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

@media (min-width: 768px) {
    .avatar-circle {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
}

.subject-tag {
    display: inline-block;
    padding: 4px 8px;
    background: #f0f4ff;
    color: #3730a3;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
}

@media (min-width: 640px) {
    .subject-tag {
        padding: 6px 10px;
        font-size: 0.75rem;
    }
}

@media (min-width: 768px) {
    .subject-tag {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

.schedule-item {
    padding: 4px 0;
}

@media (min-width: 640px) {
    .schedule-item {
        padding: 6px 0;
    }
}

@media (min-width: 768px) {
    .schedule-item {
        padding: 8px 0;
    }
}

.badge-outline {
    display: inline-block;
    padding: 2px 6px;
    border: 1px solid;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 600;
}

@media (min-width: 640px) {
    .badge-outline {
        padding: 2px 7px;
        font-size: 0.7rem;
    }
}

@media (min-width: 768px) {
    .badge-outline {
        padding: 3px 8px;
        font-size: 0.75rem;
    }
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
