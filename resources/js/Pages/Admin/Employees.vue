<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Modal from '../../Components/Modal.vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';

const props = defineProps({
    employees: Array,
    filters:   Object,
});

const q = ref(props.filters.q || '');
let t;
watch(q, (val) => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get('/admin/employees', { q: val }, { preserveState: true, preserveScroll: true, replace: true });
    }, 250);
});

const showAdd = ref(false);
const form = useForm({
    name: '', username: '', password: '', annual_leave_quota: 12,
});

function openAdd() {
    form.reset();
    form.clearErrors();
    showAdd.value = true;
}

function closeAdd() {
    if (form.processing) return;
    showAdd.value = false;
}

function submit() {
    form.post('/admin/employees', {
        preserveScroll: true,
        onSuccess: () => { form.reset(); showAdd.value = false; },
    });
}

const deleteTarget = ref(null);
const deleting     = ref(false);

function askRemove(e) { deleteTarget.value = e; }
function cancelRemove() { if (!deleting.value) deleteTarget.value = null; }

function confirmRemove() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/admin/employees/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <Head title="Admin · Employees" />
    <AppLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Employees</h1>
                <p class="text-sm text-slate-500">{{ employees.length }} total</p>
            </div>
            <div class="flex gap-2">
                <div class="relative">
                    <input v-model="q" placeholder="Search..." class="input pl-9">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                </div>
                <button @click="openAdd" class="btn btn-primary whitespace-nowrap">+ Add employee</button>
            </div>
        </div>

        <div class="card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-100">
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Username</th>
                        <th class="py-3 pr-4">Email</th>
                        <th class="py-3 pr-4">Role</th>
                        <th class="py-3 pr-4">Quota</th>
                        <th class="py-3 pr-4">Joined</th>
                        <th class="py-3 pr-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!employees.length"><td colspan="7" class="py-6 text-center text-slate-500">No employees yet.</td></tr>
                    <tr v-for="e in employees" :key="e.id" class="border-b border-slate-50 last:border-0">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-3">
                                <img v-if="e.avatar_url" :src="e.avatar_url" class="w-9 h-9 rounded-full object-cover">
                                <div v-else class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-sm font-semibold">{{ e.name[0] }}</div>
                                <span class="font-medium">{{ e.name }}</span>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-slate-500">@{{ e.username }}</td>
                        <td class="py-3 pr-4 text-slate-500">{{ e.email || '—' }}</td>
                        <td class="py-3 pr-4">{{ e.designation || '—' }}</td>
                        <td class="py-3 pr-4">{{ e.annual_leave_quota }}</td>
                        <td class="py-3 pr-4 text-slate-500">{{ e.created_at }}</td>
                        <td class="py-3 pr-4 text-right">
                            <button @click="askRemove(e)" class="text-xs text-red-600 hover:underline">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="showAdd" title="Add employee" @close="closeAdd">
            <template #subtitle>
                <p class="text-sm text-slate-500 mt-1">Share the username and password with the new employee. They will fill in their profile after first login.</p>
            </template>

            <form @submit.prevent="submit" class="space-y-3" id="employee-form">
                <div>
                    <label class="label">Full name</label>
                    <input v-model="form.name" class="input" required>
                    <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="label">Username</label>
                    <input v-model="form.username" class="input" required>
                    <p v-if="form.errors.username" class="text-xs text-red-600 mt-1">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label class="label">Temporary password</label>
                    <input v-model="form.password" class="input" required>
                    <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="label">Annual leave quota</label>
                    <input v-model.number="form.annual_leave_quota" type="number" min="0" max="60" class="input">
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeAdd" class="btn btn-secondary" :disabled="form.processing">Cancel</button>
                    <button type="submit" form="employee-form" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create' }}
                    </button>
                </div>
            </template>
        </Modal>

        <ConfirmDialog
            :show="!!deleteTarget"
            title="Remove employee?"
            :message="deleteTarget ? `${deleteTarget.name} (@${deleteTarget.username}) will lose access immediately and all their records will be removed.` : ''"
            confirmLabel="Remove"
            :processing="deleting"
            @close="cancelRemove"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>
