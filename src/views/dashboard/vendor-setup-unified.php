<?php
/**
 * Unified Vendor Restaurant Setup & Profile Page
 * Modern design with conditional logic for new and existing vendors
 * Mobile-first responsive design with gradient accents
 */

$setupMode = $setupMode ?? false;
$restaurant = $restaurant ?? [];
$errors = $errors ?? [];
$success = $success ?? '';
$old = $old ?? [];

// Helper function to display errors
function displayError($errors, $field) {
    if (!isset($errors[$field])) return '';
    $error = $errors[$field];
    if (is_array($error)) {
        return htmlspecialchars($error[0]);
    }
    return htmlspecialchars($error);
}
?>

<!-- Page Header -->
<div class="tw-mb-6 md:tw-mb-8">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-w-12 tw-h-12 md:tw-w-14 md:tw-h-14 tw-rounded-2xl tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-flex-shrink-0">
                <i data-feather="<?= $setupMode ? 'plus-circle' : 'home' ?>" class="tw-h-6 tw-w-6 md:tw-h-7 md:tw-w-7 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            </div>
            <div>
                <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-gray-900">
                    <?= $setupMode ? 'Welcome to Time2Eat!' : 'Restaurant Profile' ?>
                </h1>
                <p class="tw-text-sm tw-text-gray-600 tw-mt-1 tw-flex tw-items-center">
                    <i data-feather="info" class="tw-h-4 tw-w-4 tw-mr-1 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <?= $setupMode ? "Let's set up your restaurant profile to get started" : 'Manage your restaurant information and settings' ?>
                </p>
            </div>
        </div>
        <?php if (!$setupMode): ?>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
            <button type="button" onclick="previewProfile()"
                    class="tw-px-4 md:tw-px-6 tw-py-2.5 md:tw-py-3 tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 tw-rounded-xl tw-font-medium tw-shadow-md hover:tw-shadow-lg tw-transition-all tw-duration-200 tw-flex tw-items-center tw-gap-2">
                <i data-feather="eye" class="tw-h-4 tw-w-4 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                <span class="tw-hidden md:tw-inline">Preview</span>
            </button>
            <button type="submit" form="profile-form" id="save-btn"
                    class="tw-px-4 md:tw-px-6 tw-py-2.5 md:tw-py-3 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-xl tw-font-medium tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-flex tw-items-center tw-gap-2">
                <i data-feather="save" class="tw-h-4 tw-w-4 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                <span class="tw-hidden md:tw-inline">Save Changes</span>
                <span class="md:tw-hidden">Save</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success): ?>
    <div class="tw-mb-6 tw-p-4 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-xl tw-shadow-sm">
        <div class="tw-flex tw-items-center">
            <i data-feather="check-circle" class="tw-w-5 tw-h-5 tw-text-green-600 tw-mr-3 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            <span class="tw-text-green-800 tw-font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="tw-mb-6 tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-shadow-sm">
        <div class="tw-flex tw-items-start">
            <i data-feather="alert-circle" class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-3 tw-mt-0.5 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            <div class="tw-text-red-800">
                <?php foreach ($errors as $error): ?>
                    <div class="tw-mb-1"><?= htmlspecialchars(is_array($error) ? $error[0] : $error) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Profile Form -->
