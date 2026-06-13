<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    classRoom: { type: Object, required: true },
    students: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    classAttendanceSummary: { type: Object, default: () => ({}) },
    teacherName: { type: String, default: '' }
});

const searchQuery = ref('');
const expandedStudentId = ref(null);

const studentList = computed(() => {
    return props.students.map((student, index) => ({
        ...student,
        index: index + 1
    }));
});

const filteredStudentList = computed(() => {
    if (!searchQuery.value.trim()) {
        return studentList.value;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return studentList.value.filter(student => 
        student.name.toLowerCase().includes(query) || 
        student.nis.toLowerCase().includes(query)
    );
});

const getGenderLabel = (gender) => {
    return gender === 'M' ? 'Laki-laki' : 'Perempuan';
};

const getGenderColor = (gender) => {
    return gender === 'M' ? 'bg-blue-100' : 'bg-pink-100';
};

const getGenderTextColor = (gender) => {
    return gender === 'M' ? 'text-blue-700' : 'text-pink-700';
};

const getAttendanceRateColor = (rate) => {
    if (rate >= 90) return 'text-green-600';
    if (rate >= 75) return 'text-yellow-600';
    return 'text-red-600';
};

const getAttendanceRateBgColor = (rate) => {
    if (rate >= 90) return 'bg-green-100';
    if (rate >= 75) return 'bg-yellow-100';
    return 'bg-red-100';
};

const formatDateToIndonesian = (dateString) => {
    const date = new Date(dateString);
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const day = date.getDate();
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
};
</script>

<template>
    <Head :title="`Wali Kelas - ${classRoom.name}`" />
    
    <GuruLayout :title="`${classRoom.name}`">
        <div class="space-y-6">
            <!-- Back Link -->
            <div>
                <Link
                    :href="route('guru.wali-kelas.index')"
                    class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-xs sm:text-sm md:text-base"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Kembali ke Ruang Wali Kelas</span>
                </Link>
            </div>

            <!-- Class Info Card -->
            <div class="info-card bg-white p-3 sm:p-4 md:p-6 rounded-lg border border-gray-200">
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="stat-box">
                        <div class="text-xs sm:text-sm text-gray-600 font-medium mb-1">Kelas</div>
                        <div class="text-base sm:text-lg md:text-xl font-bold text-gray-900">{{ classRoom.name }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="text-xs sm:text-sm text-gray-600 font-medium mb-1">Tahun Akademik</div>
                        <div class="text-base sm:text-lg md:text-xl font-bold text-gray-900">{{ classRoom.academic_year }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="text-xs sm:text-sm text-gray-600 font-medium mb-1">Program</div>
                        <div class="text-base sm:text-lg md:text-xl font-bold text-gray-900">{{ classRoom.program }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="text-xs sm:text-sm text-gray-600 font-medium mb-1">Wali Kelas</div>
                        <div class="text-base sm:text-lg md:text-xl font-bold text-gray-900">{{ classRoom.head_teacher.name }}</div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                <div class="stat-card p-3 sm:p-4 md:p-5 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-xs sm:text-xs md:text-sm text-gray-600 font-semibold">Total Siswa</p>
                            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 mt-1">{{ stats.total_students }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-2 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-3 sm:p-4 md:p-5 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-xs sm:text-xs md:text-sm text-gray-600 font-semibold">Laki-laki</p>
                            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-600 mt-1">{{ stats.male_count }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-2 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-3 sm:p-4 md:p-5 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-xs sm:text-xs md:text-sm text-gray-600 font-semibold">Perempuan</p>
                            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-pink-600 mt-1">{{ stats.female_count }}</p>
                        </div>
                        <div class="bg-pink-100 rounded-lg p-2 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Students Table -->
            <div v-if="students.length > 0" class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-3 sm:p-4 md:p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                    <div>
                        <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900">Rekapan Kehadiran Per Siswa</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mt-1">Total {{ filteredStudentList.length }} dari {{ students.length }} siswa</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                        <div class="relative flex-1 sm:flex-none">
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari nama atau NIS siswa..."
                                    class="w-full sm:w-64 pl-9 pr-4 py-2 sm:py-2.5 text-xs sm:text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button
                                    v-if="searchQuery"
                                    @click="searchQuery = ''"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <a
                            :href="route('guru.wali-kelas.export', classRoom.id)"
                            class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-medium rounded-lg transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-4m0 0V8m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="hidden sm:inline">Export Excel</span>
                            <span class="sm:hidden">Export</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">No</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">NIS</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">Nama Siswa</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">Jenis Kelamin</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Hadir</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Sakit</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Izin</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Alfa</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Total</th>
                                <th class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Rate Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredStudentList.length === 0" class="border-b border-gray-200">
                                <td colspan="10" class="px-3 sm:px-4 md:px-6 py-6 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-xs sm:text-sm text-gray-600 font-medium">Tidak ada siswa yang sesuai dengan pencarian</p>
                                    </div>
                                </td>
                            </tr>
                            <template v-for="student in filteredStudentList" :key="student.id">
                                <tr 
                                    @click="expandedStudentId = expandedStudentId === student.id ? null : student.id"
                                    class="border-b border-gray-200 hover:bg-blue-50 transition cursor-pointer"
                                >
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900 font-medium">{{ student.index }}</td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900 font-medium">{{ student.nis }}</td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-xs sm:text-sm text-gray-900 font-medium">
                                        <div class="inline-flex items-center gap-1 text-blue-600 font-medium">
                                            <span>{{ student.name }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': expandedStudentId === student.id }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-xs sm:text-sm">
                                        <span :class="['inline-block px-2 sm:px-3 py-1 rounded-full text-black text-xs sm:text-xs md:text-sm font-semibold']">
                                            {{ getGenderLabel(student.gender) }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full text-black text-xs sm:text-xs md:text-sm font-bold">
                                            {{ student.attendance_stats.hadir }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full bg-yellow-100 text-yellow-700 text-xs sm:text-xs md:text-sm font-bold">
                                            {{ student.attendance_stats.sakit }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full bg-blue-100 text-blue-700 text-xs sm:text-xs md:text-sm font-bold">
                                            {{ student.attendance_stats.izin }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full bg-red-100 text-red-700 text-xs sm:text-xs md:text-sm font-bold">
                                            {{ student.attendance_stats.alfa }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center">
                                        <span class="text-xs sm:text-sm font-semibold text-gray-900">{{ student.attendance_stats.total }}</span>
                                    </td>
                                    <td class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-center">
                                        <span :class="['inline-block px-2 sm:px-3 py-1 rounded-full text-black text-xs sm:text-xs md:text-sm font-bold']">
                                            {{ student.attendance_rate }}%
                                        </span>
                                    </td>
                                </tr>
                                
                                <!-- Dropdown Detail Row -->
                                <tr v-if="expandedStudentId === student.id" class="bg-blue-50 border-b border-gray-200">
                                    <td colspan="10" class="px-3 sm:px-4 md:px-6 py-4 sm:py-5">
                                        <div class="space-y-3 sm:space-y-4">
                                            <!-- Sakit Details -->
                                            <div v-if="student.attendance_stats.sakit > 0" class="bg-white rounded-lg border border-yellow-200 p-3 sm:p-4">
                                                <div class="flex items-center gap-2 mb-2 sm:mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                    </svg>
                                                    <h4 class="font-bold text-yellow-700 text-xs sm:text-sm">SAKIT ({{ student.attendance_stats.sakit }} kali)</h4>
                                                </div>
                                                <ul class="space-y-1 ml-1">
                                                    <li v-for="(detail, idx) in student.attendance_details.sakit" :key="idx" class="text-xs sm:text-sm text-gray-700 flex items-start">
                                                        <span class="text-yellow-600 mr-2 font-bold">•</span>
                                                        <span>{{ detail.subject_name }} <span class="text-gray-500">({{ formatDateToIndonesian(detail.date) }})</span></span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- Izin Details -->
                                            <div v-if="student.attendance_stats.izin > 0" class="bg-white rounded-lg border border-blue-200 p-3 sm:p-4">
                                                <div class="flex items-center gap-2 mb-2 sm:mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <h4 class="font-bold text-blue-700 text-xs sm:text-sm">IZIN ({{ student.attendance_stats.izin }} kali)</h4>
                                                </div>
                                                <ul class="space-y-1 ml-1">
                                                    <li v-for="(detail, idx) in student.attendance_details.izin" :key="idx" class="text-xs sm:text-sm text-gray-700 flex items-start">
                                                        <span class="text-blue-600 mr-2 font-bold">•</span>
                                                        <span>{{ detail.subject_name }} <span class="text-gray-500">({{ formatDateToIndonesian(detail.date) }})</span></span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- Alfa Details -->
                                            <div v-if="student.attendance_stats.alfa > 0" class="bg-white rounded-lg border border-red-200 p-3 sm:p-4">
                                                <div class="flex items-center gap-2 mb-2 sm:mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    <h4 class="font-bold text-red-700 text-xs sm:text-sm">ALFA ({{ student.attendance_stats.alfa }} kali)</h4>
                                                </div>
                                                <ul class="space-y-1 ml-1">
                                                    <li v-for="(detail, idx) in student.attendance_details.alfa" :key="idx" class="text-xs sm:text-sm text-gray-700 flex items-start">
                                                        <span class="text-red-600 mr-2 font-bold">•</span>
                                                        <span>{{ detail.subject_name }} <span class="text-gray-500">({{ formatDateToIndonesian(detail.date) }})</span></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg border border-gray-200 p-6 sm:p-8 md:p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2">Belum Ada Siswa</h3>
                <p class="text-sm sm:text-base text-gray-600">Kelas ini belum memiliki siswa yang terdaftar</p>
            </div>
        </div>
    </GuruLayout>
</template>

<style scoped>
.info-card {
    background: linear-gradient(135deg, #f0f4ff 0%, #f9fafb 100%);
}

.stat-box {
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}
</style>
