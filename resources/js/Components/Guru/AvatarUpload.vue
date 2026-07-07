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
        <!-- Upload Section -->
        <div v-if="previewImage" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <p class="text-sm text-gray-600 font-semibold mb-4">
                Preview Gambar
            </p>

            <div class="flex gap-3">
                <button
                    type="button"
                    @click="handleUpload"
                    :disabled="isUploading"
                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition duration-200"
                >
                    <span v-if="isUploading">Mengunggah...</span>
                    <span v-else>Simpan Foto</span>
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
</template>

<style scoped>
/* Smooth transitions */
.transition {
    transition: all 0.2s ease-in-out;
}
</style>
