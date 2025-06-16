<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_main_title ?? 'إدارة الكوبونات') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        .toast-top-left { top: 12px; left: 12px; }
    </style>
</head>
<body class="bg-gray-100" x-data="couponsPage()">
    
<?php include_once APPROOT . '/app/views/includes/nav.php'; ?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6"><?= htmlspecialchars($page_main_title); ?></h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-500 text-white p-5 rounded-lg shadow-lg">
            <h3 class="text-sm font-medium opacity-80">إجمالي الكوبونات</h3>
            <p class="text-3xl font-bold mt-1"><?= number_format($stats['total']) ?></p>
        </div>
        <div class="bg-green-500 text-white p-5 rounded-lg shadow-lg">
            <h3 class="text-sm font-medium opacity-80">الكوبونات المستخدمة</h3>
            <p class="text-3xl font-bold mt-1"><?= number_format($stats['used']) ?></p>
        </div>
        <div class="bg-yellow-500 text-white p-5 rounded-lg shadow-lg">
            <h3 class="text-sm font-medium opacity-80">الكوبونات المتاحة</h3>
            <p class="text-3xl font-bold mt-1"><?= number_format($stats['unused']) ?></p>
        </div>
    </div>
    
    <!-- Collapsible Add Coupon Section -->
    <div class="mb-8 bg-white rounded-lg shadow-md border border-gray-200" x-data="{ open: false }">
        <div class="p-5 cursor-pointer flex justify-between items-center" @click="open = !open">
            <h2 class="text-xl font-semibold text-gray-700">إضافة كوبونات جديدة</h2>
            <i class="fas fa-chevron-down transition-transform" :class="{'rotate-180': open}"></i>
        </div>
        <div x-show="open" x-collapse x-cloak>
            <form action="<?= BASE_PATH ?>/admin/coupons" method="POST" class="p-5 border-t">
                <input type="hidden" name="action" value="add_bulk">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Codes Textarea -->
                    <div class="md:col-span-3">
                        <label for="codes" class="block mb-2 text-sm font-medium text-gray-900">أكواد الكوبونات</label>
                        <textarea name="codes" id="codes" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="ضع كل كوبون في سطر جديد..."></textarea>
                        <p class="mt-1 text-xs text-gray-500">يمكنك لصق قائمة من الأكواد هنا، مفصولة بسطر جديد أو مسافة أو فاصلة.</p>
                    </div>
                    <!-- Value -->
                    <div>
                        <label for="value" class="block mb-2 text-sm font-medium text-gray-900">قيمة الكوبون</label>
                        <input type="number" step="0.01" name="value" id="value" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                    </div>
                    <!-- Country -->
                    <div>
                        <label for="country_id" class="block mb-2 text-sm font-medium text-gray-900">الدولة (اختياري)</label>
                        <select name="country_id" id="country_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">كل الدول</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Submit -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            <i class="fas fa-plus ml-2"></i>إضافة الكوبونات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Coupons List Section -->
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">قائمة الكوبونات</h2>

        <!-- Filters & Actions -->
        <div class="mb-6">
            <!-- Filter Form -->
            <form action="" method="GET" class="flex flex-wrap items-center gap-4 mb-4">
                 <div class="flex-grow">
                    <label for="search" class="sr-only">بحث عن كوبون</label>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="ابحث بالكود..." class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-grow">
                     <label for="filter_country_id" class="sr-only">الدولة</label>
                    <select name="country_id" id="filter_country_id" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">كل الدول</option>
                         <?php foreach ($countries as $country): ?>
                            <option value="<?= $country['id'] ?>" <?= (isset($filters['country_id']) && $filters['country_id'] == $country['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-grow">
                    <label for="is_used" class="sr-only">حالة الاستخدام</label>
                    <select name="is_used" id="is_used" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">كل الحالات</option>
                        <option value="0" <?= (isset($filters['is_used']) && $filters['is_used'] === '0') ? 'selected' : '' ?>>غير مستخدم</option>
                        <option value="1" <?= (isset($filters['is_used']) && $filters['is_used'] === '1') ? 'selected' : '' ?>>مستخدم</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2 space-x-reverse">
                     <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 text-sm font-medium">تطبيق</button>
                     <a href="<?= BASE_PATH ?>/admin/coupons" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-300 text-sm font-medium">مسح</a>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4">
                <!-- Bulk Actions Dropdown -->
                <div class="relative" x-show="selectedCoupons.length > 0" x-cloak>
                    <button @click="actionOpen = !actionOpen" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        إجراءات للمحدد <span x-text="`(${selectedCoupons.length})`" class="mr-1"></span>
                        <i class="fas fa-chevron-down -ml-1 mr-2 h-5 w-5 transition-transform" :class="{'rotate-180': actionOpen}"></i>
                    </button>
                    <div x-show="actionOpen" @click.away="actionOpen = false" x-collapse class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                        <div class="py-1">
                            <button @click="submitBulkDelete()" class="w-full text-right flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-trash text-red-500 w-5 text-center ml-2"></i> حذف المحدد
                            </button>
                            <button @click="copySelected()" class="w-full text-right flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                <i class="fas fa-copy text-blue-500 w-5 text-center ml-2"></i> نسخ المحدد
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Button -->
                <div>
                    <button @click="openExportModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <i class="fas fa-file-export ml-2"></i> تصدير
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Table Form for Bulk Actions -->
        <form action="<?= BASE_PATH ?>/admin/coupons" method="POST" id="bulkActionForm">
            <input type="hidden" name="action" id="bulkActionInput" value="">
            <template x-for="couponId in selectedCoupons" :key="couponId">
                <input type="hidden" name="coupon_ids[]" :value="couponId">
            </template>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input type="checkbox" @click="toggleAll($event.target.checked)" :checked="allVisibleUnusedCoupons.length > 0 && selectedCoupons.length === allVisibleUnusedCoupons.length" :disabled="allVisibleUnusedCoupons.length === 0" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3">الكود</th>
                            <th scope="col" class="px-4 py-3">القيمة</th>
                            <th scope="col" class="px-4 py-3">الدولة</th>
                            <th scope="col" class="px-4 py-3">الحالة</th>
                            <th scope="col" class="px-4 py-3">تاريخ الإنشاء</th>
                            <th scope="col" class="px-4 py-3">معلومات الاستخدام</th>
                            <th scope="col" class="px-4 py-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                            <tr><td colspan="8" class="px-6 py-4 text-center">لا توجد كوبونات تطابق البحث.</td></tr>
                        <?php else: ?>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="w-4 p-4">
                                    <?php if (!$coupon['is_used']): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" :value="<?= $coupon['id'] ?>" x-model="selectedCoupons" data-code="<?= htmlspecialchars($coupon['code']) ?>" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 font-mono">
                                    <span class="mr-2"><?= htmlspecialchars($coupon['code']) ?></span>
                                    <button @click="copyToClipboard('<?= htmlspecialchars($coupon['code']) ?>')" class="text-gray-400 hover:text-blue-600"><i class="far fa-copy"></i></button>
                                </td>
                                <td class="px-4 py-4 font-semibold"><?= htmlspecialchars($coupon['value']) ?></td>
                                <td class="px-4 py-4"><?= htmlspecialchars($coupon['country_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-4">
                                    <?php if ($coupon['is_used']): ?>
                                        <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">مستخدم</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full">غير مستخدم</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4"><?= date('Y-m-d', strtotime($coupon['created_at'])) ?></td>
                                <td class="px-4 py-4">
                                    <?php if ($coupon['is_used']): ?>
                                        <div class="text-xs">
                                            <p><strong>التذكرة:</strong> <a href="<?= BASE_PATH . '/tickets/details/' . $coupon['used_in_ticket'] ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($coupon['ticket_number']) ?></a></p>
                                            <p><strong>المستخدم:</strong> <?= htmlspecialchars($coupon['used_by_username']) ?></p>
                                            <p><strong>بتاريخ:</strong> <?= date('Y-m-d H:i', strtotime($coupon['used_at'])) ?></p>
                                        </div>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4">
                                    <?php if (!$coupon['is_used']): ?>
                                    <button @click="openEditModal(<?= htmlspecialchars(json_encode($coupon)) ?>)" class="text-blue-600 hover:text-blue-800"><i class="fas fa-edit"></i></button>
                                    <button @click.prevent="submitSingleDelete(<?= $coupon['id'] ?>)" class="text-red-600 hover:text-red-800 mr-2"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
             <?php require_once APPROOT . '/app/views/inc/pagination.php'; ?>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="editModalOpen = false">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="editModalOpen = false">
        <h3 class="text-lg font-semibold mb-4">تعديل كوبون</h3>
        <form :action="'<?= BASE_PATH ?>/admin/coupons'" method="POST">
             <input type="hidden" name="action" value="update">
             <input type="hidden" name="id" :value="editingCoupon.id">
            
             <div class="mb-4">
                <label for="edit_code" class="block text-sm font-medium text-gray-700">الكود</label>
                <input type="text" name="code" id="edit_code" :value="editingCoupon.code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>
             <div class="mb-4">
                <label for="edit_value" class="block text-sm font-medium text-gray-700">القيمة</label>
                <input type="number" step="0.01" name="value" id="edit_value" :value="editingCoupon.value" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="edit_country_id" class="block text-sm font-medium text-gray-700">الدولة</label>
                 <select name="country_id" id="edit_country_id" :value="editingCoupon.country_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">كل الدول</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= $country['id'] ?>"><?= htmlspecialchars($country['name']) ?></option>
                    <?php endforeach; ?>
                 </select>
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse">
                <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">إلغاء</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>

<!-- Export Modal -->
<div x-show="exportModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @keydown.escape.window="closeExportModal()">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="closeExportModal()">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-800">تصدير الكوبونات</h3>
            <button @click="closeExportModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <p class="text-sm text-gray-600 mb-6">سيتم تصدير البيانات بناءً على الفلاتر المطبقة حالياً.</p>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">اختر صيغة التصدير:</label>
            <div class="grid grid-cols-3 gap-3">
                <button type="button" @click="exportType = 'excel'" :class="{ 'bg-blue-600 text-white border-blue-600 shadow-lg': exportType === 'excel', 'bg-white hover:bg-gray-50 border-gray-300': exportType !== 'excel' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                    <i class="fas fa-file-excel text-3xl mb-2" :class="exportType === 'excel' ? 'text-white' : 'text-green-500'"></i>
                    <span class="font-semibold text-sm">Excel</span>
                </button>
                <button type="button" @click="exportType = 'txt'" :class="{ 'bg-blue-600 text-white border-blue-600 shadow-lg': exportType === 'txt', 'bg-white hover:bg-gray-50 border-gray-300': exportType !== 'txt' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                    <i class="fas fa-file-alt text-3xl mb-2" :class="exportType === 'txt' ? 'text-white' : 'text-gray-500'"></i>
                    <span class="font-semibold text-sm">Text</span>
                </button>
                 <button type="button" @click="exportType = 'json'" :class="{ 'bg-blue-600 text-white border-blue-600 shadow-lg': exportType === 'json', 'bg-white hover:bg-gray-50 border-gray-300': exportType !== 'json' }" class="w-full flex flex-col items-center justify-center p-4 rounded-lg border transition-all duration-200">
                    <i class="fas fa-file-code text-3xl mb-2" :class="exportType === 'json' ? 'text-white' : 'text-blue-500'"></i>
                    <span class="font-semibold text-sm">JSON</span>
                </button>
            </div>
        </div>

        <div class="flex justify-end space-x-2 space-x-reverse pt-4 border-t">
            <button type="button" @click="closeExportModal()" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-semibold text-sm">إلغاء</button>
            <button type="button" @click="submitExport()" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-sm flex items-center">
                <i class="fas fa-download ml-2"></i>
                تصدير الآن
            </button>
        </div>
    </div>
</div>

<script>
    function couponsPage() {
        return {
            editModalOpen: false,
            editingCoupon: {},
            selectedCoupons: [],
            actionOpen: false,
            exportModalOpen: false,
            exportType: 'excel',
            get allVisibleUnusedCoupons() {
                return <?= json_encode(array_column(array_filter($coupons, fn($c) => !$c['is_used']), 'id')) ?>;
            },
            toggleAll(checked) {
                if (checked) {
                    this.selectedCoupons = this.allVisibleUnusedCoupons;
                } else {
                    this.selectedCoupons = [];
                }
            },
            openEditModal(coupon) {
                this.editingCoupon = coupon;
                setTimeout(() => {
                    document.getElementById('edit_country_id').value = coupon.country_id || '';
                }, 50);
                this.editModalOpen = true;
            },
            copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    toastr.success('تم نسخ الكود بنجاح!');
                }).catch(err => {
                    toastr.error('فشل النسخ!');
                    console.error('Failed to copy: ', err);
                });
            },
            copySelected() {
                const codes = [];
                this.selectedCoupons.forEach(id => {
                    const checkbox = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                    if(checkbox) {
                        codes.push(checkbox.dataset.code);
                    }
                });
                if(codes.length > 0) {
                    this.copyToClipboard(codes.join('\\n'));
                }
            },
            submitBulkDelete() {
                if (this.selectedCoupons.length === 0) {
                    toastr.error('الرجاء تحديد كوبون واحد على الأقل.');
                    return;
                }
                if (confirm(`هل أنت متأكد من رغبتك في حذف الكوبونات المحددة (${this.selectedCoupons.length})؟`)) {
                    document.getElementById('bulkActionInput').value = 'bulk_delete';
                    const singleIdInput = document.getElementById('singleDeleteIdInput');
                    if(singleIdInput) singleIdInput.remove();
                    document.getElementById('bulkActionForm').submit();
                }
            },
            submitSingleDelete(id) {
                if (confirm('هل أنت متأكد من رغبتك في حذف هذا الكوبون؟')) {
                    document.getElementById('bulkActionInput').value = 'delete';
                    
                    document.querySelectorAll('input[name="coupon_ids[]"]').forEach(el => el.remove());

                    const form = document.getElementById('bulkActionForm');
                    let idInput = document.getElementById('singleDeleteIdInput');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.id = 'singleDeleteIdInput';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    form.submit();
                }
            },
            openExportModal() {
                this.exportModalOpen = true;
            },
            closeExportModal() {
                this.exportModalOpen = false;
            },
            submitExport() {
                window.location.href = this.exportUrl(this.exportType);
                this.closeExportModal();
            },
            exportUrl(type) {
                const url = new URL(window.location.href);
                url.searchParams.set('export', type);
                return url.toString();
            },
            init() {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-left",
                    "timeOut": "5000",
                };

                <?php if (isset($message)): ?>
                    toastr.success('<?= addslashes($message) ?>');
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    toastr.error('<?= addslashes($error) ?>');
                <?php endif; ?>
            }
        }
    }
</script>

</body>
</html> 