<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>إنشاء حساب جديد - نظام تكسي</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f0f2f5;
        }
        .form-input-container {
            position: relative;
        }
        .form-input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0.75rem;
            color: #9ca3af;
            pointer-events: none;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            padding-left: 2.5rem;
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen px-4 py-8">

    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <svg class="mx-auto h-12 w-auto text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900">إنشاء حساب جديد</h1>
            <p class="mt-2 text-sm text-gray-600">
                املأ البيانات التالية للانضمام إلينا.
            </p>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-xl sm:px-10">
            <?php if (isset($data['error'])): ?>
                <div id="alert-message" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-7a1 1 0 100-2 1 1 0 000 2zm-1-4a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($data['error']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="/taxi/register" method="POST" class="space-y-5" autocomplete="off" novalidate>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">اسم المستخدم</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-user"></i></span>
                        <input id="username" name="username" type="text" required minlength="4"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="4 أحرف على الأقل (انجليزي)">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-envelope"></i></span>
                        <input id="email" name="email" type="email" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="ادخل بريدك الإلكتروني">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-lock"></i></span>
                        <input id="password" name="password" type="password" required minlength="6"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="6 أحرف على الأقل">
                    </div>
                </div>
                
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        إنشاء الحساب
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-8 text-center text-sm text-gray-500">
            لديك حساب بالفعل؟
            <a href="/taxi/login" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500 hover:underline">
                قم بتسجيل الدخول من هنا
            </a>
        </p>
    </div>

    <script>
        // إخفاء رسالة الخطأ بعد 5 ثوانٍ
        const alertMessage = document.getElementById('alert-message');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.style.transition = 'opacity 0.5s ease-out';
                alertMessage.style.opacity = '0';
                setTimeout(() => alertMessage.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>