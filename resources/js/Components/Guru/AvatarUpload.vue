<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    currentAvatar: {
        type: String,
        default: null,
    },
    teacherName: {
        type: String,
        default: 'Guru',
    },
});

const emit = defineEmits(['updated']);

const fileInput = ref(null);
const previewImage = ref(null);
const isUploading = ref(false);

// Form for avatar upload
const form = useForm({
    avatar: null,
});

// Get initials from teacher name
const initials = computed(() => {
    const parts = (props.teacherName || 'GU').split(' ');
    return parts.map(p => p[0]).join('').toUpperCase().slice(0, 2);
});

// Determine which avatar to show (preview, current, or placeholder)
const displayAvatar = computed(() => {
    if (previewImage.value) {
        return previewImage.value;
    }
    if (props.currentAvatar) {
        return props.currentAvatar;
    }
    return null;
});

// Handle file selection
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('Silakan pilih file gambar yang valid.');
        return;
    }

    // Validate file size (5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if (file.size > maxSize) {
        alert('Ukuran gambar tidak boleh lebih dari 5MB.');
        return;
    }

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImage.value = e.target.result;
    };
    reader.readAsDataURL(file);

    // Set form data
    form.avatar = file;
};

// Handle upload
const handleUpload = () => {
    if (!form.avatar) {
        alert('Silakan pilih file gambar terlebih dahulu.');
        return;
    }

    isUploading.value = true;

    form.post(route('guru.profile.updateAvatar'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form and preview
            previewImage.value = null;
            form.reset();
            fileInput.value.value = '';
            emit('updated');
        },
        onError: () => {
            console.error('Upload failed');
        },
        onFinish: () => {
            isUploading.value = false;
        },
    });
};

// Cancel upload
const handleCancel = () => {
    previewImage.value = null;
    form.reset();
    fileInput.value.value = '';
};

// Trigger file input
const triggerFileInput = () => {
    fileInput.value.click();
};
</script>

<template>
    <div class="space-y-6">
        <!-- Avatar Display -->
        <div class="flex flex-col items-center">
            <!-- Avatar Circle -->
            <div class="relative mb-4">
                <div class="w-40 h-40 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-lg border-4 border-blue-100">
                    <img
                        v-if="displayAvatar"
                        :src="displayAvatar"
                        :alt="teacherName"
                        class="w-full h-full object-cover"
                    />
                    <span v-else class="text-6xl font-bold text-white opacity-80">
                        {{ initials }}
                    </span>
                </div>

                <!-- Upload Badge -->
                <button
                    type="button"
                    @click="triggerFileInput"
                    class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-3 shadow-lg transition duration-200 hover:shadow-xl"
                    title="Upload avatar"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>

            <!-- Hidden File Input -->
            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleFileSelect"
            />

            <!-- Teacher Name -->
            <h2 class="text-2xl font-bold text-gray-900">{{ teacherName }}</h2>
        </div>

        <!-- Upload Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <!-- File Input Display -->
            <div class="space-y-4">
                <!-- Info Text -->
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Format yang didukung:</span> JPEG, PNG, JPG, GIF<br>
                    <span class="font-semibold">Ukuran maksimal:</span> 5MB
                </p>

                <!-- Upload Button -->
                <div v-if="!previewImage" class="flex gap-3">
                    <button
                        type="button"
                        @click="triggerFileInput"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200"
                    >
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Pilih Gambar
                    </button>
                </div>

                <!-- Preview & Upload/Cancel Buttons -->
                <div v-if="previewImage" class="space-y-4">
                    <p class="text-sm text-gray-600 font-semibold">Preview:</p>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="handleUpload"
                            :disabled="isUploading"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition duration-200"
                        >
                            <svg v-if="!isUploading" class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span v-if="isUploading" class="inline-block">
                                <svg class="animate-spin h-5 w-5 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Uploading...
                            </span>
                            <span v-else>Upload</span>
                        </button>
                        <button
                            type="button"
                            @click="handleCancel"
                            :disabled="isUploading"
                            class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 disabled:bg-gray-200 text-gray-800 font-semibold rounded-lg transition duration-200"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
.transition {
    transition: all 0.2s ease-in-out;
}
</style>
