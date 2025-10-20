<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

defineProps<{
    events: {
        data: Array<{
            id: number;
            event_name: string;
            event_datetime: string;
            description: string;
            image_url: string | null;
            creator: {
                name: string;
            } | null;
        }>;
        links: any;
        meta: any;
    };
}>();

const page = usePage();
const user = page.props.auth?.user;
const isAdmin = user?.role === 'admin' || user?.role === 'superadmin';

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
</script>

<template>
    <Head title="Events" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="flex justify-between items-center w-full mb-8">
                        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Upcoming Events</h1>
                        <Link
                            v-if="isAdmin"
                            :href="'/events/create'"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                        >
                            Add Event
                        </Link>
                    </div>
                    
                    <div v-if="events.data.length === 0" class="text-center py-12">
                        <p class="text-lg text-gray-600 dark:text-gray-400">No events scheduled at this time.</p>
                        <Link
                            v-if="isAdmin"
                            :href="'/events/create'"
                            class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                        >
                            Create First Event
                        </Link>
                    </div>
                    
                    <div v-else class="w-full space-y-6">
                        <div
                            v-for="event in events.data"
                            :key="event.id"
                            class="bg-white rounded-lg shadow-lg p-6 dark:bg-[#161615] border border-gray-200 dark:border-gray-700"
                        >
                            <div class="flex flex-col lg:flex-row gap-6">
                                <div v-if="event.image_url" class="lg:w-1/3">
                                    <img
                                        :src="event.image_url"
                                        :alt="event.event_name"
                                        class="w-full h-48 object-cover rounded-lg"
                                    />
                                </div>
                                
                                <div class="flex-1">
                                    <h2 class="text-xl font-bold mb-2 text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ event.event_name }}
                                    </h2>
                                    
                                    <p class="text-blue-600 font-medium mb-3 dark:text-blue-400">
                                        {{ formatDate(event.event_datetime) }}
                                    </p>
                                    
                                    <p class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                                        {{ event.description }}
                                    </p>
                                    
                                    <div class="flex justify-between items-center">
                                        <Link
                                            :href="`/events/${event.id}`"
                                            class="text-blue-600 hover:text-blue-800 font-medium dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            View Details →
                                        </Link>
                                        
                                        <div v-if="isAdmin" class="flex gap-3">
                                            <Link
                                                :href="`/events/${event.id}/edit`"
                                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-200"
                                            >
                                                Edit
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <p v-if="event.creator" class="text-xs text-gray-500 mt-2">
                                        Created by {{ event.creator.name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <Footer />
        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
