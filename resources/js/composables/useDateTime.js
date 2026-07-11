import { ref, onMounted, onUnmounted } from 'vue';

const INDONESIAN_DAYS = {
    1: 'Senin',
    2: 'Selasa',
    3: 'Rabu',
    4: 'Kamis',
    5: 'Jumat',
    6: 'Sabtu',
    7: 'Minggu',
};

const INDONESIAN_DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Get current day of week (1 = Monday, 7 = Sunday in ISO format)
export const getTodayDayNumber = () => {
    const today = new Date();
    const dayNum = today.getDay();
    return dayNum === 0 ? 7 : dayNum;
};

// Map day number to Indonesian day name
export const getDayName = (dayNum) => {
    return INDONESIAN_DAYS[dayNum] || '-';
};

// Get today's date in YYYY-MM-DD format
export const getTodayDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Format date as "Rabu, 13 May 2025"
export const formatDateIndonesian = (date = new Date()) => {
    const day = date.getDate();
    const month = MONTH_NAMES[date.getMonth()];
    const year = date.getFullYear();
    const dayName = INDONESIAN_DAY_NAMES[date.getDay()];
    return `${dayName}, ${day} ${month} ${year}`;
};

// Get formatted date and time with day name
export const getFormattedDateTime = (date = new Date()) => {
    const dateStr = formatDateIndonesian(date);
    const timeStr = date.toLocaleTimeString('id-ID', { hour12: false });
    return `${dateStr} - ${timeStr}`;
};

// Composable for real-time date/time updates
export const useCurrentDateTime = () => {
    const currentDateTime = ref('');
    let intervalId = null;

    const updateDateTime = () => {
        currentDateTime.value = getFormattedDateTime();
    };

    onMounted(() => {
        updateDateTime();
        intervalId = setInterval(updateDateTime, 1000);
    });

    onUnmounted(() => {
        if (intervalId) clearInterval(intervalId);
    });

    return {
        currentDateTime
    };
};

// Check if a class is scheduled for today
export const isScheduledToday = (classData, currentDayNum) => {
    if (!classData.schedules || classData.schedules.length === 0) return false;
    return classData.schedules.some(schedule => {
        const dayNum = INDONESIAN_DAYS ? Object.keys(INDONESIAN_DAYS).find(key => INDONESIAN_DAYS[key] === schedule.day) : null;
        return parseInt(dayNum) === currentDayNum;
    });
};

// Get today's schedule for a class
export const getTodayScheduleForClass = (classData, currentDayNum) => {
    if (!classData.schedules || classData.schedules.length === 0) return null;
    return classData.schedules.find(schedule => {
        const dayNum = INDONESIAN_DAYS ? Object.keys(INDONESIAN_DAYS).find(key => INDONESIAN_DAYS[key] === schedule.day) : null;
        return parseInt(dayNum) === currentDayNum;
    });
};

// Check if current time falls within schedule time slot + buffer
export const isTimeWindowActive = (timeSlot, bufferMinutes = 20) => {
    if (!timeSlot) return true;
    const parts = timeSlot.split('-');
    if (parts.length !== 2) return true;

    const now = new Date();
    const currentTotalMinutes = now.getHours() * 60 + now.getMinutes();

    const [startH, startM] = parts[0].trim().split(':').map(Number);
    const startTotalMinutes = startH * 60 + startM;

    const [endH, endM] = parts[1].trim().split(':').map(Number);
    const endTotalMinutes = endH * 60 + endM + parseInt(bufferMinutes);

    return currentTotalMinutes >= startTotalMinutes && currentTotalMinutes <= endTotalMinutes;
};
