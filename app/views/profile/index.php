<?php
// Assuming the main layout file includes the header, so we just need the content part.
// You might need to adjust this based on your application's layout structure.
// Start session if not already started to access session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$user = $data['user'] ?? null;
$current_token = $data['current_token'] ?? null;
// إضافة التوكن للـ user object إذا كان متوفراً
if ($user && $current_token) {
    $user->current_token = $current_token;
}
if (!$user) {
    // Redirect to login if user is not logged in or data is missing
    header('Location: ' . URLROOT . '/login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile - Taxi System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
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
        input {
            padding-left: 2.5rem !important;
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen px-4 py-8">
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <svg class="mx-auto h-12 w-auto text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900">My Profile</h1>
            <p class="mt-2 text-sm text-gray-600">
                Update your personal information.
            </p>
        </div>
        <div class="bg-white py-8 px-6 shadow-xl rounded-xl sm:px-10">
            <?php if (isset($_SESSION['error'])): ?>
                <div id="alert-error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-md">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div id="alert-success" class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-md">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <form action="<?= URLROOT ?>/profile/update" method="POST" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username (cannot be
                        changed)</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-user-lock"></i></span>
                        <input id="username" name="username" type="text"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm"
                            value="<?= htmlspecialchars($user->username ?? '') ?>" readonly>
                    </div>
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-id-card"></i></span>
                        <input id="name" name="name" type="text" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            value="<?= htmlspecialchars($user->name ?? '') ?>" placeholder="Enter your full name">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-envelope"></i></span>
                        <input id="email" name="email" type="email" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            value="<?= htmlspecialchars($user->email ?? '') ?>" placeholder="Enter your email">
                    </div>
                </div>
                <div>
                    <label for="current_token" class="block text-sm font-medium text-gray-700 mb-1">Current Token</label>
                    <div class="flex mt-1">
                        <div class="form-input-container flex-1">
                            <span class="form-input-icon"><i class="fas fa-key"></i></span>
                            <input id="current_token" name="current_token" type="text"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-l-md shadow-sm bg-gray-50 sm:text-sm font-mono text-xs"
                                value="<?= htmlspecialchars($user->current_token ?? '') ?>" readonly>
                        </div>
                        <button type="button" onclick="copyToken()"
                            class="px-4 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-700 rounded-r-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                            title="Copy Token">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">This is your current authentication token (read-only). Click the copy button to copy to clipboard.
                    </p>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-lock"></i></span>
                        <input id="password" name="password" type="password"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Leave blank to keep current password">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must be at least 6 characters long if you want to change it.
                    </p>
                </div>
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        Update Profile
                    </button>
                </div>
                <div class="text-center mt-4">
                    <a href="<?= URLROOT ?>/dashboard" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Hide alerts after 5 seconds
        const alertError = document.getElementById('alert-error');
        if (alertError) {
            setTimeout(() => {
                alertError.style.transition = 'opacity 0.5s ease-out';
                alertError.style.opacity = '0';
                setTimeout(() => alertError.remove(), 500);
            }, 5000);
        }
        const alertSuccess = document.getElementById('alert-success');
        if (alertSuccess) {
            setTimeout(() => {
                alertSuccess.style.transition = 'opacity 0.5s ease-out';
                alertSuccess.style.opacity = '0';
                setTimeout(() => alertSuccess.remove(), 500);
            }, 5000);
        }

        // دالة نسخ التوكن
        function copyToken() {
            const tokenInput = document.getElementById('current_token');
            const tokenValue = tokenInput.value;

            if (!tokenValue) {
                showNotification('لا يوجد توكن للنسخ', 'error');
                return;
            }

            // نسخ النص إلى الحافظة
            navigator.clipboard.writeText(tokenValue).then(function() {
                showNotification('تم نسخ التوكن بنجاح!', 'success');
            }).catch(function(err) {
                console.error('فشل في نسخ التوكن: ', err);
                // طريقة بديلة للمتصفحات القديمة
                fallbackCopyTextToClipboard(tokenValue);
            });
        }

        // دالة بديلة لنسخ النص في المتصفحات القديمة
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showNotification('تم نسخ التوكن بنجاح!', 'success');
                } else {
                    showNotification('فشل في نسخ التوكن', 'error');
                }
            } catch (err) {
                console.error('فشل في نسخ التوكن: ', err);
                showNotification('فشل في نسخ التوكن', 'error');
            }

            document.body.removeChild(textArea);
        }

        // دالة عرض الإشعارات
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-md text-white text-sm font-medium z-50 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // إزالة الإشعار بعد 3 ثواني
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s ease-out';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 500);
            }, 3000);
        }
    </script>
</body>
</html>