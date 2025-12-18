<?php
/**
 * Admin Delivery Zones Management Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'delivery-zones';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Delivery Zone Management</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Configure delivery zones and fees for all restaurants
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <button onclick="exportZones()" class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export
            </button>
            <button onclick="showBulkEditModal()" class="tw-bg-primary-600 tw-text-white tw-rounded-md tw-px-4 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-primary-700 tw-transition-colors">
                <i data-feather="edit-3" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Bulk Edit
            </button>
        </div>
    </div>
</div>

<!-- Zone Statistics -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-6 tw-mb-8">
    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Total Restaurants</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['total_restaurants'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">With delivery zones</p>
            </div>
            <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                <i data-feather="map-pin" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Delivery Radius</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['avg_radius'] ?? 0, 1) ?></p>
                <p class="tw-text-sm tw-text-gray-500">kilometers</p>
            </div>
            <div class="tw-p-3 tw-bg-green-100 tw-rounded-full">
                <i data-feather="target" class="tw-h-6 tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Avg Base Fee</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['avg_base_fee'] ?? 0) ?></p>
                <p class="tw-text-sm tw-text-gray-500">XAF</p>
            </div>
            <div class="tw-p-3 tw-bg-purple-100 tw-rounded-full">
                <i data-feather="dollar-sign" class="tw-h-6 tw-w-6 tw-text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-sm tw-font-medium tw-text-gray-600">Largest Zone</p>
                <p class="tw-text-3xl tw-font-bold tw-text-gray-900"><?= number_format($stats['max_radius'] ?? 0, 1) ?></p>
                <p class="tw-text-sm tw-text-gray-500">km radius</p>
            </div>
            <div class="tw-p-3 tw-bg-yellow-100 tw-rounded-full">
                <i data-feather="maximize-2" class="tw-h-6 tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Info Banner -->
<div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-border tw-border-blue-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-start">
        <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
            <i data-feather="info" class="tw-h-6 tw-w-6 tw-text-blue-600"></i>
        </div>
        <div class="tw-ml-4 tw-flex-1">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">How Delivery Zones Work</h3>
            <p class="tw-text-sm tw-text-gray-600 tw-mt-1">
                Each restaurant has a <strong>delivery radius</strong> (free zone) where the <strong>base fee</strong> applies. 
                Beyond this radius, an <strong>extra fee per km</strong> is added. Maximum delivery distance is <strong>2× the radius</strong>.
            </p>
            <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-4 tw-text-sm">
                <div class="tw-flex tw-items-center">
                    <span class="tw-w-3 tw-h-3 tw-bg-green-500 tw-rounded-full tw-mr-2"></span>
                    <span class="tw-text-gray-700">Within radius: Base fee only</span>
                </div>
                <div class="tw-flex tw-items-center">
                    <span class="tw-w-3 tw-h-3 tw-bg-yellow-500 tw-rounded-full tw-mr-2"></span>
                    <span class="tw-text-gray-700">Beyond radius: Base + extra fee</span>
                </div>
                <div class="tw-flex tw-items-center">
                    <span class="tw-w-3 tw-h-3 tw-bg-red-500 tw-rounded-full tw-mr-2"></span>
                    <span class="tw-text-gray-700">Beyond 2× radius: Delivery rejected</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6 tw-mb-8">
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-4">
        <div class="md:tw-col-span-2">
            <label for="search" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Search Restaurants</label>
            <div class="tw-relative">
                <input type="text" id="search" placeholder="Search by name, location..." 
                       class="tw-w-full tw-pl-10 tw-pr-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                       onkeyup="filterZones()">
                <i data-feather="search" class="tw-h-5 tw-w-5 tw-text-gray-400 tw-absolute tw-left-3 tw-top-2.5"></i>
            </div>
        </div>
        <div>
            <label for="sortBy" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Sort By</label>
            <select id="sortBy" class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                    onchange="sortZones()">
                <option value="name">Restaurant Name</option>
                <option value="radius">Delivery Radius</option>
                <option value="base_fee">Base Fee</option>
                <option value="extra_fee">Extra Fee/km</option>
            </select>
        </div>
        <div>
            <label for="filterCity" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Filter by City</label>
            <select id="filterCity" class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                    onchange="filterZones()">
                <option value="">All Cities</option>
                <option value="Bamenda">Bamenda</option>
                <option value="Douala">Douala</option>
                <option value="Yaoundé">Yaoundé</option>
            </select>
        </div>
    </div>
</div>

<!-- Delivery Zones Table -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-items-center tw-justify-between">
            <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900">Restaurant Delivery Zones</h2>
            <span class="tw-text-sm tw-text-gray-500">Showing <?= count($zones ?? []) ?> restaurants</span>
        </div>
    </div>

    <div class="tw-overflow-x-auto">
        <table class="tw-min-w-full tw-divide-y tw-divide-gray-200" id="zonesTable">
            <thead class="tw-bg-gray-50">
                <tr>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="tw-rounded tw-border-gray-300">
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Restaurant
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Location
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Delivery Radius
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Base Fee
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Extra Fee/km
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Max Distance
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Min Order
                    </th>
                    <th class="tw-px-6 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="zonesTableBody">
                <?php if (!empty($zones)): ?>
                    <?php foreach ($zones as $zone): ?>
                        <tr class="hover:tw-bg-gray-50 tw-transition-colors zone-row" 
                            data-name="<?= strtolower($zone['name']) ?>"
                            data-city="<?= $zone['city'] ?? '' ?>"
                            data-radius="<?= $zone['delivery_radius'] ?>"
                            data-base-fee="<?= $zone['delivery_fee'] ?>"
                            data-extra-fee="<?= $zone['delivery_fee_per_extra_km'] ?>">
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <input type="checkbox" class="zone-checkbox tw-rounded tw-border-gray-300" value="<?= $zone['id'] ?>">
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <div class="tw-h-10 tw-w-10 tw-flex-shrink-0">
                                        <?php if (!empty($zone['image'])): ?>
                                            <img class="tw-h-10 tw-w-10 tw-rounded-full tw-object-cover" src="<?= $zone['image'] ?>" alt="">
                                        <?php else: ?>
                                            <div class="tw-h-10 tw-w-10 tw-rounded-full tw-bg-gray-200 tw-flex tw-items-center tw-justify-center">
                                                <i data-feather="home" class="tw-h-5 tw-w-5 tw-text-gray-500"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tw-ml-4">
                                        <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= htmlspecialchars($zone['name']) ?></div>
                                        <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($zone['phone'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4">
                                <div class="tw-text-sm tw-text-gray-900"><?= htmlspecialchars($zone['address'] ?? 'N/A') ?></div>
                                <div class="tw-text-sm tw-text-gray-500"><?= htmlspecialchars($zone['city'] ?? 'N/A') ?></div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-flex tw-items-center">
                                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                        <i data-feather="target" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                        <?= number_format($zone['delivery_radius'], 1) ?> km
                                    </span>
                                </div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-text-sm tw-font-medium tw-text-gray-900"><?= number_format($zone['delivery_fee']) ?> XAF</div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-text-sm tw-text-gray-900"><?= number_format($zone['delivery_fee_per_extra_km']) ?> XAF</div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-text-sm tw-text-gray-500"><?= number_format($zone['delivery_radius'] * 2, 1) ?> km</div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                                <div class="tw-text-sm tw-text-gray-900"><?= number_format($zone['minimum_order']) ?> XAF</div>
                            </td>
                            <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-right tw-text-sm tw-font-medium">
                                <button onclick="editZone(<?= $zone['id'] ?>)" class="tw-text-primary-600 hover:tw-text-primary-900 tw-mr-3" title="Edit Zone">
                                    <i data-feather="edit-2" class="tw-h-4 tw-w-4"></i>
                                </button>
                                <a href="<?= url('/admin/restaurants/' . $zone['id'] . '/edit') ?>" class="tw-text-gray-600 hover:tw-text-gray-900" title="View Restaurant">
                                    <i data-feather="external-link" class="tw-h-4 tw-w-4"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="tw-px-6 tw-py-12 tw-text-center">
                            <div class="tw-flex tw-flex-col tw-items-center tw-justify-center">
                                <i data-feather="map-pin" class="tw-h-12 tw-w-12 tw-text-gray-400 tw-mb-4"></i>
                                <p class="tw-text-gray-500 tw-text-sm">No delivery zones found</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Zone Modal -->
<div id="editZoneModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50 tw-p-4">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-2xl tw-max-w-2xl tw-w-full tw-max-h-[90vh] tw-overflow-y-auto">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h3 class="tw-text-xl tw-font-bold tw-text-gray-900">Edit Delivery Zone</h3>
                <button onclick="closeEditModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-6 tw-w-6"></i>
                </button>
            </div>
        </div>
        <form id="editZoneForm" onsubmit="saveZone(event)">
            <div class="tw-p-6 tw-space-y-6">
                <input type="hidden" id="edit_restaurant_id" name="restaurant_id">
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                    <div>
                        <label for="edit_delivery_radius" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                            Delivery Radius (km)
                            <span class="tw-text-xs tw-text-gray-500 tw-block tw-font-normal">Free zone where base fee applies</span>
                        </label>
                        <input type="number" id="edit_delivery_radius" name="delivery_radius" step="0.5" min="1" max="50" required
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                    </div>
                    
                    <div>
                        <label for="edit_delivery_fee" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                            Base Delivery Fee (XAF)
                            <span class="tw-text-xs tw-text-gray-500 tw-block tw-font-normal">Fee within free zone</span>
                        </label>
                        <input type="number" id="edit_delivery_fee" name="delivery_fee" step="50" min="0" required
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                    </div>
                    
                    <div>
                        <label for="edit_delivery_fee_per_extra_km" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                            Extra Fee per KM (XAF)
                            <span class="tw-text-xs tw-text-gray-500 tw-block tw-font-normal">Added beyond free zone</span>
                        </label>
                        <input type="number" id="edit_delivery_fee_per_extra_km" name="delivery_fee_per_extra_km" step="10" min="0" required
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                    </div>
                    
                    <div>
                        <label for="edit_minimum_order" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                            Minimum Order (XAF)
                            <span class="tw-text-xs tw-text-gray-500 tw-block tw-font-normal">Minimum order amount</span>
                        </label>
                        <input type="number" id="edit_minimum_order" name="minimum_order" step="100" min="0" required
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500">
                    </div>
                </div>
                
                <!-- Preview Calculation -->
                <div class="tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg tw-p-4">
                    <h4 class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Zone Preview</h4>
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-text-sm">
                        <div>
                            <span class="tw-text-gray-600">Max Delivery Distance:</span>
                            <span class="tw-font-semibold tw-text-gray-900" id="preview_max_distance">-</span>
                        </div>
                        <div>
                            <span class="tw-text-gray-600">Example (15 km):</span>
                            <span class="tw-font-semibold tw-text-gray-900" id="preview_example_fee">-</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tw-p-6 tw-border-t tw-border-gray-200 tw-flex tw-justify-end tw-space-x-3">
                <button type="button" onclick="closeEditModal()" class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="tw-px-4 tw-py-2 tw-bg-primary-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulkEditModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50 tw-p-4">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-2xl tw-max-w-2xl tw-w-full">
        <div class="tw-p-6 tw-border-b tw-border-gray-200">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h3 class="tw-text-xl tw-font-bold tw-text-gray-900">Bulk Edit Delivery Zones</h3>
                <button onclick="closeBulkEditModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-6 tw-w-6"></i>
                </button>
            </div>
        </div>
        <form id="bulkEditForm" onsubmit="saveBulkEdit(event)">
            <div class="tw-p-6 tw-space-y-6">
                <div class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4">
                    <p class="tw-text-sm tw-text-yellow-800">
                        <i data-feather="alert-triangle" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                        This will update <span id="selectedCount" class="tw-font-semibold">0</span> selected restaurants.
                    </p>
                </div>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                    <div>
                        <label class="tw-flex tw-items-center tw-mb-2">
                            <input type="checkbox" id="bulk_update_radius" class="tw-rounded tw-border-gray-300 tw-mr-2">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Update Delivery Radius</span>
                        </label>
                        <input type="number" id="bulk_delivery_radius" name="delivery_radius" step="0.5" min="1" max="50"
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                               disabled>
                    </div>
                    
                    <div>
                        <label class="tw-flex tw-items-center tw-mb-2">
                            <input type="checkbox" id="bulk_update_fee" class="tw-rounded tw-border-gray-300 tw-mr-2">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Update Base Fee</span>
                        </label>
                        <input type="number" id="bulk_delivery_fee" name="delivery_fee" step="50" min="0"
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                               disabled>
                    </div>
                    
                    <div>
                        <label class="tw-flex tw-items-center tw-mb-2">
                            <input type="checkbox" id="bulk_update_extra_fee" class="tw-rounded tw-border-gray-300 tw-mr-2">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Update Extra Fee/km</span>
                        </label>
                        <input type="number" id="bulk_delivery_fee_per_extra_km" name="delivery_fee_per_extra_km" step="10" min="0"
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                               disabled>
                    </div>
                    
                    <div>
                        <label class="tw-flex tw-items-center tw-mb-2">
                            <input type="checkbox" id="bulk_update_min_order" class="tw-rounded tw-border-gray-300 tw-mr-2">
                            <span class="tw-text-sm tw-font-medium tw-text-gray-700">Update Min Order</span>
                        </label>
                        <input type="number" id="bulk_minimum_order" name="minimum_order" step="100" min="0"
                               class="tw-w-full tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500"
                               disabled>
                    </div>
                </div>
            </div>
            
            <div class="tw-p-6 tw-border-t tw-border-gray-200 tw-flex tw-justify-end tw-space-x-3">
                <button type="button" onclick="closeBulkEditModal()" class="tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="tw-px-4 tw-py-2 tw-bg-primary-600 tw-text-white tw-rounded-md tw-text-sm tw-font-medium hover:tw-bg-primary-700">
                    Update Selected
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Filter and search functions
function filterZones() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const cityFilter = document.getElementById('filterCity').value.toLowerCase();
    const rows = document.querySelectorAll('.zone-row');
    
    rows.forEach(row => {
        const name = row.dataset.name;
        const city = row.dataset.city.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm);
        const matchesCity = !cityFilter || city === cityFilter;
        
        row.style.display = (matchesSearch && matchesCity) ? '' : 'none';
    });
}

function sortZones() {
    const sortBy = document.getElementById('sortBy').value;
    const tbody = document.getElementById('zonesTableBody');
    const rows = Array.from(tbody.querySelectorAll('.zone-row'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            case 'name':
                aVal = a.dataset.name;
                bVal = b.dataset.name;
                return aVal.localeCompare(bVal);
            case 'radius':
                aVal = parseFloat(a.dataset.radius);
                bVal = parseFloat(b.dataset.radius);
                return bVal - aVal;
            case 'base_fee':
                aVal = parseFloat(a.dataset.baseFee);
                bVal = parseFloat(b.dataset.baseFee);
                return bVal - aVal;
            case 'extra_fee':
                aVal = parseFloat(a.dataset.extraFee);
                bVal = parseFloat(b.dataset.extraFee);
                return bVal - aVal;
            default:
                return 0;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Selection functions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.zone-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.zone-checkbox:checked').length;
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.textContent = selected;
}

// Edit zone modal
function editZone(restaurantId) {
    // Fetch zone data and populate modal
    fetch(`<?= url('/api/admin/delivery-zones/') ?>${restaurantId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_restaurant_id').value = data.zone.id;
                document.getElementById('edit_delivery_radius').value = data.zone.delivery_radius;
                document.getElementById('edit_delivery_fee').value = data.zone.delivery_fee;
                document.getElementById('edit_delivery_fee_per_extra_km').value = data.zone.delivery_fee_per_extra_km;
                document.getElementById('edit_minimum_order').value = data.zone.minimum_order;
                
                updatePreview();
                document.getElementById('editZoneModal').classList.remove('tw-hidden');
            }
        })
        .catch(error => {
            console.error('Error fetching zone data:', error);
            alert('Error loading zone data');
        });
}

function closeEditModal() {
    document.getElementById('editZoneModal').classList.add('tw-hidden');
}

function updatePreview() {
    const radius = parseFloat(document.getElementById('edit_delivery_radius').value) || 0;
    const baseFee = parseFloat(document.getElementById('edit_delivery_fee').value) || 0;
    const extraFee = parseFloat(document.getElementById('edit_delivery_fee_per_extra_km').value) || 0;
    
    const maxDistance = radius * 2;
    const exampleDistance = 15;
    const exampleFee = exampleDistance <= radius ? baseFee : baseFee + ((exampleDistance - radius) * extraFee);
    
    document.getElementById('preview_max_distance').textContent = maxDistance.toFixed(1) + ' km';
    document.getElementById('preview_example_fee').textContent = Math.round(exampleFee) + ' XAF';
}

// Listen for input changes to update preview
['edit_delivery_radius', 'edit_delivery_fee', 'edit_delivery_fee_per_extra_km'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updatePreview);
});

function saveZone(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const restaurantId = formData.get('restaurant_id');
    
    fetch(`<?= url('/api/admin/delivery-zones/') ?>${restaurantId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Delivery zone updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update zone'));
        }
    })
    .catch(error => {
        console.error('Error saving zone:', error);
        alert('Error saving zone');
    });
}

// Bulk edit modal
function showBulkEditModal() {
    const selected = document.querySelectorAll('.zone-checkbox:checked').length;
    if (selected === 0) {
        alert('Please select at least one restaurant');
        return;
    }
    
    updateSelectedCount();
    document.getElementById('bulkEditModal').classList.remove('tw-hidden');
}

function closeBulkEditModal() {
    document.getElementById('bulkEditModal').classList.add('tw-hidden');
}

// Enable/disable bulk edit inputs based on checkboxes
['bulk_update_radius', 'bulk_update_fee', 'bulk_update_extra_fee', 'bulk_update_min_order'].forEach(checkboxId => {
    const checkbox = document.getElementById(checkboxId);
    const inputId = checkboxId.replace('bulk_update_', 'bulk_');
    const input = document.getElementById(inputId);
    
    if (checkbox && input) {
        checkbox.addEventListener('change', () => {
            input.disabled = !checkbox.checked;
        });
    }
});

function saveBulkEdit(event) {
    event.preventDefault();
    
    const selectedIds = Array.from(document.querySelectorAll('.zone-checkbox:checked')).map(cb => cb.value);
    const updates = {};
    
    if (document.getElementById('bulk_update_radius').checked) {
        updates.delivery_radius = document.getElementById('bulk_delivery_radius').value;
    }
    if (document.getElementById('bulk_update_fee').checked) {
        updates.delivery_fee = document.getElementById('bulk_delivery_fee').value;
    }
    if (document.getElementById('bulk_update_extra_fee').checked) {
        updates.delivery_fee_per_extra_km = document.getElementById('bulk_delivery_fee_per_extra_km').value;
    }
    if (document.getElementById('bulk_update_min_order').checked) {
        updates.minimum_order = document.getElementById('bulk_minimum_order').value;
    }
    
    if (Object.keys(updates).length === 0) {
        alert('Please select at least one field to update');
        return;
    }
    
    fetch('<?= url('/api/admin/delivery-zones/bulk-update') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            restaurant_ids: selectedIds,
            updates: updates
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully updated ${data.updated_count} restaurants!`);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update zones'));
        }
    })
    .catch(error => {
        console.error('Error saving bulk edit:', error);
        alert('Error saving changes');
    });
}

function exportZones() {
    window.location.href = '<?= url('/api/admin/delivery-zones/export') ?>';
}

// Update selected count when checkboxes change
document.addEventListener('change', (e) => {
    if (e.target.classList.contains('zone-checkbox')) {
        updateSelectedCount();
    }
});
</script>

