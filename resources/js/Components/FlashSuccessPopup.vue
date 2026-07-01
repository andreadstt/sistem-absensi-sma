<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: '',
    },
    actionHref: {
        type: String,
        required: true,
    },
    actionLabel: {
        type: String,
        default: 'OK',
    },
});

const visible = ref(props.show);

watch(
    () => props.show,
    (value) => {
        visible.value = value;
    },
);

const close = () => {
    visible.value = false;
};
</script>

<template>
    <Modal :show="visible" maxWidth="lg" :closeable="false" @close="close">
        <div class="px-8 py-10 sm:px-10">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                <svg
                    class="h-8 w-8"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
            </div>

            <div class="mt-6 text-center">
                <h3 class="text-2xl font-bold text-gray-900">
                    Berhasil
                </h3>

                <p class="mt-4 text-base leading-7 text-gray-600">
                    {{ message }}
                </p>
            </div>

            <div class="mt-8 flex justify-center">
                <Link
                    :href="actionHref"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-7 py-3 text-base font-semibold text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    @click="close"
                >
                    {{ actionLabel }}
                </Link>
            </div>
        </div>
    </Modal>
</template>