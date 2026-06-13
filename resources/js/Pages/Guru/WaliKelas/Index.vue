<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    myClasses: { type: Array, default: () => [] },
    teacherName: { type: String, default: '' },
    totalClasses: { type: Number, default: 0 }
});
</script>

<template>
    <Head title="Ruang Wali Kelas" />
    
    <GuruLayout title="Ruang Wali Kelas">
        <div class="page-container">
            <div class="content-wrapper">
                <!-- Welcome Section -->
                <div class="welcome-card mb-4 sm:mb-5 md:mb-6 p-3 sm:p-4 md:p-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-lg flex-shrink-0 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="text-white">
                            <h3 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold">Welcome, {{ teacherName }}</h3>
                            <p class="text-white/90 font-medium mt-1 text-xs sm:text-sm md:text-base">Kelola kelas yang Anda pandu sebagai wali kelas</p>
                        </div>
                    </div>
                </div>

                <!-- Header Section -->
                <div class="section-header mb-4 sm:mb-5 md:mb-6">
                    <div class="section-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h4 class="section-title">Kelas yang Saya Pandu</h4>
                    <span class="card-header-badge ml-auto">{{ totalClasses }} {{ totalClasses === 1 ? 'Kelas' : 'Kelas' }}</span>
                </div>

                <!-- Empty State -->
                <div v-if="myClasses.length === 0" class="empty-state">
                    <div class="empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h5 class="text-lg sm:text-xl md:text-2xl lg:text-2xl font-bold text-gray-900 mb-2">Anda Belum Menjadi Wali Kelas</h5>
                    <p class="text-sm sm:text-base md:text-lg lg:text-lg text-gray-600">Hubungi administrator untuk ditugaskan sebagai wali kelas</p>
                </div>

                <!-- Classes Grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                    <Link
                        v-for="classItem in myClasses"
                        :key="classItem.id"
                        :href="route('guru.wali-kelas.show', classItem.id)"
                        class="class-card p-3 sm:p-4 md:p-6"
                    >
                        <div class="flex items-start justify-between mb-3 sm:mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold text-gray-900">{{ classItem.name }}</h3>
                                <p class="text-xs sm:text-sm md:text-base text-gray-600 mt-1">{{ classItem.academic_year }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-lg p-2 sm:p-2.5 md:p-3 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-2 sm:space-y-2.5 mb-3 sm:mb-4">
    
                            <div v-if="classItem.section" class="flex items-center gap-2 text-xs sm:text-sm md:text-base">
                            </div>
                        </div>

                        <div class="pt-3 sm:pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs sm:text-xs md:text-sm text-gray-600 font-medium">Total Siswa</p>
                                    <p class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">{{ classItem.student_count }}</p>
                                </div>
                                <div class="bg-blue-100 rounded-lg p-2 sm:p-2.5 md:p-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 md:h-7 md:w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>
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

.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

@media (min-width: 640px) {
    .section-header {
        gap: 10px;
        padding-bottom: 14px;
    }
}

@media (min-width: 768px) {
    .section-header {
        gap: 12px;
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

.class-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
}

.class-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
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
</style>
