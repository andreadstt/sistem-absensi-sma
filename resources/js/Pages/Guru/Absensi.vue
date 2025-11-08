<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">Form Absensi Siswa</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-700 uppercase">Class</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ classRoom.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-700 uppercase">Subject</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ subject.name }}</p>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-700 uppercase">Date</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ date }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert if read-only -->
                <div v-if="isReadOnly" class="alert alert-info mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-base font-semibold">Attendance has already been recorded for this class. Viewing mode only.</span>
                </div>

                <!-- Attendance Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">
                                Student List ({{ students.length }} students)
                            </h3>
                            <button
                                v-if="!isReadOnly"
                                type="button"
                                @click="markAllPresent"
                                class="btn btn-outline btn-success font-bold text-base"
                            >
                                ✓ Mark Semua Siswa Hadir
                            </button>
                        </div>

                        <!-- Student List -->
                        <div class="space-y-4">
                            <div
                                v-for="(student, index) in students"
                                :key="student.id"
                                class="card bg-base-100 border-2 border-gray-300"
                            >
                                <div class="card-body p-4">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <!-- Student Info -->
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                <div class="bg-neutral text-neutral-content rounded-full w-14">
                                                    <span class="text-2xl font-bold">{{ student.name.charAt(0) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-900 text-lg">{{ student.name }}</h4>
                                                <p class="text-base text-gray-600 font-semibold">NIS: {{ student.nis }}</p>
                                            </div>
                                            <div class="badge badge-lg font-bold text-sm" :class="student.gender === 'M' ? 'badge-info' : 'badge-success'">
                                                {{ student.gender === 'M' ? 'Male' : 'Female' }}
                                            </div>
                                        </div>

                                        <!-- Status Radio Buttons or Badge -->
                                        <div v-if="!isReadOnly" class="flex flex-wrap gap-3">
                                            <label class="cursor-pointer flex items-center gap-2 py-2 px-3 rounded-lg border-2 hover:bg-green-50 transition-colors" :class="form.attendances[index].status === 'HADIR' ? 'border-green-500 bg-green-50' : 'border-gray-300'">
                                                <input
                                                    type="radio"
                                                    :name="`status-${student.id}`"
                                                    value="HADIR"
                                                    v-model="form.attendances[index].status"
                                                    class="radio radio-success"
                                                />
                                                <span class="font-bold text-base">Hadir</span>
                                            </label>
                                            <label class="cursor-pointer flex items-center gap-2 py-2 px-3 rounded-lg border-2 hover:bg-yellow-50 transition-colors" :class="form.attendances[index].status === 'SAKIT' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-300'">
                                                <input
                                                    type="radio"
                                                    :name="`status-${student.id}`"
                                                    value="SAKIT"
                                                    v-model="form.attendances[index].status"
                                                    class="radio radio-warning"
                                                />
                                                <span class="font-bold text-base">Sakit</span>
                                            </label>
                                            <label class="cursor-pointer flex items-center gap-2 py-2 px-3 rounded-lg border-2 hover:bg-blue-50 transition-colors" :class="form.attendances[index].status === 'IZIN' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                                <input
                                                    type="radio"
                                                    :name="`status-${student.id}`"
                                                    value="IZIN"
                                                    v-model="form.attendances[index].status"
                                                    class="radio radio-info"
                                                />
                                                <span class="font-bold text-base">Izin</span>
                                            </label>
                                            <label class="cursor-pointer flex items-center gap-2 py-2 px-3 rounded-lg border-2 hover:bg-red-50 transition-colors" :class="form.attendances[index].status === 'ALFA' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                                                <input
                                                    type="radio"
                                                    :name="`status-${student.id}`"
                                                    value="ALFA"
                                                    v-model="form.attendances[index].status"
                                                    class="radio radio-error"
                                                />
                                                <span class="font-bold text-base">Alfa</span>
                                            </label>
                                        </div>
                                        <div v-else>
                                            <div class="badge badge-lg font-bold text-base py-4 px-4" :class="getStatusBadgeClass(student.status)">
                                                {{ student.status }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                                                <!-- Submit Button -->
                        <div v-if="!isReadOnly" class="mt-6 flex gap-4">
                            <button
                                type="button"
                                @click="submit"
                                :disabled="form.processing"
                                class="btn btn-primary btn-lg flex-1 font-bold text-lg"
                            >
                                <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                {{ form.processing ? 'Saving...' : 'Save Attendance' }}
                            </button>
                            <button
                                type="button"
                                @click="$inertia.visit(route('guru.dashboard'))"
                                class="btn btn-outline btn-lg font-bold text-lg"
                            >
                                Cancel
                            </button>
                        </div>

                        <!-- Back Button if read-only -->
                        <div v-else class="mt-6">
                            <button
                                type="button"
                                @click="router.visit(route('guru.dashboard'))"
                                class="btn btn-outline btn-lg w-full"
                            >
                                ← Back to Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
