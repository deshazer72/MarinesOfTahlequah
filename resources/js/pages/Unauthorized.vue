<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
</script>

<template>
    <Head title="Access Denied" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full text-center">
                    <div class="bg-white rounded-lg shadow-lg p-12 dark:bg-[#161615] max-w-md">
                        <div class="text-red-500 text-6xl mb-6">🚫</div>
                        
                        <h1 class="text-3xl font-bold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">
                            Access Denied
                        </h1>
                        
                        <p class="text-gray-700 dark:text-gray-300 mb-6 leading-relaxed">
                            You don't have permission to access this resource. Only administrators and super administrators can manage events.
                        </p>
                        
                        <div class="space-y-3">
                            <Link
                                :href="'/events'"
                                class="block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200"
                            >
                                View Events
                            </Link>
                            
                            <Link
                                :href="'/'"
                                class="block text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Go Home
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
