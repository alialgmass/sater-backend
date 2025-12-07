<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useVendorApi } from '@/composables/useVendorApi'

const router = useRouter()
const { register, loading, errors } = useVendorApi()

const form = ref({
    name: '',
    phone: '',
    password: '',
    password_confirmation: '',
    shop_name: '',
    shop_slug: '',
    whatsapp: '',
    description: '',
    logo: null,
    cover: null
})

const logoPreview = ref(null)
const coverPreview = ref(null)

// Auto-generate slug from shop_name
watch(() => form.value.shop_name, (newVal) => {
    if (newVal) {
        form.value.shop_slug = newVal
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
    }
})

const handleLogoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.value.logo = file
        const reader = new FileReader()
        reader.onload = (e) => {
            logoPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const handleCoverChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.value.cover = file
        const reader = new FileReader()
        reader.onload = (e) => {
            coverPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const handleSubmit = async () => {
    const formData = new FormData()

    Object.keys(form.value).forEach(key => {
        if (form.value[key] !== null && form.value[key] !== '') {
            formData.append(key, form.value[key])
        }
    })

    const result = await register(formData)

    if (result.success) {
        router.push({ name: 'vendor.dashboard' })
    }
}

const getError = (field) => {
    return errors.value[field] ? errors.value[field][0] : null
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-6">
                    <h2 class="text-3xl font-bold text-white text-center">
                        Become a Vendor
                    </h2>
                    <p class="mt-2 text-center text-indigo-100">
                        Start selling your products today
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="px-8 py-10 space-y-6">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Personal Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500': getError('name') }"
                                />
                                <p v-if="getError('name')" class="mt-1 text-sm text-red-600">
                                    {{ getError('name') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number *
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500': getError('phone') }"
                                />
                                <p v-if="getError('phone')" class="mt-1 text-sm text-red-600">
                                    {{ getError('phone') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Password *
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500': getError('password') }"
                                />
                                <p v-if="getError('password')" class="mt-1 text-sm text-red-600">
                                    {{ getError('password') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm Password *
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Shop Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Shop Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Shop Name *
                                </label>
                                <input
                                    v-model="form.shop_name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500': getError('shop_name') }"
                                />
                                <p v-if="getError('shop_name')" class="mt-1 text-sm text-red-600">
                                    {{ getError('shop_name') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Shop URL Slug *
                                </label>
                                <input
                                    v-model="form.shop_slug"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50"
                                    :class="{ 'border-red-500': getError('shop_slug') }"
                                />
                                <p v-if="form.shop_slug" class="mt-1 text-xs text-gray-500">
                                    Your shop URL: /shop/{{ form.shop_slug }}
                                </p>
                                <p v-if="getError('shop_slug')" class="mt-1 text-sm text-red-600">
                                    {{ getError('shop_slug') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    WhatsApp Number *
                                </label>
                                <input
                                    v-model="form.whatsapp"
                                    type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    :class="{ 'border-red-500': getError('whatsapp') }"
                                />
                                <p v-if="getError('whatsapp')" class="mt-1 text-sm text-red-600">
                                    {{ getError('whatsapp') }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Shop Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    placeholder="Tell customers about your shop..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Shop Images
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Shop Logo
                                </label>
                                <div class="mt-2 flex items-center space-x-4">
                                    <div v-if="logoPreview" class="w-24 h-24 rounded-lg overflow-hidden border-2 border-gray-300">
                                        <img :src="logoPreview" class="w-full h-full object-cover" />
                                    </div>
                                    <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                        <span class="text-sm text-gray-700">Choose Logo</span>
                                        <input
                                            type="file"
                                            @change="handleLogoChange"
                                            accept="image/*"
                                            class="hidden"
                                        />
                                    </label>
                                </div>
                                <p v-if="getError('logo')" class="mt-1 text-sm text-red-600">
                                    {{ getError('logo') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Cover Image
                                </label>
                                <div class="mt-2 flex items-center space-x-4">
                                    <div v-if="coverPreview" class="w-32 h-24 rounded-lg overflow-hidden border-2 border-gray-300">
                                        <img :src="coverPreview" class="w-full h-full object-cover" />
                                    </div>
                                    <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                        <span class="text-sm text-gray-700">Choose Cover</span>
                                        <input
                                            type="file"
                                            @change="handleCoverChange"
                                            accept="image/*"
                                            class="hidden"
                                        />
                                    </label>
                                </div>
                                <p v-if="getError('cover')" class="mt-1 text-sm text-red-600">
                                    {{ getError('cover') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-4 px-6 rounded-lg font-semibold text-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="loading">Processing...</span>
                            <span v-else>Register as Vendor</span>
                        </button>
                    </div>

                    <div class="text-center text-sm text-gray-600">
                        Already have an account?
                        <router-link to="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">
                            Sign in
                        </router-link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
https://claude.ai/chat/b26b9725-046a-4aee-895c-6eae38de5d86
