<?php
/**
 * Customer Profile Page - Admin-Style Design
 * Modern profile management with admin-inspired layout and styling
 */

$user = $user ?? null;
$profile = $profile ?? null;
$errors = $errors ?? [];
$success = $success ?? '';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-p-4 tw-rounded-2xl tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-shadow-lg tw-flex-shrink-0">
                <i data-feather="user" class="tw-h-8 tw-w-8 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            </div>
            <div class="tw-min-w-0">
                <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-text-gray-900">My Profile</h1>
                <p class="tw-mt-1 tw-text-xs sm:tw-text-sm tw-text-gray-500 tw-flex tw-items-center tw-gap-1">
                    <i data-feather="shield" class="tw-h-4 tw-w-4 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <span class="tw-truncate">Manage your personal information and account settings</span>
                </p>
            </div>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3 tw-flex-shrink-0">
            <button onclick="toggleEditMode()" id="edit-btn"
                    class="tw-inline-flex tw-items-center tw-px-4 sm:tw-px-6 tw-py-2 sm:tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-lg tw-text-xs sm:tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 hover:tw-from-blue-600 hover:tw-to-blue-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-ease-in-out tw-transform hover:tw-scale-105 active:tw-scale-95 tw-whitespace-nowrap">
                    <i data-feather="edit" class="tw-h-4 tw-w-4 tw-mr-1 sm:tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <span class="tw-hidden sm:tw-inline">Edit Profile</span>
                    <span class="sm:tw-hidden">Edit</span>
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
<form id="profile-form" method="POST" action="<?= url('/customer/profile/update') ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-8">
        
        <!-- Left Column - Profile Card -->
        <div class="lg:tw-col-span-1">
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-8 tw-text-center tw-sticky tw-top-8">
                <!-- Profile Picture -->
                <div class="tw-relative tw-inline-block tw-mb-6">
                    <div class="tw-w-32 tw-h-32 tw-rounded-full tw-overflow-hidden tw-bg-gradient-to-br tw-from-blue-100 tw-to-purple-100 tw-mx-auto tw-shadow-xl tw-border-4 tw-border-white">
                        <?php 
                        $userAvatar = is_object($user) ? ($user->avatar ?? '') : ($user['avatar'] ?? '');
                        $userFirstName = is_object($user) ? ($user->first_name ?? '') : ($user['first_name'] ?? '');
                        ?>
                        <?php if (!empty($userAvatar)): ?>
                            <img id="avatar-preview" src="<?= htmlspecialchars($userAvatar) ?>" alt="Profile Picture" class="tw-w-full tw-h-full tw-object-cover">
                        <?php else: ?>
                            <div id="avatar-placeholder" class="tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center tw-text-4xl tw-font-bold tw-text-gray-600">
                                <?= strtoupper(substr($userFirstName ?: 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="document.getElementById('avatar-input').click()" class="tw-absolute tw-bottom-2 tw-right-2 tw-w-10 tw-h-10 tw-bg-blue-600 tw-text-white tw-rounded-full tw-flex tw-items-center tw-justify-center tw-shadow-lg hover:tw-bg-blue-700 tw-transition-all tw-duration-200 edit-only tw-hidden tw-border-2 tw-border-white">
                        <i data-feather="camera" class="tw-w-5 tw-h-5"></i>
                    </button>
                </div>
                <input type="file" id="avatar-input" name="avatar" accept="image/*" class="tw-hidden" onchange="previewAvatar(this)">
                
                <?php 
                $userLastName = is_object($user) ? ($user->last_name ?? '') : ($user['last_name'] ?? '');
                $userEmail = is_object($user) ? ($user->email ?? '') : ($user['email'] ?? '');
                $userRole = is_object($user) ? ($user->role ?? '') : ($user['role'] ?? '');
                ?>
                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2"><?= htmlspecialchars(($userFirstName ?: '') . ' ' . ($userLastName ?: '')) ?></h2>
                <p class="tw-text-gray-600 tw-mb-4"><?= htmlspecialchars($userEmail ?: '') ?></p>
                
                <!-- Role Badge -->
                <div class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-blue-50 tw-text-blue-700 tw-rounded-full tw-text-sm tw-font-semibold tw-border tw-border-blue-200 tw-mb-6">
                    <i data-feather="user" class="tw-w-4 tw-h-4 tw-mr-2 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <?= ucfirst($userRole ?: 'customer') ?>
                </div>
                
                <!-- Quick Stats -->
                <div class="tw-space-y-4">
                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-gray-50 tw-rounded-xl">
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <i data-feather="calendar" class="tw-w-4 tw-h-4 tw-text-gray-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                            <span class="tw-text-sm tw-text-gray-600">Member Since</span>
                        </div>
                        <span class="tw-text-sm tw-font-semibold tw-text-gray-900">
                            <?= date('M Y', strtotime($user->created_at ?? 'now')) ?>
                        </span>
                    </div>

                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-gray-50 tw-rounded-xl">
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <i data-feather="shield" class="tw-w-4 tw-h-4 tw-text-gray-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                            <span class="tw-text-sm tw-text-gray-600">Account Status</span>
                        </div>
                        <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                            <div class="tw-w-2 tw-h-2 tw-bg-green-400 tw-rounded-full tw-mr-1"></div>
                            Active
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Form Sections -->
        <div class="lg:tw-col-span-2 tw-space-y-8">
            
            <!-- Personal Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-8">
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                    <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-r tw-from-purple-500 tw-to-purple-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-flex-shrink-0">
                        <i data-feather="user" class="tw-w-6 tw-h-6 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div class="tw-min-w-0">
                        <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900">Personal Information</h3>
                        <p class="tw-text-xs sm:tw-text-sm tw-text-gray-600">Update your personal details</p>
                    </div>
                </div>
                
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                    <!-- First Name -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($userFirstName ?: '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly required>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($userLastName ?: '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly required>
                    </div>

                    <!-- Email -->
                    <div class="md:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($userEmail ?: '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly required>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly>
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly>
                    </div>

                    <!-- Gender -->
                    <div class="md:tw-col-span-2">
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Gender</label>
                        <select name="gender" class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" disabled>
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($profile['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-8">
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                    <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-r tw-from-green-500 tw-to-green-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-flex-shrink-0">
                        <i data-feather="map-pin" class="tw-w-6 tw-h-6 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div class="tw-min-w-0">
                        <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900">Address Information</h3>
                        <p class="tw-text-xs sm:tw-text-sm tw-text-gray-600">Manage your delivery addresses</p>
                    </div>
                </div>
                
                <div class="tw-space-y-6">
                    <!-- Street Address -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Street Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($profile['address'] ?? '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly>
                    </div>

                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                        <!-- City -->
                        <div>
                            <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">City</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($profile['city'] ?? 'Bamenda') ?>" 
                                   class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                                   readonly>
                        </div>

                        <!-- State -->
                        <div>
                            <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">State/Region</label>
                            <input type="text" name="state" value="<?= htmlspecialchars($profile['state'] ?? 'North West') ?>" 
                                   class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                                   readonly>
                        </div>
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Postal Code</label>
                        <input type="text" name="postal_code" value="<?= htmlspecialchars($profile['postal_code'] ?? '') ?>" 
                               class="profile-input tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm tw-bg-gray-50 tw-transition-all tw-duration-200 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                               readonly>
                    </div>
                </div>
            </div>

            <!-- Preferences -->
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-8">
                <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
                    <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-flex-shrink-0">
                        <i data-feather="settings" class="tw-w-6 tw-h-6 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div class="tw-min-w-0">
                        <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900">Preferences</h3>
                        <p class="tw-text-xs sm:tw-text-sm tw-text-gray-600">Customize your experience</p>
                    </div>
                </div>
                
                <div class="tw-space-y-8">
                    <!-- Dietary Restrictions -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-4">Dietary Restrictions</label>
                        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4">
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="dietary_restrictions[]" value="vegetarian"
                                       <?= in_array('vegetarian', json_decode($profile['dietary_restrictions'] ?? '[]', true)) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-2">
                                    <i data-feather="leaf" class="tw-w-4 tw-h-4 tw-text-green-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Vegetarian</span>
                                </div>
                            </label>
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="dietary_restrictions[]" value="vegan"
                                       <?= in_array('vegan', json_decode($profile['dietary_restrictions'] ?? '[]', true)) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-2">
                                    <i data-feather="heart" class="tw-w-4 tw-h-4 tw-text-red-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Vegan</span>
                                </div>
                            </label>
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="dietary_restrictions[]" value="gluten_free"
                                       <?= in_array('gluten_free', json_decode($profile['dietary_restrictions'] ?? '[]', true)) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-2">
                                    <i data-feather="shield" class="tw-w-4 tw-h-4 tw-text-blue-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Gluten Free</span>
                                </div>
                            </label>
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="dietary_restrictions[]" value="halal"
                                       <?= in_array('halal', json_decode($profile['dietary_restrictions'] ?? '[]', true)) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-2">
                                    <i data-feather="star" class="tw-w-4 tw-h-4 tw-text-yellow-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Halal</span>
                                </div>
                            </label>
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200 sm:tw-col-span-2">
                                <input type="checkbox" name="dietary_restrictions[]" value="spicy"
                                       <?= in_array('spicy', json_decode($profile['dietary_restrictions'] ?? '[]', true)) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-2">
                                    <i data-feather="zap" class="tw-w-4 tw-h-4 tw-text-orange-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Likes Spicy Food</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Notification Preferences -->
                    <div>
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-4">Notification Preferences</label>
                        <div class="tw-space-y-4">
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="email_notifications" value="1"
                                       <?= ($profile['email_notifications'] ?? 1) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-3">
                                    <i data-feather="mail" class="tw-w-4 tw-h-4 tw-text-blue-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <div class="tw-min-w-0">
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">Email Notifications</span>
                                        <p class="tw-text-xs tw-text-gray-500">Receive updates via email</p>
                                    </div>
                                </div>
                            </label>
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="sms_notifications" value="1"
                                       <?= ($profile['sms_notifications'] ?? 1) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-3">
                                    <i data-feather="message-circle" class="tw-w-4 tw-h-4 tw-text-green-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <div class="tw-min-w-0">
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">SMS Notifications</span>
                                        <p class="tw-text-xs tw-text-gray-500">Receive updates via SMS</p>
                                    </div>
                                </div>
                            </label>
                            <label class="tw-flex tw-items-center tw-p-4 tw-bg-gray-50 tw-rounded-xl tw-cursor-pointer hover:tw-bg-gray-100 tw-transition-colors tw-border tw-border-gray-200">
                                <input type="checkbox" name="push_notifications" value="1"
                                       <?= ($profile['push_notifications'] ?? 1) ? 'checked' : '' ?>
                                       class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-3 tw-scale-110" disabled>
                                <div class="tw-flex tw-items-center tw-flex-1 tw-gap-3">
                                    <i data-feather="bell" class="tw-w-4 tw-h-4 tw-text-purple-500 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                                    <div class="tw-min-w-0">
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">Push Notifications</span>
                                        <p class="tw-text-xs tw-text-gray-500">Receive browser notifications</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="tw-space-y-4 edit-only tw-hidden">
                <button type="submit" class="tw-w-full tw-px-8 tw-py-4 tw-bg-gradient-to-r tw-from-blue-600 tw-to-blue-700 tw-text-white tw-rounded-xl tw-font-semibold hover:tw-from-blue-700 hover:tw-to-blue-800 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl tw-flex tw-items-center tw-justify-center tw-gap-3">
                    <i data-feather="check" class="tw-w-5 tw-h-5 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <span>Save Changes</span>
                </button>
                <button type="button" onclick="cancelEdit()" class="tw-w-full tw-px-8 tw-py-4 tw-bg-gray-100 tw-text-gray-700 tw-rounded-xl tw-font-semibold hover:tw-bg-gray-200 tw-transition-all tw-duration-200 tw-flex tw-items-center tw-justify-center tw-gap-3">
                    <i data-feather="x" class="tw-w-5 tw-h-5 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                    <span>Cancel</span>
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Account Actions -->
<div class="tw-mt-8">
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-8">
        <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
            <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-r tw-from-red-500 tw-to-red-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-flex-shrink-0">
                <i data-feather="shield" class="tw-w-6 tw-h-6 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            </div>
            <div class="tw-min-w-0">
                <h3 class="tw-text-lg sm:tw-text-xl tw-font-bold tw-text-gray-900">Account Actions</h3>
                <p class="tw-text-xs sm:tw-text-sm tw-text-gray-600">Manage your account security and data</p>
            </div>
        </div>

        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <button onclick="changePassword()" class="tw-p-6 tw-bg-gray-50 tw-text-gray-700 tw-rounded-xl tw-font-medium hover:tw-bg-gray-100 tw-transition-all tw-duration-200 tw-text-left tw-flex tw-items-center tw-gap-4 tw-group tw-border tw-border-gray-200 hover:tw-border-gray-300">
                <div class="tw-w-12 tw-h-12 tw-bg-blue-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center group-hover:tw-bg-blue-200 tw-transition-colors tw-flex-shrink-0">
                    <i data-feather="lock" class="tw-w-6 tw-h-6 tw-text-blue-600 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                </div>
                <div class="tw-min-w-0">
                    <div class="tw-font-semibold tw-text-base sm:tw-text-lg">Change Password</div>
                    <div class="tw-text-xs sm:tw-text-sm tw-text-gray-500">Update your account password</div>
                </div>
            </button>

            <button onclick="downloadData()" class="tw-p-6 tw-bg-gray-50 tw-text-gray-700 tw-rounded-xl tw-font-medium hover:tw-bg-gray-100 tw-transition-all tw-duration-200 tw-text-left tw-flex tw-items-center tw-gap-4 tw-group tw-border tw-border-gray-200 hover:tw-border-gray-300">
                <div class="tw-w-12 tw-h-12 tw-bg-green-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center group-hover:tw-bg-green-200 tw-transition-colors tw-flex-shrink-0">
                    <i data-feather="download" class="tw-w-6 tw-h-6 tw-text-green-600 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                </div>
                <div class="tw-min-w-0">
                    <div class="tw-font-semibold tw-text-base sm:tw-text-lg">Download My Data</div>
                    <div class="tw-text-xs sm:tw-text-sm tw-text-gray-500">Export your account information</div>
                </div>
            </button>

            <button onclick="deleteAccount()" class="tw-p-6 tw-bg-red-50 tw-text-red-700 tw-rounded-xl tw-font-medium hover:tw-bg-red-100 tw-transition-all tw-duration-200 tw-text-left tw-flex tw-items-center tw-gap-4 tw-group tw-border tw-border-red-200 hover:tw-border-red-300">
                <div class="tw-w-12 tw-h-12 tw-bg-red-100 tw-rounded-xl tw-flex tw-items-center tw-justify-center group-hover:tw-bg-red-200 tw-transition-colors tw-flex-shrink-0">
                    <i data-feather="trash-2" class="tw-w-6 tw-h-6 tw-text-red-600 tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
                </div>
                <div class="tw-min-w-0">
                    <div class="tw-font-semibold tw-text-base sm:tw-text-lg">Delete Account</div>
                    <div class="tw-text-xs sm:tw-text-sm tw-text-red-500">Permanently delete your account</div>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="password-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-p-4 tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-w-full tw-max-w-md tw-shadow-2xl">
        <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
            <div class="tw-w-12 tw-h-12 tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-shadow-lg tw-flex-shrink-0">
                <i data-feather="lock" class="tw-w-6 tw-h-6 tw-text-white tw-flex-shrink-0" style="display: flex; align-items: center; justify-content: center;"></i>
            </div>
            <h3 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-gray-900 tw-min-w-0">Change Password</h3>
        </div>
        
        <form id="password-form" class="tw-space-y-6">
            <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Current Password</label>
                <input type="password" id="current-password" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200" required>
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">New Password</label>
                <input type="password" id="new-password" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200" required>
            </div>
            <div>
                <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-2">Confirm New Password</label>
                <input type="password" id="confirm-password" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200" required>
            </div>
            <div class="tw-flex tw-space-x-4 tw-pt-4">
                <button type="button" onclick="closePasswordModal()" class="tw-flex-1 tw-px-6 tw-py-3 tw-bg-gray-100 tw-text-gray-700 tw-rounded-xl tw-font-semibold hover:tw-bg-gray-200 tw-transition-all tw-duration-200">
                    Cancel
                </button>
                <button type="submit" class="tw-flex-1 tw-px-6 tw-py-3 tw-bg-gradient-to-r tw-from-blue-600 tw-to-blue-700 tw-text-white tw-rounded-xl tw-font-semibold hover:tw-from-blue-700 tw-to-blue-800 tw-transition-all tw-duration-200">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Feather Icons
function initializeFeatherIcons() {
    // Check if feather is loaded and has the replace method
    if (typeof feather !== 'undefined' && typeof feather.replace === 'function') {
        try {
            feather.replace();
            console.log('Feather icons initialized successfully');
            return true;
        } catch (error) {
            console.error('Error initializing Feather icons:', error);
            showIconFallbacks();
            return false;
        }
    } else {
        console.log('Feather not loaded yet, retrying...');
        setTimeout(initializeFeatherIcons, 100);
        return false;
    }
}

// Fallback function to show text if icons fail
function showIconFallbacks() {
    const iconElements = document.querySelectorAll('[data-feather]');
    iconElements.forEach(function(element) {
        if (element.innerHTML.trim() === '') {
            const iconName = element.getAttribute('data-feather');
            element.innerHTML = '[' + iconName + ']';
            element.style.fontSize = '12px';
            element.style.color = '#6b7280';
        }
    });
}

let isEditMode = false;

function toggleEditMode() {
    isEditMode = !isEditMode;
    const editBtn = document.getElementById('edit-btn');
    const editOnlyElements = document.querySelectorAll('.edit-only');
    const profileInputs = document.querySelectorAll('.profile-input');
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const selects = document.querySelectorAll('select');

    if (isEditMode) {
        The rider management page should show real information. Also, on the admin sidebar, the rider management text is missing an icon.        // Enable edit mode
        editBtn.innerHTML = '<i data-feather="x" class="tw-h-4 tw-w-4 tw-mr-2"></i>Cancel';
        editBtn.classList.remove('tw-from-blue-500', 'tw-to-blue-600', 'hover:tw-from-blue-600', 'hover:tw-to-blue-700');
        editBtn.classList.add('tw-from-red-500', 'tw-to-red-600', 'hover:tw-from-red-600', 'hover:tw-to-red-700');
        
        editOnlyElements.forEach(el => el.classList.remove('tw-hidden'));
        
        profileInputs.forEach(input => {
            input.removeAttribute('readonly');
            input.classList.remove('tw-bg-gray-50');
            input.classList.add('tw-bg-white', 'focus:tw-ring-2', 'focus:tw-ring-blue-500', 'focus:tw-border-blue-500');
        });
        
        checkboxes.forEach(cb => cb.removeAttribute('disabled'));
        selects.forEach(select => select.removeAttribute('disabled'));
    } else {
        // Disable edit mode
        editBtn.innerHTML = '<i data-feather="edit" class="tw-h-4 tw-w-4 tw-mr-2"></i>Edit Profile';
        editBtn.classList.remove('tw-from-red-500', 'tw-to-red-600', 'hover:tw-from-red-600', 'hover:tw-to-red-700');
        editBtn.classList.add('tw-from-blue-500', 'tw-to-blue-600', 'hover:tw-from-blue-600', 'hover:tw-to-blue-700');
        
        editOnlyElements.forEach(el => el.classList.add('tw-hidden'));
        
        profileInputs.forEach(input => {
            input.setAttribute('readonly', 'readonly');
            input.classList.remove('tw-bg-white', 'focus:tw-ring-2', 'focus:tw-ring-blue-500', 'focus:tw-border-blue-500');
            input.classList.add('tw-bg-gray-50');
        });
        
        checkboxes.forEach(cb => cb.setAttribute('disabled', 'disabled'));
        selects.forEach(select => select.setAttribute('disabled', 'disabled'));
    }
    
    // Re-initialize Feather Icons
    initializeFeatherIcons();
}

function cancelEdit() {
    // Reset form to original values
    location.reload();
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const placeholder = document.getElementById('avatar-placeholder');
            
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Create new img element if placeholder exists
                if (placeholder) {
                    const img = document.createElement('img');
                    img.id = 'avatar-preview';
                    img.src = e.target.result;
                    img.alt = 'Profile Picture';
                    img.className = 'tw-w-full tw-h-full tw-object-cover';
                    placeholder.parentNode.replaceChild(img, placeholder);
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function changePassword() {
    document.getElementById('password-modal').classList.remove('tw-hidden');
}

function closePasswordModal() {
    document.getElementById('password-modal').classList.add('tw-hidden');
    document.getElementById('password-form').reset();
}

// Handle password form submission
document.getElementById('password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('New password must be at least 6 characters long');
        return;
    }
    
    fetch('<?= url('/customer/profile/change-password') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password updated successfully');
            closePasswordModal();
        } else {
            alert(data.message || 'Failed to update password');
        }
    })
    .catch(error => {
        console.error('Error updating password:', error);
        alert('Failed to update password');
    });
});

function downloadData() {
    if (confirm('Download all your account data? This may take a few moments.')) {
        window.location.href = '<?= url('/customer/profile/download-data') ?>';
    }
}

function deleteAccount() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        if (confirm('This will permanently delete all your data. Are you absolutely sure?')) {
            fetch('<?= url('/customer/profile/delete-account') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Account deleted successfully');
                    window.location.href = '/';
                } else {
                    alert(data.message || 'Failed to delete account');
                }
            })
            .catch(error => {
                console.error('Error deleting account:', error);
                alert('Failed to delete account');
            });
        }
    }
}

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for external scripts to load
    setTimeout(function() {
        // Initialize Feather Icons
        initializeFeatherIcons();
        
        // Fallback timeout in case Feather Icons never loads
        setTimeout(function() {
            if (typeof feather === 'undefined' || typeof feather.replace !== 'function') {
                console.warn('Feather Icons failed to load, showing fallbacks');
                showIconFallbacks();
            }
        }, 2000);
    }, 100);
    
    // Add touch-friendly interactions
    const buttons = document.querySelectorAll('button, .tw-cursor-pointer');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>