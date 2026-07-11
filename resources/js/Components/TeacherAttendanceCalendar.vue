<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    attendancesByDate: Array, // Array of { date, records, total_jadwal, total_hadir, total_tidak_hadir, aggregate_status }
    currentMonth: String, // Format: YYYY-MM
    isEditable: Boolean,
})

const emit = defineEmits(['date-click', 'month-change'])

const getMonthDate = (monthValue) => {
    if (!monthValue) return new Date()

    const parsed = new Date(`${monthValue}-01`)
    return isNaN(parsed.getTime()) ? new Date() : parsed
}

const currentDate = ref(getMonthDate(props.currentMonth))

watch(
    () => props.currentMonth,
    (newMonth) => {
        currentDate.value = getMonthDate(newMonth)
    }
)

const daysInMonth = computed(() => {
    return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
    return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1).getDay()
})

const calendarDays = computed(() => {
    const days = []
    // Empty cells for days before month starts
    for (let i = 0; i < firstDayOfMonth.value; i++) {
        days.push(null)
    }
    // Days of the month
    for (let i = 1; i <= daysInMonth.value; i++) {
        days.push(i)
    }
    return days
})

const monthName = computed(() => {
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
    return `${months[currentDate.value.getMonth()]} ${currentDate.value.getFullYear()}`
})

/**
 * Get all attendance data for a specific day (grouped record from backend).
 * Returns null if no data exists for that day.
 */
const getAttendanceForDate = (day) => {
    if (!day) return null
    const dateStr = `${currentDate.value.getFullYear()}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    return (props.attendancesByDate || []).find(att => att.date === dateStr) || null
}

/**
 * Determine background color based on aggregate status.
 */
const getStatusColor = (dayData) => {
    if (!dayData) return 'bg-gray-100'
    switch (dayData.aggregate_status) {
        case 'ALL_HADIR': return 'bg-green-100'
        case 'ALL_TIDAK_HADIR': return 'bg-red-100'
        case 'CAMPURAN': return 'bg-orange-100'
        default: return 'bg-gray-100'
    }
}

/**
 * Determine text color based on aggregate status.
 */
const getStatusTextColor = (dayData) => {
    if (!dayData) return 'text-gray-600'
    switch (dayData.aggregate_status) {
        case 'ALL_HADIR': return 'text-green-700'
        case 'ALL_TIDAK_HADIR': return 'text-red-700'
        case 'CAMPURAN': return 'text-orange-800'
        default: return 'text-gray-600'
    }
}

/**
 * Get the status icon for the calendar cell.
 */
const getStatusIcon = (dayData) => {
    if (!dayData) return ''
    switch (dayData.aggregate_status) {
        case 'ALL_HADIR': return '✓'
        case 'ALL_TIDAK_HADIR': return '✗'
        case 'CAMPURAN': return '!'
        default: return ''
    }
}

/**
 * Get ratio text (e.g., "2/3") for a day.
 */
const getRatioText = (dayData) => {
    if (!dayData || dayData.total_jadwal === 0) return ''
    return `${dayData.total_hadir}/${dayData.total_jadwal}`
}

const previousMonth = () => {
    const newDate = new Date(currentDate.value)
    newDate.setMonth(newDate.getMonth() - 1)
    currentDate.value = newDate
    const yearMonth = `${newDate.getFullYear()}-${String(newDate.getMonth() + 1).padStart(2, '0')}`
    emit('month-change', yearMonth)
}

const nextMonth = () => {
    const newDate = new Date(currentDate.value)
    newDate.setMonth(newDate.getMonth() + 1)
    currentDate.value = newDate
    const yearMonth = `${newDate.getFullYear()}-${String(newDate.getMonth() + 1).padStart(2, '0')}`
    emit('month-change', yearMonth)
}

const handleDayClick = (day) => {
    if (!day) return
    const dayData = getAttendanceForDate(day)
    if (dayData) {
        emit('date-click', dayData)
    }
}
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
            <div class="flex items-center justify-between">
                <button @click="previousMonth" class="p-2 hover:bg-blue-500 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h2 class="text-2xl font-bold">{{ monthName }}</h2>
                <button @click="nextMonth" class="p-2 hover:bg-blue-500 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Calendar -->
        <div class="p-6">
            <!-- Day headers -->
            <div class="grid grid-cols-7 gap-2 mb-4">
                <div v-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day"
                    class="text-center font-semibold text-gray-600 py-2">
                    {{ day }}
                </div>
            </div>

            <!-- Calendar days -->
            <div class="grid grid-cols-7 gap-2">
                <div v-for="(day, index) in calendarDays" :key="index"
                    class="aspect-square flex flex-col items-center justify-center rounded-lg border-2 transition-all cursor-pointer group"
                    :class="[
                        day ? (getStatusColor(getAttendanceForDate(day)) + ' border-transparent') : 'bg-gray-50 border-gray-200',
                        day && getAttendanceForDate(day) ? 'hover:shadow-lg hover:scale-105' : ''
                    ]"
                    @click="handleDayClick(day)"
                >
                    <div v-if="day" class="w-full h-full flex flex-col items-center justify-center p-1">
                        <span class="font-bold text-lg" :class="getStatusTextColor(getAttendanceForDate(day))">
                            {{ day }}
                        </span>
                        <!-- Ratio text (e.g., "2/3") -->
                        <span v-if="getAttendanceForDate(day)" class="text-xs font-semibold" :class="getStatusTextColor(getAttendanceForDate(day))">
                            {{ getRatioText(getAttendanceForDate(day)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-gray-50 px-6 py-4 flex gap-6 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-100 border-2 border-green-500 rounded"></div>
                <span class="text-sm text-gray-700">Semua Hadir</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-100 border-2 border-red-500 rounded"></div>
                <span class="text-sm text-gray-700">Semua Tidak Hadir</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-orange-100 border-2 border-orange-500 rounded"></div>
                <span class="text-sm text-gray-700">Campuran</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-gray-100 border-2 border-gray-300 rounded"></div>
                <span class="text-sm text-gray-700">Belum Ada Data</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
