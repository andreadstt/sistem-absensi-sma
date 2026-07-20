<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GuruLayout from '@/Layouts/GuruLayout.vue';

const props = defineProps({
    days: {
        type: Array,
        required: true,
    },
    currentMonth: {
        type: Number,
        required: true,
    },
    currentYear: {
        type: Number,
        required: true,
    },
    monthName: {
        type: String,
        required: true,
    },
});

const weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

const previousMonth = () => {
    let month = props.currentMonth - 1;
    let year = props.currentYear;
    if (month < 1) {
        month = 12;
        year--;
    }
    router.get(route('guru.kalender-akademik.index'), { month, year }, { preserveState: true, preserveScroll: true });
};

const nextMonth = () => {
    let month = props.currentMonth + 1;
    let year = props.currentYear;
    if (month > 12) {
        month = 1;
        year++;
    }
    router.get(route('guru.kalender-akademik.index'), { month, year }, { preserveState: true, preserveScroll: true });
};

const goToToday = () => {
    const today = new Date();
    router.get(route('guru.kalender-akademik.index'), { month: today.getMonth() + 1, year: today.getFullYear() }, { preserveState: true, preserveScroll: true });
};

const getEventColorClass = (type) => {
    switch (type) {
        case 'holiday': return 'bg-red-100 text-red-700';
        case 'exam': return 'bg-orange-100 text-orange-700';
        case 'meeting': return 'bg-blue-100 text-blue-700';
        case 'activity': return 'bg-emerald-100 text-emerald-700';
        default: return 'bg-gray-200 text-gray-700';
    }
};

const getEventTypeName = (type) => {
    switch (type) {
        case 'holiday': return 'Hari Libur';
        case 'exam': return 'Ujian';
        case 'meeting': return 'Rapat';
        case 'activity': return 'Kegiatan';
        default: return 'Lainnya';
    }
};

// Modal Logic
const selectedEvent = ref(null);
const showModal = ref(false);

const openEventDetail = (event) => {
    selectedEvent.value = event;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedEvent.value = null;
};
</script>

