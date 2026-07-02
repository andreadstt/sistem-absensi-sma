<script setup>
import { ref, computed } from 'vue'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import TeacherAttendanceCalendar from '@/Components/TeacherAttendanceCalendar.vue'
import { Head, usePage } from '@inertiajs/vue3'

defineOptions({
    layout: GuruLayout,
})

const props = defineProps({
    attendances: Array,
    stats: Object,
    teacherName: String,
})

const currentMonth = ref(new Date().toISOString().slice(0, 7))
const selectedDate = ref(null)

const normalizeDate = (dateValue) => {
    if (!dateValue) return ''

    const date = new Date(dateValue)
    if (!isNaN(date.getTime())) {
        return date.toISOString().slice(0, 10)
    }

    return String(dateValue).slice(0, 10)
}

const normalizedAttendances = computed(() => {
    return (props.attendances || []).map((attendance) => ({
        ...attendance,
        date: normalizeDate(attendance.date),
    }))
})

const filteredAttendances = computed(() => {
    return normalizedAttendances.value.filter(att => att.date.startsWith(currentMonth.value))
})

const monthlyStats = computed(() => {
    const filtered = filteredAttendances.value
    
    return {
        hadir: filtered.filter(att => att.status === 'HADIR').length,
        tidakHadir: filtered.filter(att => att.status === 'TIDAK_HADIR').length,
        total: filtered.length,
        persentase: filtered.length > 0 ? Math.round((filtered.filter(att => att.status === 'HADIR').length / filtered.length) * 100) : 0,
    }
})

const handleDateClick = (attendance) => {
    selectedDate.value = attendance
}

const handleMonthChange = (newMonth) => {
    currentMonth.value = newMonth
}

const formatDate = (date) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(date + 'T00:00:00').toLocaleDateString('id-ID', options)
}
</script>

<template>
    <Head title="Kehadiran Saya" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Laporan Kehadiran Saya</h1>
                <p class="text-gray-600 mt-2">Pantau kehadiran Anda secara visual melalui kalender</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <span class="text-lg">📊</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Rekor</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_records }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                                <span class="text-lg">✓</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Hadir</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.total_hadir }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                <span class="text-lg">✕</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Tidak Hadir</p>
                            <p class="text-2xl font-bold text-red-600">{{ stats.total_tidak_hadir }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                                <span class="text-lg">%</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Persentase Hadir</p>
                            <p class="text-2xl font-bold text-purple-600">{{ stats.percentage_hadir }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Calendar -->
                <div class="lg:col-span-2">
                    <TeacherAttendanceCalendar 
                        :attendances="filteredAttendances"
                        :current-month="currentMonth"
                        :is-editable="false"
                        @update:attendance="handleDateClick"
                        @month-change="handleMonthChange"
                    />
                </div>

                <!-- Detail Panel -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Detail Bulan Ini</h3>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-green-50 rounded-lg">
                            <p class="text-sm text-gray-600">Hadir</p>
                            <p class="text-3xl font-bold text-green-600">{{ monthlyStats.hadir }}</p>
                        </div>

                        <div class="p-4 bg-red-50 rounded-lg">
                            <p class="text-sm text-gray-600">Tidak Hadir</p>
                            <p class="text-3xl font-bold text-red-600">{{ monthlyStats.tidakHadir }}</p>
                        </div>

                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-600">Total Hari</p>
                            <p class="text-3xl font-bold text-blue-600">{{ monthlyStats.total }}</p>
                        </div>

                        <div class="p-4 bg-purple-50 rounded-lg">
                            <p class="text-sm text-gray-600">Persentase</p>
                            <p class="text-3xl font-bold text-purple-600">{{ monthlyStats.persentase }}%</p>
                        </div>

                        <!-- Selected Date Detail -->
                        <div v-if="selectedDate" class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200 mt-6">
                            <h4 class="font-semibold text-gray-900 mb-3">Detail Terpilih</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-gray-600">Tanggal</p>
                                    <p class="font-medium">{{ formatDate(selectedDate.date) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Status</p>
                                    <p :class="[
                                        'font-medium inline-block px-3 py-1 rounded-full',
                                        selectedDate.status === 'HADIR' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                        {{ selectedDate.status === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
                                    </p>
                                </div>
                                <div v-if="selectedDate.notes">
                                    <p class="text-gray-600">Catatan</p>
                                    <p class="text-gray-700">{{ selectedDate.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
