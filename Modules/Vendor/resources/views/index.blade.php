<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Become a Vendor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input:disabled, button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-6">
            <h2 class="text-3xl font-bold text-white text-center">Become a Vendor</h2>
            <p class="mt-2 text-center text-indigo-100">Start selling your products today</p>
        </div>

        <!-- Form -->
        <form id="vendorForm" class="px-8 py-10 space-y-6">

            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" name="phone" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Shop Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Shop Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shop Name *</label>
                        <input type="text" id="shop_name" name="shop_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shop URL Slug *</label>
                        <input type="text" id="shop_slug" name="shop_slug" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50" />
                        <p class="mt-1 text-xs text-gray-500">Your shop URL: /shop/<span id="slugPreview"></span></p>
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number *</label>
                        <input type="tel" name="whatsapp" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shop Description</label>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                  placeholder="Tell customers about your shop..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Shop Images</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shop Logo</label>
                        <div class="mt-2 flex items-center space-x-4">
                            <div id="logoPreview" class="w-24 h-24 rounded-lg overflow-hidden border-2 border-gray-300 hidden">
                                <img id="logoImg" class="w-full h-full object-cover" />
                            </div>
                            <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <span class="text-sm text-gray-700">Choose Logo</span>
                                <input type="file" id="logoInput" accept="image/*" class="hidden" />
                            </label>
                        </div>
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                        <div class="mt-2 flex items-center space-x-4">
                            <div id="coverPreview" class="w-32 h-24 rounded-lg overflow-hidden border-2 border-gray-300 hidden">
                                <img id="coverImg" class="w-full h-full object-cover" />
                            </div>
                            <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <span class="text-sm text-gray-700">Choose Cover</span>
                                <input type="file" id="coverInput" accept="image/*" class="hidden" />
                            </label>
                        </div>
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-6">
                <button type="submit" id="submitBtn"
                        class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-4 px-6 rounded-lg font-semibold text-lg hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Register as Vendor
                </button>
            </div>

            <div class="text-center text-sm text-gray-600">
                Already have an account?
                <a href="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign in</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-generate slug
    const shopNameInput = document.getElementById('shop_name');
    const shopSlugInput = document.getElementById('shop_slug');
    const slugPreview = document.getElementById('slugPreview');

    shopNameInput.addEventListener('input', () => {
        let slug = shopNameInput.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        shopSlugInput.value = slug;
        slugPreview.textContent = slug || '...';
    });

    // Image previews
    function handleImagePreview(inputId, previewId, imgId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const img = document.getElementById(imgId);

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        });
    }

    handleImagePreview('logoInput', 'logoPreview', 'logoImg');
    handleImagePreview('coverInput', 'coverPreview', 'coverImg');

    // Form submission
    const form = document.getElementById('vendorForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Clear previous errors
        document.querySelectorAll('.error-text').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
            el.previousElementSibling.classList.remove('border-red-500');
        });

        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Processing...';

        const formData = new FormData(form);

        try {
            // Replace this with your actual API endpoint
            const response = await fetch('/api/vendors/register', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Registration successful! Redirecting...');
                window.location.href = '/vendor/dashboard'; // Change to your dashboard route
            } else {
                // Show Laravel-style validation errors
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            const errorEl = input.parentNode.querySelector('.error-text');
                            if (errorEl) {
                                errorEl.textContent = result.errors[field][0];
                                errorEl.classList.remove('hidden');
                                input.classList.add('border-red-500');
                            }
                        }
                    });
                } else {
                    alert(result.message || 'Registration failed. Please try again.');
                }
            }
        } catch (err) {
            console.error(err);
            alert('Network error. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Register as Vendor';
        }
    });
</script>
</body>
</html>
