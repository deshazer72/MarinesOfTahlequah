<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

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
    };
}>();

// Convert the datetime to the format expected by datetime-local input
const formatDateTimeLocal = (datetime: string) => {
    const date = new Date(datetime);
    // Get the local date and time in ISO format but remove the 'Z' at the end
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
    event_name: props.event.event_name,
    event_datetime: formatDateTimeLocal(props.event.event_datetime),
    description: props.event.description,
    image_url: props.event.image_url || ''
});

const showDeleteConfirm = ref(false);

const submit = () => {
    
    form.put(`/events/${props.event.id}`, {
        onSuccess: () => {
            // Redirect handled by controller
        }
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
    <Head :title="`Edit ${event.event_name}`" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <h1 class="text-3xl font-bold mb-8 text-[#1b1b18] dark:text-[#EDEDEC]">Edit Event</h1>
                    
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
                                    Update the date and time for your event
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

                            <!-- Action Buttons -->
                            <div class="flex gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing">Updating Event...</span>
                                    <span v-else>Update Event</span>
                                </button>
                                
                                <button
                                    type="button"
                                    @click="confirmDelete"
                                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200"
                                >
                                    Delete Event
                                </button>
                            </div>
                        </form>

                        <!-- Event Info -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500">
                                Created by {{ event.creator?.name || 'Unknown' }} on {{ new Date(event.event_datetime).toLocaleDateString() }}
                            </p>
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
        
        <Footer />
        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
