<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    classRoom: {
        type: Object,
        required: true
    },
    students: {
        type: Array,
        default: () => []
    },
    attendanceDates: {
        type: Array,
        default: () => []
    }
});

const getStatusBadge = (status) => {
    switch (status) {
        case 'HADIR':
            return { icon: '✓', color: 'text-green-600', bg: 'bg-green-50' };
        case 'SAKIT':
            return { icon: 'S', color: 'text-blue-600', bg: 'bg-blue-50' };
        case 'IZIN':
            return { icon: 'I', color: 'text-yellow-600', bg: 'bg-yellow-50' };
        case 'ALFA':
            return { icon: '✗', color: 'text-red-600', bg: 'bg-red-50' };
        default:
            return { icon: '-', color: 'text-gray-400', bg: 'bg-gray-50' };
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'HADIR':
            return 'text-green-600 font-bold';
        case 'SAKIT':
            return 'text-blue-600';
        case 'IZIN':
            return 'text-yellow-600';
        case 'ALFA':
            return 'text-red-600 font-bold';
        default:
            return 'text-gray-400';
    }
};
</script>

<template>
    <Head :title="`Detail Kelas - ${classRoom.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                        Detail Kelas: {{ classRoom.name }}
                    </h2>
                    <p class="text-base text-gray-700 font-semibold mt-2">
                        {{ classRoom.program }} • {{ classRoom.academic_year }} • {{ classRoom.student_count }} Siswa
                    </p>
                </div>
                <Link 
                    :href="route('guru.dashboard')" 
                    class="btn btn-ghost font-bold text-base"
                >
                    ← Kembali ke Dashboard
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Info Banner -->
                        <div class="alert alert-info mb-6 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg">Rekap Kehadiran Siswa</h3>
                                <div class="text-sm font-semibold">
                                    <span class="font-bold text-green-600 text-base">✓ = Hadir</span> • 
                                    <span class="font-bold text-red-600 text-base">✗ = Alfa</span> • 
                                    <span class="text-blue-600 text-base">S = Sakit</span> • 
                                    <span class="text-yellow-600 text-base">I = Izin</span>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Table -->
                        <div class="overflow-x-auto border-2 border-gray-300 rounded-lg shadow-md">
                            <table class="table table-zebra w-full">
                                <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white sticky top-0">
                                    <tr>
                                        <th class="text-center font-bold text-base py-4">#</th>
                                        <th class="font-bold min-w-[250px] text-base py-4">Nama Siswa</th>
                                        <th 
                                            v-for="date in attendanceDates" 
                                            :key="date" 
                                            class="text-center font-bold min-w-[90px] bg-green-600 text-base py-4"
                                        >
                                            {{ date }}
                                        </th>
                                        <th class="text-center font-bold bg-indigo-800 text-base py-4">JUMLAH HADIR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="(student, index) in students" 
                                        :key="student.id"
                                        class="hover:bg-gray-100 transition-colors"
                                    >
                                        <td class="text-center font-bold text-gray-700 text-base py-3">{{ index + 1 }}</td>
                                        <td class="font-bold text-gray-900 text-base py-3">{{ student.name }}</td>
                                        <td 
                                            v-for="date in attendanceDates" 
                                            :key="date" 
                                            class="text-center py-3"
                                        >
                                            <span 
                                                v-if="student.attendances[date]"
                                                :class="[
                                                    'inline-flex items-center justify-center w-10 h-10 rounded-full text-base font-bold',
                                                    getStatusBadge(student.attendances[date]).bg,
                                                    getStatusBadge(student.attendances[date]).color
                                                ]"
                                            >
                                                {{ getStatusBadge(student.attendances[date]).icon }}
                                            </span>
                                            <span v-else class="text-gray-400 text-base font-bold">-</span>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="badge badge-primary font-bold text-base py-3 px-3">
                                                {{ Object.values(student.attendances).filter(a => a === 'HADIR').length }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-if="students.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-bold text-gray-900">Tidak Ada Siswa</h3>
                            <p class="mt-1 text-base text-gray-600 font-medium">Belum ada siswa terdaftar di kelas ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Sticky header for table */
thead {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Custom scrollbar */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
