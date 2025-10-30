<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

const images = ref<string[]>([]);
const loading = ref(true);
const current = ref(0);

function next() { 
    if (images.value.length > 0) {
        current.value = (current.value + 1) % images.value.length; 
    }
}

function prev() { 
    if (images.value.length > 0) {
        current.value = (current.value - 1 + images.value.length) % images.value.length; 
    }
}

let interval: NodeJS.Timeout | null = null;

onMounted(async () => { 
    try {
        const response = await fetch('/api/slideshow/photos');
        const data = await response.json();
        images.value = data.photos.map((photo: { url: string }) => photo.url);
    } catch (error) {
        console.error('Failed to load slideshow photos:', error);
        // Fallback to default images if API fails
        images.value = [
            'IMG_8884.jpg','IMG_8885.jpg','IMG_8886.jpg','IMG_8887.jpg','IMG_8888.jpg','IMG_8889.jpg','IMG_8890.jpg','IMG_8891.jpg','IMG_8892.jpg',
            'IMG_8898.JPG','IMG_8899.JPG','IMG_8900.JPG','IMG_8901.JPG','IMG_8932.JPG','IMG_8933.JPG','IMG_8934.JPG','IMG_8935.JPG','IMG_8936.JPG',
            'IMG_8951.jpg','IMG_8952.jpg','IMG_8953.jpg','IMG_8954.jpg','IMG_8955.jpg','IMG_8956.jpg','IMG_8957.jpg','IMG_8958.jpg','IMG_8959.jpg',
            'IMG_8960.jpg','IMG_8961.jpg','IMG_8962.jpg','IMG_8963.jpg','IMG_8964.jpg','IMG_8965.jpg','IMG_8966.jpg','IMG_8967.jpg','IMG_8968.jpg',
            'IMG_8969.jpg','IMG_8970.jpg','IMG_8971.jpg','pic1.jpg','pic2.jpg','pic3.jpg','pic4.jpg'
        ].map(img => `/MarinesPictures/${img}`);
    } finally {
        loading.value = false;
    }
    
    interval = setInterval(next, 4000); 
});

onUnmounted(() => { if (interval) clearInterval(interval); });
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <h1 class="text-3xl font-bold mb-6 text-[#1b1b18] dark:text-[#EDEDEC]">Marines of Tahlequah</h1>
                    <div class="relative w-full h-[500px] flex items-center justify-center bg-white rounded-lg shadow-lg dark:bg-[#161615]">
                        <div v-if="loading" class="text-gray-500 dark:text-gray-400">Loading...</div>
                        <div v-else-if="images.length === 0" class="text-gray-500 dark:text-gray-400">No photos available</div>
                        <template v-else>
                            <img :src="images[current]" alt="Marine Slideshow" class="object-contain w-full h-full rounded-lg transition duration-500" />
                            <button @click="prev" class="absolute left-4 top-1/2 -translate-y-1/2 bg-[#1b1b18] text-white px-3 py-2 rounded-full shadow hover:bg-[#232323] transition">&#8592;</button>
                            <button @click="next" class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#1b1b18] text-white px-3 py-2 rounded-full shadow hover:bg-[#232323] transition">&#8594;</button>
                        </template>
                    </div>

                    <!-- Mission Statement -->
                    <div class="mt-12 max-w-4xl text-center">
                        <h2 class="text-2xl font-bold mb-6 text-[#1b1b18] dark:text-[#EDEDEC]">Our Mission</h2>
                        <div class="text-lg leading-relaxed text-[#1b1b18] dark:text-[#EDEDEC] space-y-4">
                            <p>
                                Since 2015, the Marines of Tahlequah (MOT) have been dedicated advocates for service members, veterans, and their families. While initially founded as a group of Marines celebrating their shared heritage, we have grown into a community organization committed to improving the lives of those who have served our country. We consider it a great honor to serve our community and fellow service members.
                            </p>
                            
                            <p class="font-semibold">Our work includes a wide range of support initiatives, such as:</p>
                            
                            <div class="text-left max-w-3xl mx-auto space-y-3">
                                <p><strong>Disaster and Emergency Relief:</strong> Assisting flood victims and organizing food and clothing drives.</p>
                                
                                <p><strong>Shelter and Housing Support:</strong> Donating to local women's shelters, men's homes, and halfway houses, as well as providing housing assistance to veterans to prevent evictions and cover utility costs.</p>
                                
                                <p><strong>Community and Charitable Partnerships:</strong> Collaborating with local charities that serve the homeless and working closely with the Cherokee Nation on their Elder Care Programs and the Angel Tree initiative.</p>
                            </div>
                            
                            <p class="italic">
                                We are deeply grateful for the support of our community, including the Turnpike Troubadours, whose partnership was instrumental in our fundraising efforts to better serve the Tahlequah area.
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <Footer />
        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
