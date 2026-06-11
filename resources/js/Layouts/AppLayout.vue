<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const open = ref(false);

const employeeNav = [
    { name: 'Dashboard',  href: '/me',           icon: 'home' },
    { name: 'Attendance', href: '/me/attendance',icon: 'clock' },
    { name: 'Leaves',     href: '/me/leaves',    icon: 'calendar' },
    { name: 'Directory',  href: '/me/directory', icon: 'users' },
    { name: 'Events',     href: '/events',       icon: 'star' },
    { name: 'Profile',    href: '/me/profile',   icon: 'user' },
];

const adminNav = [
    { name: 'Dashboard', href: '/admin',           icon: 'home' },
    { name: 'Employees', href: '/admin/employees', icon: 'users' },
    { name: 'Leaves',    href: '/admin/leaves',    icon: 'calendar' },
    { name: 'Events',    href: '/events',          icon: 'star' },
    { name: 'Profile',   href: '/me/profile',      icon: 'user' },
];

const nav = computed(() => (isAdmin.value ? adminNav : employeeNav));

const flash = computed(() => page.props.flash || {});

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Mobile top bar -->
        <div class="lg:hidden sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
            <button @click="open = !open" class="p-2 rounded-md hover:bg-slate-100" aria-label="Menu">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="font-bold text-brand-600">HRMS</div>
            <Link href="/me/profile" class="flex items-center">
                <img v-if="user?.avatar_url" :src="user.avatar_url" class="w-8 h-8 rounded-full object-cover" alt="me">
                <span v-else class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 grid place-items-center text-sm font-semibold">
                    {{ user?.name?.[0] }}
                </span>
            </Link>
        </div>

        <div class="lg:grid lg:grid-cols-[260px_1fr]">
            <!-- Sidebar -->
            <aside
                :class="['lg:sticky lg:top-0 lg:h-screen bg-white border-r border-slate-200 px-4 py-6',
                    open ? 'block' : 'hidden lg:block']">
                <div class="hidden lg:flex items-center gap-2 mb-8">
                    <div class="w-9 h-9 rounded-lg bg-brand-600 text-white grid place-items-center font-bold">H</div>
                    <div class="font-bold text-lg">HRMS</div>
                </div>

                <div class="mb-5 hidden lg:flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                    <img v-if="user?.avatar_url" :src="user.avatar_url" class="w-10 h-10 rounded-full object-cover" alt="me">
                    <div v-else class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 grid place-items-center font-semibold">
                        {{ user?.name?.[0] }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold truncate">{{ user?.name }}</div>
                        <div class="text-xs text-slate-500 capitalize">{{ user?.role }}</div>
                    </div>
                </div>

                <nav class="space-y-1">
                    <Link v-for="item in nav" :key="item.href" :href="item.href"
                          @click="open = false"
                          :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition',
                                   $page.url.startsWith(item.href) && (item.href !== '/me' || $page.url === '/me')
                                     ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100']">
                        <span class="w-5 h-5 grid place-items-center">
                            <!-- minimal inline icons -->
                            <svg v-if="item.icon === 'home'"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M3 12 12 3l9 9M5 10v10h14V10" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <svg v-else-if="item.icon === 'clock'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round"/></svg>
                            <svg v-else-if="item.icon === 'calendar'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                            <svg v-else-if="item.icon === 'users'"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="9" cy="8" r="4"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7M17 11a4 4 0 1 0-2-7.5M22 21c0-3-2-5.5-5-6.5"/></svg>
                            <svg v-else-if="item.icon === 'star'"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="m12 3 2.9 6 6.6.9-4.8 4.7 1.2 6.5L12 17.8l-5.9 3.3 1.2-6.5L2.5 9.9 9.1 9 12 3z"/></svg>
                            <svg v-else                              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                        </span>
                        {{ item.name }}
                    </Link>
                </nav>

                <button @click="logout"
                        class="mt-8 w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M15 17l5-5-5-5M20 12H9M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Sign out
                </button>
            </aside>

            <main class="px-4 sm:px-6 lg:px-10 py-6 lg:py-8">
                <div v-if="flash.success" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                    {{ flash.error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
