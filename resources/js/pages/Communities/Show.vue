<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    posts: Object,
    isMember: Boolean,
    isModerator: Boolean,
});

const showRules = ref(false);
</script>

<template>
    <Head :title="'r/' + community.name" />

    <AppLayout>
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">r/{{ community.name }}</h2>
            <div class="flex space-x-2">
                <Link
                    v-if="$page.props.auth.user && !isMember"
                    :href="route('communities.join', community)"
                    method="post"
                    as="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                >
                    Join
                </Link>
                <Link
                    v-if="$page.props.auth.user && isMember && !isModerator"
                    :href="route('communities.leave', community)"
                    method="post"
                    as="button"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                >
                    Leave
                </Link>
                <Link
                    v-if="isModerator"
                    :href="route('communities.edit', community)"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                >
                    Edit Community
                </Link>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold">About r/{{ community.name }}</h3>
                                <p class="mt-2">{{ community.description }}</p>
                                <p class="mt-2 text-sm text-gray-500">
                                    Created by u/{{ community.creator.name }}
                                </p>
                            </div>
                            <button
                                @click="showRules = !showRules"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300"
                            >
                                {{ showRules ? 'Hide Rules' : 'Show Rules' }}
                            </button>
                        </div>

                        <div v-if="showRules" class="mt-4 p-4 bg-gray-50 rounded">
                            <h4 class="font-semibold mb-2">Community Rules</h4>
                            <pre class="whitespace-pre-line">{{ community.rules }}</pre>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Posts</h3>
                    <Link
                        v-if="$page.props.auth.user && isMember"
                        :href="route('posts.create', community)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                    >
                        Create Post
                    </Link>
                </div>

                <div v-if="posts.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p>No posts yet. Be the first to post!</p>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="post in posts.data" :key="post.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <Link :href="route('posts.show', post)" class="block">
                                <h4 class="text-xl font-bold">{{ post.title }}</h4>
                                <div class="mt-2 text-sm text-gray-500 flex space-x-4">
                                    <span>Posted by u/{{ post.user.name }}</span>
                                    <span>{{ post.comments_count }} comments</span>
                                    <span>Score: {{ post.votes_count }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <div class="flex justify-between">
                        <Link
                            v-if="posts.prev_page_url"
                            :href="posts.prev_page_url"
                            class="px-4 py-2 bg-gray-200 rounded"
                        >
                            Previous
                        </Link>
                        <span v-else class="px-4 py-2 bg-gray-100 text-gray-400 rounded">Previous</span>

                        <Link
                            v-if="posts.next_page_url"
                            :href="posts.next_page_url"
                            class="px-4 py-2 bg-gray-200 rounded"
                        >
                            Next
                        </Link>
                        <span v-else class="px-4 py-2 bg-gray-100 text-gray-400 rounded">Next</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
