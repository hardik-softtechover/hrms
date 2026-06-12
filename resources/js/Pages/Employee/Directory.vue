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
        router.get(window.location.pathname, { q: val }, { preserveState: true, preserveScroll: true, replace: true });
    }, 250);
});
</script>

<template>
    <Head title="Directory" />
    <AppLayout>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold">Employee directory</h1>
                <p class="text-sm text-slate-500">
                    <span v-if="q" class="font-medium text-brand-600">{{ employees.length }} matches</span>
                    <span v-else>{{ employees.length }} teammates</span>
                </p>
            </div>
            <div class="relative max-w-sm w-full">
                <input v-model="q" type="search" placeholder="Search by name, role, department..." class="input pl-9">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
            </div>
        </div>

        <div v-if="!employees.length" class="card text-sm text-slate-500">No employees match your search.</div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="e in employees" :key="e.id" class="card relative overflow-hidden group">
                <!-- Status Bar -->
                <div :class="[
                    'absolute top-0 left-0 w-full h-1',
                    e.status === 'Active' ? 'bg-emerald-500' : 
                    e.status === 'On Break' ? 'bg-amber-500' : 
                    e.status === 'Checked Out' ? 'bg-slate-400' : 'bg-slate-100'
                ]"></div>

                <div class="pt-4 text-center">
                    <div class="relative inline-block">
                        <img v-if="e.avatar_url" :src="e.avatar_url" class="w-20 h-20 rounded-full mx-auto object-cover" :alt="e.name">
                        <div v-else class="w-20 h-20 rounded-full mx-auto bg-brand-100 text-brand-700 grid place-items-center text-2xl font-bold">
                            {{ e.name?.[0] }}
                        </div>
                        <!-- Status Dot -->
                        <div :class="[
                            'absolute bottom-0 right-0 w-5 h-5 rounded-full border-2 border-white',
                            e.status === 'Active' ? 'bg-emerald-500' : 
                            e.status === 'On Break' ? 'bg-amber-500' : 
                            e.status === 'Checked Out' ? 'bg-slate-400' : 'bg-slate-200'
                        ]" :title="e.status"></div>
                    </div>

                    <div class="font-semibold mt-3">{{ e.name }}</div>
                    <div class="text-xs text-slate-500">{{ e.designation || '—' }}</div>
                    
                    <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium uppercase tracking-wider"
                         :class="[
                            e.status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 
                            e.status === 'On Break' ? 'bg-amber-50 text-amber-700' : 
                            e.status === 'Checked Out' ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400'
                         ]">
                        {{ e.status }} {{ e.status_time ? '· ' + e.status_time : '' }}
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-50 text-xs text-slate-500 space-y-1">
                        <div v-if="e.email" class="flex items-center justify-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ e.email }}
                        </div>
                        <div v-if="e.phone" class="flex items-center justify-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ e.phone }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
