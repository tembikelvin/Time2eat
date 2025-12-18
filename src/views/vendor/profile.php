<?php
/**
 * Vendor Restaurant Profile Page - Modern Design
 * Mobile-first responsive design with gradient accents
 */

$restaurant = $restaurant ?? [];
$errors = $errors ?? [];
$success = $success ?? '';
?>

<!-- Page Header -->
<div class="tw-mb-6 md:tw-mb-8">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-w-12 tw-h-12 md:tw-w-14 md:tw-h-14 tw-rounded-2xl tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                <i data-feather="home" class="tw-h-6 tw-w-6 md:tw-h-7 md:tw-w-7 tw-text-white"></i>
            </div>
            <div>
                <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-gray-900">Restaurant Profile</h1>
                <p class="tw-text-sm tw-text-gray-600 tw-mt-1 tw-flex tw-items-center">
                    <i data-feather="info" class="tw-h-4 tw-w-4 tw-mr-1"></i>
                    Manage your restaurant information and settings
                </p>
            </div>
        </div>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
            <button type="button" onclick="previewProfile()"
                    class="tw-px-4 md:tw-px-6 tw-py-2.5 md:tw-py-3 tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 tw-rounded-xl tw-font-medium tw-shadow-md hover:tw-shadow-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-gap-2">
                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                <span class="tw-hidden md:tw-inline">Preview</span>
                <span class="md:tw-hidden">Preview</span>
            </button>
            <button type="submit" form="profile-form" id="save-btn"
                    class="tw-px-4 md:tw-px-6 tw-py-2.5 md:tw-py-3 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-xl tw-font-medium tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-flex tw-items-center tw-gap-2">
                <i data-feather="save" class="tw-h-4 tw-w-4"></i>
                <span class="tw-hidden md:tw-inline">Save Changes</span>
                <span class="md:tw-hidden">Save</span>
            </button>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success): ?>
    <div class="tw-mb-6 tw-p-4 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-xl tw-shadow-sm">
        <div class="tw-flex tw-items-center">
            <i data-feather="check-circle" class="tw-w-5 tw-h-5 tw-text-green-600 tw-mr-3 tw-flex-shrink-0"></i>
            <span class="tw-text-green-800 tw-font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="tw-mb-6 tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-shadow-sm">
        <div class="tw-flex tw-items-start">
            <i data-feather="alert-circle" class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-3 tw-mt-0.5 tw-flex-shrink-0"></i>
            <div class="tw-text-red-800">
                <?php foreach ($errors as $error): ?>
                    <div class="tw-mb-1"><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Profile Form -->
