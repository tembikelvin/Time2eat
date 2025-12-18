<?php
/**
 * Admin Restaurant Edit View
 * Allows editing restaurant information
 */

// Set current page for sidebar highlighting
$currentPage = 'restaurants';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <div class="tw-flex tw-items-center tw-space-x-3 tw-mb-2">
                <a href="<?= url('/admin/restaurants/' . $restaurant['id']) ?>" class="tw-text-gray-500 hover:tw-text-gray-700">
                    <i data-feather="arrow-left" class="tw-h-5 tw-w-5"></i>
                </a>
                <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Edit Restaurant</h1>
            </div>
            <p class="tw-text-sm tw-text-gray-600">
                Update restaurant information and settings
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <a href="<?= url('/admin/restaurants/' . $restaurant['id']) ?>" class="tw-bg-gray-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-700">
                Cancel
            </a>
        </div>
    </div>
</div>

<!-- Edit Form -->
<form id="restaurant-form" class="tw-space-y-6">
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
        <!-- Main Form -->
        <div class="lg:tw-col-span-2 tw-space-y-6">
            <!-- Basic Information -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Basic Information</h2>
                </div>
                <div class="tw-p-6 tw-space-y-4">
                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Restaurant Name *</label>
                            <input type="text" id="name" name="name" value="<?= e($restaurant['name']) ?>" required
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label for="cuisine_type" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Cuisine Type</label>
                            <input type="text" id="cuisine_type" name="cuisine_type" value="<?= e($restaurant['cuisine_type'] ?? '') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                    </div>

                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label for="phone" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Phone *</label>
                            <input type="tel" id="phone" name="phone" value="<?= e($restaurant['phone']) ?>" required
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Email</label>
                            <input type="email" id="email" name="email" value="<?= e($restaurant['email'] ?? '') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"><?= e($restaurant['description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label for="address" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Address *</label>
                        <input type="text" id="address" name="address" value="<?= e($restaurant['address']) ?>" required
                               class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                    </div>

                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-4">
                        <div>
                            <label for="city" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">City</label>
                            <input type="text" id="city" name="city" value="<?= e($restaurant['city'] ?? 'Bamenda') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label for="state" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">State/Region</label>
                            <input type="text" id="state" name="state" value="<?= e($restaurant['state'] ?? 'Northwest') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label for="postal_code" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" value="<?= e($restaurant['postal_code'] ?? '') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                    </div>

                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label for="latitude" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Latitude</label>
                            <input type="text" id="latitude" name="latitude" value="<?= e($restaurant['latitude'] ?? '') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                   placeholder="e.g. 5.9631">
                        </div>
                        <div>
                            <label for="longitude" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Longitude</label>
                            <input type="text" id="longitude" name="longitude" value="<?= e($restaurant['longitude'] ?? '') ?>"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                                   placeholder="e.g. 10.1591">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Settings -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Business Settings</h2>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <!-- Delivery Fee Settings -->
                    <div>
                        <h3 class="tw-text-md tw-font-semibold tw-text-gray-800 tw-mb-3">Delivery Fee Structure</h3>
                        <div class="tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg tw-p-4 tw-mb-4">
                            <div class="tw-flex tw-items-start">
                                <svg class="tw-w-5 tw-h-5 tw-text-blue-600 tw-mt-0.5 tw-mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="tw-text-sm tw-text-blue-800">
                                    <p class="tw-font-medium">How delivery fees work:</p>
                                    <ul class="tw-mt-1 tw-ml-4 tw-list-disc tw-space-y-1">
                                        <li><strong>Free Zone Radius:</strong> Customers within this distance pay the base fee</li>
                                        <li><strong>Base Fee:</strong> Fixed fee for deliveries within the free zone</li>
                                        <li><strong>Extra Fee/km:</strong> Additional charge per km beyond the free zone</li>
                                        <li><strong>Example:</strong> 10km radius, 500 XAF base, 100 XAF/km extra → 15km delivery = 500 + (5 × 100) = 1000 XAF</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-4">
                            <div>
                                <label for="delivery_radius" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                    Free Zone Radius (km)
                                    <span class="tw-text-xs tw-text-gray-500 tw-block">Base fee applies within this distance</span>
                                </label>
                                <input type="number" id="delivery_radius" name="delivery_radius"
                                       value="<?= $restaurant['delivery_radius'] ?? 10 ?>"
                                       min="1" max="50" step="0.5"
                                       class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                            </div>
                            <div>
                                <label for="delivery_fee" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                    Base Delivery Fee (XAF)
                                    <span class="tw-text-xs tw-text-gray-500 tw-block">Within free zone</span>
                                </label>
                                <input type="number" id="delivery_fee" name="delivery_fee"
                                       value="<?= $restaurant['delivery_fee'] ?? 500 ?>"
                                       min="0" step="50"
                                       class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                            </div>
                            <div>
                                <label for="delivery_fee_per_extra_km" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                    Extra Fee per km (XAF)
                                    <span class="tw-text-xs tw-text-gray-500 tw-block">Beyond free zone</span>
                                </label>
                                <input type="number" id="delivery_fee_per_extra_km" name="delivery_fee_per_extra_km"
                                       value="<?= $restaurant['delivery_fee_per_extra_km'] ?? 100 ?>"
                                       min="0" step="10"
                                       class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Other Business Settings -->
                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
                        <div>
                            <label for="minimum_order" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Minimum Order (XAF)</label>
                            <input type="number" id="minimum_order" name="minimum_order" value="<?= $restaurant['minimum_order'] ?? 0 ?>" min="0" step="100"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label for="delivery_time" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Delivery Time (mins)</label>
                            <input type="number" id="delivery_time" name="delivery_time" value="<?= $restaurant['delivery_time'] ?? 30 ?>" min="10" step="5"
                                   class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        </div>
                        <div>
                            <label for="commission_rate" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">
                                Commission Rate (%)
                                <span class="tw-text-xs tw-text-gray-500 tw-block">Platform commission</span>
                            </label>
                            <div class="tw-relative">
                                <input type="number" id="commission_rate" name="commission_rate"
                                       value="<?= number_format(($restaurant['commission_rate'] ?? 0.15) * 100, 1) ?>"
                                       min="0" max="100" step="0.1"
                                       class="tw-w-full tw-px-3 tw-py-2 tw-pr-8 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                                <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-3 tw-pointer-events-none">
                                    <span class="tw-text-gray-500 tw-text-sm">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-flex tw-items-center">
                        <input type="checkbox" id="is_active" name="is_active" <?= ($restaurant['is_active'] ?? 0) ? 'checked' : '' ?>
                               class="tw-h-4 tw-w-4 tw-text-primary-600 tw-border-gray-300 tw-rounded focus:tw-ring-primary-500">
                        <label for="is_active" class="tw-ml-2 tw-block tw-text-sm tw-text-gray-900">
                            Restaurant is currently active and accepting orders
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="tw-space-y-6">
            <!-- Status -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Status</h2>
                </div>
                <div class="tw-p-6">
                    <label for="status" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Restaurant Status</label>
                    <select id="status" name="status"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                        <option value="pending" <?= ($restaurant['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="active" <?= ($restaurant['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= ($restaurant['status'] === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                        <option value="rejected" <?= ($restaurant['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <p class="tw-mt-2 tw-text-sm tw-text-gray-500">
                        Control the restaurant's availability on the platform
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Actions</h2>
                </div>
                <div class="tw-p-6 tw-space-y-3">
                    <button type="submit" class="tw-w-full tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                        <i data-feather="save" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        Save Changes
                    </button>
                    <a href="<?= url('/admin/restaurants/' . $restaurant['id']) ?>" class="tw-block tw-w-full tw-text-center tw-bg-gray-200 tw-text-gray-700 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-gray-300">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Restaurant Info -->
            <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
                <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
                    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Restaurant Info</h2>
                </div>
                <div class="tw-p-6 tw-space-y-3 tw-text-sm">
                    <div>
                        <div class="tw-font-medium tw-text-gray-900">ID</div>
                        <div class="tw-text-gray-500">#<?= $restaurant['id'] ?></div>
                    </div>
                    <div>
                        <div class="tw-font-medium tw-text-gray-900">Created</div>
                        <div class="tw-text-gray-500"><?= date('M j, Y', strtotime($restaurant['created_at'])) ?></div>
                    </div>
                    <div>
                        <div class="tw-font-medium tw-text-gray-900">Last Updated</div>
                        <div class="tw-text-gray-500"><?= date('M j, Y', strtotime($restaurant['updated_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('restaurant-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Handle checkbox
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="tw-animate-spin tw-h-4 tw-w-4 tw-inline tw-mr-2">⟳</i>Saving...';
    submitButton.disabled = true;
    
    // Send update request
    fetch('<?= url('/admin/restaurants/' . $restaurant['id'] . '/update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '<?= url('/admin/restaurants/' . $restaurant['id']) ?>';
        } else {
            alert('Error: ' + data.message);
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving changes.');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
});

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

