<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    classRoom: Object,
    subject: Object,
    date: String,
    students: Array,
    isReadOnly: Boolean,
});

const form = useForm({
    class_room_id: props.classRoom.id,
    subject_id: props.subject.id,
    date: props.date,
    attendances: props.students.map(student => ({
        student_id: student.id,
        status: student.status
    }))
});

const markAllPresent = () => {
    form.attendances.forEach((attendance, index) => {
        attendance.status = 'HADIR';
    });
};

const submit = () => {
    form.post(route('guru.absensi.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'HADIR': 'badge-success',
        'SAKIT': 'badge-warning',
        'IZIN': 'badge-info',
        'ALFA': 'badge-error',
    };
    return classes[status] || 'badge-ghost';
};
</script>

<template>
    <Head title="Attendance Form" />

    <GuruLayout title="Form Absensi Siswa">
        <div class="min-h-screen bg-gray-50 py-4 md:py-6 lg:py-8">
            <div class="max-w-6xl mx-auto px-3 md:px-6">
                <!-- Header Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 md:p-5 lg:p-6 mb-4 md:mb-6">
                    <h1 class="text-xl md:text-2xl lg:text-4xl font-bold text-gray-900 mb-3 md:mb-5 lg:mb-6">Form Input Absensi</h1>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5 md:gap-2 lg:gap-3">
                        <div class="p-2 md:p-3 lg:p-4 bg-gray-100 rounded-lg border-l-4 border-blue-500">
                            <p class="text-xs font-bold uppercase text-gray-600">Kelas</p>
                            <p class="text-base md:text-lg lg:text-2xl font-bold text-gray-900 mt-1 md:mt-2">{{ classRoom.name }}</p>
                        </div>
                        <div class="p-2 md:p-3 lg:p-4 bg-gray-100 rounded-lg border-l-4 border-blue-500">
                            <p class="text-xs font-bold uppercase text-gray-600">Mata Pelajaran</p>
                            <p class="text-base md:text-lg lg:text-2xl font-bold text-gray-900 mt-1 md:mt-2">{{ subject.name }}</p>
                        </div>
                        <div class="p-2 md:p-3 lg:p-4 bg-gray-100 rounded-lg border-l-4 border-blue-500">
                            <p class="text-xs font-bold uppercase text-gray-600">Tanggal</p>
                            <p class="text-base md:text-lg lg:text-2xl font-bold text-gray-900 mt-1 md:mt-2">{{ date }}</p>
                        </div>
                        <div class="p-2 md:p-3 lg:p-4 bg-gray-100 rounded-lg border-l-4 border-blue-500">
                            <p class="text-xs font-bold uppercase text-gray-600">Total Siswa</p>
                            <p class="text-base md:text-lg lg:text-2xl font-bold text-gray-900 mt-1 md:mt-2">{{ students.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Alert if read-only -->
                <div v-if="isReadOnly" class="bg-blue-50 border-2 border-blue-200 rounded-lg p-3 md:p-4 lg:p-5 mb-4 md:mb-6">
                    <div class="flex gap-2 md:gap-3 lg:gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 md:h-5 lg:h-6 w-4 md:w-5 lg:w-6 text-blue-600 flex-shrink-0 mt-0.5 md:mt-0.5 lg:mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-bold text-blue-900 text-sm md:text-base lg:text-lg">Absensi Sudah Disimpan</h3>
                            <p class="text-xs md:text-sm lg:text-base text-blue-800 mt-0.5 md:mt-1 font-medium">Data absensi untuk hari ini sudah disimpan. Edit dapat dilakukan melalui halaman detail kelas.</p>
                        </div>
                    </div>
                </div>

                <!-- Student List -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-3 md:px-5 lg:px-6 py-3 md:py-4 lg:py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-2 md:gap-3 lg:gap-4">
                        <h2 class="text-base md:text-lg lg:text-xl font-bold text-white">Daftar Siswa ({{ students.length }})</h2>
                        <button
                            v-if="!isReadOnly"
                            type="button"
                            @click="markAllPresent"
                            class="px-3 md:px-4 lg:px-4 py-1.5 md:py-2 lg:py-2.5 bg-white text-blue-600 font-semibold rounded hover:bg-blue-50 text-xs md:text-sm lg:text-base"
                        >
                            ✓ Tandai Semua Hadir
                        </button>
                    </div>

                    <!-- Student Rows -->
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="(student, index) in students"
                            :key="student.id"
                            class="px-3 md:px-5 lg:px-6 py-3 md:py-4 lg:py-5 hover:bg-gray-50"
                        >
                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-2 md:gap-3 lg:gap-4 items-center">
                                <!-- Student Info -->
                                <div class="lg:col-span-2">
                                    <div class="flex items-center gap-2 md:gap-3 lg:gap-4">
                                        <div class="w-8 md:w-10 lg:w-12 h-8 md:h-10 lg:h-12 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-bold text-xs md:text-sm lg:text-lg">{{ index + 1 }}</span>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <h4 class="font-bold text-gray-900 text-sm md:text-base lg:text-lg">{{ student.name }}</h4>
                                            <p class="text-xs md:text-sm lg:text-base text-gray-700 font-semibold">NIS: {{ student.nis }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-block mt-1 md:mt-2 lg:mt-3 text-xs px-2 md:px-2.5 lg:px-3 py-0.5 md:py-1 lg:py-1.5 rounded font-semibold" :class="student.gender === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'">
                                        {{ student.gender === 'M' ? 'Laki-Laki' : 'Perempuan' }}
                                    </span>
                                </div>

                                <!-- Status Selection -->
                                <div v-if="!isReadOnly" class="lg:col-span-3">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5 md:gap-2 lg:gap-3">
                                        <!-- Hadir -->
                                        <label 
                                            class="flex items-center justify-center gap-0.5 md:gap-1 lg:gap-2 p-1.5 md:p-2 lg:p-3 border-2 rounded font-bold text-xs md:text-sm lg:text-base cursor-pointer"
                                            :class="[form.attendances[index].status === 'HADIR' ? ['border-green-600', 'bg-green-50', 'text-green-700'] : ['border-gray-300', 'text-gray-700', 'hover:border-gray-400']]"
                                        >
                                            <input type="radio" :name="`status-${student.id}`" value="HADIR" v-model="form.attendances[index].status" class="w-3 md:w-4 lg:w-5 h-3 md:h-4 lg:h-5" />
                                            <span>Hadir</span>
                                        </label>
                                        <!-- Sakit -->
                                        <label 
                                            class="flex items-center justify-center gap-0.5 md:gap-1 lg:gap-2 p-1.5 md:p-2 lg:p-3 border-2 rounded font-bold text-xs md:text-sm lg:text-base cursor-pointer"
                                            :class="[form.attendances[index].status === 'SAKIT' ? ['border-yellow-600', 'bg-yellow-50', 'text-yellow-700'] : ['border-gray-300', 'text-gray-700', 'hover:border-gray-400']]"
                                        >
                                            <input type="radio" :name="`status-${student.id}`" value="SAKIT" v-model="form.attendances[index].status" class="w-3 md:w-4 lg:w-5 h-3 md:h-4 lg:h-5" />
                                            <span>Sakit</span>
                                        </label>
                                        <!-- Izin -->
                                        <label 
                                            class="flex items-center justify-center gap-0.5 md:gap-1 lg:gap-2 p-1.5 md:p-2 lg:p-3 border-2 rounded font-bold text-xs md:text-sm lg:text-base cursor-pointer"
                                            :class="[form.attendances[index].status === 'IZIN' ? ['border-blue-600', 'bg-blue-50', 'text-blue-700'] : ['border-gray-300', 'text-gray-700', 'hover:border-gray-400']]"
                                        >
                                            <input type="radio" :name="`status-${student.id}`" value="IZIN" v-model="form.attendances[index].status" class="w-3 md:w-4 lg:w-5 h-3 md:h-4 lg:h-5" />
                                            <span>Izin</span>
                                        </label>
                                        <!-- Alfa -->
                                        <label 
                                            class="flex items-center justify-center gap-0.5 md:gap-1 lg:gap-2 p-1.5 md:p-2 lg:p-3 border-2 rounded font-bold text-xs md:text-sm lg:text-base cursor-pointer"
                                            :class="[form.attendances[index].status === 'ALFA' ? ['border-red-600', 'bg-red-50', 'text-red-700'] : ['border-gray-300', 'text-gray-700', 'hover:border-gray-400']]"
                                        >
                                            <input type="radio" :name="`status-${student.id}`" value="ALFA" v-model="form.attendances[index].status" class="w-3 md:w-4 lg:w-5 h-3 md:h-4 lg:h-5" />
                                            <span>Alfa</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Read-only Status -->
                                <div v-else class="lg:col-span-3 flex justify-end">
                                    <span class="px-2 md:px-3 lg:px-4 py-1 md:py-1.5 lg:py-2 rounded text-xs md:text-sm lg:text-base font-bold" :class="{
                                        'bg-green-100 text-green-800': student.status === 'HADIR',
                                        'bg-yellow-100 text-yellow-800': student.status === 'SAKIT',
                                        'bg-blue-100 text-blue-800': student.status === 'IZIN',
                                        'bg-red-100 text-red-800': student.status === 'ALFA',
                                    }">
                                        {{ student.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-3 md:px-5 lg:px-6 py-3 md:py-4 lg:py-5 border-t border-gray-200 flex flex-col md:flex-row gap-2 md:gap-3 lg:gap-3">
                        <button
                            v-if="!isReadOnly"
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="flex-1 px-3 md:px-4 lg:px-4 py-1.5 md:py-2 lg:py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded text-xs md:text-sm lg:text-base transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Absensi' }}
                        </button>
                        <button
                            v-if="!isReadOnly"
                            type="button"
                            @click="$inertia.visit(route('guru.dashboard'))"
                            class="flex-1 px-3 md:px-4 lg:px-4 py-1.5 md:py-2 lg:py-3 bg-gray-400 hover:bg-gray-500 text-white font-bold rounded text-xs md:text-sm lg:text-base transition"
                        >
                            Batal
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="router.visit(route('guru.dashboard'))"
                            class="w-full px-3 md:px-4 lg:px-4 py-1.5 md:py-2 lg:py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded text-xs md:text-sm lg:text-base transition"
                        >
                            Kembali ke Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuruLayout>
</template>
