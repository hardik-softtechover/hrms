<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
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
const showEdit = ref(false);
const editingEmployee = ref(null);

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

function openEdit(employee) {
    editingEmployee.value = employee;
    form.reset();
    form.clearErrors();
    form.name = employee.name;
    form.username = employee.username;
    form.annual_leave_quota = employee.annual_leave_quota;
    form.password = ''; // Keep empty unless changing
    showEdit.value = true;
}

function closeEdit() {
    if (form.processing) return;
    showEdit.value = false;
    editingEmployee.value = null;
}

function submit() {
    form.post('/admin/employees', {
        preserveScroll: true,
        onSuccess: () => { form.reset(); showAdd.value = false; },
    });
}

function update() {
    form.put(`/admin/employees/${editingEmployee.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { 
            form.reset(); 
            showEditModal.value = false; 
            editingEmployee.value = null;
        },
    });
}

const isDirty = computed(() => {
    if (!editingEmployee.value) return false;
    return form.name !== editingEmployee.value.name ||
           form.username !== editingEmployee.value.username ||
           form.annual_leave_quota !== editingEmployee.value.annual_leave_quota ||
           form.password !== '';
});

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
                        <th class="py-3 px-4 text-right">Actions</th>
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
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-end gap-4">
                                <Link :href="`/admin/employees/${e.id}/attendance`" 
                                      class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Attendance
                                </Link>
                                <button @click="openEdit(e)" 
                                        class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Edit
                                </button>
                                <button @click="askRemove(e)" 
                                        class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add Modal -->
        <Modal :show="showAdd" title="Add employee" @close="closeAdd">
            <template #subtitle>
                <p class="text-sm text-slate-500 mt-1">Share the username and password with the new employee. They will fill in their profile after first login.</p>
            </template>

            <form @submit.prevent="submit" class="space-y-3" id="employee-add-form">
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
                    <button type="submit" form="employee-add-form" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEdit" title="Edit employee" @close="closeEdit">
            <form @submit.prevent="update" class="space-y-3" id="employee-edit-form">
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
                    <label class="label">New password (leave blank to keep current)</label>
                    <input v-model="form.password" class="input" type="password" placeholder="••••••••">
                    <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="label">Annual leave quota</label>
                    <input v-model.number="form.annual_leave_quota" type="number" min="0" max="60" class="input">
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeEdit" class="btn btn-secondary" :disabled="form.processing">Cancel</button>
                    <button v-if="isDirty" type="submit" form="employee-edit-form" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save changes' }}
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
