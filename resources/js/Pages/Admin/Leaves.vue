<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    leaves:  Array,
    filters: Object,
});

const noteFor = ref({});

function approve(l) {
    router.post(`/admin/leaves/${l.id}/approve`, { admin_note: noteFor.value[l.id] || '' }, {
        preserveScroll: true,
        onSuccess: () => { delete noteFor.value[l.id]; },
    });
}

function reject(l) {
    router.post(`/admin/leaves/${l.id}/reject`, { admin_note: noteFor.value[l.id] || '' }, {
        preserveScroll: true,
        onSuccess: () => { delete noteFor.value[l.id]; },
    });
}

function suggestedPayType(emp, days) {
    return emp.remaining >= days ? 'paid' : 'unpaid';
}
</script>

<template>
    <Head title="Admin · Leaves" />
    <AppLayout>
        <div class="flex items-center justify-between mb-6 gap-3">
            <h1 class="text-2xl font-bold">Leave requests</h1>
            <div class="flex gap-2 text-xs">
                <Link href="/admin/leaves?status=all"      :class="['btn',  filters.status==='all'      ? 'btn-primary' : 'btn-secondary']">All</Link>
                <Link href="/admin/leaves?status=pending"  :class="['btn',  filters.status==='pending'  ? 'btn-primary' : 'btn-secondary']">Pending</Link>
                <Link href="/admin/leaves?status=approved" :class="['btn',  filters.status==='approved' ? 'btn-primary' : 'btn-secondary']">Approved</Link>
                <Link href="/admin/leaves?status=rejected" :class="['btn',  filters.status==='rejected' ? 'btn-primary' : 'btn-secondary']">Rejected</Link>
            </div>
        </div>

        <div v-if="!leaves.length" class="card text-sm text-slate-500">No leave records.</div>

        <div class="space-y-4">
            <div v-for="l in leaves" :key="l.id" class="card">
                <div class="flex flex-col lg:flex-row lg:items-start gap-4 justify-between">
                    <div class="flex gap-3 min-w-0">
                        <img v-if="l.employee.avatar_url" :src="l.employee.avatar_url" class="w-12 h-12 rounded-full object-cover">
                        <div v-else class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 grid place-items-center font-semibold">{{ l.employee.name[0] }}</div>
                        <div class="min-w-0">
                            <div class="font-semibold">{{ l.employee.name }} <span class="text-xs text-slate-500">@{{ l.employee.username }}</span></div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Quota {{ l.employee.quota }} · Used {{ l.employee.used_paid }} · Remaining
                                <span class="font-semibold text-emerald-600">{{ l.employee.remaining }}</span>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="font-medium">
                                    {{ new Date(l.from_date).toLocaleDateString(undefined,{day:'2-digit',month:'short'}) }}
                                    <span v-if="l.from_date !== l.to_date">– {{ new Date(l.to_date).toLocaleDateString(undefined,{day:'2-digit',month:'short'}) }}</span>
                                </span>
                                <span class="badge ml-2" :class="l.half_day ? 'badge-slate' : 'badge-blue'">{{ l.total_days }} day{{ l.total_days > 1 ? 's' : '' }}<span v-if="l.half_day"> · half</span></span>
                            </div>
                            <div class="text-sm text-slate-600 mt-1">{{ l.reason }}</div>
                        </div>
                    </div>

                    <div class="lg:max-w-sm w-full">
                        <div v-if="l.status==='pending'" class="space-y-2">
                            <div class="text-xs text-slate-500">
                                Suggested: <span class="font-semibold">{{ suggestedPayType(l.employee, l.total_days) }}</span>
                                <span v-if="suggestedPayType(l.employee, l.total_days) === 'unpaid'" class="text-red-600">(no balance left)</span>
                            </div>
                            <textarea v-model="noteFor[l.id]" rows="2" placeholder="Optional note to employee" class="input"></textarea>
                            <div class="flex gap-2">
                                <button @click="approve(l)" class="btn btn-success flex-1">Approve</button>
                                <button @click="reject(l)"  class="btn btn-danger flex-1">Reject</button>
                            </div>
                        </div>
                        <div v-else class="text-right text-sm">
                            <div v-if="l.status==='approved'" class="space-y-1">
                                <span class="badge badge-green">Approved</span>
                                <span class="badge ml-1" :class="l.pay_type==='paid' ? 'badge-blue' : 'badge-red'">{{ l.pay_type }}</span>
                            </div>
                            <div v-else><span class="badge badge-slate">Rejected</span></div>
                            <div v-if="l.admin_note" class="text-xs text-slate-500 mt-2">HR: {{ l.admin_note }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
