<?php
// Assuming the main layout file includes the header, so we just need the content part.
// You might need to adjust this based on your application's layout structure.

// Start session if not already started to access session variables
if (session_status() == PHP_SESSION_NONE) {

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
            <svg class="mx-auto h-12 w-auto text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
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

            <form action="<?= BASE_PATH ?>/profile/update" method="POST" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username (cannot be changed)</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-user-lock"></i></span>
                        <input id="username" name="username" type="text"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm"
                               value="<?= htmlspecialchars($data['user']['username'] ?? '') ?>" readonly>
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-id-card"></i></span>
                        <input id="name" name="name" type="text" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= htmlspecialchars($data['user']['name'] ?? '') ?>"
                               placeholder="Enter your full name">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-envelope"></i></span>
                        <input id="email" name="email" type="email" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               value="<?= htmlspecialchars($data['user']['email'] ?? '') ?>"
                               placeholder="Enter your email">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="form-input-container mt-1">
                        <span class="form-input-icon"><i class="fas fa-lock"></i></span>
                        <input id="password" name="password" type="password"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Leave blank to keep current password">
                    </div>
                     <p class="mt-1 text-xs text-gray-500">Must be at least 6 characters long if you want to change it.</p>
                </div>
                
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        Update Profile
                    </button>
                </div>
                 <div class="text-center mt-4">
                     <a href="<?= BASE_PATH ?>/dashboard" class="font-medium text-indigo-600 hover:text-indigo-500">
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
    </script>
</body>
</html> 