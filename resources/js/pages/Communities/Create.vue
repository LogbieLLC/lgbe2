<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const form = useForm({
    name: '',
    description: '',
    rules: '',
});

const submit = () => {
    form.post(route('communities.store'));
};
</script>

<template>
    <Head title="Create Community" />

    <AppLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">Create Community</h2>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <Label for="name">Community Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Community names cannot be changed after creation.
                                </p>
                            </div>

                            <div class="mb-4">
                                <Label for="description">Description</Label>
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    v-model="form.description"
                                    required
                                    rows="4"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Briefly describe your community.
                                </p>
                            </div>

                            <div class="mb-4">
                                <Label for="rules">Community Rules</Label>
                                <textarea
                                    id="rules"
                                    class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    v-model="form.rules"
                                    rows="6"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.rules" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Set guidelines for your community. This is optional but recommended.
                                </p>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Button class="ml-4" :disabled="form.processing">
                                    Create Community
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
