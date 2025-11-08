<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    success: {
        type: String,
        default: null
    }
});

const form = useForm({
    name: '',
    email: '',
    nip: '',
    phone: '',
    notes: '',
});

const submit = () => {
    form.post(route('teacher.register.store'), {
        onFinish: () => {
            if (!form.hasErrors) {
                form.reset();
            }
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrasi Guru" />

        <div class="mb-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Pendaftaran Akun Guru
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Daftarkan diri Anda sebagai guru. Admin akan melakukan verifikasi terlebih dahulu.
            </p>
        </div>

        <!-- Success Message -->
        <div v-if="success" class="mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium">{{ success }}</p>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nama Lengkap *" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    placeholder="Masukkan nama lengkap Anda"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email *" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    placeholder="contoh@email.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="nip" value="NIP (Opsional)" />
                <TextInput
                    id="nip"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nip"
                    placeholder="Nomor Induk Pegawai"
                />
                <InputError class="mt-2" :message="form.errors.nip" />
            </div>

            <div class="mt-4">
                <InputLabel for="phone" value="Nomor Telepon (Opsional)" />
                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    placeholder="08123456789"
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="mt-4">
                <InputLabel for="notes" value="Catatan Tambahan (Opsional)" />
                <textarea
                    id="notes"
                    v-model="form.notes"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    rows="3"
                    placeholder="Informasi tambahan yang ingin Anda sampaikan..."
                ></textarea>
                <InputError class="mt-2" :message="form.errors.notes" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link
                    :href="route('login')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline"
                >
                    Sudah punya akun? Login
                </Link>

                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    <span v-if="form.processing">Mengirim...</span>
                    <span v-else>Daftar</span>
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-medium mb-1">Catatan Penting:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Pendaftaran akan diverifikasi oleh admin sekolah</li>
                        <li>Anda akan menerima email konfirmasi setelah akun disetujui</li>
                        <li>Gunakan email yang aktif dan bisa dihubungi</li>
                        <li>Proses verifikasi membutuhkan waktu 1-3 hari kerja</li>
                    </ul>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
