<?php
// --- Server-Side State Management ---

$status_file_path = __DIR__ . '/../app/cache/task_status.json';

// API Endpoint to handle updates
if (isset($_GET['action']) && $_GET['action'] === 'update_status') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['taskId'], $input['completed'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    $taskId = $input['taskId'];
    $isCompleted = $input['completed'];

    $completed_tasks = get_completed_tasks_from_file($status_file_path);

    if ($isCompleted) {
        if (!in_array($taskId, $completed_tasks)) {
            $completed_tasks[] = $taskId;
        }
    } else {
        $completed_tasks = array_filter($completed_tasks, function($id) use ($taskId) {
            return $id !== $taskId;
        });
    }

    file_put_contents($status_file_path, json_encode(array_values($completed_tasks), JSON_PRETTY_PRINT));
    
    echo json_encode(['status' => 'ok']);
    exit;
}

function get_completed_tasks_from_file($filepath) {
    if (!file_exists($filepath)) {
        return [];
    }
    $content = file_get_contents($filepath);
    return json_decode($content, true) ?: [];
}

// --- Page Rendering Logic ---

// Function to parse the routes.txt file
function parse_routes($filepath) {
    $routes = [];
    if (!file_exists($filepath)) {
        return [['method' => 'Error', 'uri' => 'routes.txt not found', 'controller' => '', 'is_get' => false]];
    }
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match('/Method: (GET|POST|PUT|DELETE|PATCH), URI: (.*?), Controller: (.*)/', $line, $matches)) {
            $uri = $matches[2];
            $is_get = ($matches[1] === 'GET') && !str_contains($uri, '{');
            $routes[] = [
                'method' => $matches[1],
                'uri' => $uri,
                'controller' => $matches[3],
                'is_get' => $is_get
            ];
        }
    }
    return $routes;
}

$routes_file = __DIR__ . '/../routes.txt';
$tasks = parse_routes($routes_file);
$completed_tasks_list = get_completed_tasks_from_file($status_file_path);

// --- Calculations for Progress Summary ---
$total_tasks = count($tasks);
$completed_count = count($completed_tasks_list);
$remaining_count = $total_tasks > 0 ? $total_tasks - $completed_count : 0;
$avg_time_per_task_seconds = 30 * 60; // Average 30 minutes per task
$total_seconds_remaining = $remaining_count * $avg_time_per_task_seconds;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Routes Checklist</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #111827; }
        .task-item.completed .task-link { text-decoration: line-through; opacity: 0.5; }
    </style>
</head>
<body class="text-gray-200 font-sans">