<form id="profile-form" method="POST" action="<?= url('/vendor/profile') ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6 md:tw-gap-8">

        <!-- Left Column - Restaurant Card -->
        <div class="lg:tw-col-span-1">
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6 md:tw-p-8 tw-text-center tw-sticky tw-top-8">
                <!-- Restaurant Logo -->
                <div class="tw-relative tw-inline-block tw-mb-6">
                    <div class="tw-w-32 tw-h-32 tw-rounded-2xl tw-overflow-hidden tw-bg-gradient-to-br tw-from-orange-100 tw-to-red-100 tw-mx-auto tw-shadow-xl tw-border-4 tw-border-white">
                        <?php if (!empty($restaurant['logo'])): ?>
                            <img id="logo-preview" src="<?= htmlspecialchars($restaurant['logo']) ?>" alt="Restaurant Logo" class="tw-w-full tw-h-full tw-object-cover">
                        <?php else: ?>
                            <div id="logo-placeholder" class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-4xl tw-font-bold tw-text-gray-600">
                                <?= strtoupper(substr($restaurant['name'] ?? 'R', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="document.getElementById('logo-input').click()" class="tw-absolute tw-bottom-2 tw-right-2 tw-w-10 tw-h-10 tw-bg-orange-600 tw-text-white tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg hover:tw-bg-orange-700 tw-transition-all tw-duration-200 tw-border-2 tw-border-white">
                        <i data-feather="camera" class="tw-w-5 tw-h-5"></i>
                    </button>
                </div>
                <input type="file" id="logo-input" name="logo" accept="image/*" class="tw-hidden" onchange="previewLogo(this)">

                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2"><?= htmlspecialchars($restaurant['name'] ?? 'Restaurant Name') ?></h2>
                <p class="tw-text-gray-600 tw-mb-4"><?= htmlspecialchars($restaurant['cuisine_type'] ?? 'Cuisine Type') ?></p>

                <!-- Status Badge -->
                <div class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-<?= ($restaurant['status'] ?? 'pending') === 'active' ? 'green' : 'yellow' ?>-50 tw-text-<?= ($restaurant['status'] ?? 'pending') === 'active' ? 'green' : 'yellow' ?>-700 tw-rounded-full tw-text-sm tw-font-semibold tw-border tw-border-<?= ($restaurant['status'] ?? 'pending') === 'active' ? 'green' : 'yellow' ?>-200 tw-mb-6">
                    <i data-feather="<?= ($restaurant['status'] ?? 'pending') === 'active' ? 'check-circle' : 'clock' ?>" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                    <?= ucfirst($restaurant['status'] ?? 'Pending') ?>
                </div>

                <!-- Quick Stats -->
                <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-pt-6 tw-border-t tw-border-gray-200">
                    <div class="tw-text-center">
                        <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($restaurant['rating'] ?? 0, 1) ?></div>
                        <div class="tw-text-xs tw-text-gray-600 tw-mt-1">Rating</div>
                    </div>
                    <div class="tw-text-center">
                        <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= number_format($restaurant['total_orders'] ?? 0) ?></div>
                        <div class="tw-text-xs tw-text-gray-600 tw-mt-1">Orders</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Form Sections -->
        <div class="lg:tw-col-span-2 tw-space-y-6">

            <!-- Basic Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="info" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Basic Information</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label for="name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Restaurant Name *</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($restaurant['name'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                                   placeholder="Enter restaurant name" required>
                        </div>
                        <div>
                            <label for="cuisine_type" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Cuisine Type *</label>
                            <select name="cuisine_type" id="cuisine_type"
                                    class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all">
                                <option value="">Select cuisine type</option>
                                <option value="african" <?= ($restaurant['cuisine_type'] ?? '') === 'african' ? 'selected' : '' ?>>African</option>
                                <option value="chinese" <?= ($restaurant['cuisine_type'] ?? '') === 'chinese' ? 'selected' : '' ?>>Chinese</option>
                                <option value="fast_food" <?= ($restaurant['cuisine_type'] ?? '') === 'fast_food' ? 'selected' : '' ?>>Fast Food</option>
                                <option value="italian" <?= ($restaurant['cuisine_type'] ?? '') === 'italian' ? 'selected' : '' ?>>Italian</option>
                                <option value="continental" <?= ($restaurant['cuisine_type'] ?? '') === 'continental' ? 'selected' : '' ?>>Continental</option>
                                <option value="local" <?= ($restaurant['cuisine_type'] ?? '') === 'local' ? 'selected' : '' ?>>Local</option>
                                <option value="asian" <?= ($restaurant['cuisine_type'] ?? '') === 'asian' ? 'selected' : '' ?>>Asian</option>
                                <option value="mexican" <?= ($restaurant['cuisine_type'] ?? '') === 'mexican' ? 'selected' : '' ?>>Mexican</option>
                                <option value="seafood" <?= ($restaurant['cuisine_type'] ?? '') === 'seafood' ? 'selected' : '' ?>>Seafood</option>
                                <option value="vegetarian" <?= ($restaurant['cuisine_type'] ?? '') === 'vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                                <option value="other" <?= ($restaurant['cuisine_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="md:tw-col-span-2">
                            <label for="description" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Description</label>
                            <textarea name="description" id="description" rows="4"
                                      class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-resize-none"
                                      placeholder="Tell customers about your restaurant, specialties, and what makes you unique..."><?= htmlspecialchars($restaurant['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="phone" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Contact Information</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label for="phone" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Phone Number *</label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <i data-feather="phone" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                                </div>
                                <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($restaurant['phone'] ?? '') ?>"
                                       class="tw-w-full tw-pl-11 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                       placeholder="+237 123 456 789" required>
                            </div>
                        </div>
                        <div>
                            <label for="email" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Email Address *</label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <i data-feather="mail" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                                </div>
                                <input type="email" name="email" id="email" value="<?= htmlspecialchars($restaurant['email'] ?? '') ?>"
                                       class="tw-w-full tw-pl-11 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                       placeholder="restaurant@example.com" required>
                            </div>
                        </div>
                        <div class="md:tw-col-span-2">
                            <label for="address" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Street Address *</label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                                </div>
                                <input type="text" name="address" id="address" value="<?= htmlspecialchars($restaurant['address'] ?? '') ?>"
                                       class="tw-w-full tw-pl-11 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                       placeholder="Enter your restaurant address" required>
                            </div>
                        </div>
                        <div>
                            <label for="city" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">City *</label>
                            <input type="text" name="city" id="city" value="<?= htmlspecialchars($restaurant['city'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="Enter city" required>
                        </div>
                        <div>
                            <label for="state" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">State/Region</label>
                            <input type="text" name="state" id="state" value="<?= htmlspecialchars($restaurant['state'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="Enter state or region">
                        </div>
                        <div>
                            <label for="postal_code" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Postal Code</label>
                            <input type="text" name="postal_code" id="postal_code" value="<?= htmlspecialchars($restaurant['postal_code'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="Enter postal code">
                        </div>
                        <div>
                            <label for="country" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Country *</label>
                            <input type="text" name="country" id="country" value="<?= htmlspecialchars($restaurant['country'] ?? 'Cameroon') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="Enter country" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GPS Location Section -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-green-500 tw-to-emerald-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="map" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <div class="tw-flex-1">
                            <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">GPS Location</h2>
                            <p class="tw-text-sm tw-text-white/80 tw-mt-0.5">Set your restaurant's exact location for accurate delivery calculations</p>
                        </div>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <!-- GPS Coordinates -->
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label for="latitude" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">
                                Latitude *
                                <span class="tw-text-xs tw-font-normal tw-text-gray-500">(e.g., 5.9631)</span>
                            </label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <i data-feather="navigation" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                                </div>
                                <input type="number" name="latitude" id="latitude" step="0.00000001"
                                       value="<?= htmlspecialchars($restaurant['latitude'] ?? '5.9631') ?>"
                                       class="tw-w-full tw-pl-11 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500 tw-transition-all"
                                       placeholder="5.9631" required readonly>
                            </div>
                        </div>
                        <div>
                            <label for="longitude" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">
                                Longitude *
                                <span class="tw-text-xs tw-font-normal tw-text-gray-500">(e.g., 10.1591)</span>
                            </label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <i data-feather="navigation" class="tw-h-4 tw-w-4 tw-text-gray-400"></i>
                                </div>
                                <input type="number" name="longitude" id="longitude" step="0.00000001"
                                       value="<?= htmlspecialchars($restaurant['longitude'] ?? '10.1591') ?>"
                                       class="tw-w-full tw-pl-11 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500 tw-transition-all"
                                       placeholder="10.1591" required readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="tw-flex tw-flex-wrap tw-gap-3">
                        <button type="button" id="geocode-address-btn"
                                class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-blue-600 tw-text-white tw-rounded-xl tw-font-semibold tw-text-sm hover:tw-bg-blue-700 tw-transition-colors tw-shadow-md hover:tw-shadow-lg">
                            <i data-feather="search" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                            Get GPS from Address
                        </button>
                        <button type="button" id="show-map-picker-btn"
                                class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-green-600 tw-text-white tw-rounded-xl tw-font-semibold tw-text-sm hover:tw-bg-green-700 tw-transition-colors tw-shadow-md hover:tw-shadow-lg">
                            <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                            Pick Location on Map
                        </button>
                        <button type="button" id="use-current-location-btn"
                                class="tw-inline-flex tw-items-center tw-px-4 tw-py-2.5 tw-bg-purple-600 tw-text-white tw-rounded-xl tw-font-semibold tw-text-sm hover:tw-bg-purple-700 tw-transition-colors tw-shadow-md hover:tw-shadow-lg">
                            <i data-feather="crosshair" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                            Use Current Location
                        </button>
                    </div>

                    <!-- Map Container (Hidden by default) -->
                    <div id="location-map-container" class="tw-hidden tw-space-y-3">
                        <div class="tw-flex tw-items-center tw-justify-between tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-xl tw-p-4">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <i data-feather="info" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                <p class="tw-text-sm tw-text-blue-800 tw-font-medium">Click on the map or drag the marker to set your restaurant's location</p>
                            </div>
                            <button type="button" id="close-map-btn" class="tw-text-blue-600 hover:tw-text-blue-800">
                                <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                            </button>
                        </div>
                        <div id="location-map" class="tw-w-full tw-h-96 tw-rounded-xl tw-border-2 tw-border-gray-300 tw-shadow-lg"></div>
                        <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4 tw-border tw-border-gray-200">
                            <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-text-sm">
                                <div>
                                    <span class="tw-font-semibold tw-text-gray-700">Selected Latitude:</span>
                                    <span id="map-lat-display" class="tw-ml-2 tw-text-gray-900">-</span>
                                </div>
                                <div>
                                    <span class="tw-font-semibold tw-text-gray-700">Selected Longitude:</span>
                                    <span id="map-lng-display" class="tw-ml-2 tw-text-gray-900">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Text -->
                    <div class="tw-bg-amber-50 tw-border tw-border-amber-200 tw-rounded-xl tw-p-4">
                        <div class="tw-flex tw-gap-3">
                            <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-amber-600 tw-flex-shrink-0 tw-mt-0.5"></i>
                            <div class="tw-text-sm tw-text-amber-800">
                                <p class="tw-font-semibold tw-mb-1">Why is GPS location important?</p>
                                <ul class="tw-list-disc tw-list-inside tw-space-y-1 tw-text-amber-700">
                                    <li>Accurate delivery fee calculation based on distance</li>
                                    <li>Better customer experience with precise delivery estimates</li>
                                    <li>Helps riders find your restaurant easily</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-purple-500 tw-to-purple-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Operating Hours</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-4">
                    <?php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $dayIcons = ['sun', 'sun', 'sun', 'sun', 'sun', 'sun', 'sun'];
                    ?>
                    <?php foreach ($days as $index => $day): ?>
                    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center tw-gap-4 tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                        <div class="tw-flex tw-items-center tw-gap-3 tw-min-w-[140px]">
                            <i data-feather="calendar" class="tw-h-4 tw-w-4 tw-text-purple-600"></i>
                            <span class="tw-text-sm tw-font-semibold tw-text-gray-900"><?= $dayNames[$index] ?></span>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="<?= $day ?>_open" id="<?= $day ?>_open" value="1"
                                       class="tw-sr-only tw-peer" <?= !empty($restaurant[$day . '_open']) ? 'checked' : '' ?>>
                                <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-purple-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-purple-600"></div>
                                <span class="tw-ml-3 tw-text-sm tw-font-medium tw-text-gray-700">Open</span>
                            </label>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-2 tw-flex-1">
                            <input type="time" name="<?= $day ?>_open_time" value="<?= htmlspecialchars($restaurant[$day . '_open_time'] ?? '09:00') ?>"
                                   class="tw-px-3 tw-py-2 tw-text-sm tw-border tw-border-gray-300 tw-rounded-lg tw-bg-white focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-purple-500 tw-transition-all">
                            <span class="tw-text-gray-500 tw-text-sm tw-font-medium">to</span>
                            <input type="time" name="<?= $day ?>_close_time" value="<?= htmlspecialchars($restaurant[$day . '_close_time'] ?? '22:00') ?>"
                                   class="tw-px-3 tw-py-2 tw-text-sm tw-border tw-border-gray-300 tw-rounded-lg tw-bg-white focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-purple-500 tw-transition-all">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Delivery Settings -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-green-500 tw-to-green-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="truck" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Delivery Settings</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
                        <div>
                            <label for="delivery_fee" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Delivery Fee (XAF)</label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <span class="tw-text-gray-500 tw-text-sm tw-font-medium">XAF</span>
                                </div>
                                <input type="number" name="delivery_fee" id="delivery_fee" value="<?= htmlspecialchars($restaurant['delivery_fee'] ?? '500') ?>"
                                       class="tw-w-full tw-pl-16 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500 tw-transition-all"
                                       placeholder="500" min="0" step="50">
                            </div>
                        </div>
                        <div>
                            <label for="minimum_order" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Minimum Order (XAF)</label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <span class="tw-text-gray-500 tw-text-sm tw-font-medium">XAF</span>
                                </div>
                                <input type="number" name="minimum_order" id="minimum_order" value="<?= htmlspecialchars($restaurant['minimum_order'] ?? '2000') ?>"
                                       class="tw-w-full tw-pl-16 tw-pr-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500 tw-transition-all"
                                       placeholder="2000" min="0" step="100">
                            </div>
                        </div>
                        <div>
                            <label for="delivery_time" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Delivery Time (min)</label>
                            <div class="tw-relative">
                                <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-4 tw-flex tw-items-center tw-pointer-events-none">
                                    <span class="tw-text-gray-500 tw-text-sm tw-font-medium">min</span>
                                </div>
                                <input type="number" name="delivery_time" id="delivery_time" value="<?= htmlspecialchars($restaurant['delivery_time'] ?? '30') ?>"
                                       class="tw-w-full tw-pl-4 tw-pr-16 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500 tw-transition-all"
                                       placeholder="30" min="5" max="120" step="5">
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Radius -->
                    <div>
                        <label for="delivery_radius" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Delivery Radius (km)</label>
                        <div class="tw-relative">
                            <div class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-4 tw-flex tw-items-center tw-pointer-events-none">
                                <span class="tw-text-gray-500 tw-text-sm tw-font-medium">km</span>
                            </div>
                            <input type="number" name="delivery_radius" id="delivery_radius" value="<?= htmlspecialchars($restaurant['delivery_radius'] ?? '10') ?>"
                                   class="tw-w-full tw-pl-4 tw-pr-16 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500 tw-transition-all"
                                   placeholder="10" min="1" max="50" step="1">
                        </div>
                        <p class="tw-mt-2 tw-text-xs tw-text-gray-600">Maximum distance for delivery from your restaurant</p>
                    </div>
                </div>
            </div>

            <!-- Restaurant Images -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-pink-500 tw-to-pink-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="image" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Restaurant Images</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-3">Cover Image</label>
                            <div class="tw-relative tw-group">
                                <div class="tw-aspect-video tw-w-full tw-rounded-xl tw-overflow-hidden tw-bg-gradient-to-br tw-from-pink-100 tw-to-orange-100 tw-border-2 tw-border-dashed tw-border-gray-300 hover:tw-border-pink-500 tw-transition-all tw-cursor-pointer">
                                    <?php if (!empty($restaurant['cover_image'])): ?>
                                        <img id="cover-preview" src="<?= htmlspecialchars($restaurant['cover_image']) ?>" alt="Cover" class="tw-w-full tw-h-full tw-object-cover">
                                    <?php else: ?>
                                        <div id="cover-placeholder" class="tw-w-full tw-h-full tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-gray-400">
                                            <i data-feather="image" class="tw-h-12 tw-w-12 tw-mb-2"></i>
                                            <span class="tw-text-sm tw-font-medium">Click to upload cover</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" onclick="document.getElementById('cover-input').click()" class="tw-absolute tw-inset-0 tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-bg-black/50 tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-rounded-xl">
                                    <span class="tw-px-4 tw-py-2 tw-bg-white tw-text-pink-600 tw-rounded-lg tw-font-medium tw-text-sm">
                                        <i data-feather="upload" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                        Upload Image
                                    </span>
                                </button>
                            </div>
                            <input type="file" id="cover-input" name="cover_image" accept="image/*" class="tw-hidden" onchange="previewCover(this)">
                            <p class="tw-mt-2 tw-text-xs tw-text-gray-600">Recommended: 1200x400px</p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-3">Additional Images</label>
                            <div class="tw-grid tw-grid-cols-2 tw-gap-3">
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div class="tw-relative tw-group">
                                    <div class="tw-aspect-square tw-w-full tw-rounded-xl tw-overflow-hidden tw-bg-gradient-to-br tw-from-gray-100 tw-to-gray-200 tw-border-2 tw-border-dashed tw-border-gray-300 hover:tw-border-pink-500 tw-transition-all tw-cursor-pointer">
                                        <?php if (!empty($restaurant['image_' . $i])): ?>
                                            <img id="image-<?= $i ?>-preview" src="<?= htmlspecialchars($restaurant['image_' . $i]) ?>" alt="Image <?= $i ?>" class="tw-w-full tw-h-full tw-object-cover">
                                        <?php else: ?>
                                            <div class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-gray-400">
                                                <i data-feather="plus" class="tw-h-8 tw-w-8"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" onclick="document.getElementById('image-<?= $i ?>-input').click()" class="tw-absolute tw-inset-0 tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-bg-black/50 tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-rounded-xl">
                                        <i data-feather="upload" class="tw-h-5 tw-w-5 tw-text-white"></i>
                                    </button>
                                    <input type="file" id="image-<?= $i ?>-input" name="image_<?= $i ?>" accept="image/*" class="tw-hidden" onchange="previewImage(this, <?= $i ?>)">
                                </div>
                                <?php endfor; ?>
                            </div>
                            <p class="tw-mt-2 tw-text-xs tw-text-gray-600">Upload up to 4 additional images</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-indigo-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                            <i data-feather="credit-card" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <div>
                            <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Bank Information</h2>
                            <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">For payment processing</p>
                        </div>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label for="bank_name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" value="<?= htmlspecialchars($restaurant['bank_name'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-border-indigo-500 tw-transition-all"
                                   placeholder="Enter bank name">
                        </div>
                        <div>
                            <label for="account_name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Account Name</label>
                            <input type="text" name="account_name" id="account_name" value="<?= htmlspecialchars($restaurant['account_name'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-border-indigo-500 tw-transition-all"
                                   placeholder="Enter account name">
                        </div>
                        <div>
                            <label for="account_number" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Account Number</label>
                            <input type="text" name="account_number" id="account_number" value="<?= htmlspecialchars($restaurant['account_number'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-border-indigo-500 tw-transition-all"
                                   placeholder="Enter account number">
                        </div>
                        <div>
                            <label for="mobile_money" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Mobile Money Number</label>
                            <input type="tel" name="mobile_money" id="mobile_money" value="<?= htmlspecialchars($restaurant['mobile_money'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-border-indigo-500 tw-transition-all"
                                   placeholder="+237 XXX XXX XXX">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-placeholder');
            if (preview) {
                preview.src = e.target.result;
            } else if (placeholder) {
                placeholder.parentElement.innerHTML = '<img id="logo-preview" src="' + e.target.result + '" alt="Restaurant Logo" class="tw-w-full tw-h-full tw-object-cover">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('cover-preview');
            const placeholder = document.getElementById('cover-placeholder');
            if (preview) {
                preview.src = e.target.result;
            } else if (placeholder) {
                placeholder.parentElement.innerHTML = '<img id="cover-preview" src="' + e.target.result + '" alt="Cover" class="tw-w-full tw-h-full tw-object-cover">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewImage(input, index) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-' + index + '-preview');
            if (preview) {
                preview.src = e.target.result;
            } else {
                const container = input.parentElement.querySelector('.tw-aspect-square > div');
                if (container) {
                    container.parentElement.innerHTML = '<img id="image-' + index + '-preview" src="' + e.target.result + '" alt="Image ' + index + '" class="tw-w-full tw-h-full tw-object-cover">';
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewProfile() {
    // Open restaurant public profile in new tab
    window.open('<?= url('/restaurant/' . ($restaurant['slug'] ?? '')) ?>', '_blank');
}

// Form submission with loading state
document.getElementById('profile-form').addEventListener('submit', function(e) {
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i><span class="tw-hidden md:tw-inline">Saving...</span><span class="md:tw-hidden">Saving...</span>';
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// ============================================================================
// GPS LOCATION FUNCTIONALITY
// ============================================================================

let mapProvider;
let map;
let marker;

// Initialize map when needed
async function initMap(lat, lng) {
    const container = document.getElementById('location-map');
    
    // Initialize MapProvider
    mapProvider = new MapProvider({
        container: container,
        center: [parseFloat(lat), parseFloat(lng)],
        zoom: 15,
        provider: window.MAP_CONFIG ? window.MAP_CONFIG.provider : 'leaflet',
        apiKey: window.MAP_CONFIG ? window.MAP_CONFIG.apiKey : ''
    });

    map = await mapProvider.init();

    // Add draggable marker
    marker = mapProvider.addMarker('location', parseFloat(lat), parseFloat(lng), {
        draggable: true,
        title: 'Restaurant Location',
        // Handle drag end
        onClick: null // Click handled separately for now if needed, but drag is key
    });

    // Add event listeners for drag/click based on provider
    if (mapProvider.provider === 'google') {
        marker.addListener('dragend', function(event) {
            updateCoordinates(event.latLng.lat(), event.latLng.lng());
        });
        map.addListener('click', function(event) {
            marker.setPosition(event.latLng);
            updateCoordinates(event.latLng.lat(), event.latLng.lng());
        });
    } else {
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }
}

// Update coordinate fields
function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    document.getElementById('map-lat-display').textContent = lat.toFixed(8);
    document.getElementById('map-lng-display').textContent = lng.toFixed(8);
}

// Show map picker
document.getElementById('show-map-picker-btn').addEventListener('click', function() {
    const container = document.getElementById('location-map-container');
    const lat = document.getElementById('latitude').value || 5.9631;
    const lng = document.getElementById('longitude').value || 10.1591;

    container.classList.remove('tw-hidden');

    // Initialize map if not already done
    if (!map) {
        initMap(lat, lng);
    } else {
        // Update position
        if (mapProvider) {
            mapProvider.setCenter(parseFloat(lat), parseFloat(lng));
            mapProvider.updateMarker('location', parseFloat(lat), parseFloat(lng));
        }
    }

    // Scroll to map
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});

// Close map
document.getElementById('close-map-btn').addEventListener('click', function() {
    document.getElementById('location-map-container').classList.add('tw-hidden');
});

// Geocode address to get GPS coordinates
document.getElementById('geocode-address-btn').addEventListener('click', async function() {
    const address = document.getElementById('address').value;
    const city = document.getElementById('city').value;
    const country = document.getElementById('country').value || 'Cameroon';

    if (!address || !city) {
        alert('Please enter address and city first');
        return;
    }

    const fullAddress = `${address}, ${city}, ${country}`;
    const btn = this;
    const originalHTML = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Getting GPS...';
    if (typeof feather !== 'undefined') feather.replace();

    try {
        // Use Nominatim (OpenStreetMap) for geocoding (free, no API key needed)
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fullAddress)}&limit=1`);
        const data = await response.json();

        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);

            updateCoordinates(lat, lng);

            // Show success message
            showNotification('GPS coordinates found successfully!', 'success');

            // Optionally show on map
            if (map) {
                map.setCenter({ lat, lng });
                marker.setPosition({ lat, lng });
            }
        } else {
            showNotification('Could not find GPS coordinates for this address. Please use the map picker.', 'warning');
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        showNotification('Error getting GPS coordinates. Please try the map picker.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        if (typeof feather !== 'undefined') feather.replace();
    }
});

// Use current location (browser geolocation)
document.getElementById('use-current-location-btn').addEventListener('click', function() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }

    const btn = this;
    const originalHTML = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Getting location...';
    if (typeof feather !== 'undefined') feather.replace();

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            updateCoordinates(lat, lng);
            showNotification('Current location set successfully!', 'success');

            // Optionally show on map
            if (map) {
                map.setCenter({ lat, lng });
                marker.setPosition({ lat, lng });
            }

            btn.disabled = false;
            btn.innerHTML = originalHTML;
            if (typeof feather !== 'undefined') feather.replace();
        },
        function(error) {
            console.error('Geolocation error:', error);
            alert('Could not get your current location. Please check your browser permissions.');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            if (typeof feather !== 'undefined') feather.replace();
        }
    );
});

// Load Google Maps API dynamically
function loadGoogleMaps() {
    return new Promise((resolve, reject) => {
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&libraries=places`;
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// Notification helper
function showNotification(message, type = 'info') {
    const colors = {
        success: 'tw-bg-green-500',
        error: 'tw-bg-red-500',
        warning: 'tw-bg-yellow-500',
        info: 'tw-bg-blue-500'
    };

    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-right-4 ${colors[type]} tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-shadow-lg tw-z-50 tw-animate-fade-in`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<!-- Leaflet Map (Alternative to Google Maps - Free, No API Key) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Alternative: Use Leaflet (OpenStreetMap) instead of Google Maps
// This is FREE and doesn't require an API key

let leafletMap, leafletMarker;

function initLeafletMap(lat, lng) {
    const location = [parseFloat(lat), parseFloat(lng)];

    leafletMap = L.map('location-map').setView(location, 15);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(leafletMap);

    // Add marker
    leafletMarker = L.marker(location, { draggable: true }).addTo(leafletMap);

    // Update coordinates when marker is dragged
    leafletMarker.on('dragend', function(event) {
        const position = event.target.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    // Allow clicking on map to set location
    leafletMap.on('click', function(event) {
        leafletMarker.setLatLng(event.latlng);
        updateCoordinates(event.latlng.lat, event.latlng.lng);
    });
}

// Update the show map button to use Leaflet
document.getElementById('show-map-picker-btn').addEventListener('click', function() {
    const container = document.getElementById('location-map-container');
    const lat = document.getElementById('latitude').value || 5.9631;
    const lng = document.getElementById('longitude').value || 10.1591;

    container.classList.remove('tw-hidden');

    // Initialize Leaflet map if not already initialized
    if (!leafletMap) {
        setTimeout(() => {
            initLeafletMap(lat, lng);
            updateCoordinates(parseFloat(lat), parseFloat(lng));
        }, 100); // Small delay to ensure container is visible
    } else {
        leafletMap.setView([parseFloat(lat), parseFloat(lng)], 15);
        leafletMarker.setLatLng([parseFloat(lat), parseFloat(lng)]);
        updateCoordinates(parseFloat(lat), parseFloat(lng));
    }

    // Scroll to map
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});
</script>