<form id="profile-form" method="POST" action="<?= $setupMode ? url('/vendor/setup') : url('/vendor/profile') ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6 md:tw-gap-8">

        <!-- Left Column - Restaurant Card (Only for edit mode) -->
        <?php if (!$setupMode): ?>
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
        <?php endif; ?>

        <!-- Right Column - Form Sections -->
        <div class="<?= $setupMode ? 'lg:tw-col-span-3' : 'lg:tw-col-span-2' ?> tw-space-y-6">

            <!-- Basic Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="info" class="tw-h-5 tw-w-5 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Basic Information</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label for="name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Restaurant Name *</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($restaurant['name'] ?? $old['name'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all"
                                   placeholder="e.g., Mama Grace's Kitchen" required>
                            <?php if (isset($errors['name'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'name') ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="cuisine_type" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Cuisine Type *</label>
                            <select name="cuisine_type" id="cuisine_type"
                                    class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all" required>
                                <option value="">Select cuisine type</option>
                                <option value="African" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'African' ? 'selected' : '' ?>>African</option>
                                <option value="Cameroonian" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Cameroonian' ? 'selected' : '' ?>>Cameroonian</option>
                                <option value="Fast Food" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Fast Food' ? 'selected' : '' ?>>Fast Food</option>
                                <option value="Chinese" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Chinese' ? 'selected' : '' ?>>Chinese</option>
                                <option value="Italian" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Italian' ? 'selected' : '' ?>>Italian</option>
                                <option value="Indian" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Indian' ? 'selected' : '' ?>>Indian</option>
                                <option value="Lebanese" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Lebanese' ? 'selected' : '' ?>>Lebanese</option>
                                <option value="Continental" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Continental' ? 'selected' : '' ?>>Continental</option>
                                <option value="Mixed" <?= ($restaurant['cuisine_type'] ?? $old['cuisine_type'] ?? '') === 'Mixed' ? 'selected' : '' ?>>Mixed</option>
                            </select>
                            <?php if (isset($errors['cuisine_type'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'cuisine_type') ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="md:tw-col-span-2">
                            <label for="description" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Description *</label>
                            <textarea name="description" id="description" rows="4"
                                      class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-resize-none"
                                      placeholder="Tell customers about your restaurant, specialties, and what makes you unique..." required><?= htmlspecialchars($restaurant['description'] ?? $old['description'] ?? '') ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'description') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
                <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-px-6 tw-py-4">
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="phone" class="tw-h-5 tw-w-5 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                        </div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Contact Information</h2>
                    </div>
                </div>
                <div class="tw-p-6 tw-space-y-6">
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <div>
                            <label for="phone" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($restaurant['phone'] ?? $old['phone'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="+237 6XX XXX XXX" required>
                            <?php if (isset($errors['phone'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'phone') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$setupMode): ?>
                        <div>
                            <label for="email" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Email Address *</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($restaurant['email'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="restaurant@example.com" required>
                        </div>
                        <?php endif; ?>
                        <div class="<?= $setupMode ? 'md:tw-col-span-2' : '' ?>">
                            <label for="address" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Street Address *</label>
                            <input type="text" name="address" id="address" value="<?= htmlspecialchars($restaurant['address'] ?? $old['address'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="123 Main Street" required>
                            <?php if (isset($errors['address'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'address') ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="city" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">City *</label>
                            <input type="text" name="city" id="city" value="<?= htmlspecialchars($restaurant['city'] ?? $old['city'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="Douala" required>
                            <?php if (isset($errors['city'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'city') ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="state" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">State/Region *</label>
                            <input type="text" name="state" id="state" value="<?= htmlspecialchars($restaurant['state'] ?? $old['state'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="Littoral" required>
                            <?php if (isset($errors['state'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'state') ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="postal_code" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-900 tw-mb-2">Postal Code *</label>
                            <input type="text" name="postal_code" id="postal_code" value="<?= htmlspecialchars($restaurant['postal_code'] ?? $old['postal_code'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all"
                                   placeholder="12345" required>
                            <?php if (isset($errors['postal_code'])): ?>
                                <p class="tw-mt-1 tw-text-sm tw-text-red-600"><?= displayError($errors, 'postal_code') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button for Setup Mode -->
            <?php if ($setupMode): ?>
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-p-6 tw-text-center">
                <button type="submit"
                        class="tw-w-full tw-px-6 tw-py-3 tw-bg-gradient-to-r tw-from-orange-500 tw-to-red-600 tw-text-white tw-rounded-xl tw-font-semibold tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-gap-2">
                    <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    Create Restaurant Profile
                </button>
                <p class="tw-text-sm tw-text-gray-600 tw-mt-4">
                    After creating your profile, you'll be able to add menu items and manage orders.
                </p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</form>

<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-placeholder');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewProfile() {
    // Placeholder for preview functionality
    alert('Preview functionality coming soon!');
}
</script>

