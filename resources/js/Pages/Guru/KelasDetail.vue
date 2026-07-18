<script setup>
import GuruLayout from '@/Layouts/GuruLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classRoom: { type: Object, required: true },
    students: { type: Array, default: () => [] },
    attendanceDates: { type: Array, default: () => [] },
    availableMonths: { type: Array, default: () => [] },
    selectedMonth: { type: String, default: '' },
    attendanceData: { type: Object, default: () => ({}) }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const selectedMonth = ref(props.selectedMonth || '');

const selectedMonthLabel = computed(() => {
    return props.availableMonths.find((month) => month.value === selectedMonth.value)?.label || 'Semua Bulan';
});

const showEditModal = ref(false);
const showAttendanceSuccessModal = ref(false);
const attendanceSuccessMessage = ref('');
const editingAttendance = ref({
    studentId: null,
    studentName: '',
    date: '',
    currentStatus: null,
    newStatus: null
});

// Format date properly - handle both ISO string and date object
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    
    // If it's already formatted as DD/MM or similar, return as is
    if (typeof dateStr === 'string' && dateStr.match(/^\d{2}\/\d{2}/)) {
        return dateStr;
    }
    
    // Parse the date
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) {
        console.warn('Invalid date:', dateStr);
        return dateStr; // Return original if can't parse
    }
    
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    return `${day}/${month}`;
};

const formatFullDate = (dateStr) => {
    if (!dateStr) return 'Invalid Date';
    
    let date;
    
    // Handle DD/MM/YY format
    if (typeof dateStr === 'string' && dateStr.match(/^\d{2}\/\d{2}\/\d{2}$/)) {
        const [day, month, year] = dateStr.split('/').map(Number);
        // Assume 20xx for years < 50, 19xx for years >= 50
        const fullYear = year < 50 ? 2000 + year : 1900 + year;
        date = new Date(fullYear, month - 1, day);
    } else if (typeof dateStr === 'string' && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
        // Handle YYYY-MM-DD format
        date = new Date(dateStr);
    } else {
        // Try parsing as is
        date = new Date(dateStr);
    }
    
    if (isNaN(date.getTime())) {
        console.warn('Invalid full date:', dateStr);
        return dateStr;
    }
    
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const openEditModal = (student, date) => {
    const status = student.attendances[date];
    if (!status) return;
    
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

const closeAttendanceSuccessModal = () => {
    showAttendanceSuccessModal.value = false;
    attendanceSuccessMessage.value = '';
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
            attendanceSuccessMessage.value = 'Status kehadiran berhasil diubah.';
            showAttendanceSuccessModal.value = true;
        },
        onError: (errors) => alert('Gagal mengubah status: ' + Object.values(errors).join(', '))
    });
};

