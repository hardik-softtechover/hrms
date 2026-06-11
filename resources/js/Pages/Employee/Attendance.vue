<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
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
</script>

<template>
    <Head title="Attendance" />
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Attendance</h1>
                <p class="text-sm text-slate-500">Target: 8h 30m staffing per day</p>
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
                        <th class="py-3 pr-4 min-w-[180px]">Progress</th>
                        <th class="py-3 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!records.length"><td colspan="7" class="py-6 text-center text-slate-500">No attendance records yet.</td></tr>
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
                            <span v-if="r.completed" class="badge badge-green">Goal met</span>
                            <span v-else class="badge badge-red">Short</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
