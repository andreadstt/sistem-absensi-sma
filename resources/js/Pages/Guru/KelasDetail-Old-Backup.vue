<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const page = usePage();
const flash = computed(() => page.props.flash || {});

// Modal state
const showEditModal = ref(false);
const editingAttendance = ref({
    studentId: null,
    studentName: '',
    date: '',
    currentStatus: null,
    newStatus: null
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

const openEditModal = (student, date) => {
    const status = student.attendances[date];
    if (!status) return; // Can't edit if no attendance record
    
    editingAttendance.value = {
        studentId: student.id,
        studentName: student.name,
        date: date,
        currentStatus: status,
        newStatus: status
    };
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingAttendance.value = {
        studentId: null,
        studentName: '',
        date: '',
        currentStatus: null,
        newStatus: null
    };
};

const updateAttendance = () => {
    if (editingAttendance.value.currentStatus === editingAttendance.value.newStatus) {
        closeEditModal();
        return;
    }

    router.post(route('guru.attendance.update'), {
        student_id: editingAttendance.value.studentId,
        date: editingAttendance.value.date,
        class_room_id: props.classRoom.id,
        status: editingAttendance.value.newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
        onError: (errors) => {
            alert('Gagal mengubah status: ' + Object.values(errors).join(', '));
        }
    });
};
</script>

<template>
    <Head :title="`Detail Kelas - ${classRoom.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="font-black text-3xl text-gray-900 leading-tight flex items-center gap-3">
                        <div class="p-2 bg-gradient-to-br from-primary to-purple-600 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">
                            {{ classRoom.name }}
                        </span>
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 mt-3">
                        <span class="badge badge-primary badge-lg font-bold text-sm px-4 py-3 shadow-md">
                            {{ classRoom.program }}
                        </span>
                        <span class="badge badge-secondary badge-lg font-bold text-sm px-4 py-3 shadow-md">
                            {{ classRoom.academic_year }}
                        </span>
                        <span class="badge badge-accent badge-lg font-bold text-sm px-4 py-3 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{ classRoom.student_count }} Students
                        </span>
                    </div>
                </div>
                <Link 
                    :href="route('guru.dashboard')" 
                    class="btn btn-ghost gap-2 font-bold text-base hover:bg-primary/10 hover:text-primary transition-all"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="py-8 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <!-- Success/Error Messages -->
                <div v-if="flash.success" class="alert alert-success shadow-2xl mb-8 border-l-4 border-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-7 w-7" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold text-lg">{{ flash.success }}</span>
                </div>

                <div v-if="flash.error" class="alert alert-error shadow-2xl mb-8 border-l-4 border-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-7 w-7" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold text-lg">{{ flash.error }}</span>
                </div>

                <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-2xl sm:rounded-3xl border-2 border-gray-200">
                    <div class="p-8">
                        <!-- Info Banner -->
                        <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl mb-8">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                            <div class="relative p-6 text-white">
                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                    <div class="flex items-start gap-4">
                                        <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-10 h-10 stroke-white stroke-[2.5]">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-black text-2xl mb-3">Student Attendance Summary</h3>
                                            <div class="flex flex-wrap gap-4 text-base font-bold">
                                                <div class="flex items-center gap-2 bg-white/20 rounded-xl px-4 py-2 backdrop-blur-sm">
                                                    <span class="text-2xl">✓</span>
                                                    <span>Hadir (Present)</span>
                                                </div>
                                                <div class="flex items-center gap-2 bg-white/20 rounded-xl px-4 py-2 backdrop-blur-sm">
                                                    <span class="text-2xl">✗</span>
                                                    <span>Alfa (Absent)</span>
                                                </div>
                                                <div class="flex items-center gap-2 bg-white/20 rounded-xl px-4 py-2 backdrop-blur-sm">
                                                    <span class="text-xl font-black">S</span>
                                                    <span>Sakit (Sick)</span>
                                                </div>
                                                <div class="flex items-center gap-2 bg-white/20 rounded-xl px-4 py-2 backdrop-blur-sm">
                                                    <span class="text-xl font-black">I</span>
                                                    <span>Izin (Permission)</span>
                                                </div>
                                            </div>
                                            <p class="mt-3 text-sm font-semibold text-white/80">
                                                💡 Click on any attendance badge to edit the status
                                            </p>
                                        </div>
                                    </div>
                                    <a 
                                        :href="route('guru.kelas.export', classRoom.id)" 
                                        class="btn btn-success gap-2 font-bold text-base shadow-xl hover:scale-105 transition-transform"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Export CSV
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Table -->
                        <div class="overflow-x-auto border-2 border-indigo-200 rounded-2xl shadow-2xl bg-white">
                            <table class="table table-zebra w-full">
                                <thead class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white sticky top-0">
                                    <tr>
                                        <th class="text-center font-black text-base py-5 border-r-2 border-white/20">#</th>
                                        <th class="font-black min-w-[250px] text-base py-5 border-r-2 border-white/20">
                                            <div class="flex items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Student Name
                                            </div>
                                        </th>
                                        <th 
                                            v-for="date in attendanceDates" 
                                            :key="date" 
                                            class="text-center font-black min-w-[100px] text-base py-5 border-r-2 border-white/20"
                                        >
                                            <div class="flex flex-col items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ date }}
                                            </div>
                                        </th>
                                        <th class="text-center font-black text-base py-5 bg-indigo-800">
                                            <div class="flex flex-col items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                TOTAL PRESENT
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="(student, index) in students" 
                                        :key="student.id"
                                        class="hover:bg-indigo-50/50 transition-colors group"
                                    >
                                        <td class="text-center font-bold text-gray-700 text-base py-4 border-r border-gray-200">
                                            <div class="flex items-center justify-center">
                                                <span class="w-8 h-8 flex items-center justify-center bg-gradient-to-br from-primary to-purple-600 text-white rounded-full font-black text-sm shadow-md">
                                                    {{ index + 1 }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="font-bold text-gray-900 text-base py-4 border-r border-gray-200">
                                            <div class="flex items-center gap-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-gradient-to-br from-secondary to-pink-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-black">
                                                        {{ student.name.charAt(0).toUpperCase() }}
                                                    </div>
                                                </div>
                                                <span>{{ student.name }}</span>
                                            </div>
                                        </td>
                                        <td 
                                            v-for="date in attendanceDates" 
                                            :key="date" 
                                            class="text-center py-4 border-r border-gray-200"
                                        >
                                            <button
                                                v-if="student.attendances[date]"
                                                @click="openEditModal(student, date)"
                                                :class="[
                                                    'inline-flex items-center justify-center w-12 h-12 rounded-xl text-lg font-black cursor-pointer transition-all hover:scale-125 hover:shadow-2xl hover:rotate-6 active:scale-95',
                                                    getStatusBadge(student.attendances[date]).bg,
                                                    getStatusBadge(student.attendances[date]).color,
                                                    'border-2 border-transparent hover:border-gray-300'
                                                ]"
                                                :title="`Click to change status - ${student.attendances[date]}`"
                                            >
                                                {{ getStatusBadge(student.attendances[date]).icon }}
                                            </button>
                                            <span v-else class="text-gray-300 text-lg font-black">-</span>
                                        </td>
                                        <td class="text-center py-4 bg-indigo-50/50">
                                            <div class="flex items-center justify-center">
                                                <span class="badge badge-primary font-black text-base py-4 px-5 shadow-lg">
                                                    {{ Object.values(student.attendances).filter(a => a === 'HADIR').length }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-if="students.length === 0" class="text-center py-16">
                            <div class="inline-block p-8 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full mb-6">
                                <svg class="h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 mb-2">No Students Found</h3>
                            <p class="text-lg font-semibold text-gray-600">There are no students registered in this class yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Attendance Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay with blur -->
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="closeEditModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-4 border-indigo-200">
                    <!-- Header Section -->
                    <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 px-6 py-6">
                        <div class="flex items-center gap-4">
                            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-white" id="modal-title">
                                    Edit Attendance Status
                                </h3>
                                <p class="text-white/80 font-semibold">Update student attendance record</p>
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="space-y-5">
                            <!-- Student Info -->
                            <div class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-200">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-gradient-to-br from-secondary to-pink-500 text-white rounded-full w-12 h-12 flex items-center justify-center font-black text-xl">
                                            {{ editingAttendance.studentName.charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 font-bold uppercase tracking-wider">Student</p>
                                        <p class="text-lg font-black text-gray-900">{{ editingAttendance.studentName }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-bold">{{ editingAttendance.date }}</span>
                                </div>
                            </div>

                            <!-- Current Status -->
                            <div>
                                <p class="text-sm font-bold text-gray-600 mb-3 uppercase tracking-wider">Current Status:</p>
                                <div class="inline-flex items-center gap-2 px-5 py-3 rounded-xl shadow-lg"
                                     :class="[
                                         getStatusBadge(editingAttendance.currentStatus).bg,
                                         getStatusBadge(editingAttendance.currentStatus).color,
                                         'border-2'
                                     ]">
                                    <span class="text-2xl font-black">{{ getStatusBadge(editingAttendance.currentStatus).icon }}</span>
                                    <span class="text-lg font-black">{{ editingAttendance.currentStatus }}</span>
                                </div>
                            </div>

                            <!-- New Status Selection -->
                            <div>
                                <label class="flex items-center gap-2 text-sm font-black text-gray-900 mb-4 uppercase tracking-wider">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                    Change Status To:
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        @click="editingAttendance.newStatus = 'HADIR'"
                                        :class="[
                                            'px-5 py-4 rounded-2xl font-black text-base transition-all duration-300 border-3',
                                            editingAttendance.newStatus === 'HADIR' 
                                                ? 'bg-gradient-to-br from-green-500 to-green-600 text-white shadow-2xl scale-110 border-green-700' 
                                                : 'bg-green-50 text-green-700 hover:bg-green-100 border-green-200 hover:scale-105 hover:shadow-lg'
                                        ]"
                                    >
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-2xl">✓</span>
                                            <span>HADIR</span>
                                        </div>
                                    </button>
                                    <button
                                        @click="editingAttendance.newStatus = 'SAKIT'"
                                        :class="[
                                            'px-5 py-4 rounded-2xl font-black text-base transition-all duration-300 border-3',
                                            editingAttendance.newStatus === 'SAKIT' 
                                                ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-2xl scale-110 border-blue-700' 
                                                : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200 hover:scale-105 hover:shadow-lg'
                                        ]"
                                    >
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-2xl font-black">S</span>
                                            <span>SAKIT</span>
                                        </div>
                                    </button>
                                    <button
                                        @click="editingAttendance.newStatus = 'IZIN'"
                                        :class="[
                                            'px-5 py-4 rounded-2xl font-black text-base transition-all duration-300 border-3',
                                            editingAttendance.newStatus === 'IZIN' 
                                                ? 'bg-gradient-to-br from-yellow-500 to-yellow-600 text-white shadow-2xl scale-110 border-yellow-700' 
                                                : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border-yellow-200 hover:scale-105 hover:shadow-lg'
                                        ]"
                                    >
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-2xl font-black">I</span>
                                            <span>IZIN</span>
                                        </div>
                                    </button>
                                    <button
                                        @click="editingAttendance.newStatus = 'ALFA'"
                                        :class="[
                                            'px-5 py-4 rounded-2xl font-black text-base transition-all duration-300 border-3',
                                            editingAttendance.newStatus === 'ALFA' 
                                                ? 'bg-gradient-to-br from-red-500 to-red-600 text-white shadow-2xl scale-110 border-red-700' 
                                                : 'bg-red-50 text-red-700 hover:bg-red-100 border-red-200 hover:scale-105 hover:shadow-lg'
                                        ]"
                                    >
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-2xl">✗</span>
                                            <span>ALFA</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                        <button
                            type="button"
                            @click="updateAttendance"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl border-2 border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-base font-black text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-300 sm:w-auto transition-all hover:scale-105"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Changes
                        </button>
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="mt-3 w-full inline-flex justify-center items-center gap-2 rounded-xl border-2 border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 sm:mt-0 sm:w-auto transition-all hover:scale-105"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </button>
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

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 10px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: linear-gradient(to right, #e0e7ff, #ddd6fe);
    border-radius: 10px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: linear-gradient(to right, #6366f1, #a855f7);
    border-radius: 10px;
    border: 2px solid #e0e7ff;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to right, #4f46e5, #9333ea);
}

/* Smooth animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert {
    animation: slideIn 0.3s ease-out;
}

/* Border styles */
.border-3 {
    border-width: 3px;
}
</style>
