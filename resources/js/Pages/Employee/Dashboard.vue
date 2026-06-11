<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    today: String,
    todayAttendance: Object,
    leaveSummary: Object,
    upcomingEvents: Array,
});

const now = ref(new Date());
let timer;
onMounted(() => { timer = setInterval(() => (now.value = new Date()), 1000); });
onUnmounted(() => clearInterval(timer));

const TARGET_SECONDS = 8 * 3600 + 30 * 60;

function formatHMS(seconds) {
    const s = Math.max(0, Math.floor(seconds));
    const h = String(Math.floor(s / 3600)).padStart(2, '0');
    const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
    const sec = String(s % 60).padStart(2, '0');
    return `${h}:${m}:${sec}`;
}

const liveStaffing = computed(() => {
    const t = props.todayAttendance;
    if (!t || !t.check_in_at) return 0;
    const end = t.check_out_at ? new Date(t.check_out_at) : now.value;
    const start = new Date(t.check_in_at);
    let breakSec = t.total_break_seconds || 0;
    if (t.on_break && t.open_break_at) {
        // server already added accumulated up to the page render moment;
        // continue live by adding seconds since render only if check_out is null
    }
    let s = Math.max(0, (end - start) / 1000 - breakSec);
    if (t.on_break && !t.check_out_at) {
        // freeze growth while on break: subtract elapsed since now vs open_break_at
        // already accounted via breakSec from server
    }
    return Math.floor(s);
});

const progress = computed(() => Math.min(100, (liveStaffing.value / TARGET_SECONDS) * 100));
const completed = computed(() => liveStaffing.value >= TARGET_SECONDS);

function action(path) {
    router.post(path, {}, { preserveScroll: true });
}

const ta = computed(() => props.todayAttendance);
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="flex flex-col gap-2 mb-6">
            <h1 class="text-2xl font-bold">Hi {{ $page.props.auth.user.name.split(' ')[0] }} 👋</h1>
            <p class="text-sm text-slate-500">{{ new Date(today).toLocaleDateString(undefined, { weekday:'long', day:'numeric', month:'long', year:'numeric' }) }}</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-5">
            <!-- Attendance widget -->
            <div class="card lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Today's attendance</div>
                        <div class="text-3xl font-bold mt-1 tabular-nums">{{ formatHMS(liveStaffing) }}</div>
                    </div>
                    <div class="text-right text-sm">
                        <div class="text-slate-500">Target</div>
                        <div class="font-semibold">8h 30m</div>
                    </div>
                </div>

                <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div :class="['h-3 rounded-full transition-all duration-500',
                                  completed ? 'bg-emerald-500' : 'bg-red-500']"
                         :style="{ width: progress + '%' }"></div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
                    <span>{{ Math.round(progress) }}% of target</span>
                    <span v-if="completed" class="badge badge-green">Goal complete</span>
                    <span v-else class="badge badge-red">In progress</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
                    <button v-if="!ta || !ta.check_in_at"
                            @click="action('/me/attendance/check-in')"
                            class="btn btn-primary">Check in</button>
                    <button v-if="ta && ta.check_in_at && !ta.check_out_at && !ta.on_break"
                            @click="action('/me/attendance/break-in')"
                            class="btn btn-secondary">Break in</button>
                    <button v-if="ta && ta.on_break"
                            @click="action('/me/attendance/break-out')"
                            class="btn btn-secondary">Break out</button>
                    <button v-if="ta && ta.check_in_at && !ta.check_out_at"
                            @click="action('/me/attendance/check-out')"
                            class="btn btn-danger">Check out</button>

                    <div class="col-span-2 md:col-span-4 grid grid-cols-2 md:grid-cols-3 gap-3 text-sm pt-2">
                        <div class="bg-slate-50 rounded-lg px-3 py-2">
                            <div class="text-xs text-slate-500">Check in</div>
                            <div class="font-semibold">{{ ta?.check_in_at ? new Date(ta.check_in_at).toLocaleTimeString() : '—' }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-lg px-3 py-2">
                            <div class="text-xs text-slate-500">Break</div>
                            <div class="font-semibold">{{ formatHMS(ta?.total_break_seconds || 0) }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-lg px-3 py-2 col-span-2 md:col-span-1">
                            <div class="text-xs text-slate-500">Check out</div>
                            <div class="font-semibold">{{ ta?.check_out_at ? new Date(ta.check_out_at).toLocaleTimeString() : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave summary -->
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Leave balance</div>
                        <div class="text-3xl font-bold mt-1 tabular-nums">{{ leaveSummary.remaining }}</div>
                        <div class="text-xs text-slate-500">of {{ leaveSummary.quota }} days</div>
                    </div>
                    <Link href="/me/leaves" class="text-xs text-brand-600 font-medium">Manage →</Link>
                </div>
                <ul class="text-sm space-y-2">
                    <li class="flex justify-between"><span class="text-slate-500">Used (paid)</span><span class="font-medium">{{ leaveSummary.used_paid }}</span></li>
                    <li class="flex justify-between"><span class="text-slate-500">Used (unpaid)</span><span class="font-medium">{{ leaveSummary.used_unpaid }}</span></li>
                    <li class="flex justify-between"><span class="text-slate-500">Pending</span><span class="font-medium">{{ leaveSummary.pending }}</span></li>
                </ul>
            </div>

            <!-- Upcoming events -->
            <div class="card lg:col-span-3">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Upcoming events</div>
                    <Link href="/events" class="text-xs text-brand-600 font-medium">All events →</Link>
                </div>
                <div v-if="!upcomingEvents.length" class="text-sm text-slate-500">No events scheduled.</div>
                <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="e in upcomingEvents" :key="e.id" class="bg-slate-50 rounded-xl p-4">
                        <div class="text-xs text-brand-600 font-semibold">
                            {{ new Date(e.starts_at).toLocaleDateString(undefined,{day:'numeric',month:'short'}) }}
                            ·
                            {{ new Date(e.starts_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) }}
                        </div>
                        <div class="font-semibold mt-1">{{ e.title }}</div>
                        <div v-if="e.location" class="text-xs text-slate-500 mt-1">📍 {{ e.location }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