<template>
    <Head title="Kalender Akademik" />

    <GuruLayout title="Kalender Akademik">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header (Optional title above calendar like in Kehadiran) -->
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kalender Akademik</h1>
                    <p class="text-gray-600 mt-2">Jadwal kegiatan akademik sekolah</p>
                </div>
                <div>
                    <button @click="goToToday" class="px-4 py-2 bg-white text-gray-700 text-sm font-semibold border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-all duration-200">
                        Hari Ini
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header Solid Block (Different Color: Indigo Gradient) -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white p-6">
                    <div class="flex items-center justify-between">
                        <button @click="previousMonth" class="p-2 hover:bg-indigo-500 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h2 class="text-2xl font-bold">{{ monthName }} {{ currentYear }}</h2>
                        <button @click="nextMonth" class="p-2 hover:bg-indigo-500 rounded-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Calendar Body -->
                <div class="p-4 sm:p-6 overflow-x-auto w-full">
                    <div class="min-w-[700px]">
                        <!-- Days Header -->
                        <div class="grid grid-cols-7 gap-2 mb-4">
                            <div v-for="day in weekdays" :key="day" class="text-center font-semibold text-gray-600 py-2">
                                {{ day }}
                            </div>
                        </div>
                        
                        <!-- Calendar Grid (Box Style) -->
                        <div class="grid grid-cols-7 gap-1 sm:gap-2">
                            <div v-for="(day, index) in days" :key="index" 
                                class="min-h-[100px] sm:min-h-[120px] rounded-lg transition-all duration-200 border-2 group flex flex-col"
                             :class="[
                                 !day.isCurrentMonth ? 'bg-white border-gray-100 opacity-50' : 
                                 day.isToday ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-100 border-transparent hover:border-gray-200'
                             ]">
                            
                            <div v-if="day.isCurrentMonth || day.events.length > 0" class="w-full h-full flex flex-col p-2">
                                <!-- Date Number -->
                                <div class="flex justify-center mb-1">
                                    <span class="font-bold text-lg"
                                          :class="[
                                              day.isToday ? 'text-indigo-700' : 
                                              day.isWeekend ? 'text-red-600' : 'text-gray-700'
                                          ]">
                                        {{ day.day }}
                                    </span>
                                </div>
                                
                                <!-- Events -->
                                <div class="space-y-1.5 overflow-y-auto flex-1 scrollbar-none mt-1">
                                    <div v-for="event in day.events" :key="event.id" 
                                         @click.stop="openEventDetail(event)"
                                         class="text-[11px] px-2 py-1.5 rounded truncate cursor-pointer transition-transform hover:scale-105 font-semibold text-center leading-tight"
                                         :class="getEventColorClass(event.type)"
                                         :title="event.title">
                                        {{ event.title }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Legend (Bottom) -->
                <div class="bg-gray-50 px-6 py-4 flex gap-6 flex-wrap justify-center border-t border-gray-200">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-100 border-2 border-red-300 rounded"></div>
                        <span class="text-sm text-gray-700">Hari Libur</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-orange-100 border-2 border-orange-300 rounded"></div>
                        <span class="text-sm text-gray-700">Ujian</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-100 border-2 border-blue-300 rounded"></div>
                        <span class="text-sm text-gray-700">Rapat</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-emerald-100 border-2 border-emerald-300 rounded"></div>
                        <span class="text-sm text-gray-700">Kegiatan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-gray-200 border-2 border-gray-300 rounded"></div>
                        <span class="text-sm text-gray-700">Lainnya</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Detail Modal (DaisyUI dark theme style for consistency) -->
        <input type="checkbox" :checked="showModal" class="modal-toggle" />
        <div class="modal" role="dialog" :class="{ 'modal-open': showModal }">
            <div class="modal-box max-w-lg bg-gray-900 text-white">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-white">Detail Kegiatan</h3>
                    <button @click="closeModal" class="btn btn-sm btn-circle btn-ghost text-gray-400 hover:text-white">✕</button>
                </div>

                <template v-if="selectedEvent">
                    <!-- Date info -->
                    <div class="bg-gray-800 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-300">Tanggal Mulai</span>
                            <span class="font-semibold text-white text-sm">{{ selectedEvent.start_date?.split('T')[0] || selectedEvent.start_date }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-300">Tanggal Selesai</span>
                            <span class="font-semibold text-white text-sm">{{ selectedEvent.end_date?.split('T')[0] || selectedEvent.end_date }}</span>
                        </div>
                    </div>

                    <!-- Summary badges -->
                    <div class="flex items-center gap-2 mb-4 flex-wrap">
                        <span class="badge badge-lg bg-indigo-600 text-white border-transparent font-semibold uppercase tracking-wider text-xs px-3">
                            {{ getEventTypeName(selectedEvent.type) }}
                        </span>
                    </div>

                    <!-- Event Details -->
                    <div class="bg-gray-800 rounded-lg p-4 mb-4">
                        <h4 class="text-xl font-bold text-white mb-2">{{ selectedEvent.title }}</h4>
                        <div v-if="selectedEvent.description" class="mt-3 pt-3 border-t border-gray-700 text-sm">
                            <p class="text-gray-400 text-xs font-medium mb-1">Keterangan:</p>
                            <p class="text-gray-200 whitespace-pre-wrap">{{ selectedEvent.description }}</p>
                        </div>
                        <div v-else class="mt-3 pt-3 border-t border-gray-700 text-sm">
                            <p class="text-gray-400 italic">Tidak ada keterangan tambahan.</p>
                        </div>
                    </div>
                </template>

                <!-- Modal Action -->
                <div class="modal-action">
                    <button @click="closeModal" class="btn btn-ghost text-white">Tutup</button>
                </div>
            </div>
            <label class="modal-backdrop" @click="closeModal"></label>
        </div>
    </GuruLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
