<script setup>
import { ref, watch } from 'vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import GuruLayout from '@/Layouts/GuruLayout.vue';
import AvatarUpload from '@/Components/Guru/AvatarUpload.vue';

const props = defineProps({
    teacher: {
        type: Object,
        default: () => ({
            id: null,
            name: 'Teacher',
            nip: null,
            phone: null,
            avatar: null,
        }),
    },
    user: {
        type: Object,
        default: () => ({
            email: 'user@example.com',
        }),
    },
});

const page = usePage();
const refreshKey = ref(0);

// Form for profile updates
const form = useForm({
    name: props.teacher?.name || '',
    nip: props.teacher?.nip || '',
    phone: props.teacher?.phone || '',
});

// Watch for changes in props and update form
watch(
    () => props.teacher,
    (newTeacher) => {
        if (newTeacher) {
            form.name = newTeacher.name || '';
            form.nip = newTeacher.nip || '';
            form.phone = newTeacher.phone || '';
        }
    },
    { deep: true }
);

// Submit form
const submitForm = () => {
    form.put(route('guru.profile.update'), {
        onSuccess: () => {
            // Optional: trigger any UI updates if needed
        },
    });
};

// Watch for successful updates
const handleAvatarUpdated = () => {
    // Refresh the component to show new avatar
    refreshKey.value += 1;
};
</script>

<template>
    <Head title="Profil Guru" />

    <GuruLayout title="Profil Guru">
        <!-- Success Message -->
        <div v-if="page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="font-semibold">Berhasil!</p>
                <p class="text-sm">{{ page.props.flash?.success }}</p>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="font-semibold">Error!</p>
                <p class="text-sm">{{ page.props.flash?.error }}</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Avatar Upload Section (Left Column) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <AvatarUpload
                        :key="refreshKey"
                        :current-avatar="teacher.avatar"
                        :teacher-name="teacher.name"
                        @updated="handleAvatarUpdated"
                    />
                </div>
            </div>

            <!-- Profile Information Section (Right Column) -->
            <div class="lg:col-span-2">
                <form @submit.prevent="submitForm">
                    <!-- Personal Information Card -->
                    <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Informasi Pribadi
                        </h3>

                        <div class="space-y-6">
                            <!-- Name Field -->
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Nama Guru
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-900 placeholder-gray-500"
                                    :class="form.errors.name ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-white'"
                                    placeholder="Masukkan nama guru"
                                />
                                <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                            </div>

                            <!-- NIP Field -->
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    NIP (Nomor Induk Pegawai)
                                </label>
                                <input
                                    v-model="form.nip"
                                    type="text"
                                    class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition font-mono text-gray-900 placeholder-gray-500"
                                    :class="form.errors.nip ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-white'"
                                    placeholder="Masukkan NIP"
                                />
                                <p v-if="form.errors.nip" class="text-xs text-red-600 mt-1">{{ form.errors.nip }}</p>
                            </div>

                            <!-- Phone Field -->
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Nomor Telepon
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-900 placeholder-gray-500"
                                    :class="form.errors.phone ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-white'"
                                    placeholder="Masukkan nomor telepon (opsional)"
                                />
                                <p v-if="form.errors.phone" class="text-xs text-red-600 mt-1">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Informasi Akun
                        </h3>

                        <div class="space-y-6">
                            <!-- Email Field (Read-only) -->
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Email
                                </label>
                                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 font-medium break-all">
                                    {{ user.email }}
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Read-only field</p>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-800">
                                    <span class="font-semibold">💡 Info:</span> Email hanya bisa diubah oleh administrator.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
                        >
                            <span v-if="!form.processing">💾 Simpan Perubahan</span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        <button
                            type="button"
                            @click="form.reset()"
                            class="px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GuruLayout>
</template>

<style scoped>
.form-group {
    display: flex;
    flex-direction: column;
}
</style>
