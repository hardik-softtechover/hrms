<script setup>
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show:  { type: Boolean, default: false },
    title: { type: String,  default: '' },
    maxWidth: { type: String, default: 'max-w-md' },
});
const emit = defineEmits(['close']);

function close() { emit('close'); }

function onKey(e) {
    if (e.key === 'Escape' && props.show) close();
}

onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));

watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});
</script>

<template>
    <transition
        enter-active-class="transition duration-150 ease-out"
        leave-active-class="transition duration-100 ease-in"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 backdrop-blur-sm p-4"
             @click.self="close">
            <transition
                enter-active-class="transition duration-200 ease-out"
                leave-active-class="transition duration-150 ease-in"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                leave-to-class="opacity-0 scale-95 translate-y-2">
                <div v-if="show"
                     :class="['bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 w-full', maxWidth]"
                     role="dialog" aria-modal="true">
                    <div class="flex items-start justify-between px-6 pt-5 pb-3 border-b border-slate-100">
                        <div class="min-w-0">
                            <h3 v-if="title" class="text-lg font-bold text-slate-900 truncate">{{ title }}</h3>
                            <slot name="subtitle" />
                        </div>
                        <button @click="close" type="button"
                                class="-mt-1 -mr-2 ml-4 grid place-items-center w-9 h-9 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition"
                                aria-label="Close">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                                <path d="M6 6l12 12M18 6l-12 12" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5">
                        <slot />
                    </div>

                    <div v-if="$slots.footer" class="px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <slot name="footer" />
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