const changeMonth = () => {
    router.get(route('guru.kelas.show', [props.classRoom.id, props.classRoom.subject_id]), {
        month: selectedMonth.value || undefined,
    }, {
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <Head :title="`Detail Kelas - ${classRoom.name} - ${classRoom.subject_name}`" />

    <GuruLayout :title="`${classRoom.name} - ${classRoom.subject_name}`">
        <div class="space-y-6">
            <!-- Success/Error Message -->
            <div v-if="flash.success" class="alert-message alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ flash.success }}</span>
            </div>

            <div v-if="flash.error" class="alert-message alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ flash.error }}</span>
            </div>

            <!-- Info Card with Export -->
            <div class="info-card mb-6">
                <div class="flex items-start justify-between gap-4 mb-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Rekap Absensi Siswa</h3>
                            <p class="text-sm text-gray-600">Click ikon pada tanggal yang ingin anda edit</p>
                        </div>
                    </div>
                    <a 
                        :href="route('guru.kelas.export', [classRoom.id, classRoom.subject_id])" 
                        class="btn-formal"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export to CSV
                    </a>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="stat-mini">
                        <span class="status-badge-hadir text-xs">✓ = HADIR</span>
                    </div>
                    <div class="stat-mini">
                        <span class="status-badge-sakit text-xs">S = SAKIT</span>
                    </div>
                    <div class="stat-mini">
                        <span class="status-badge-izin text-xs">I = IZIN</span>
                    </div>
                    <div class="stat-mini">
                        <span class="status-badge-alfa text-xs">✗ = ALFA</span>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div v-if="students.length > 0" class="attendance-table-container">
                <div class="attendance-table-header flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Student Attendance Records</h3>
                        <span class="text-sm text-gray-600">{{ students.length }} students | {{ selectedMonthLabel }} | {{ attendanceDates.length }} dates</span>
                    </div>

                    <div class="w-fit overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-sky-50 shadow-lg shadow-slate-200/60">
                        <div class="h-1.5 bg-gradient-to-r from-indigo-400 via-sky-400 to-emerald-400"></div>
                        <div class="flex items-center gap-3 px-3 py-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-300/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <label for="month-filter" class="block text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Bulan</label>
                                <div class="relative mt-1">
                                    <select
                                        id="month-filter"
                                        v-model="selectedMonth"
                                        @change="changeMonth"
                                        class="select select-bordered select-sm w-52 sm:w-56 appearance-none rounded-2xl border-slate-300 bg-white/95 pr-9 text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                                        :disabled="!availableMonths.length"
                                    >
                                        <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                                            {{ month.label }}
                                        </option>
                                    </select>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-wrapper">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th class="sticky-col text-white">No</th>
                                <th class="sticky-col-name text-white">Nama Siswa</th>
                                <th v-for="date in attendanceDates" :key="date" class="text-white">
                                    {{ formatDate(date) }}
                                </th>
                                <th class="text-center text-white">JUMLAH HADIR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in students" :key="student.id">
                                <td class="sticky-col text-center font-bold text-gray-900">{{ index + 1 }}</td>
                                <td class="sticky-col-name font-semibold text-gray-900">{{ student.name }}</td>
                                <td v-for="date in attendanceDates" :key="date" class="text-center text-gray-900">
                                    <button
                                        v-if="student.attendances[date]"
                                        @click="openEditModal(student, date)"
                                        :class="`status-badge-${student.attendances[date].toLowerCase()}`"
                                    >
                                        <span v-if="student.attendances[date] === 'HADIR'">✓</span>
                                        <span v-else-if="student.attendances[date] === 'SAKIT'">S</span>
                                        <span v-else-if="student.attendances[date] === 'IZIN'">I</span>
                                        <span v-else-if="student.attendances[date] === 'ALFA'">✗</span>
                                        <span v-else>-</span>
                                    </button>
                                    <span v-else class="status-badge-empty">-</span>
                                </td>
                                <td class="text-center text-gray-900">
                                    <span class="font-bold text-lg">
                                        {{ Object.values(student.attendances).filter(status => status === 'HADIR').length }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h5 class="text-xl font-bold text-gray-900 mb-2">No Students Found</h5>
                <p class="text-gray-600">This class does not have any students enrolled yet.</p>
            </div>
        </div>

        <!-- Edit Attendance Modal - Using DaisyUI -->
        <input type="checkbox" v-model="showEditModal" class="modal-toggle" />
        <div class="modal" role="dialog">
            <div class="modal-box max-w-md bg-gray-900 text-white">
                <h3 class="font-bold text-lg mb-4 text-white">Edit Attendance Status</h3>
                
                <!-- Student Info -->
                <div class="bg-gray-800 rounded-lg p-4 mb-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-300">Student:</span>
                        <span class="font-semibold text-white">{{ editingAttendance.studentName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-300">Date:</span>
                        <span class="font-semibold text-white">{{ formatFullDate(editingAttendance.date) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Current:</span>
                        <span :class="`badge font-semibold text-white ${editingAttendance.currentStatus === 'HADIR' ? 'badge-success' : editingAttendance.currentStatus === 'SAKIT' ? 'badge-info' : editingAttendance.currentStatus === 'IZIN' ? 'badge-warning' : 'badge-error'}`">
                            {{ editingAttendance.currentStatus }}
                        </span>
                    </div>
                </div>

                <!-- Status Selection -->
                <div class="mb-6">
                    <label class="text-sm font-semibold text-gray-200 mb-3 block">Select New Status:</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" v-model="editingAttendance.newStatus" value="HADIR" class="hidden peer" />
                            <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-400 text-white peer-checked:text-gray-900">
                                <div class="text-2xl mb-1">✓</div>
                                <div class="font-semibold text-sm">HADIR</div>
                            
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" v-model="editingAttendance.newStatus" value="SAKIT" class="hidden peer" />
                            <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-400 text-white peer-checked:text-gray-900">
                                <div class="text-2xl mb-1 font-bold">S</div>
                                <div class="font-semibold text-sm">SAKIT</div>
                            
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" v-model="editingAttendance.newStatus" value="IZIN" class="hidden peer" />
                            <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:border-yellow-400 text-white peer-checked:text-gray-900">
                                <div class="text-2xl mb-1 font-bold">I</div>
                                <div class="font-semibold text-sm">IZIN</div>
                        
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" v-model="editingAttendance.newStatus" value="ALFA" class="hidden peer" />
                            <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-400 text-white peer-checked:text-gray-900">
                                <div class="text-2xl mb-1">✗</div>
                                <div class="font-semibold text-sm">ALFA</div>
                    
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="modal-action">
                    <button @click="closeEditModal" class="btn btn-ghost text-white">Cancel</button>
                    <button @click="updateAttendance" class="btn btn-primary text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
            <label class="modal-backdrop" @click="closeEditModal"></label>
        </div>

        <input type="checkbox" v-model="showAttendanceSuccessModal" class="modal-toggle" />
        <div class="modal" role="dialog">
            <div class="modal-box max-w-md bg-gray-900 text-white text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-500/15 text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h3 class="font-bold text-lg mb-2 text-white">Berhasil</h3>
                <p class="text-sm text-gray-300 mb-6">{{ attendanceSuccessMessage }}</p>

                <div class="modal-action justify-center">
                    <button @click="closeAttendanceSuccessModal" class="btn btn-primary text-white">OK</button>
                </div>
            </div>
            <label class="modal-backdrop" @click="closeAttendanceSuccessModal"></label>
        </div>
    </GuruLayout>
</template>
