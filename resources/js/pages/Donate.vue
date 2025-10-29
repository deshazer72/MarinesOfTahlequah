<template>
  <Head title="Donate" />
  
  <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
    <Header />
    <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
      <main class="flex w-full max-w-[800px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
        <div class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
          <h1 class="text-3xl text-center font-bold mb-6 text-[#1b1b18] dark:text-[#EDEDEC]">Donate</h1>
          <div class="w-full text-center mb-8">
            <p class="text-lg text-gray-700 dark:text-gray-300 mx-auto">
              At the Marines of Tahlequah, we appreciate your donation.<br>
              Your donation goes to help local veterans and charities.
            </p>
          </div>
          
          <div class="w-full flex flex-col items-center space-y-8">
            <!-- Donation Methods -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-2xl">
              
              <!-- PayPal QR Code -->
              <div class="flex flex-col items-center bg-gray-50 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">PayPal</h3>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                  <img 
                    src="/QR codes/705.png" 
                    alt="PayPal QR Code for Marines of Tahlequah" 
                    class="w-48 h-48 object-contain"
                  />
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 text-center">
                  Scan with your phone's camera or PayPal app
                </p>
              </div>

              <!-- Cash App QR Code -->
              <div class="flex flex-col items-center bg-gray-50 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">Cash App</h3>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                  <img 
                    src="/QR codes/706.png" 
                    alt="Cash App QR Code for Marines of Tahlequah" 
                    class="w-48 h-48 object-contain"
                  />
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 text-center">
                  Scan with Cash App: $MarinesofTahlequah
                </p>
              </div>
              
            </div>

            <!-- Additional Donation Information -->
            <div class="text-center max-w-xl">
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Choose your preferred payment method above. All donations go directly to supporting local veterans and charitable causes in the Tahlequah community.
              </p>
              <div class="flex justify-center space-x-4 text-xs text-gray-500 dark:text-gray-500">
                <span>• Secure Payments</span>
                <span>• 100% to Veterans</span>
                <span>• Community Support</span>
              </div>
            </div>
          </div>
        </div>
        <!-- You can add an image or illustration here similar to Welcome.vue if desired -->
      </main>
    </div>
    
    <Footer />
  </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
</script>

<style scoped>
/* Add custom styles if needed */
</style>
