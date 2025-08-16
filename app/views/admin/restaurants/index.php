<?php include_once APPROOT . '/views/includes/header.php'; ?>

<div class="p-6 bg-gray-50 min-h-screen" x-data="restaurantsPage()">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800">Restaurants Dashboard</h1>
        <p class="text-gray-500 mt-1">Manage and monitor all restaurant activities.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center border-l-4 border-blue-500">
            <div class="bg-blue-100 rounded-full p-3 mr-4"><i class="fas fa-utensils text-blue-500 text-2xl"></i></div>
            <div>
                <h3 class="text-gray-500 text-sm font-semibold">Total Restaurants</h3>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['total_restaurants'] ?? 0 ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center border-l-4 border-green-500">
            <div class="bg-green-100 rounded-full p-3 mr-4"><i class="fas fa-user-check text-green-500 text-2xl"></i></div>
            <div>
                <h3 class="text-gray-500 text-sm font-semibold">Referred Registrations</h3>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['referred_registrations'] ?? 0 ?></p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center border-l-4 border-yellow-500">
            <div class="bg-yellow-100 rounded-full p-3 mr-4"><i class="fas fa-user-plus text-yellow-500 text-2xl"></i></div>
            <div>
                <h3 class="text-gray-500 text-sm font-semibold">Direct Registrations</h3>
                <p class="text-3xl font-bold text-gray-800"><?= $stats['direct_registrations'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
        <form action="<?= URLROOT ?>/admin/restaurants" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-center">
                <input type="text" name="search" placeholder="Search by name, phone..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" class="px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                <select name="governorate" class="px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Governorates</option>
                    <?php foreach ($governorates as $gov) : ?><option value="<?= htmlspecialchars($gov) ?>" <?= ($filters['governorate'] ?? '') == $gov ? 'selected' : '' ?>><?= htmlspecialchars($gov) ?></option><?php endforeach; ?>
                </select>
                <select name="category" class="px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat) : ?><option value="<?= htmlspecialchars($cat) ?>" <?= ($filters['category'] ?? '') == $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option><?php endforeach; ?>
                </select>
                <select name="marketer" class="px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Marketers</option>
                    <?php foreach ($marketers as $marketer) : ?><option value="<?= $marketer['id'] ?>" <?= ($filters['marketer'] ?? '') == $marketer['id'] ? 'selected' : '' ?>><?= htmlspecialchars($marketer['username']) ?></option><?php endforeach; ?>
                </select>
                <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>" class="px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
                <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>" class="px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500 xl:col-start-2">
                <div class="flex space-x-2 xl:col-start-3">
                    <button type="submit" class="w-full bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700">Filter</button>
                    <a href="<?= URLROOT ?>/admin/restaurants" class="w-full text-center bg-gray-200 text-gray-700 px-5 py-2 rounded-md hover:bg-gray-300">Reset</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Main Table Area -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Table Actions -->
        <div class="p-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-700">All Restaurants <span class="text-sm text-gray-500">(Total: <?= $totalRecords ?>)</span></h2>
            <div class="flex space-x-2">
                <button @click="exportData('excel')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md text-sm">Export Excel</button>
                <button @click="exportData('json')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md text-sm">Export JSON</button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Restaurant</th>
                        <th class="py-3 px-4 text-left">Contact Person</th>
                        <th class="py-3 px-4 text-left">Full Address</th>
                        <th class="py-3 px-4 text-left">Phone</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Referred By</th>
                        <th class="py-3 px-4 text-left">Created At</th>
                        <th class="py-3 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($restaurants)) : ?>
                        <tr><td colspan="9" class="text-center py-10 text-gray-500">No restaurants found.</td></tr>
                    <?php else : ?>
                        <?php foreach ($restaurants as $restaurant) : ?>
                            <tr class="border-b hover:bg-gray-100">
                                <td class="py-3 px-4"><?= $restaurant['id'] ?></td>
                                <td class="py-3 px-4"><div class="font-semibold"><?= htmlspecialchars($restaurant['name_en']) ?></div><div class="text-xs text-gray-500"><?= htmlspecialchars($restaurant['name_ar']) ?></div></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($restaurant['contact_name']) ?></td>
                                <td class="py-3 px-4 text-sm"><?= htmlspecialchars($restaurant['address'] . ', ' . $restaurant['city'] . ', ' . $restaurant['governorate']) ?></td>
                                <td class="py-3 px-4" dir="ltr"><?= htmlspecialchars($restaurant['phone']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($restaurant['email']) ?></td>
                                <td class="py-3 px-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= $restaurant['marketer_name'] ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800' ?>"><?= htmlspecialchars($restaurant['marketer_name'] ?? 'Direct') ?></span></td>
                                <td class="py-3 px-4" dir="ltr"><?= date('Y-m-d H:i', strtotime($restaurant['created_at'])) ?></td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($restaurant['pdf_path']) : ?><a href="<?= URLROOT ?>/admin/restaurants/view-pdf/<?= $restaurant['id'] ?>" target="_blank" class="text-red-500 hover:text-red-700 mx-1"><i class="fas fa-file-pdf"></i></a><?php endif; ?>
                                    <a href="<?= URLROOT ?>/admin/restaurants/edit/<?= $restaurant['id'] ?>" class="text-blue-500 hover:text-blue-700 mx-1"><i class="fas fa-edit"></i></a>
                                    <button @click="confirmDelete(<?= $restaurant['id'] ?>)" class="text-gray-500 hover:text-gray-700 mx-1"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t">
            <?php include_once APPROOT . '/views/includes/_pagination.php'; ?>
        </div>
    </div>
</div>

<script>
    function restaurantsPage() {
        return {
            deleteModalOpen: false,
            restaurantToDelete: null,
            confirmDelete(id) {
                this.restaurantToDelete = id;
                this.deleteModalOpen = true;
            },
            closeDeleteModal() {
                this.deleteModalOpen = false;
                this.restaurantToDelete = null;
            },
            deleteRestaurant() {
                if (this.restaurantToDelete) {
                    window.location.href = `<?= URLROOT ?>/admin/restaurants/delete/${this.restaurantToDelete}`;
                }
            },
            exportData(format) {
                const url = new URL('<?= URLROOT ?>/admin/restaurants/export/' + format);
                const params = new URLSearchParams(window.location.search);
                url.search = params;
                window.location.href = url.toString();
            }
        };
    }
</script>

<?php include_once APPROOT . '/views/includes/footer.php'; ?>
