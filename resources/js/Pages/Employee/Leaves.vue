<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    leaves: Array,
    summary: Object,
});

const form = useForm({
    from_date: '',
    to_date: '',
    half_day: false,
    reason: '',
});

function submit() {
    form.post('/me/leaves', {
        preserveScroll: true,
        onSuccess: () => form.reset('from_date','to_date','reason','half_day'),
    });
}

const byMonth = computed(() => {
    const map = new Map();
    for (const l of props.leaves) {
        if (!map.has(l.month)) map.set(l.month, []);
        map.get(l.month).push(l);
    }
    return [...map.entries()].map(([m, items]) => ({
        key: m,
        label: new Date(m + '-01').toLocaleDateString(undefined, { year:'numeric', month:'long' }),
        items,
        paid:   items.filter(i => i.status==='approved' && i.pay_type==='paid').reduce((a,b)=>a + Number(b.total_days), 0),
        unpaid: items.filter(i => i.status==='approved' && i.pay_type==='unpaid').reduce((a,b)=>a + Number(b.total_days), 0),
    }));
});
</script>

<template>
    <Head title="Leaves" />
    <AppLayout>
        <h1 class="text-2xl font-bold mb-6">Leave management</h1>

        <div class="grid lg:grid-cols-4 gap-4 mb-6">
            <div class="card"><div class="text-xs text-slate-500 font-semibold uppercase">Quota</div><div class="text-2xl font-bold mt-1">{{ summary.quota }}</div></div>
            <div class="card"><div class="text-xs text-slate-500 font-semibold uppercase">Remaining</div><div class="text-2xl font-bold mt-1 text-emerald-600">{{ summary.remaining }}</div></div>
            <div class="card"><div class="text-xs text-slate-500 font-semibold uppercase">Used (paid)</div><div class="text-2xl font-bold mt-1">{{ summary.used_paid }}</div></div>
            <div class="card"><div class="text-xs text-slate-500 font-semibold uppercase">Used (unpaid)</div><div class="text-2xl font-bold mt-1 text-red-600">{{ summary.used_unpaid }}</div></div>
        </div>

        <div class="grid lg:grid-cols-3 gap-5">
            <!-- Apply leave -->
            <div class="card lg:col-span-1 lg:sticky lg:top-4 self-start">
                <h2 class="font-semibold mb-4">Apply for leave</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="label">From</label>
                        <input v-model="form.from_date" type="date" class="input" required>
                        <p v-if="form.errors.from_date" class="text-xs text-red-600 mt-1">{{ form.errors.from_date }}</p>
                    </div>
                    <div>
                        <label class="label">To</label>
                        <input v-model="form.to_date" type="date" class="input" required>
                        <p v-if="form.errors.to_date" class="text-xs text-red-600 mt-1">{{ form.errors.to_date }}</p>
                    </div>
                    <label class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2 cursor-pointer">
                        <div>
                            <div class="text-sm font-medium">Half-day</div>
                            <div class="text-xs text-slate-500">Counts as 0.5 day</div>
                        </div>
                        <span class="relative inline-flex items-center">
                            <input v-model="form.half_day" type="checkbox" class="sr-only peer">
                            <span class="w-10 h-6 bg-slate-300 peer-checked:bg-brand-600 rounded-full transition"></span>
                            <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full transition peer-checked:translate-x-4"></span>
                        </span>
                    </label>
                    <p v-if="form.errors.half_day" class="text-xs text-red-600 -mt-2">{{ form.errors.half_day }}</p>

                    <div>
                        <label class="label">Reason</label>
                        <textarea v-model="form.reason" rows="3" class="input" required></textarea>
                        <p v-if="form.errors.reason" class="text-xs text-red-600 mt-1">{{ form.errors.reason }}</p>
                    </div>
                    <button class="btn btn-primary w-full" :disabled="form.processing">
                        {{ form.processing ? 'Submitting...' : 'Submit request' }}
                    </button>
                </form>
            </div>

            <!-- History by month -->
            <div class="lg:col-span-2 space-y-5">
                <div v-if="!leaves.length" class="card text-sm text-slate-500">No leave history yet.</div>

                <div v-for="m in byMonth" :key="m.key" class="card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="font-semibold">{{ m.label }}</div>
                        <div class="text-xs space-x-2">
                            <span class="badge badge-green">Paid: {{ m.paid }}</span>
                            <span class="badge badge-red">Unpaid: {{ m.unpaid }}</span>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="l in m.items" :key="l.id" class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="text-sm font-medium">
                                    {{ new Date(l.from_date).toLocaleDateString(undefined,{day:'2-digit',month:'short'}) }}
                                    <span v-if="l.from_date !== l.to_date">– {{ new Date(l.to_date).toLocaleDateString(undefined,{day:'2-digit',month:'short'}) }}</span>
                                    <span v-if="l.half_day" class="badge badge-slate ml-2">Half-day</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ l.reason }}</div>
                                <div v-if="l.admin_note" class="text-xs text-slate-500 mt-0.5">HR: {{ l.admin_note }}</div>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-500">{{ l.total_days }} day{{ l.total_days > 1 ? 's' : '' }}</span>
                                <span v-if="l.status==='pending'"  class="badge badge-amber">Pending</span>
                                <span v-else-if="l.status==='approved'" :class="['badge', l.pay_type==='paid' ? 'badge-green' : 'badge-red']">
                                    Approved · {{ l.pay_type }}
                                </span>
                                <span v-else class="badge badge-slate">Rejected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
