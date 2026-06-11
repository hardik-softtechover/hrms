<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({ user: Object });

const form = useForm({
    _method: 'POST',
    name:          props.user.name,
    email:         props.user.email,
    phone:         props.user.phone,
    designation:   props.user.designation,
    department:    props.user.department,
    date_of_birth: props.user.date_of_birth,
    joined_on:     props.user.joined_on,
    address:       props.user.address,
    avatar:        null,
    password:      '',
    password_confirmation: '',
});

const preview = ref(props.user.avatar_url);
function onFile(e) {
    const f = e.target.files?.[0];
    form.avatar = f;
    if (f) preview.value = URL.createObjectURL(f);
}

function submit() {
    form.post('/me/profile', { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <Head title="Profile" />
    <AppLayout>
        <h1 class="text-2xl font-bold mb-6">My profile</h1>

        <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-5">
            <div class="card lg:col-span-1 text-center">
                <img v-if="preview" :src="preview" class="w-32 h-32 rounded-full object-cover mx-auto">
                <div v-else class="w-32 h-32 rounded-full mx-auto bg-brand-100 text-brand-700 grid place-items-center text-4xl font-bold">
                    {{ user.name?.[0] }}
                </div>
                <label class="btn btn-secondary mt-4 cursor-pointer inline-block">
                    Change photo
                    <input type="file" accept="image/*" @change="onFile" class="hidden">
                </label>
                <p v-if="form.errors.avatar" class="text-xs text-red-600 mt-2">{{ form.errors.avatar }}</p>
                <div class="mt-4 text-xs text-slate-500">@{{ user.username }}</div>
            </div>

            <div class="card lg:col-span-2 space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Full name</label>
                        <input v-model="form.name" class="input" required>
                        <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input v-model="form.email" type="email" class="input">
                        <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input v-model="form.phone" class="input">
                    </div>
                    <div>
                        <label class="label">Designation</label>
                        <input v-model="form.designation" class="input">
                    </div>
                    <div>
                        <label class="label">Department</label>
                        <input v-model="form.department" class="input">
                    </div>
                    <div>
                        <label class="label">Date of birth</label>
                        <input v-model="form.date_of_birth" type="date" class="input">
                    </div>
                    <div>
                        <label class="label">Joined on</label>
                        <input v-model="form.joined_on" type="date" class="input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Address</label>
                        <input v-model="form.address" class="input">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <div class="text-sm font-semibold mb-3">Change password</div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">New password</label>
                            <input v-model="form.password" type="password" class="input" autocomplete="new-password">
                            <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="label">Confirm</label>
                            <input v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save changes' }}
                    </button>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
