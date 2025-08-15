<?php
// Load the main layout
include_once APPROOT . '/views/includes/header.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Restaurant: <?= htmlspecialchars($restaurant['name_en']) ?></h1>
        <a href="<?= URLROOT ?>/admin/restaurants" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg shadow-md">
        <form action="<?= URLROOT ?>/admin/restaurants/update/<?= $restaurant['id'] ?>" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name (EN) -->
                <div>
                    <label for="name_en" class="block text-sm font-medium text-gray-700">Name (English)</label>
                    <input type="text" id="name_en" name="name_en" value="<?= htmlspecialchars($restaurant['name_en']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Name (AR) -->
                <div>
                    <label for="name_ar" class="block text-sm font-medium text-gray-700">Name (Arabic)</label>
                    <input type="text" id="name_ar" name="name_ar" value="<?= htmlspecialchars($restaurant['name_ar']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <input type="text" id="category" name="category" value="<?= htmlspecialchars($restaurant['category']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($restaurant['phone']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Governorate -->
                <div>
                    <label for="governorate" class="block text-sm font-medium text-gray-700">Governorate</label>
                    <input type="text" id="governorate" name="governorate" value="<?= htmlspecialchars($restaurant['governorate']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($restaurant['city']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea id="address" name="address" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($restaurant['address']) ?></textarea>
                </div>
                
                <!-- Contact Name -->
                <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700">Contact Name</label>
                    <input type="text" id="contact_name" name="contact_name" value="<?= htmlspecialchars($restaurant['contact_name']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($restaurant['email']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Is Chain -->
                <div class="flex items-center">
                    <input type="checkbox" id="is_chain" name="is_chain" value="1" <?= $restaurant['is_chain'] ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_chain" class="ml-2 block text-sm text-gray-900">Is this a chain?</label>
                </div>
                
                <!-- Number of Stores -->
                <div>
                    <label for="num_stores" class="block text-sm font-medium text-gray-700">Number of Stores</label>
                    <input type="number" id="num_stores" name="num_stores" value="<?= htmlspecialchars($restaurant['num_stores']) ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- PDF Upload -->
                <div class="md:col-span-2">
                    <label for="pdf" class="block text-sm font-medium text-gray-700">Update PDF Agreement</label>
                    <input type="file" id="pdf" name="pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <?php if ($restaurant['pdf_path']) : ?>
                        <p class="text-xs text-gray-500 mt-1">Current file: <a href="<?= URLROOT ?>/admin/restaurants/view-pdf/<?= $restaurant['id'] ?>" target="_blank" class="text-blue-500">View PDF</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-md">
                    Update Restaurant
                </button>
            </div>
        </form>
    </div>
</div>

<?php
include_once APPROOT . '/views/includes/footer.php';
?>
