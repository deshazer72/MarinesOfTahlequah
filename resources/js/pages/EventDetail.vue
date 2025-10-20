<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Header from '@/components/Header.vue';

const props = defineProps<{
    event: {
        id: number;
        event_name: string;
        event_datetime: string;
        description: string;
        image_url: string | null;
        creator: {
            name: string;
        } | null;
        updater: {
            name: string;
        } | null;
        created_at: string;
        updated_at: string;
    };
}>();

const page = usePage();
const user = page.props.auth?.user;
const isAdmin = user?.role === 'admin' || user?.role === 'superadmin';
const showDeleteConfirm = ref(false);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
};

const formatSimpleDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const deleteEvent = () => {
    router.delete(`/events/${props.event.id}`, {
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
};

const confirmDelete = () => {
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
};
</script>

<template>
    <Head :title="event.event_name" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <!-- Back Button -->
                    <div class="w-full mb-6">
                        <Link
                            :href="'/events'"
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            ← Back to Events
                        </Link>
                    </div>

                    <div class="w-full bg-white rounded-lg shadow-lg p-8 dark:bg-[#161615]">
                        <!-- Event Header -->
                        <div class="flex justify-between items-start mb-6">
                            <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                {{ event.event_name }}
                            </h1>
                            
                            <div v-if="isAdmin" class="flex gap-3">
                                <Link
                                    :href="`/events/${event.id}/edit`"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                                >
                                    Edit Event
                                </Link>
                                <button
                                    @click="confirmDelete"
                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                                >
                                    Delete Event
                                </button>
                            </div>
                        </div>

                        <!-- Event DateTime -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2 text-[#1b1b18] dark:text-[#EDEDEC]">When</h3>
                            <p class="text-blue-600 font-medium text-lg dark:text-blue-400">
                                {{ formatDate(event.event_datetime) }}
                            </p>
                        </div>

                        <!-- Event Image -->
                        <div v-if="event.image_url" class="mb-8">
                            <img
                                :src="event.image_url"
                                :alt="event.event_name"
                                class="w-full max-w-2xl mx-auto rounded-lg shadow-md"
                            />
                        </div>

                        <!-- Event Description -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">Description</h3>
                            <div class="prose prose-lg max-w-none dark:prose-invert">
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                    {{ event.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Event Meta Information -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">Event Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <strong>Created by:</strong> {{ event.creator?.name || 'Unknown' }}
                                </div>
                                <div>
                                    <strong>Created on:</strong> {{ formatSimpleDate(event.created_at) }}
                                </div>
                                <div v-if="event.updater && event.updated_at !== event.created_at">
                                    <strong>Last updated by:</strong> {{ event.updater.name }}
                                </div>
                                <div v-if="event.updated_at !== event.created_at">
                                    <strong>Last updated on:</strong> {{ formatSimpleDate(event.updated_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteConfirm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click="cancelDelete"
        >
            <div
                class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full mx-4"
                @click.stop
            >
                <h3 class="text-lg font-bold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">
                    Delete Event
                </h3>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Are you sure you want to delete "{{ event.event_name }}"? This action cannot be undone.
                </p>
                <div class="flex gap-4">
                    <button
                        @click="deleteEvent"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                    >
                        Yes, Delete
                    </button>
                    <button
                        @click="cancelDelete"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
