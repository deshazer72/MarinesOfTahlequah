<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

interface Photo {
    filename: string;
    url: string;
    size: number;
    modified: number;
}

const props = defineProps<{
    photos: Photo[];
}>();

const showDeleteModal = ref(false);
const photoToDelete = ref<string | null>(null);
const selectedFile = ref<File | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const uploadForm = useForm({
    photo: null as File | null
});

const openDeleteModal = (filename: string) => {
    photoToDelete.value = filename;
    showDeleteModal.value = true;
};

const deletePhoto = () => {
    if (photoToDelete.value) {
        router.delete('/slideshow', {
            data: { filename: photoToDelete.value },
            onSuccess: () => {
                showDeleteModal.value = false;
                photoToDelete.value = null;
            }
        });
    }
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        uploadForm.photo = target.files[0];
    }
};

const uploadPhoto = () => {
    if (uploadForm.photo) {
        uploadForm.post('/slideshow/upload', {
            onSuccess: () => {
                selectedFile.value = null;
                uploadForm.photo = null;
                if (fileInputRef.value) {
                    fileInputRef.value.value = '';
                }
            }
        });
    }
};

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const formatDate = (timestamp: number): string => {
    return new Date(timestamp * 1000).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Slideshow Management" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-6xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full mb-8">
                        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Slideshow Management</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Upload and manage photos for the front page slideshow</p>
                    </div>

                    <!-- Upload Section -->
                    <div class="w-full bg-white rounded-lg shadow-lg dark:bg-[#161615] p-6 mb-6">
                        <h2 class="text-xl font-bold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">Upload New Photo</h2>
                        <form @submit.prevent="uploadPhoto" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Select Photo
                                </label>
                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png,image/gif"
                                    @change="handleFileSelect"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-[#EDEDEC]"
                                />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Accepted formats: JPG, JPEG, PNG, GIF. Max size: 10MB
                                </p>
                                <div v-if="uploadForm.errors.photo" class="text-red-500 text-sm mt-1">
                                    {{ uploadForm.errors.photo }}
                                </div>
                            </div>
                            <button
                                type="submit"
                                :disabled="!selectedFile || uploadForm.processing"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="uploadForm.processing">Uploading...</span>
                                <span v-else>Upload Photo</span>
                            </button>
                        </form>
                    </div>

                    <!-- Photos Grid -->
                    <div class="w-full bg-white rounded-lg shadow-lg dark:bg-[#161615] p-6">
                        <h2 class="text-xl font-bold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">
                            Current Photos ({{ photos.length }})
                        </h2>
                        
                        <div v-if="photos.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            No photos in the slideshow yet. Upload some photos to get started!
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="photo in photos"
                                :key="photo.filename"
                                class="relative group bg-gray-100 dark:bg-[#0a0a0a] rounded-lg overflow-hidden"
                            >
                                <div class="aspect-video">
                                    <img
                                        :src="photo.url"
                                        :alt="photo.filename"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="p-3">
                                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] truncate" :title="photo.filename">
                                        {{ photo.filename }}
                                    </p>
                                    <div class="flex justify-between items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ formatFileSize(photo.size) }}</span>
                                        <span>{{ formatDate(photo.modified) }}</span>
                                    </div>
                                </div>
                                <button
                                    @click="openDeleteModal(photo.filename)"
                                    class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                    title="Delete photo"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click="showDeleteModal = false"
        >
            <div
                class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full mx-4"
                @click.stop
            >
                <h3 class="text-lg font-bold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">
                    Delete Photo
                </h3>
                <p class="text-gray-700 dark:text-gray-300 mb-2">
                    Are you sure you want to delete this photo?
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 font-mono">
                    {{ photoToDelete }}
                </p>
                <div class="flex gap-4">
                    <button
                        @click="deletePhoto"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                    >
                        Yes, Delete
                    </button>
                    <button
                        @click="showDeleteModal = false"
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
