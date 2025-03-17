<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import TextArea from '@/components/TextArea.vue';

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
                                <TextArea
                                    id="description"
                                    class="mt-1 block w-full"
                                    v-model="form.description"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.description" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Briefly describe your community.
                                </p>
                            </div>

                            <div class="mb-4">
                                <Label for="rules">Community Rules</Label>
                                <TextArea
                                    id="rules"
                                    class="mt-1 block w-full"
                                    v-model="form.rules"
                                    rows="6"
                                />
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
