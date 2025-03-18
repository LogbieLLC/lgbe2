<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import axios from 'axios';

const communities = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/api/communities');
        if (response.data && response.data.data) {
            communities.value = response.data.data;
        } else {
            console.error('Unexpected API response format:', response.data);
        }
    } catch (error) {
        console.error('Error fetching communities:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Head title="Welcome to LGBE2 - A Reddit-like Platform">
        <meta name="description" content="A community-driven platform for sharing and discussing content">
    </Head>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="relative isolate px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                        Welcome to LGBE2
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        A community-driven platform where you can share content, engage through upvotes and comments, and join communities of like-minded individuals.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Link
                            :href="route('communities.index')"
                            class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Browse Communities
                        </Link>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('communities.create')"
                            class="text-sm font-semibold leading-6 text-gray-900 dark:text-white"
                        >
                            Create a Community <span aria-hidden="true">→</span>
                        </Link>
                        <Link
                            v-else
                            :href="route('register')"
                            class="text-sm font-semibold leading-6 text-gray-900 dark:text-white"
                        >
                            Sign up <span aria-hidden="true">→</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-indigo-600 dark:text-indigo-400 font-semibold tracking-wide uppercase">Communities</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                        Discover Popular Communities
                    </p>
                </div>

                <div class="mt-10">
                    <div v-if="loading" class="text-center py-10">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status">
                            <span class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="communities.length === 0" class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400">No communities found.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="community in communities" :key="community.id" class="bg-gray-50 dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    r/{{ community.name }}
                                </h3>
                                <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-300">
                                    {{ community.description }}
                                </p>
                                <div class="mt-5">
                                    <Link
                                        :href="route('communities.show', community)"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        View Community
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="bg-gray-100 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto py-12 px-4 overflow-hidden sm:px-6 lg:px-8">
                <p class="mt-8 text-center text-base text-gray-400">
                    &copy; 2025 LGBE2. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>
