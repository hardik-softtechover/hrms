<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    stats: Object,
    recentLeaves: Array,
});
</script>

<template>
    <Head title="Admin · Dashboard" />
    <AppLayout>
        <h1 class="text-2xl font-bold mb-6">Welcome back, HR</h1>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="card"><div class="text-xs uppercase font-semibold text-slate-500">Employees</div><div class="text-3xl font-bold mt-1">{{ stats.employees }}</div></div>
            <div class="card"><div class="text-xs uppercase font-semibold text-slate-500">Pending leaves</div><div class="text-3xl font-bold mt-1 text-amber-600">{{ stats.pendingLeaves }}</div></div>
            <div class="card"><div class="text-xs uppercase font-semibold text-slate-500">Present today</div><div class="text-3xl font-bold mt-1 text-emerald-600">{{ stats.presentToday }}</div></div>
            <div class="card"><div class="text-xs uppercase font-semibold text-slate-500">Upcoming events</div><div class="text-3xl font-bold mt-1">{{ stats.upcomingEvents }}</div></div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Pending leave requests</h2>
                <Link href="/admin/leaves" class="text-sm text-brand-600 font-medium">View all →</Link>
            </div>
            <div v-if="!recentLeaves.length" class="text-sm text-slate-500">No pending requests.</div>
            <div v-else class="divide-y divide-slate-100">
                <div v-for="l in recentLeaves" :key="l.id" class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <img v-if="l.employee.avatar_url" :src="l.employee.avatar_url" class="w-10 h-10 rounded-full object-cover">
                        <div v-else class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 grid place-items-center font-semibold">
                            {{ l.employee.name[0] }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium truncate">{{ l.employee.name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ l.reason }}</div>
                        </div>
                    </div>
                    <div class="text-right text-xs text-slate-500 whitespace-nowrap">
                        <div>{{ new Date(l.from_date).toLocaleDateString(undefined,{day:'2-digit',month:'short'}) }} – {{ new Date(l.to_date).toLocaleDateString(undefined,{day:'2-digit',month:'short'}) }}</div>
                        <div class="font-semibold text-slate-700">{{ l.total_days }} day(s)</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
