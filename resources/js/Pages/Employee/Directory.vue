<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    employees: Array,
    filters:   Object,
});

const q = ref(props.filters.q || '');
let t;
watch(q, (val) => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get('/me/directory', { q: val }, { preserveState: true, preserveScroll: true, replace: true });
    }, 250);
});
</script>

<template>
    <Head title="Directory" />
    <AppLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Employee directory</h1>
                <p class="text-sm text-slate-500">{{ employees.length }} teammates</p>
            </div>
            <div class="relative max-w-sm w-full">
                <input v-model="q" type="search" placeholder="Search by name, role, department..." class="input pl-9">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
            </div>
        </div>

        <div v-if="!employees.length" class="card text-sm text-slate-500">No employees match your search.</div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="e in employees" :key="e.id" class="card text-center">
                <img v-if="e.avatar_url" :src="e.avatar_url" class="w-20 h-20 rounded-full mx-auto object-cover" :alt="e.name">
                <div v-else class="w-20 h-20 rounded-full mx-auto bg-brand-100 text-brand-700 grid place-items-center text-2xl font-bold">
                    {{ e.name?.[0] }}
                </div>
                <div class="font-semibold mt-3">{{ e.name }}</div>
                <div class="text-xs text-slate-500">{{ e.designation || '—' }}</div>
                <div class="text-xs text-slate-400">{{ e.department || '' }}</div>

                <div class="mt-3 text-xs text-slate-500 space-y-1">
                    <div v-if="e.email">✉ {{ e.email }}</div>
                    <div v-if="e.phone">📞 {{ e.phone }}</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
