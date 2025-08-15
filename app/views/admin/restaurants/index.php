<?php
// Load the main layout
include_once APPROOT . '/views/includes/header.php';
?>
<div class="p-6" x-data="restaurantsPage()">
    <!-- Header and Summary Cards -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Manage Restaurants</h1>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <i class="fas fa-utensils text-blue-500 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 text-sm font-semibold">Total Restaurants</h3>
                    <p class="text-2xl font-bold text-gray-800"><?= count($restaurants ?? []) ?></p>
                </div>
            </div>
        </div>
    </div>
 
    
    <!-- Main Table and Actions -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <!-- Table Actions -->
        <div class="flex justify-between items-center mb-4">
            <div class="w-1/3">
                <input type="text" x-model="searchQuery" placeholder="Search by name, category, or city..." class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex space-x-2">
                <button @click="exportData('excel')" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md text-sm flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Export to Excel
                </button>
                <button @click="exportData('json')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-md text-sm flex items-center">
                    <i class="fas fa-file-code mr-2"></i> Export to JSON
                </button>
            </div>
        </div>

        <!-- Restaurants Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Restaurant</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Details</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Contact</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Created At</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <template x-for="restaurant in filteredRestaurants" :key="restaurant.id">
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="font-semibold" x-text="restaurant.name_en"></div>
                                <div class="text-xs text-gray-500" x-text="restaurant.name_ar"></div>
                            </td>
                            <td class="py-3 px-4">
                                <div x-text="restaurant.category"></div>
                                <div class="text-xs text-gray-500" x-text="restaurant.governorate + ', ' + restaurant.city"></div>
                            </td>
                            <td class="py-3 px-4">
                                <div x-text="restaurant.contact_name"></div>
                                <div class="text-xs text-gray-500" dir="ltr" x-text="restaurant.phone"></div>
                            </td>
                            <td class="py-3 px-4" dir="ltr" x-text="new Date(restaurant.created_at).toLocaleDateString()"></td>
                            <td class="py-3 px-4 text-center">
                                <template x-if="restaurant.pdf_path">
                                    <button @click="openPdf('<?= URLROOT ?>/admin/restaurants/view-pdf/' + restaurant.id)" class="text-red-500 hover:text-red-700 mx-1" title="View PDF">
                                        <i class="fas fa-file-pdf fa-lg"></i>
                                    </button>
                                </template>
                                <button @click="editRestaurant(restaurant.id)" class="text-blue-500 hover:text-blue-700 mx-1" title="Edit">
                                    <i class="fas fa-edit fa-lg"></i>
                                </button>
                                <button @click="confirmDelete(restaurant.id)" class="text-gray-500 hover:text-gray-700 mx-1" title="Delete">
                                    <i class="fas fa-trash-alt fa-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRestaurants.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">No restaurants found.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div x-show="pdfModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" @keydown.escape.window="closePdfModal()" style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl h-5/6 flex flex-col" @click.away="closePdfModal()">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold">PDF Preview</h3>
                <button @click="closePdfModal()" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
            </div>
            <div class="flex-grow p-4">
                <iframe :src="pdfUrl" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white rounded-lg p-6 shadow-xl" @click.away="closeDeleteModal()">
            <h3 class="text-lg font-bold mb-4">Confirm Deletion</h3>
            <p class="mb-6">Are you sure you want to delete this restaurant record? This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button @click="closeDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                <button @click="deleteRestaurant()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    function restaurantsPage() {
        return {
            searchQuery: '',
            restaurants: <?= json_encode($restaurants ?? []) ?>,
            pdfModalOpen: false,
            pdfUrl: '',
            deleteModalOpen: false,
            restaurantToDelete: null,

            get filteredRestaurants() {
                if (this.searchQuery === '') {
                    return this.restaurants;
                }
                return this.restaurants.filter(restaurant => {
                    const search = this.searchQuery.toLowerCase();
                    return (
                        restaurant.name_en?.toLowerCase().includes(search) ||
                        restaurant.name_ar?.toLowerCase().includes(search) ||
                        restaurant.category?.toLowerCase().includes(search) ||
                        restaurant.city?.toLowerCase().includes(search)
                    );
                });
            },

            openPdf(url) {
                this.pdfUrl = url;
                this.pdfModalOpen = true;
            },
            closePdfModal() {
                this.pdfModalOpen = false;
                this.pdfUrl = '';
            },
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
            editRestaurant(id) {
                window.location.href = `<?= URLROOT ?>/admin/restaurants/edit/${id}`;
            },
            exportData(format) {
                window.location.href = `<?= URLROOT ?>/admin/restaurants/export/${format}`;
            }
        };
    }
</script>

<?php
include_once APPROOT . '/views/includes/footer.php';
?>
