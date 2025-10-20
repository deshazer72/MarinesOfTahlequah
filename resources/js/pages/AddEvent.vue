<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

const form = useForm({
    event_name: '',
    event_datetime: '',
    description: '',
    image_url: ''
});

const submit = () => {
    form.post('/events', {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Add Event" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <h1 class="text-3xl font-bold mb-8 text-[#1b1b18] dark:text-[#EDEDEC]">Add New Event</h1>
                    
                    <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg p-8 dark:bg-[#161615]">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Event Name -->
                            <div>
                                <label for="event_name" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Event Name
                                </label>
                                <input
                                    id="event_name"
                                    v-model="form.event_name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-[#EDEDEC]"
                                    placeholder="Enter event name"
                                />
                                <div v-if="form.errors.event_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.event_name }}
                                </div>
                            </div>

                            <!-- Event Date & Time -->
                            <div>
                                <label for="event_datetime" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Event Date & Time
                                </label>
                                <input
                                    id="event_datetime"
                                    v-model="form.event_datetime"
                                    type="datetime-local"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-[#EDEDEC] dark:[color-scheme:dark]"
                                />
                                <div v-if="form.errors.event_datetime" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.event_datetime }}
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Select the date and time for your event
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="5"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-[#EDEDEC]"
                                    placeholder="Enter event description"
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Image URL -->
                            <div>
                                <label for="image_url" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Flyer Image URL (Optional)
                                </label>
                                <input
                                    id="image_url"
                                    v-model="form.image_url"
                                    type="url"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-[#EDEDEC]"
                                    placeholder="https://example.com/flyer.jpg"
                                />
                                <div v-if="form.errors.image_url" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.image_url }}
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Upload your flyer to an image hosting service and paste the URL here
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing">Creating Event...</span>
                                    <span v-else>Create Event</span>
                                </button>
                                
                                <button
                                    type="button"
                                    @click="form.reset()"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200 dark:border-gray-600 dark:text-[#EDEDEC] dark:hover:bg-[#1a1a1a]"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
        
        <Footer />
        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
