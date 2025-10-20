<script setup lang="ts">
// Get current year dynamically
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="mt-auto w-full max-w-[335px] lg:max-w-4xl">
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 pb-4">
      <div class="text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Created by 
          <span class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Mid-Tech Software Consulting</span>
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
          Copyright © {{ currentYear }}. All rights reserved.
        </p>
         <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
            Contact us at <a href="mailto:jdeshazer@mid-tech.net" class="text-blue-600 dark:text-blue-400">jdeshazer@mid-tech.net</a>
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Add any custom footer styles if needed */
</style>
