<script setup lang="ts">
import { login, register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const showUserMenu = ref(false);
const mobileMenuOpen = ref(false);

// Get current page URL to determine active state
const currentPath = computed(() => page.url);

const isActive = (path: string) => {
  if (path === '/' && currentPath.value === '/') return true;
  if (path !== '/' && currentPath.value.startsWith(path)) return true;
  return false;
};

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
};
</script>

<template>
  <header class="mb-6 w-full text-sm not-has-[nav]:hidden">
    <!-- Desktop Navigation -->
    <nav class="hidden lg:flex items-center justify-end gap-4">
      <Link 
        :href="'/'" 
        :class="[
          'inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A] relative',
          isActive('/') 
            ? 'text-blue-600 dark:text-blue-400' 
            : 'text-[#1b1b18] dark:text-[#EDEDEC]'
        ]"
      >
        Home
        <div v-if="isActive('/')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"></div>
      </Link>
      <Link 
        :href="'/about'" 
        :class="[
          'inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A] relative',
          isActive('/about') 
            ? 'text-blue-600 dark:text-blue-400' 
            : 'text-[#1b1b18] dark:text-[#EDEDEC]'
        ]"
      >
        About
        <div v-if="isActive('/about')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"></div>
      </Link>
      <Link 
        :href="'/events'" 
        :class="[
          'inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A] relative',
          isActive('/events') 
            ? 'text-blue-600 dark:text-blue-400' 
            : 'text-[#1b1b18] dark:text-[#EDEDEC]'
        ]"
      >
        Events
        <div v-if="isActive('/events')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"></div>
      </Link>
      <Link 
        :href="'/donate'" 
        :class="[
          'inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A] relative',
          isActive('/donate') 
            ? 'text-blue-600 dark:text-blue-400' 
            : 'text-[#1b1b18] dark:text-[#EDEDEC]'
        ]"
      >
        Donate
        <div v-if="isActive('/donate')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 dark:bg-blue-400"></div>
      </Link>
      
      <!-- Admin Link - Only show for admin/superadmin -->
      <Link
        v-if="page.props.auth?.user && (page.props.auth.user.role === 'admin' || page.props.auth.user.role === 'superadmin')"
        :href="'/events/create'"
        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-orange-600 hover:border-[#19140035] dark:text-orange-400 dark:hover:border-[#3E3E3A]"
      >
        Add Event
      </Link>
      
      <!-- Admin Link for About - Only show for admin/superadmin -->
      <Link
        v-if="page.props.auth?.user && (page.props.auth.user.role === 'admin' || page.props.auth.user.role === 'superadmin')"
        :href="'/about/create'"
        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-green-600 hover:border-[#19140035] dark:text-green-400 dark:hover:border-[#3E3E3A]"
      >
        Add About
      </Link>
      
      <!-- Super Admin Link - Only show for superadmin -->
      <Link
        v-if="page.props.auth?.user && page.props.auth.user.role === 'superadmin'"
        :href="'/users'"
        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-red-600 hover:border-[#19140035] dark:text-red-400 dark:hover:border-[#3E3E3A]"
      >
        User Roles
      </Link>
      
      <!-- Slideshow Management - Only show for superadmin -->
      <Link
        v-if="page.props.auth?.user && page.props.auth.user.role === 'superadmin'"
        :href="'/slideshow'"
        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-purple-600 hover:border-[#19140035] dark:text-purple-400 dark:hover:border-[#3E3E3A]"
      >
        Slideshow
      </Link>
      
      <!-- User Menu Dropdown - Only show when logged in -->
      <div v-if="page.props.auth && page.props.auth.user" class="relative">
        <button
          @click="showUserMenu = !showUserMenu"
          @blur="setTimeout(() => showUserMenu = false, 200)"
          class="inline-flex items-center gap-2 rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
        >
          <!-- User Avatar or Initial -->
          <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white">
            {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
          </div>
          <span>{{ page.props.auth.user.name }}</span>
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        
        <!-- Dropdown Menu -->
        <div 
          v-if="showUserMenu"
          class="absolute right-0 top-full mt-1 w-56 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-[#161615] z-50"
        >
          <div class="p-2">
            <!-- User Info Header -->
            <div class="flex items-center gap-2 px-2 py-2 text-left text-sm border-b border-gray-200 dark:border-gray-700">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-medium text-white">
                {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] truncate">
                  {{ page.props.auth.user.name }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ page.props.auth.user.email }}
                </div>
              </div>
            </div>
            
            <!-- Menu Items -->
            <div class="py-1">
              <Link
                :href="'/settings'"
                class="flex items-center gap-2 px-2 py-2 text-sm text-[#1b1b18] hover:bg-gray-100 rounded dark:text-[#EDEDEC] dark:hover:bg-[#1a1a1a]"
                @click="showUserMenu = false"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </Link>
              
              <Link
                :href="'/logout'"
                method="post"
                class="flex items-center gap-2 px-2 py-2 text-sm text-red-600 hover:bg-gray-100 rounded dark:text-red-400 dark:hover:bg-[#1a1a1a]"
                @click="showUserMenu = false"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Log out
              </Link>
            </div>
          </div>
        </div>
      </div>
      <template v-else>
        <Link
          :href="login()"
          class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
        >
          Log in
        </Link>
        <Link
          :href="register()"
          class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
        >
          Register
        </Link>
      </template>
    </nav>

    <!-- Mobile Navigation -->
    <div class="lg:hidden">
      <div class="flex items-center justify-between">
        <!-- Logo/Brand name (optional) -->
        <div class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
          Marines of Tahlequah
        </div>
        
        <!-- Hamburger Button -->
        <button
          @click="toggleMobileMenu"
          class="inline-flex items-center justify-center p-2 rounded-md text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          aria-expanded="false"
        >
          <span class="sr-only">Open main menu</span>
          <!-- Menu icon when closed -->
          <svg
            v-if="!mobileMenuOpen"
            class="block h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <!-- X icon when open -->
          <svg
            v-if="mobileMenuOpen"
            class="block h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu Panel -->
      <div v-if="mobileMenuOpen" class="mt-4">
        <div class="bg-white dark:bg-[#161615] rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 space-y-2">
          <!-- Main Navigation Links -->
          <Link
            :href="'/'"
            @click="closeMobileMenu"
            :class="[
              'block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100 dark:hover:bg-gray-800 relative border-l-4',
              isActive('/') 
                ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-l-blue-600 dark:border-l-blue-400' 
                : 'text-[#1b1b18] dark:text-[#EDEDEC] border-l-transparent'
            ]"
          >
            Home
          </Link>
          <Link
            :href="'/about'"
            @click="closeMobileMenu"
            :class="[
              'block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100 dark:hover:bg-gray-800 relative border-l-4',
              isActive('/about') 
                ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-l-blue-600 dark:border-l-blue-400' 
                : 'text-[#1b1b18] dark:text-[#EDEDEC] border-l-transparent'
            ]"
          >
            About
          </Link>
          <Link
            :href="'/events'"
            @click="closeMobileMenu"
            :class="[
              'block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100 dark:hover:bg-gray-800 relative border-l-4',
              isActive('/events') 
                ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-l-blue-600 dark:border-l-blue-400' 
                : 'text-[#1b1b18] dark:text-[#EDEDEC] border-l-transparent'
            ]"
          >
            Events
          </Link>
          <Link
            :href="'/donate'"
            @click="closeMobileMenu"
            :class="[
              'block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100 dark:hover:bg-gray-800 relative border-l-4',
              isActive('/donate') 
                ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border-l-blue-600 dark:border-l-blue-400' 
                : 'text-[#1b1b18] dark:text-[#EDEDEC] border-l-transparent'
            ]"
          >
            Donate
          </Link>

          <!-- Admin Links - Only show for admin/superadmin -->
          <template v-if="page.props.auth?.user && (page.props.auth.user.role === 'admin' || page.props.auth.user.role === 'superadmin')">
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2">
              <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide px-3 py-1">
                Admin
              </div>
              <Link
                :href="'/events/create'"
                @click="closeMobileMenu"
                class="block px-3 py-2 rounded-md text-base font-medium text-orange-600 hover:bg-gray-100 dark:text-orange-400 dark:hover:bg-gray-800"
              >
                Add Event
              </Link>
              <Link
                :href="'/about/create'"
                @click="closeMobileMenu"
                class="block px-3 py-2 rounded-md text-base font-medium text-green-600 hover:bg-gray-100 dark:text-green-400 dark:hover:bg-gray-800"
              >
                Add About
              </Link>
            </div>
          </template>

          <!-- Super Admin Links - Only show for superadmin -->
          <template v-if="page.props.auth?.user && page.props.auth.user.role === 'superadmin'">
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2">
              <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide px-3 py-1">
                Super Admin
              </div>
              <Link
                :href="'/users'"
                @click="closeMobileMenu"
                class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-800"
              >
                User Roles
              </Link>
              <Link
                :href="'/slideshow'"
                @click="closeMobileMenu"
                class="block px-3 py-2 rounded-md text-base font-medium text-purple-600 hover:bg-gray-100 dark:text-purple-400 dark:hover:bg-gray-800"
              >
                Slideshow
              </Link>
            </div>
          </template>

          <!-- User Menu - Only show when logged in -->
          <template v-if="page.props.auth && page.props.auth.user">
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2">
              <!-- User Info -->
              <div class="flex items-center gap-3 px-3 py-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-medium text-white">
                  {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] truncate">
                    {{ page.props.auth.user.name }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ page.props.auth.user.email }}
                  </div>
                </div>
              </div>
              
              <!-- User Menu Items -->
              <Link
                :href="'/settings'"
                @click="closeMobileMenu"
                class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium text-[#1b1b18] hover:bg-gray-100 dark:text-[#EDEDEC] dark:hover:bg-gray-800"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
              </Link>
              
              <Link
                :href="'/logout'"
                method="post"
                @click="closeMobileMenu"
                class="flex items-center gap-2 px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-800"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Log out
              </Link>
            </div>
          </template>

          <!-- Login/Register - Only show when not logged in -->
          <template v-else>
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2 space-y-2">
              <Link
                :href="login()"
                @click="closeMobileMenu"
                class="block px-3 py-2 rounded-md text-base font-medium text-[#1b1b18] hover:bg-gray-100 dark:text-[#EDEDEC] dark:hover:bg-gray-800"
              >
                Log in
              </Link>
              <Link
                :href="register()"
                @click="closeMobileMenu"
                class="block px-3 py-2 rounded-md text-base font-medium text-blue-600 hover:bg-gray-100 dark:text-blue-400 dark:hover:bg-gray-800"
              >
                Register
              </Link>
            </div>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* Add custom styles if needed */
</style>
