<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';

defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role: 'user' | 'admin' | 'superadmin';
            email_verified_at: string | null;
            created_at: string;
        }>;
        links: any;
        meta: any;
    };
}>();

const selectedUserId = ref<number | null>(null);
const selectedRole = ref<string>('');
const showRoleModal = ref(false);
const showDeleteModal = ref(false);
const userToDelete = ref<number | null>(null);

const roleForm = useForm({
    role: ''
});

const openRoleModal = (userId: number, currentRole: string) => {
    selectedUserId.value = userId;
    selectedRole.value = currentRole;
    roleForm.role = currentRole;
    showRoleModal.value = true;
};

const updateRole = () => {
    if (selectedUserId.value) {
        roleForm.put(`/users/${selectedUserId.value}/role`, {
            onSuccess: () => {
                showRoleModal.value = false;
                selectedUserId.value = null;
            }
        });
    }
};

const openDeleteModal = (userId: number) => {
    userToDelete.value = userId;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(`/users/${userToDelete.value}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                userToDelete.value = null;
            }
        });
    }
};

const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case 'superadmin':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'admin':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="User Role Management" />
    
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <Header />
        
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[800px] flex-col overflow-hidden rounded-lg lg:max-w-6xl">
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="w-full mb-8">
                        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">User Management</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Manage user roles and permissions. Users can register themselves, and you can assign roles here.</p>
                    </div>
                    
                    <div class="w-full bg-white rounded-lg shadow-lg dark:bg-[#161615]">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-[#0a0a0a]">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Joined
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="user in users.data"
                                        :key="user.id"
                                        class="hover:bg-gray-50 dark:hover:bg-[#1a1a1a]"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                                    {{ user.name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ user.email }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="getRoleBadgeClass(user.role)"
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize"
                                            >
                                                {{ user.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="user.email_verified_at ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                                class="text-sm font-medium"
                                            >
                                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(user.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <button
                                                @click="openRoleModal(user.id, user.role)"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                                            >
                                                Change Role
                                            </button>
                                            <button
                                                @click="openDeleteModal(user.id)"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Role Change Modal -->
        <div
            v-if="showRoleModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click="showRoleModal = false"
        >
            <div
                class="bg-white dark:bg-[#161615] rounded-lg p-6 max-w-md w-full mx-4"
                @click.stop
            >
                <h3 class="text-lg font-bold mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">
                    Change User Role
                </h3>
                
                <form @submit.prevent="updateRole">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                            Select Role
                        </label>
                        <select
                            v-model="roleForm.role"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#0a0a0a] dark:border-gray-600 dark:text-[#EDEDEC]"
                        >
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                        <div v-if="roleForm.errors.role" class="text-red-500 text-sm mt-1">
                            {{ roleForm.errors.role }}
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="roleForm.processing"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                        >
                            <span v-if="roleForm.processing">Updating...</span>
                            <span v-else>Update Role</span>
                        </button>
                        <button
                            type="button"
                            @click="showRoleModal = false"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
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
                    Delete User
                </h3>
                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>
                <div class="flex gap-4">
                    <button
                        @click="deleteUser"
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
