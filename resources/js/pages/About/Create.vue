<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

const form = useForm({
    title: '',
    content: '',
    image_url: '',
    order: 0,
    is_published: true,
});

const submit = () => {
    form.post('/about', {
        onSuccess: () => {
            console.log('About content created successfully');
        },
        onError: (errors) => {
            console.error('Error creating about content:', errors);
        }
    });
};
</script>

<template>
    <Head title="Add About Content" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full mb-8">
                        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Add About Content</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Create new content for the About page.</p>
                    </div>
                    
                    <div class="w-full bg-white rounded-lg shadow-lg dark:bg-[#161615] p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Title *
                                </label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-white"
                                    placeholder="Enter the title"
                                />
                                <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.title }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Content *
                                </label>
                                <textarea
                                    id="content"
                                    v-model="form.content"
                                    rows="10"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-white"
                                    placeholder="Enter the content"
                                ></textarea>
                                <div v-if="form.errors.content" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.content }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="image_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Image URL (optional)
                                </label>
                                <input
                                    id="image_url"
                                    v-model="form.image_url"
                                    type="url"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-white"
                                    placeholder="https://example.com/image.jpg"
                                />
                                <div v-if="form.errors.image_url" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.image_url }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Display Order
                                </label>
                                <input
                                    id="order"
                                    v-model.number="form.order"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-white"
                                    placeholder="0"
                                />
                                <p class="text-sm text-gray-500 mt-1">Lower numbers appear first. Leave as 0 for newest content to appear first.</p>
                                <div v-if="form.errors.order" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.order }}
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <input
                                    id="is_published"
                                    v-model="form.is_published"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label for="is_published" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Published (visible to visitors)
                                </label>
                            </div>
                            
                            <div class="flex gap-4 pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-medium py-3 px-6 rounded-lg transition duration-200"
                                >
                                    <span v-if="form.processing">Creating...</span>
                                    <span v-else>Create About Content</span>
                                </button>
                                
                                <a
                                    href="/about"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-6 rounded-lg transition duration-200 text-center"
                                >
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
        
        <Footer />
    </div>
</template>
