<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

defineProps<{
    aboutItems: Array<{
        id: number;
        title: string;
        content: string;
        image_url: string | null;
        order: number;
        is_published: boolean;
        user: {
            name: string;
        } | null;
        created_at: string;
        updated_at: string;
    }>;
}>();

const page = usePage();
const user = page.props.auth?.user;
const isAdmin = user?.role === 'admin' || user?.role === 'superadmin';

const deleteAbout = async (id: number) => {
    if (confirm('Are you sure you want to delete this about content?')) {
        // Use standard Inertia delete
        const form = useForm({});
        form.delete(`/about/${id}`, {
            onSuccess: () => {
                console.log('About content deleted successfully');
            },
            onError: (errors) => {
                console.error('Error deleting about content:', errors);
            }
        });
    }
};
</script>

<template>
    <Head title="About Us" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full mb-8">
                        <h1 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">About Marines of Tahlequah</h1>
                        
                        <div v-if="isAdmin" class="flex gap-4 mb-6">
                            <Link
                                :href="'/about/create'"
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                            >
                                Add New About Content
                            </Link>
                        </div>
                    </div>
                    
                    <div v-if="aboutItems.length === 0" class="text-center py-12">
                        <div class="text-gray-500 text-6xl mb-6">📝</div>
                        <h2 class="text-2xl font-semibold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">
                            No About Content Yet
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            The about page content will appear here once it's added.
                        </p>
                        <Link
                            v-if="isAdmin"
                            :href="'/about/create'"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200"
                        >
                            Add First About Content
                        </Link>
                    </div>
                    
                    <div v-else class="w-full space-y-8">
                        <div
                            v-for="item in aboutItems"
                            :key="item.id"
                            class="bg-white rounded-lg shadow-lg overflow-hidden dark:bg-[#161615]"
                        >
                            <div v-if="item.image_url" class="w-full h-64 overflow-hidden">
                                <img
                                    :src="item.image_url"
                                    :alt="item.title"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ item.title }}
                                    </h2>
                                    
                                    <div v-if="isAdmin" class="flex gap-2 ml-4">
                                        <Link
                                            :href="`/about/${item.id}/edit`"
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-200"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteAbout(item.id)"
                                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-200"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                                    {{ item.content }}
                                </div>
                                
                                <div v-if="item.user" class="text-xs text-gray-500 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    Created by {{ item.user.name }} on {{ new Date(item.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <Footer />
    </div>
</template>
