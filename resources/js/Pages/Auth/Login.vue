<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <Head title="Sign in" />

    <div class="min-h-screen grid lg:grid-cols-2">
        <div class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-brand-600 to-brand-700 text-white p-12">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white/15 grid place-items-center font-bold">H</div>
                    <span class="text-xl font-bold">HRMS</span>
                </div>
            </div>
            <div>
                <h2 class="text-3xl font-bold leading-tight max-w-md">
                    One place for your team — attendance, leaves, and people.
                </h2>
                <p class="text-brand-100 mt-3 max-w-md text-sm">
                    Sign in with the credentials your HR team shared with you.
                </p>
            </div>
            <div class="text-xs text-brand-100/80">© {{ new Date().getFullYear() }} HRMS</div>
        </div>

        <div class="flex items-center justify-center p-6">
            <div class="w-full max-w-sm">
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <div class="w-9 h-9 rounded-lg bg-brand-600 text-white grid place-items-center font-bold">H</div>
                    <div class="font-bold text-lg">HRMS</div>
                </div>
                <h1 class="text-2xl font-bold mb-1">Welcome back</h1>
                <p class="text-sm text-slate-500 mb-6">Sign in to continue</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="label">Username</label>
                        <input v-model="form.username" type="text" class="input" autocomplete="username" required>
                        <p v-if="form.errors.username" class="text-xs text-red-600 mt-1">{{ form.errors.username }}</p>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input v-model="form.password" type="password" class="input" autocomplete="current-password" required>
                        <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input v-model="form.remember" type="checkbox" class="rounded">
                        Remember me
                    </label>
                    <button class="btn btn-primary w-full" :disabled="form.processing">
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
