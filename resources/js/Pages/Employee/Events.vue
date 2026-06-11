<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ events: Array });

const upcoming = computed(() => props.events.filter(e => e.is_upcoming));
const past     = computed(() => props.events.filter(e => e.is_past).reverse());
</script>

<template>
    <Head title="Events" />
    <AppLayout>
        <h1 class="text-2xl font-bold mb-6">Events</h1>

        <div v-if="upcoming[0]" class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-700 text-white p-6 mb-6 shadow-md">
            <div class="text-xs uppercase tracking-wide text-brand-100 font-semibold">Next up</div>
            <h2 class="text-2xl font-bold mt-1">{{ upcoming[0].title }}</h2>
            <div class="text-sm mt-2 opacity-90">
                {{ new Date(upcoming[0].starts_at).toLocaleString(undefined,{weekday:'short',day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) }}
                <span v-if="upcoming[0].location">· 📍 {{ upcoming[0].location }}</span>
            </div>
            <p v-if="upcoming[0].description" class="mt-3 text-sm opacity-90 max-w-2xl">{{ upcoming[0].description }}</p>
        </div>

        <section class="mb-8">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Upcoming</h3>
            <div v-if="!upcoming.length" class="card text-sm text-slate-500">Nothing on the calendar.</div>
            <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="e in upcoming.slice(1)" :key="e.id" class="card">
                    <div class="text-xs text-brand-600 font-semibold">{{ new Date(e.starts_at).toLocaleString(undefined,{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) }}</div>
                    <div class="font-semibold mt-1">{{ e.title }}</div>
                    <div v-if="e.location" class="text-xs text-slate-500 mt-1">📍 {{ e.location }}</div>
                    <p v-if="e.description" class="text-xs text-slate-500 mt-2 line-clamp-3">{{ e.description }}</p>
                </div>
            </div>
        </section>

        <section v-if="past.length">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Past</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="e in past" :key="e.id" class="card opacity-70">
                    <div class="text-xs text-slate-500 font-semibold">{{ new Date(e.starts_at).toLocaleString(undefined,{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) }}</div>
                    <div class="font-semibold mt-1">{{ e.title }}</div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
