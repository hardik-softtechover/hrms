<script setup>
import Modal from './Modal.vue';

const props = defineProps({
    show:    { type: Boolean, default: false },
    title:   { type: String,  default: 'Are you sure?' },
    message: { type: String,  default: '' },
    confirmLabel: { type: String, default: 'Confirm' },
    cancelLabel:  { type: String, default: 'Cancel' },
    variant: { type: String,  default: 'danger' }, // danger | primary
    processing: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'confirm']);

function close()   { if (!props.processing) emit('close'); }
function confirm() { emit('confirm'); }
</script>

<template>
    <Modal :show="show" :title="title" maxWidth="max-w-sm" @close="close">
        <div class="flex items-start gap-4">
            <div :class="['shrink-0 w-11 h-11 rounded-full grid place-items-center',
                          variant === 'danger' ? 'bg-red-100 text-red-600' : 'bg-brand-100 text-brand-600']">
                <svg v-if="variant === 'danger'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                    <path d="M12 9v4M12 17h.01M10.3 3.7 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/>
                </svg>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed">{{ message }}</p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <button type="button" @click="close" class="btn btn-secondary" :disabled="processing">
                    {{ cancelLabel }}
                </button>
                <button type="button" @click="confirm"
                        :class="['btn', variant === 'danger' ? 'btn-danger' : 'btn-primary']"
                        :disabled="processing">
                    {{ processing ? 'Working...' : confirmLabel }}
                </button>
            </div>
        </template>
    </Modal>
</template>
