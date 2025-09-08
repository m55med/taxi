<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - Taxi System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <div class="mb-4">
            <svg class="mx-auto h-24 w-24 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1 class="text-6xl font-bold text-gray-800">500</h1>
        <p class="text-xl md:text-2xl font-semibold text-gray-700 mt-4">Internal Server Error</p>
        <p class="text-gray-500 mt-2">
            An unexpected error has occurred on our server.
            <br>
            Please contact the developer for assistance.
        </p>
        <div class="mt-8">
            <a href="/" 
               class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-md shadow-md hover:bg-indigo-700 transition duration-300">
                Go to Homepage
            </a>
        </div>

        <?php if (isset($data['showDetails']) && $data['showDetails'] === true && isset($data['exception'])): ?>
            <div class="mt-10 text-left bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto overflow-auto">
                <h3 class="text-lg font-bold text-red-700 mb-2">Developer Details</h3>
                <pre class="bg-gray-800 text-white text-sm p-4 rounded-md">
<strong>Message:</strong> <?= htmlspecialchars($data['exception']->getMessage()) ?>

<strong>File:</strong> <?= $data['exception']->getFile() ?> on line <?= $data['exception']->getLine() ?>

<strong>Stack Trace:</strong>
<?= htmlspecialchars($data['exception']->getTraceAsString()) ?>
                </pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 