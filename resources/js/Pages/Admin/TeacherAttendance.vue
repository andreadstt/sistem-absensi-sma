<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const props = defineProps({
    teachersAttendance: Array, // Array of teachers with their attendance data
    currentMonth: String,
})

const selectedTeacher = ref(null)
const currentMonth = ref(props.currentMonth || new Date().toISOString().slice(0, 7))

const teachers = computed(() => {
    return props.teachersAttendance || []
})

const selectedTeacherData = computed(() => {
    if (!selectedTeacher.value) return null
    return teachers.value.find(t => t.id === selectedTeacher.value)
})

const handleTeacherSelect = (teacherId) => {
    selectedTeacher.value = teacherId
}
</script>

<template>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Tracking Kehadiran Guru</h1>
                <p class="text-gray-600 mt-2">Pantau kehadiran semua guru melalui kalender</p>
            </div>

            <!-- Teachers List -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Pilih Guru</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <button
                            v-for="teacher in teachers"
                            :key="teacher.id"
                            @click="handleTeacherSelect(teacher.id)"
                            :class="[
                                'p-4 rounded-lg border-2 transition',
                                selectedTeacher === teacher.id
                                    ? 'border-blue-600 bg-blue-50'
                                    : 'border-gray-200 hover:border-blue-400 bg-white'
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex-1 text-left">
                                    <p class="font-semibold text-gray-900">{{ teacher.name }}</p>
                                    <p class="text-sm text-gray-600">{{ teacher.nip }}</p>
                                    <div class="mt-2 flex gap-2">
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                            ✓ {{ teacher.hadir_count }}
                                        </span>
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                            ✕ {{ teacher.tidak_hadir_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Calendar View for Selected Teacher -->
            <div v-if="selectedTeacherData" class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ selectedTeacherData.name }} - Kehadiran {{ currentMonth }}
                    </h2>
                </div>
                
                <!-- Insert TeacherAttendanceCalendar Component here later -->
                <div class="p-6">
                    <p class="text-gray-600">Calendar view akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
