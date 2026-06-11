<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Modal from '../../Components/Modal.vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';

defineProps({ events: Array });

// Convert an ISO timestamp (e.g. "2026-06-15T14:30:00+00:00") to a value
// accepted by <input type="datetime-local"> — local "YYYY-MM-DDTHH:MM".
function isoToLocalInput(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const p = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}

// Convert a local datetime-local value ("YYYY-MM-DDTHH:MM") to UTC ISO
// so the server stores the absolute instant the user actually meant.
function localInputToIso(local) {
    if (!local) return null;
    return new Date(local).toISOString();
}

const showFormModal = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '', description: '', starts_at: '', ends_at: '', location: '',
});

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
}

function openEdit(e) {
    editingId.value = e.id;
    form.title       = e.title || '';
    form.description = e.description || '';
    form.starts_at   = isoToLocalInput(e.starts_at);
    form.ends_at     = isoToLocalInput(e.ends_at);
    form.location    = e.location || '';
    form.clearErrors();
    showFormModal.value = true;
}

function closeFormModal() {
    if (form.processing) return;
    showFormModal.value = false;
}

function submit() {
    form.transform((data) => ({
        ...data,
        starts_at: localInputToIso(data.starts_at),
        ends_at:   localInputToIso(data.ends_at),
    }));

    if (editingId.value) {
        form.put(`/admin/events/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => { showFormModal.value = false; },
        });
    } else {
        form.post('/admin/events', {
            preserveScroll: true,
            onSuccess: () => { showFormModal.value = false; form.reset(); },
        });
    }
}

// Delete confirm
const deleteTarget = ref(null);
const deleting     = ref(false);

function askDelete(e)   { deleteTarget.value = e; }
function cancelDelete() { if (!deleting.value) deleteTarget.value = null; }

function confirmDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/admin/events/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <Head title="Admin · Events" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold">Events</h1>
                <p class="text-sm text-slate-500">{{ events.length }} events</p>
            </div>
            <button @click="openCreate" class="btn btn-primary">+ New event</button>
        </div>

        <div v-if="!events.length" class="card text-sm text-slate-500">No events yet.</div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="e in events" :key="e.id" :class="['card', e.is_past && 'opacity-60']">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div :class="['text-xs font-semibold', e.is_upcoming ? 'text-brand-600' : 'text-slate-500']">
                            {{ new Date(e.starts_at).toLocaleString(undefined,{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) }}
                        </div>
                        <div class="font-semibold mt-1 truncate">{{ e.title }}</div>
                        <div v-if="e.location" class="text-xs text-slate-500 mt-1">📍 {{ e.location }}</div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="openEdit(e)" class="text-xs text-brand-600 hover:underline">Edit</button>
                        <span class="text-slate-300">·</span>
                        <button @click="askDelete(e)" class="text-xs text-red-600 hover:underline">Delete</button>
                    </div>
                </div>
                <p v-if="e.description" class="text-xs text-slate-500 mt-3 line-clamp-3">{{ e.description }}</p>
            </div>
        </div>

        <Modal :show="showFormModal"
               :title="editingId ? 'Edit event' : 'New event'"
               @close="closeFormModal">
            <form @submit.prevent="submit" class="space-y-3" id="event-form">
                <div>
                    <label class="label">Title</label>
                    <input v-model="form.title" class="input" required>
                    <p v-if="form.errors.title" class="text-xs text-red-600 mt-1">{{ form.errors.title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Starts at</label>
                        <input v-model="form.starts_at" type="datetime-local" class="input" required>
                        <p v-if="form.errors.starts_at" class="text-xs text-red-600 mt-1">{{ form.errors.starts_at }}</p>
                    </div>
                    <div>
                        <label class="label">Ends at</label>
                        <input v-model="form.ends_at" type="datetime-local" class="input">
                        <p v-if="form.errors.ends_at" class="text-xs text-red-600 mt-1">{{ form.errors.ends_at }}</p>
                    </div>
                </div>
                <div>
                    <label class="label">Location</label>
                    <input v-model="form.location" class="input">
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea v-model="form.description" rows="3" class="input"></textarea>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeFormModal" class="btn btn-secondary" :disabled="form.processing">Cancel</button>
                    <button type="submit" form="event-form" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : (editingId ? 'Save changes' : 'Create event') }}
                    </button>
                </div>
            </template>
        </Modal>

        <ConfirmDialog
            :show="!!deleteTarget"
            title="Delete event?"
            :message="deleteTarget ? `“${deleteTarget.title}” will be permanently removed.` : ''"
            confirmLabel="Delete"
            :processing="deleting"
            @close="cancelDelete"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
