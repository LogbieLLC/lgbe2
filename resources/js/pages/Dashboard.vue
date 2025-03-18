<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';

interface Community {
    id: number;
    name: string;
    description: string;
    members_count: number;
    [key: string]: any; // Add index signature for TypeScript compatibility with route function
}

defineProps<{
    createdCommunities: Community[];
    moderatedCommunities: Community[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            
            <!-- Community Management Section -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 bg-white dark:bg-gray-800">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Community Management</h2>
                    <Link :href="route('communities.create')">
                        <Button size="lg" class="bg-indigo-600 hover:bg-indigo-700">
                            Create Community
                        </Button>
                    </Link>
                </div>
                
                <!-- Communities You Created -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Communities You Created</h3>
                    <div v-if="createdCommunities.length === 0" class="text-gray-500 dark:text-gray-400 italic">
                        You haven't created any communities yet.
                    </div>
                    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="community in createdCommunities" :key="community.id" 
                             class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <Link :href="route('communities.show', { community: community.id })" class="block">
                                <h4 class="text-lg font-bold">r/{{ community.name }}</h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ community.description }}</p>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ community.members_count }} members
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
                
                <!-- Communities You Moderate -->
                <div v-if="moderatedCommunities.length > 0">
                    <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Communities You Moderate</h3>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="community in moderatedCommunities" :key="community.id" 
                             class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <Link :href="route('communities.show', { community: community.id })" class="block">
                                <h4 class="text-lg font-bold">r/{{ community.name }}</h4>
                                <p class="text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ community.description }}</p>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ community.members_count }} members
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
