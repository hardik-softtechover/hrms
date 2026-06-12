<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Modal from '../../Components/Modal.vue';

const props = defineProps({
    employee: Object,
    records: Array,
    targetSeconds: Number,
});

function formatHMS(seconds) {
    const s = Math.max(0, Math.floor(seconds || 0));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    return `${h}h ${String(m).padStart(2,'0')}m`;
}

function pct(staff) {
    return Math.min(100, Math.round((staff / props.targetSeconds) * 100));
}

const showEditModal = ref(false);
const selectedRecord = ref(null);
const editForm = useForm({
    check_in_at: '',
    check_out_at: '',
    note: '',
    breaks: [],
});

function openEditModal(record) {
    selectedRecord.value = record;
    // Extract time (HH:mm) from ISO strings
    editForm.check_in_at = record.check_in_at ? record.check_in_at.slice(11, 16) : '';
    editForm.check_out_at = record.check_out_at ? record.check_out_at.slice(11, 16) : '';
    editForm.note = record.note || '';
    editForm.breaks = (record.breaks || []).map(b => ({
        id: b.id,
        break_in_at: b.break_in_at ? b.break_in_at.slice(11, 16) : '',
        break_out_at: b.break_out_at ? b.break_out_at.slice(11, 16) : '',
    }));
    showEditModal.value = true;
}

function submitEdit() {
    editForm.put(`/admin/attendance/${selectedRecord.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            selectedRecord.value = null;
        },
    });
}

const isDirty = computed(() => {
    if (!selectedRecord.value) return false;
    
    const checkIn = selectedRecord.value.check_in_at ? selectedRecord.value.check_in_at.slice(11, 16) : '';
    const checkOut = selectedRecord.value.check_out_at ? selectedRecord.value.check_out_at.slice(11, 16) : '';
    
    if (editForm.check_in_at !== checkIn) return true;
    if (editForm.check_out_at !== checkOut) return true;
    if (editForm.note !== (selectedRecord.value.note || '')) return true;

    // Check breaks
    for (let i = 0; i < editForm.breaks.length; i++) {
        const originalBreak = selectedRecord.value.breaks[i];
        const bIn = originalBreak.break_in_at ? originalBreak.break_in_at.slice(11, 16) : '';
        const bOut = originalBreak.break_out_at ? originalBreak.break_out_at.slice(11, 16) : '';
        
        if (editForm.breaks[i].break_in_at !== bIn) return true;
        if (editForm.breaks[i].break_out_at !== bOut) return true;
    }

    return false;
});
</script>

<template>
    <Head :title="`Attendance · ${employee.name}`" />
    <AppLayout>
        <div class="mb-6">
            <Link href="/admin/employees" class="text-sm text-brand-600 hover:underline flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to Employees
            </Link>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ employee.name }}'s Attendance</h1>
                    <p class="text-sm text-slate-500">Target: 8h 30m staffing per day</p>
                </div>
            </div>
        </div>

        <div class="card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-100">
                        <th class="py-3 pr-4">Date</th>
                        <th class="py-3 pr-4">Check in</th>
                        <th class="py-3 pr-4">Check out</th>
                        <th class="py-3 pr-4">Break</th>
                        <th class="py-3 pr-4">Staffing</th>
                        <th class="py-3 pr-4 min-w-[150px]">Progress</th>
                        <th class="py-3 pr-4">Note</th>
                        <th class="py-3 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!records.length"><td colspan="8" class="py-6 text-center text-slate-500">No attendance records yet.</td></tr>
                    <tr v-for="r in records" :key="r.id" class="border-b border-slate-50 last:border-0">
                        <td class="py-3 pr-4 whitespace-nowrap">{{ new Date(r.work_date).toLocaleDateString(undefined,{day:'2-digit',month:'short',year:'numeric'}) }}</td>
                        <td class="py-3 pr-4 whitespace-nowrap">{{ r.check_in_at ? new Date(r.check_in_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '—' }}</td>
                        <td class="py-3 pr-4 whitespace-nowrap">{{ r.check_out_at ? new Date(r.check_out_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '—' }}</td>
                        <td class="py-3 pr-4 whitespace-nowrap">{{ formatHMS(r.total_break_seconds) }}</td>
                        <td class="py-3 pr-4 whitespace-nowrap font-semibold">{{ formatHMS(r.staffing_seconds) }}</td>
                        <td class="py-3 pr-4">
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div :class="['h-2 rounded-full', r.completed ? 'bg-emerald-500' : 'bg-red-500']"
                                     :style="{ width: pct(r.staffing_seconds) + '%' }"></div>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">{{ pct(r.staffing_seconds) }}%</div>
                        </td>
                        <td class="py-3 pr-4">
                            <div v-if="r.note" class="text-xs text-slate-500 italic max-w-[150px] truncate" :title="r.note">
                                {{ r.note }}
                            </div>
                            <div v-else class="text-slate-300">—</div>
                        </td>
                        <td class="py-3 pr-4 text-right">
                            <button @click="openEditModal(r)" class="text-xs text-brand-600 hover:underline">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="showEditModal" title="Edit Attendance Record" @close="showEditModal = false">
            <form @submit.prevent="submitEdit" id="edit-attendance-form" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Check In Time</label>
                        <input v-model="editForm.check_in_at" type="time" class="input">
                    </div>
                    <div>
                        <label class="label">Check Out Time</label>
                        <input v-model="editForm.check_out_at" type="time" class="input">
                    </div>
                </div>

                <div v-if="editForm.breaks.length" class="space-y-3 pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Breaks</h3>
                    <div v-for="(b, idx) in editForm.breaks" :key="b.id" class="p-3 bg-slate-50 rounded-lg space-y-3">
                        <div class="text-[10px] font-bold text-slate-400">BREAK #{{ idx + 1 }}</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-medium text-slate-500 mb-1 block">Break In</label>
                                <input v-model="b.break_in_at" type="time" class="input py-1 text-sm">
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-slate-500 mb-1 block">Break Out</label>
                                <input v-model="b.break_out_at" type="time" class="input py-1 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="label">Note / Correction Reason</label>
                    <textarea v-model="editForm.note" class="input min-h-[80px]"></textarea>
                </div>
            </form>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="btn btn-secondary">Cancel</button>
                    <button v-if="isDirty" type="submit" form="edit-attendance-form" class="btn btn-primary" :disabled="editForm.processing">Update Record</button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
