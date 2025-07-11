<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Email Sent - Taxi System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-lg text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
            <i class="fas fa-check fa-2x text-green-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Check Your Email</h1>
        <p class="mt-2 text-gray-600">
            <?php if (isset($data['message'])): ?>
                <?= htmlspecialchars($data['message']) ?>
            <?php else: ?>
                We have sent a password reset link to your email address.
            <?php endif; ?>
        </p>
        <div class="pt-4">
            <a href="<?= BASE_PATH ?>/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                &larr; Back to Login
            </a>
        </div>
    </div>
</body>
</html> 