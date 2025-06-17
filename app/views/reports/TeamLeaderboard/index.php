<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../../includes/nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center"><?= htmlspecialchars($data['title']) ?></h1>

        <div class="space-y-4">
            <?php foreach($data['leaderboard'] as $index => $team): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="text-4xl font-bold text-gray-700 w-16 text-center">#<?= $index + 1 ?></div>
                    <div class="ml-6">
                        <h2 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($team['team_name']) ?></h2>
                        <p class="text-gray-500"><?= htmlspecialchars($team['member_count']) ?> أعضاء</p>
                    </div>
                </div>
                <div class="text-3xl font-bold text-green-600">
                    <?= htmlspecialchars(number_format($team['total_team_score'])) ?> نقطة
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 