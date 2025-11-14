<script setup>
// Guest layout for authentication pages
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-gray-100 via-gray-50 to-slate-100 px-4 py-8">
        <!-- Logo & Title Section -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-4">
                <img 
                    src="/images/logo.png" 
                    alt="Logo SMAN 10" 
                    class="h-24 w-auto object-contain drop-shadow-lg"
                />
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                PORTAL GURU SMAN 10
            </h1>
            <p class="text-gray-600 font-medium">Sistem Manajemen Absensi Siswa</p>
        </div>

        <!-- Auth Card -->
        <div class="w-full max-w-md">
            <div class="bg-white px-8 py-8 shadow-xl rounded-xl border border-gray-200">
                <slot />
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>© 2025 @drestz. All rights reserved.</p>
        </div>
    </div>
</template>
