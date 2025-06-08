<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز الاتصال</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        /* Loading Spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Form Controls */
        select, input[type="text"], input[type="email"], input[type="datetime-local"], textarea {
            transition: all 0.2s ease-in-out;
        }

        select:focus, input[type="text"]:focus, input[type="email"]:focus, 
        input[type="datetime-local"]:focus, textarea:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        /* Buttons */
        button {
            transition: all 0.2s ease-in-out;
        }

        button:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Call Status Tags */
        .status-tag {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .status-tag:hover {
            transform: translateY(-1px);
        }

        /* Modal Animation */
        #transferModal {
            transition: opacity 0.2s ease-in-out;
        }

        #transferModal.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* Notification Animation */
        #notification {
            transition: all 0.3s ease-in-out;
            transform: translateY(-100%);
        }

        #notification:not(.hidden) {
            transform: translateY(0);
        }

        /* Documents Section Animation */
        #documentsSection {
            transition: max-height 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
        }

        #documentsSection:not(.hidden) {
            max-height: 1000px;
        }

        /* Call History Items */
        .call-history-item {
            transition: all 0.2s ease-in-out;
        }

        .call-history-item:hover {
            transform: translateX(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../includes/nav.php'; ?>

    <script>
        const BASE_PATH = '<?= BASE_PATH ?>';
    </script>

    <div class="container mx-auto px-4 py-8">
        <!-- Notification -->
        <div id="notification" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 max-w-sm w-full bg-white rounded-lg shadow-lg z-50">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i id="notificationIcon" class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                    <div class="mr-3">
                        <p id="notificationMessage" class="text-sm font-medium text-gray-900"></p>
                    </div>
                </div>
            </div>
            <div class="absolute top-0 left-0 mt-2 ml-2">
                <button onclick="hideNotification()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="mb-6 grid grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">مكالمات اليوم</h3>
                <p class="text-2xl font-bold text-indigo-600"><?= $today_calls_count ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">إجمالي المكالمات المطلوبة</h3>
                <p class="text-2xl font-bold text-indigo-600"><?= $total_pending_calls ?></p>
            </div>
        </div>

        <!-- Search Section -->
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <form action="" method="GET" class="flex gap-4">
                    <input type="text" name="phone" placeholder="بحث برقم الهاتف..." 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                        بحث
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($rate_limit_exceeded) && $rate_limit_exceeded === true): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md text-center" role="alert">
                <p class="font-bold text-lg mb-2">لقد تجاوزت الحد الأقصى للمكالمات</p>
                <p class="mb-4">لقد قمت بإجراء عدد كبير من المكالمات في وقت قصير جدًا.</p>
                <p>يرجى الانتظار لمدة <span id="countdown" class="font-bold text-xl"><?= isset($wait_time) ? $wait_time : 0 ?></span> ثانية قبل محاولة جلب سائق جديد.</p>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let countdownElement = document.getElementById('countdown');
                    let timeLeft = <?= isset($wait_time) ? $wait_time : 0 ?>;
                    if (countdownElement && timeLeft > 0) {
                        const interval = setInterval(() => {
                            timeLeft--;
                            countdownElement.innerText = timeLeft;
                            if (timeLeft <= 0) {
                                clearInterval(interval);
                                window.location.reload();
                            }
                        }, 1000);
                    } else if (timeLeft <= 0) {
                        // If for some reason the time is already 0, just reload.
                        window.location.reload();
                    }
                });
            </script>
        <?php elseif (empty($driver)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">لا يوجد سائقين في قائمة الانتظار حالياً</span>
            </div>
        <?php else: ?>
            <!-- Main Content Grid -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Sidebar -->
                <div class="col-span-4">
                    <div class="bg-white rounded-lg shadow p-6">
                        <!-- Phone Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold">رقم الهاتف</h3>
                                <button onclick="copyToClipboard('<?= htmlspecialchars($driver['phone']) ?>')" 
                                        class="text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p id="driverPhone" class="text-xl font-bold"><?= htmlspecialchars($driver['phone']) ?></p>
                        </div>

                        <!-- App Status -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">حالة التطبيق</h3>
                            <select id="appStatus" onchange="updateAppStatus(<?= $driver['id'] ?>, this.value)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                <option value="active" <?= $driver['app_status'] == 'active' ? 'selected' : '' ?>>نشط</option>
                                <option value="inactive" <?= $driver['app_status'] == 'inactive' ? 'selected' : '' ?>>غير نشط</option>
                                <option value="banned" <?= $driver['app_status'] == 'banned' ? 'selected' : '' ?>>محظور</option>
                            </select>
                        </div>

                        <!-- Driver Info Form -->
                        <form id="driverInfoForm" method="POST" class="space-y-4">
                            <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">اسم السائق</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($driver['name']) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($driver['email'] ?? '') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
                                <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                    <option value="">اختر النوع</option>
                                    <option value="male" <?= $driver['gender'] == 'male' ? 'selected' : '' ?>>ذكر</option>
                                    <option value="female" <?= $driver['gender'] == 'female' ? 'selected' : '' ?>>أنثى</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">الجنسية</label>
                                <select name="nationality" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                    <option value="">اختر الجنسية</option>
                                    <?php foreach ($nationalities as $nationality): ?>
                                        <option value="<?= htmlspecialchars($nationality) ?>" 
                                                <?= $driver['nationality'] == $nationality ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nationality) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">مصدر البيانات</label>
                                <select name="data_source" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                    <option value="form" <?= $driver['data_source'] == 'form' ? 'selected' : '' ?>>نموذج</option>
                                    <option value="referral" <?= $driver['data_source'] == 'referral' ? 'selected' : '' ?>>إحالة</option>
                                    <option value="telegram" <?= $driver['data_source'] == 'telegram' ? 'selected' : '' ?>>تيليجرام</option>
                                    <option value="staff" <?= $driver['data_source'] == 'staff' ? 'selected' : '' ?>>موظف</option>
                                    <option value="excel" <?= $driver['data_source'] == 'excel' ? 'selected' : '' ?>>إكسل</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                حفظ التغييرات
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="col-span-8 space-y-6">
                    <!-- Call History -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4">سجل المكالمات</h3>
                        <?php if (empty($call_history)): ?>
                            <p class="text-gray-500">لا يوجد سجل مكالمات سابق</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($call_history as $call): ?>
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="font-semibold"><?= htmlspecialchars($call['staff_name']) ?></span>
                                                <span class="text-gray-500 text-sm">
                                                    <?= date('Y/m/d H:i', strtotime($call['created_at'])) ?>
                                                </span>
                                            </div>
                                            <span class="px-2 py-1 rounded-full text-sm 
                                                <?php
                                                switch($call['call_status']) {
                                                    case 'answered': echo 'bg-green-100 text-green-800'; break;
                                                    case 'no_answer': echo 'bg-red-100 text-red-800'; break;
                                                    case 'busy': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'not_available': echo 'bg-orange-100 text-orange-800'; break;
                                                    case 'wrong_number': echo 'bg-red-100 text-red-800'; break;
                                                    case 'rescheduled': echo 'bg-blue-100 text-blue-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?= $call_status_text[$call['call_status']] ?? $call['call_status'] ?>
                                            </span>
                                        </div>
                                        <?php if ($call['notes']): ?>
                                            <p class="text-gray-600 text-sm"><?= nl2br(htmlspecialchars($call['notes'])) ?></p>
                                        <?php endif; ?>
                                        <?php if ($call['next_call_at']): ?>
                                            <div class="mt-2 text-sm text-indigo-600">
                                                <i class="far fa-clock ml-1"></i>
                                                المكالمة القادمة: <?= date('Y/m/d H:i', strtotime($call['next_call_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Required Documents -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center cursor-pointer mb-4" onclick="toggleDocuments()">
                            <h3 class="text-lg font-semibold">المستندات المطلوبة</h3>
                            <i id="documentsIcon" class="fas fa-chevron-down transform transition-transform duration-200"></i>
                        </div>
                        <div id="documentsSection" class="hidden space-y-4">
                            <form id="documentsForm" class="space-y-4">
                                <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <?php foreach ($required_documents as $doc): ?>
                                        <div class="border rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="documents[]" 
                                                           value="<?= $doc['id'] ?>"
                                                           <?= $doc['status'] === 'submitted' ? 'checked' : '' ?>
                                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <label class="mr-2 block text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($doc['document_name']) ?>
                                                    </label>
                                                </div>
                                                <span class="px-2 py-1 text-sm rounded-full 
                                                    <?php
                                                    switch($doc['status']) {
                                                        case 'submitted':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'rejected':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                    }
                                                    ?>">
                                                    <?php
                                                    switch($doc['status']) {
                                                        case 'submitted':
                                                            echo 'تم التقديم';
                                                            break;
                                                        case 'rejected':
                                                            echo 'مرفوض';
                                                            break;
                                                        default:
                                                            echo 'مطلوب';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <?php if ($doc['updated_at']): ?>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    <span>آخر تحديث: <?= date('Y/m/d H:i', strtotime($doc['updated_at'])) ?></span>
                                                    <?php if ($doc['updated_by_name']): ?>
                                                        <span class="mr-2">بواسطة: <?= htmlspecialchars($doc['updated_by_name']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($doc['note']): ?>
                                                <div class="text-sm text-gray-600 mt-1">
                                                    <strong>ملاحظات:</strong> <?= nl2br(htmlspecialchars($doc['note'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                        حفظ المستندات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Call Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4">تسجيل مكالمة جديدة</h3>
                        <form id="callForm" class="space-y-4">
                            <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                            
                            <!-- نتيجة المكالمة -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">نتيجة المكالمة <span class="text-red-500">*</span></label>
                                <select name="call_status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">اختر نتيجة المكالمة</option>
                                    <option value="answered">تم الرد</option>
                                    <option value="no_answer">لم يتم الرد</option>
                                    <option value="busy">مشغول</option>
                                    <option value="not_available">غير متاح</option>
                                    <option value="wrong_number">رقم خاطئ</option>
                                </select>
                            </div>

                            <!-- ملاحظات المكالمة -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ملاحظات المكالمة
                                    <span class="text-red-500" id="notesRequired">*</span>
                                    <span class="text-sm text-gray-500">(مطلوب في حالة الرد)</span>
                                </label>
                                <textarea name="notes" rows="3" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="أدخل ملاحظات المكالمة هنا..."></textarea>
                            </div>

                            <!-- موعد المكالمة القادمة -->
                            <div class="hidden" id="nextCallSection">
                                <label class="block text-sm font-medium text-gray-700 mb-2">موعد المكالمة القادمة</label>
                                <input type="datetime-local" name="next_call_at" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>

                            <!-- زر الحفظ -->
                            <div class="flex justify-between items-center">
                                <button type="submit" 
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center">
                                    <span>حفظ المكالمة</span>
                                </button>
                                
                                <a href="<?= BASE_PATH ?>/call"
                                   class="bg-yellow-500 text-white px-6 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200">
                                    تخطي الرقم
                                </a>

                                <!-- زر التحويل -->
                                <button type="button" onclick="showTransferModal()"
                                    class="bg-gray-100 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                                    <i class="fas fa-exchange-alt ml-2"></i>
                                    تحويل السائق
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl p-6 w-96">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">تحويل السائق</h3>
                <button onclick="hideTransferModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="transferForm" class="space-y-4">
                <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تحويل إلى</label>
                    <select name="to_user_id" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['username']) ?>
                                    <?= $user['is_online'] ? '(متصل)' : '' ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات التحويل</label>
                    <textarea name="note" rows="2" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md"
                        placeholder="أدخل سبب التحويل هنا..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="hideTransferModal()"
                        class="ml-2 bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        تأكيد التحويل
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_PATH ?>/public/js/call-center.js"></script>
    <script>
        // آلية تحرير القفل عند مغادرة الصفحة
        window.addEventListener('unload', function() {
            // لا حاجة للتحقق، أرسل الطلب دائمًا عند المغادرة
            // الخادم سيتحقق من الجلسة
            navigator.sendBeacon('<?= BASE_PATH ?>/call/releaseHold');
        });
    </script>
</body>
</html>