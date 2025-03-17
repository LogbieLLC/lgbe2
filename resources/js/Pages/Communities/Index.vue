<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    communities: Object,
});
</script>

<template>
    <Head title="Communities" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Communities</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Browse Communities</h3>
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('communities.create')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                            >
                                Create Community
                            </Link>
                        </div>

                        <div v-if="communities.data.length === 0" class="text-center py-8">
                            <p>No communities found.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="community in communities.data" :key="community.id" class="border rounded p-4 hover:bg-gray-50">
                                <Link :href="route('communities.show', community)" class="block">
                                    <h4 class="text-xl font-bold">r/{{ community.name }}</h4>
                                    <p class="text-gray-600 mt-1">{{ community.description }}</p>
                                    <div class="mt-2 text-sm text-gray-500">
                                        {{ community.members_count }} members
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <div class="flex justify-between">
                                <Link
                                    v-if="communities.prev_page_url"
                                    :href="communities.prev_page_url"
                                    class="px-4 py-2 bg-gray-200 rounded"
                                >
                                    Previous
                                </Link>
                                <span v-else class="px-4 py-2 bg-gray-100 text-gray-400 rounded">Previous</span>

                                <Link
                                    v-if="communities.next_page_url"
                                    :href="communities.next_page_url"
                                    class="px-4 py-2 bg-gray-200 rounded"
                                >
                                    Next
                                </Link>
                                <span v-else class="px-4 py-2 bg-gray-100 text-gray-400 rounded">Next</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>