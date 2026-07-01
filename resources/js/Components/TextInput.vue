<script setup>
import { computed, onMounted, ref, useAttrs } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const model = defineModel({
    type: String,
    required: true,
});

const attrs = useAttrs();
const input = ref(null);
const showPassword = ref(false);

const isPassword = computed(() => attrs.type === 'password');

const inputType = computed(() => {
    if (!isPassword.value) {
        return attrs.type;
    }

    return showPassword.value ? 'text' : 'password';
});

const inputClass = computed(() => [
    "rounded-lg border-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-gray-900 bg-white placeholder-gray-400 px-4 py-2.5 text-base font-medium transition duration-150 w-full",
    isPassword.value ? "pr-11" : "",
    attrs.class,
]);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({
    focus: () => input.value.focus(),
});
</script>

<template>
    <div class="relative w-full">
        <input
            ref="input"
            v-bind="{ ...attrs, class: undefined }"
            v-model="model"
            :type="inputType"
            :class="inputClass"
        />

        <button
            v-if="isPassword"
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-indigo-600"
            @click="showPassword = !showPassword"
        >
            <!-- Eye -->
            <svg
                v-if="!showPassword"
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6-3s-3-6-9-6-9 6-9 6 3 6 9 6 9-6 9-6z"
                />
            </svg>

            <!-- Eye Off -->
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 3l18 18M10.48 10.47A3 3 0 0013.53 13.52M9.88 5.09A9.77 9.77 0 0112 5c6 0 9 7 9 7a16.8 16.8 0 01-3.23 4.36M6.23 6.23A16.8 16.8 0 003 12s3 7 9 7a9.8 9.8 0 004.12-.91"
                />
            </svg>
        </button>
    </div>
</template>