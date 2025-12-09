<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title data-key="title">Become a Vendor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Cairo"', 'ui-sans-serif', 'system-ui'], // Arabic-friendly font
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        input:disabled, button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        [dir="rtl"] .rtl\\:text-right { text-align: right; }
        [dir="rtl"] .rtl\\:text-left { text-align: left; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8" dir="ltr">

<div class="max-w-4xl mx-auto">
    <!-- Language Switcher -->
    <div class="text-center mb-6">
        <button onclick="setLanguage('en')" class="mx-2 px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm font-medium">English</button>
        <button onclick="setLanguage('ar')" class="mx-2 px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-medium">العربية</button>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-6">
            <h2 class="text-3xl font-bold text-white text-center" data-key="header_title">Become a Vendor</h2>
            <p class="mt-2 text-center text-indigo-100" data-key="header_subtitle">Start selling your products today</p>
        </div>

        <!-- Form -->
        <form id="vendorForm" class="px-8 py-10 space-y-6">

            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2" data-key="personal_info">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="full_name">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="phone">Phone Number *</label>
                        <input type="tel" name="phone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="password">Password *</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="confirm_password">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Shop Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2" data-key="shop_info">Shop Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="shop_name">Shop Name *</label>
                        <input type="text" id="shop_name" name="shop_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="shop_slug">Shop URL Slug *</label>
                        <input type="text" id="shop_slug" name="shop_slug" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-gray-50" />
                        <p class="mt-1 text-xs text-gray-500">
                            <span data-key="shop_url_prefix">Your shop URL:</span>
                            <span dir="ltr">/shop/<span id="slugPreview">...</span></span>
                        </p>
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="whatsapp">WhatsApp Number *</label>
                        <input type="tel" name="whatsapp" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="shop_description">Shop Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                  data-key-placeholder="shop_description_placeholder">Tell customers about your shop...</textarea>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2" data-key="shop_images">Shop Images</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="shop_logo">Shop Logo</label>
                        <div class="mt-2 flex items-center space-x-4 rtl:space-x-reverse">
                            <div id="logoPreview" class="w-24 h-24 rounded-lg overflow-hidden border-2 border-gray-300 hidden">
                                <img id="logoImg" class="w-full h-full object-cover" />
                            </div>
                            <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <span class="text-sm text-gray-700" data-key="choose_logo">Choose Logo</span>
                                <input type="file" id="logoInput" accept="image/*" class="hidden" />
                            </label>
                        </div>
                        <p class="error-text mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" data-key="cover_image">Cover Image</label>
                        <div class="mt-2 flex items-center space-x-4 rtl:space-x-reverse">
                            <div id="coverPreview" class="w-32 h-24 rounded-lg overflow-hidden border-2 border-gray-300 hidden">
                                <img id="coverImg" class="w-full h-full object-cover" />
                            </div>
                            <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <span class="text-sm text-gray-700" data-key="choose_cover">Choose Cover</span>
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
                    <span data-key="register_button">Register as Vendor</span>
                </button>
            </div>

            <div class="text-center text-sm text-gray-600">
                <span data-key="already_have_account">Already have an account?</span>
                <a href="/login" class="text-indigo-600 hover:text-indigo-700 font-medium" data-key="sign_in">Sign in</a>
            </div>
        </form>
    </div>
</div>

<!-- Locale Files -->
<script>
    const translations = {
        en: {
            title: "Become a Vendor",
            header_title: "Become a Vendor",
            header_subtitle: "Start selling your products today",
            personal_info: "Personal Information",
            full_name: "Full Name *",
            phone: "Phone Number *",
            password: "Password *",
            confirm_password: "Confirm Password *",
            shop_info: "Shop Information",
            shop_name: "Shop Name *",
            shop_slug: "Shop URL Slug *",
            shop_url_prefix: "Your shop URL:",
            whatsapp: "WhatsApp Number *",
            shop_description: "Shop Description",
            shop_description_placeholder: "Tell customers about your shop...",
            shop_images: "Shop Images",
            shop_logo: "Shop Logo",
            cover_image: "Cover Image",
            choose_logo: "Choose Logo",
            choose_cover: "Choose Cover",
            register_button: "Register as Vendor",
            already_have_account: "Already have an account?",
            sign_in: "Sign in",
            success_message: "Registration successful! Redirecting...",
            processing: "Processing...",
            error_network: "Network error. Please try again.",
            error_generic: "Registration failed. Please try again."
        },
        ar: {
            title: "كن بائعًا",
            header_title: "كن بائعًا",
            header_subtitle: "ابدأ ببيع منتجاتك اليوم",
            personal_info: "المعلومات الشخصية",
            full_name: "الاسم الكامل *",
            phone: "رقم الهاتف *",
            password: "كلمة المرور *",
            confirm_password: "تأكيد كلمة المرور *",
            shop_info: "معلومات المتجر",
            shop_name: "اسم المتجر *",
            shop_slug: "رابط المتجر (Slug) *",
            shop_url_prefix: "رابط متجرك:",
            whatsapp: "رقم الواتساب *",
            shop_description: "وصف المتجر",
            shop_description_placeholder: "أخبر العملاء عن متجرك...",
            shop_images: "صور المتجر",
            shop_logo: "شعار المتجر",
            cover_image: "صورة الغلاف",
            choose_logo: "اختر الشعار",
            choose_cover: "اختر صورة الغلاف",
            register_button: "تسجيل كبائع",
            already_have_account: "لديك حساب بالفعل؟",
            sign_in: "تسجيل الدخول",
            success_message: "تم التسجيل بنجاح! جاري التوجيه...",
            processing: "جاري المعالجة...",
            error_network: "خطأ في الشبكة. حاول مرة أخرى.",
            error_generic: "فشل التسجيل. حاول مرة أخرى."
        }
    };

    function setLanguage(lang) {
        localStorage.setItem('lang', lang);
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
        document.body.dir = lang === 'ar' ? 'rtl' : 'ltr';

        // Update all translated elements
        document.querySelectorAll('[data-key]').forEach(el => {
            const key = el.getAttribute('data-key');
            if (translations[lang][key]) {
                el.textContent = translations[lang][key];
            }
        });

        // Update placeholders
        document.querySelectorAll('[data-key-placeholder]').forEach(el => {
            const key = el.getAttribute('data-key-placeholder');
            if (translations[lang][key]) {
                el.placeholder = translations[lang][key];
            }
        });

        // Update title
        document.title = translations[lang].title;
    }

    // Load saved language or default to en
    const savedLang = localStorage.getItem('lang') || 'en';
    setLanguage(savedLang);

    // Auto-generate slug (same logic)
    const shopNameInput = document.getElementById('shop_name');
    const shopSlugInput = document.getElementById('shop_slug');
    const slugPreview = document.getElementById('slugPreview');

    shopNameInput?.addEventListener('input', () => {
        let slug = shopNameInput.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        shopSlugInput.value = slug;
        slugPreview.textContent = slug || '...';
    });

    // Image preview (unchanged)
    function handleImagePreview(inputId, previewId, imgId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const img = document.getElementById(imgId);
        input?.addEventListener('change', () => {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    handleImagePreview('logoInput', 'logoPreview', 'logoImg');
    handleImagePreview('coverInput', 'coverPreview', 'coverImg');

    // Form submission with translated messages
    document.getElementById('vendorForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const lang = document.documentElement.lang;
        const t = translations[lang];

        document.querySelectorAll('.error-text').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
            el.previousElementSibling?.classList.remove('border-red-500');
        });

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = t.processing;

        const formData = new FormData(e.target);

        try {
            const response = await fetch('/api/vendors/register', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert(t.success_message);
                window.location.href = '/vendor/dashboard';
            } else {
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
                    alert(result.message || t.error_generic);
                }
            }
        } catch (err) {
            alert(t.error_network);
        } finally {
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = t.register_button;
        }
    });
</script>
</body>
</html>
