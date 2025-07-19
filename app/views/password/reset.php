<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Taxi System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; }
        .password-mismatch-border { border-color: #ef4444 !important; /* red-500 */ }
        .password-match-border { border-color: #22c55e !important; /* green-500 */ }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-lg">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Set a New Password</h1>
            <p class="mt-2 text-gray-600">Please choose a new password for your account.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php else: ?>
            <form action="<?= BASE_URL ?>/reset-password" method="POST" class="space-y-4">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                               class="appearance-none block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Minimum 6 characters">
                    </div>
                </div>
                
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password_confirm" name="password_confirm" type="password" required
                               class="appearance-none block w-full pl-10 px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Repeat the password">
                    </div>
                    <p id="match-message" class="mt-2 text-sm text-red-600 hidden">Passwords do not match.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submitBtn" disabled
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-indigo-300 disabled:cursor-not-allowed transition-colors">
                        Reset Password
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
<script>
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirm');
    const submitBtn = document.getElementById('submitBtn');
    const matchMessage = document.getElementById('match-message');

    function validatePasswords() {
        const pass = passwordInput.value;
        const confirmPass = confirmPasswordInput.value;
        
        const areMatching = pass === confirmPass;
        const isLongEnough = pass.length >= 6;
        
        // Update button state
        submitBtn.disabled = !(areMatching && isLongEnough);

        // Show/hide mismatch message and apply border colors
        if (confirmPass.length > 0) {
            if (areMatching) {
                matchMessage.classList.add('hidden');
                confirmPasswordInput.classList.remove('password-mismatch-border');
                confirmPasswordInput.classList.add('password-match-border');
            } else {
                matchMessage.classList.remove('hidden');
                confirmPasswordInput.classList.add('password-mismatch-border');
                confirmPasswordInput.classList.remove('password-match-border');
            }
        } else {
            // Reset if confirm password field is empty
            matchMessage.classList.add('hidden');
            confirmPasswordInput.classList.remove('password-mismatch-border', 'password-match-border');
        }
    }

    // Add event listeners to both fields
    passwordInput.addEventListener('input', validatePasswords);
    confirmPasswordInput.addEventListener('input', validatePasswords);
</script>
</html> 