<div class="container mx-auto max-w-4xl px-4 py-12">
    <header class="text-center mb-10">
        <h1 class="text-4xl font-bold text-white mb-2">System Routes Checklist</h1>
        <p class="text-lg text-gray-400">A list of all routes being reviewed and tested.</p>
    </header>

    <div class="bg-gray-800 rounded-lg shadow-2xl p-6 mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Progress Summary</h2>
        <div class="flex justify-between items-center mb-2 text-sm text-gray-400">
            <span id="progress-text"></span>
            <span id="time-remaining-text" class="font-mono font-semibold"></span>
        </div>
        <div class="w-full bg-gray-700 rounded-full h-4">
            <div id="progress-bar" class="bg-green-500 h-4 rounded-full transition-all duration-500" style="width: 0%;"></div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg shadow-2xl p-6">
        <ul id="task-list" class="space-y-3">
            <?php foreach ($tasks as $task): 
                $taskId = htmlspecialchars($task['method'] . ' ' . $task['uri']);
                $is_completed = in_array($taskId, $completed_tasks_list);
            ?>
                <li data-id="<?= $taskId ?>" class="task-item flex items-center justify-between p-4 rounded-md transition-all duration-300 <?= $is_completed ? 'bg-gray-700' : 'bg-red-900/50 border border-red-700/50' ?>">
                    <div class="flex items-center">
                        <span class="font-bold text-xs text-center rounded-md px-2 py-1 mr-4 w-16 <?= $task['method'] === 'GET' ? 'bg-sky-500/80' : '' ?> <?= $task['method'] === 'POST' ? 'bg-green-500/80' : '' ?> <?= !in_array($task['method'], ['GET', 'POST']) ? 'bg-purple-500/80' : '' ?>"><?= htmlspecialchars($task['method']) ?></span>
                        <div class="flex flex-col">
                            <?php if ($task['is_get']): ?>
                                <a href="<?= ltrim($task['uri'], '/') ?>" target="_blank" class="task-link font-mono text-lg <?= $is_completed ? 'text-gray-400' : 'text-red-300 hover:text-red-100' ?> hover:underline">
                                    <?= htmlspecialchars($task['uri']) ?>
                                </a>
                            <?php else: ?>
                                <span class="task-link font-mono text-lg <?= $is_completed ? 'text-gray-400' : 'text-red-300' ?>">
                                    <?= htmlspecialchars($task['uri']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-xs font-mono mt-1 <?= $is_completed ? 'text-gray-500' : 'text-red-400/70' ?>"><?= htmlspecialchars($task['controller']) ?></span>
                        </div>
                    </div>
                    <button class="complete-btn flex-shrink-0 text-gray-400 hover:text-white transition-transform duration-200 hover:scale-125" title="Toggle status">
                        <i class="fa-solid fa-check-circle text-2xl <?= $is_completed ? 'text-green-500' : '' ?>"></i>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const taskList = document.getElementById('task-list');
    
    // --- State Initialization ---
    let totalTasks = <?= $total_tasks ?>;
    let completedTasks = <?= $completed_count ?>;
    const AVG_TIME_PER_TASK = <?= $avg_time_per_task_seconds ?>;
    let totalSecondsRemaining = <?= $total_seconds_remaining ?>;
    let timerInterval = null;

    // --- DOM Elements ---
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const timeRemainingText = document.getElementById('time-remaining-text');

    // --- Helper Functions ---
    const formatTime = (seconds) => {
        if (seconds <= 0) return "🎉 All Done!";
        
        const d = Math.floor(seconds / 86400);
        const h = Math.floor((seconds % 86400) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);

        let parts = [];
        if (d > 0) {
            parts.push(`${d} day${d !== 1 ? 's' : ''}`);
            parts.push(`${h} hour${h !== 1 ? 's' : ''}`);
            parts.push(`${m} minute${m !== 1 ? 's' : ''}`);
            parts.push(`${s} second${s !== 1 ? 's' : ''}`);
        } else if (h > 0) {
            parts.push(`${h} hour${h !== 1 ? 's' : ''}`);
            parts.push(`${m} minute${m !== 1 ? 's' : ''}`);
            parts.push(`${s} second${s !== 1 ? 's' : ''}`);
        } else if (m > 0) {
            parts.push(`${m} minute${m !== 1 ? 's' : ''}`);
            parts.push(`${s} second${s !== 1 ? 's' : ''}`);
        } else {
            parts.push(`${s} second${s !== 1 ? 's' : ''}`);
        }
        
        return `Est. Time: ${parts.join(', ')}`;
    };

    const updateProgressDisplay = () => {
        const percentage = totalTasks > 0 ? (completedTasks / totalTasks) * 100 : 0;
        progressBar.style.width = `${percentage}%`;
        progressText.textContent = `${completedTasks} / ${totalTasks} Tasks Completed (${percentage.toFixed(0)}%)`;
        startOrUpdateCountdown();
    };
    
    const startOrUpdateCountdown = () => {
        clearInterval(timerInterval);
        if (totalSecondsRemaining <= 0) {
            timeRemainingText.textContent = "🎉 All Done!";
            return;
        }

        timerInterval = setInterval(() => {
            totalSecondsRemaining--;
            timeRemainingText.textContent = formatTime(totalSecondsRemaining);
            if (totalSecondsRemaining <= 0) {
                clearInterval(timerInterval);
                timeRemainingText.textContent = "🎉 All Done!";
            }
        }, 1000);
        timeRemainingText.textContent = formatTime(totalSecondsRemaining);
    };

    const updateTaskAppearance = (taskItem, isCompleted) => {
        const icon = taskItem.querySelector('i');
        const link = taskItem.querySelector('.task-link');
        const controller = taskItem.querySelector('.font-mono.text-xs');
        
        taskItem.classList.toggle('completed', isCompleted);
        taskItem.classList.toggle('bg-gray-700', isCompleted);
        taskItem.classList.toggle('bg-red-900/50', !isCompleted);
        taskItem.classList.toggle('border-red-700/50', !isCompleted);

        if (link) { // Check if link exists
            link.classList.toggle('text-gray-400', isCompleted);
            link.classList.toggle('text-red-300', !isCompleted);
            link.classList.toggle('hover:text-red-100', !isCompleted);
        }

        if(controller) {
            controller.classList.toggle('text-gray-500', isCompleted);
            controller.classList.toggle('text-red-400/70', !isCompleted);
        }
        
        if(icon) icon.classList.toggle('text-green-500', isCompleted);
    };

    taskList.addEventListener('click', function(e) {
        const completeBtn = e.target.closest('.complete-btn');
        if (!completeBtn) return;

        const taskItem = completeBtn.closest('.task-item');
        const taskId = taskItem.dataset.id;
        const isNowCompleted = !taskItem.classList.contains('completed');
        
        const originalCompletedCount = completedTasks;
        const originalSeconds = totalSecondsRemaining;

        // --- Optimistic UI Update ---
        if (isNowCompleted) {
            completedTasks++;
            totalSecondsRemaining -= AVG_TIME_PER_TASK;
        } else {
            completedTasks--;
            totalSecondsRemaining += AVG_TIME_PER_TASK;
        }
        totalSecondsRemaining = Math.max(0, totalSecondsRemaining);
        updateTaskAppearance(taskItem, isNowCompleted);
        updateProgressDisplay();

        // --- Send update to server ---
        fetch('test.php?action=update_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ taskId: taskId, completed: isNowCompleted })
        }).catch(error => {
            console.error('Failed to update task status:', error);
            // --- Revert UI on failure ---
            completedTasks = originalCompletedCount;
            totalSecondsRemaining = originalSeconds;
            updateTaskAppearance(taskItem, !isNowCompleted);
            updateProgressDisplay();
            alert('Failed to save status. Please try again.');
        });
    });

    // --- Initial Page Load ---
    updateProgressDisplay();
});
</script>

</body>
</html>