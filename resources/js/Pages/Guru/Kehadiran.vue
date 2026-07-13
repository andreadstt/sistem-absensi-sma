<script setup>
import { ref, computed } from 'vue'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import TeacherAttendanceCalendar from '@/Components/TeacherAttendanceCalendar.vue'
import { Head } from '@inertiajs/vue3'

defineOptions({
    layout: GuruLayout,
})

const props = defineProps({
    attendancesByDate: Array, // Array of { date, records, total_jadwal, total_hadir, total_tidak_hadir, aggregate_status }
    stats: Object,
    teacherName: String,
})

const currentMonth = ref(new Date().toISOString().slice(0, 7))
const selectedDayData = ref(null)

// Filter attendances for the currently visible month
const filteredAttendances = computed(() => {
    return (props.attendancesByDate || []).filter(att => att.date.startsWith(currentMonth.value))
})

// Monthly stats computed from the filtered grouped data
const monthlyStats = computed(() => {
    const filtered = filteredAttendances.value
    const totalJadwal = filtered.reduce((sum, d) => sum + d.total_jadwal, 0)
    const totalHadir = filtered.reduce((sum, d) => sum + d.total_hadir, 0)
    const totalTidakHadir = filtered.reduce((sum, d) => sum + d.total_tidak_hadir, 0)

    return {
        hadir: totalHadir,
        tidakHadir: totalTidakHadir,
        total: totalJadwal,
        persentase: totalJadwal > 0 ? Math.round((totalHadir / totalJadwal) * 100) : 0,
    }
})

const showDetailModal = ref(false)

const handleDateClick = (dayData) => {
    selectedDayData.value = dayData
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
}

const handleMonthChange = (newMonth) => {
    currentMonth.value = newMonth
    selectedDayData.value = null
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
                            <p class="text-sm text-gray-600">Total Jadwal</p>
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
                        :attendances-by-date="filteredAttendances"
                        :current-month="currentMonth"
                        :is-editable="false"
                        @date-click="handleDateClick"
                        @month-change="handleMonthChange"
                    />
                </div>

                <!-- Monthly Stats Panel -->
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
                            <p class="text-sm text-gray-600">Total Jadwal</p>
                            <p class="text-3xl font-bold text-blue-600">{{ monthlyStats.total }}</p>
                        </div>

                        <div class="p-4 bg-purple-50 rounded-lg">
                            <p class="text-sm text-gray-600">Persentase</p>
                            <p class="text-3xl font-bold text-purple-600">{{ monthlyStats.persentase }}%</p>
                        </div>
                    </div>

                    <!-- Hint -->
                    <div class="mt-6 pt-4 border-t border-gray-200 text-center text-gray-400 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        Klik tanggal di kalender untuk melihat detail jadwal
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Kehadiran Modal (DaisyUI dark theme) -->
    <input type="checkbox" v-model="showDetailModal" class="modal-toggle" />
    <div class="modal" role="dialog">
        <div class="modal-box max-w-lg bg-gray-900 text-white">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg text-white">Detail Kehadiran</h3>
                <button @click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost text-gray-400 hover:text-white">✕</button>
            </div>

            <template v-if="selectedDayData">
                <!-- Date info -->
                <div class="bg-gray-800 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Tanggal</span>
                        <span class="font-semibold text-white text-sm">{{ formatDate(selectedDayData.date) }}</span>
                    </div>
                </div>

                <!-- Summary badges -->
                <div class="flex items-center gap-2 mb-4 flex-wrap">
                    <span class="badge badge-lg bg-gray-700 text-gray-200 border-gray-600 font-semibold">
                        {{ selectedDayData.total_jadwal }} Jadwal
                    </span>
                    <span class="badge badge-lg badge-success text-white font-semibold">
                        {{ selectedDayData.total_hadir }} Hadir
                    </span>
                    <span v-if="selectedDayData.total_tidak_hadir > 0" class="badge badge-lg badge-error text-white font-semibold">
                        {{ selectedDayData.total_tidak_hadir }} Tidak Hadir
                    </span>
                </div>

                <!-- Schedule detail table -->
                <div class="overflow-hidden rounded-lg border border-gray-700">
                    <table class="w-full text-sm table-fixed">
                        <thead>
                            <tr class="bg-gray-800 text-xs text-gray-400 uppercase tracking-wide">
                                <th class="text-left px-3 py-2.5 w-[28%]">Waktu</th>
                                <th class="text-left px-3 py-2.5 w-[28%]">Mapel</th>
                                <th class="text-left px-3 py-2.5 w-[22%]">Kelas</th>
                                <th class="text-center px-3 py-2.5 w-[22%]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="record in selectedDayData.records" :key="record.id" class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-3 py-3 text-gray-200 font-medium truncate" :title="record.time_slot">
                                    {{ record.time_slot }}
                                </td>
                                <td class="px-3 py-3 text-gray-300 truncate" :title="record.subject_name">
                                    {{ record.subject_name }}
                                </td>
                                <td class="px-3 py-3 text-gray-300 truncate" :title="record.class_name">
                                    {{ record.class_name }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span :class="[
                                        'badge font-semibold text-white whitespace-nowrap',
                                        record.status === 'HADIR' ? 'badge-success' : 'badge-error'
                                    ]">
                                        {{ record.status === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notes -->
                <template v-for="record in selectedDayData.records.filter(r => r.notes)" :key="'note-' + record.id">
                    <div class="mt-3 p-3 bg-gray-800 rounded-lg text-sm">
                        <p class="text-gray-400 text-xs font-medium mb-1">Catatan ({{ record.time_slot }}):</p>
                        <p class="text-gray-200 italic">{{ record.notes }}</p>
                    </div>
                </template>
            </template>

            <!-- Modal Action -->
            <div class="modal-action">
                <button @click="closeDetailModal" class="btn btn-ghost text-white">Tutup</button>
            </div>
        </div>
        <!-- Backdrop click to close -->
        <label class="modal-backdrop" @click="closeDetailModal"></label>
    </div>
</template>

<style scoped>
</style>
