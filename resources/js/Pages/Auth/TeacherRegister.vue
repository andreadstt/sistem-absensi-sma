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

        <!-- Page Title -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Pendaftaran Akun Guru</h2>
            <p class="text-sm text-gray-600 mt-1">Daftarkan diri Anda sebagai guru. Admin akan melakukan verifikasi terlebih dahulu.</p>
        </div>

        <!-- Success Message -->
        <div v-if="success" class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center gap-3 text-green-800">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium">{{ success }}</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
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

            <div>
                <InputLabel for="email" value="Alamat Email *" />
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

            <div>
                <InputLabel for="nip" value="NIP (Nomor Induk Pegawai)" />
                <TextInput
                    id="nip"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nip"
                    placeholder="Opsional"
                />
                <InputError class="mt-2" :message="form.errors.nip" />
            </div>

            <div>
                <InputLabel for="phone" value="Nomor Telepon" />
                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    placeholder="08123456789 (Opsional)"
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="notes" value="Catatan Tambahan" />
                <textarea
                    id="notes"
                    v-model="form.notes"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
                    rows="3"
                    placeholder="Informasi tambahan yang ingin Anda sampaikan... (Opsional)"
                ></textarea>
                <InputError class="mt-2" :message="form.errors.notes" />
            </div>

            <PrimaryButton 
                class="w-full justify-center mt-6" 
                :class="{ 'opacity-25': form.processing }" 
                :disabled="form.processing"
            >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Mengirim...' : 'Daftar Sekarang' }}
            </PrimaryButton>

            <div class="text-center mt-4">
                <Link
                    :href="route('login')"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                >
                    Sudah punya akun? Masuk di sini
                </Link>
            </div>
        </form>

        <!-- Info Box -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Catatan Penting:</p>
                    <ul class="space-y-1 text-xs">
                        <li>• Pendaftaran akan diverifikasi oleh admin sekolah</li>
                        <li>• Anda akan menerima email konfirmasi setelah akun disetujui</li>
                        <li>• Gunakan email yang aktif dan dapat dihubungi</li>
                        <li>• Proses verifikasi membutuhkan waktu 1-3 hari kerja</li>
                    </ul>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
