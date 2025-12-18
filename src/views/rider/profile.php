<?php
$title = $title ?? 'Rider Profile - Time2Eat';
$user = $user ?? null;
$currentPage = $currentPage ?? 'profile';
?>

<!-- Mobile-First Header -->
<div class="tw-bg-gradient-to-r tw-from-blue-600 tw-to-purple-600 tw-rounded-2xl tw-p-6 tw-mb-6 tw-text-white">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-mb-1">My Profile</h1>
            <p class="tw-text-blue-100 tw-text-sm">Manage your delivery profile</p>
        </div>
        <div class="tw-relative">
            <button onclick="toggleAvailability()" class="tw-flex tw-items-center tw-space-x-2 tw-bg-white tw-bg-opacity-20 tw-backdrop-blur-sm tw-px-4 tw-py-2 tw-rounded-full tw-transition-all tw-duration-200 hover:tw-bg-opacity-30">
                <div class="tw-w-3 tw-h-3 tw-rounded-full <?= ($user->is_available ?? false) ? 'tw-bg-green-400' : 'tw-bg-red-400' ?> tw-animate-pulse"></div>
                <span class="tw-text-sm tw-font-medium">
                <?= ($user->is_available ?? false) ? 'Online' : 'Offline' ?>
            </span>
            </button>
        </div>
    </div>
    
    <!-- Quick Stats Mobile -->
    <div class="tw-grid tw-grid-cols-3 tw-gap-4">
        <div class="tw-text-center tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-3">
            <div class="tw-text-lg tw-font-bold">4.8</div>
            <div class="tw-text-xs tw-text-blue-100">Rating</div>
        </div>
        <div class="tw-text-center tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-3">
            <div class="tw-text-lg tw-font-bold">156</div>
            <div class="tw-text-xs tw-text-blue-100">Deliveries</div>
        </div>
        <div class="tw-text-center tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-3">
            <div class="tw-text-lg tw-font-bold">98%</div>
            <div class="tw-text-xs tw-text-blue-100">Success</div>
        </div>
    </div>
</div>

<!-- Mobile-First Profile Content -->
<div class="tw-space-y-4">
    <!-- Profile Avatar Card - Mobile Optimized -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6">
        <div class="tw-flex tw-items-center tw-space-x-4">
            <div class="tw-relative">
                <div class="tw-h-16 tw-w-16 tw-bg-gradient-to-r tw-from-blue-500 tw-to-purple-500 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                    <span class="tw-text-white tw-font-bold tw-text-xl">
                    <?= strtoupper(substr($user->first_name ?? 'R', 0, 1)) ?>
                </span>
                </div>
                <div class="tw-absolute -tw-bottom-1 -tw-right-1 tw-w-5 tw-h-5 tw-bg-green-400 tw-rounded-full tw-border-2 tw-border-white"></div>
            </div>
            <div class="tw-flex-1">
                <h2 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-1">
                <?= e($user->first_name ?? '') ?> <?= e($user->last_name ?? '') ?>
            </h2>
                <p class="tw-text-sm tw-text-gray-500 tw-mb-2">Delivery Rider</p>
                <div class="tw-flex tw-items-center tw-space-x-4">
                    <div class="tw-flex tw-items-center tw-space-x-1">
                        <i data-feather="star" class="tw-h-4 tw-w-4 tw-text-yellow-400"></i>
                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">4.8</span>
                    </div>
                    <div class="tw-flex tw-items-center tw-space-x-1">
                        <i data-feather="truck" class="tw-h-4 tw-w-4 tw-text-blue-500"></i>
                        <span class="tw-text-sm tw-font-medium tw-text-gray-700">156</span>
                </div>
                </div>
            </div>
                </div>
            </div>
            
    <!-- Mobile Navigation Tabs -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-1">
        <div class="tw-grid tw-grid-cols-3 tw-gap-1">
            <button onclick="showTab('personal')" id="personalTab" class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-rounded-xl tw-transition-all tw-duration-200 tw-bg-blue-600 tw-text-white">
                <i data-feather="user" class="tw-h-4 tw-w-4 tw-mr-2 tw-inline"></i>
                Personal
            </button>
            <button onclick="showTab('vehicle')" id="vehicleTab" class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-rounded-xl tw-transition-all tw-duration-200 tw-text-gray-600 hover:tw-bg-gray-100">
                <i data-feather="truck" class="tw-h-4 tw-w-4 tw-mr-2 tw-inline"></i>
                Vehicle
            </button>
            <button onclick="showTab('preferences')" id="preferencesTab" class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-rounded-xl tw-transition-all tw-duration-200 tw-text-gray-600 hover:tw-bg-gray-100">
                <i data-feather="settings" class="tw-h-4 tw-w-4 tw-mr-2 tw-inline"></i>
                Settings
                </button>
        </div>
    </div>
    
    <!-- Tab Content -->
    <div class="tw-space-y-4">
        <!-- Personal Information Tab -->
        <div id="personalContent" class="tab-content">
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                        <i data-feather="user" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-blue-600"></i>
                        Personal Information
                    </h3>
                    <button onclick="editPersonalInfo()" class="tw-px-4 tw-py-2 tw-bg-blue-50 tw-text-blue-600 tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-blue-100 tw-transition-colors">
                        <i data-feather="edit-2" class="tw-h-4 tw-w-4 tw-mr-1 tw-inline"></i>
                        Edit
                    </button>
                </div>
                
                <form id="personalInfoForm" class="tw-space-y-4">
                    <div class="tw-grid tw-grid-cols-1 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="user" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                First Name
                            </label>
                            <input type="text" name="first_name" value="<?= e($user->first_name ?? '') ?>" 
                                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500 tw-transition-colors tw-bg-gray-50" readonly>
                        </div>
                        
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="user" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Last Name
                            </label>
                            <input type="text" name="last_name" value="<?= e($user->last_name ?? '') ?>" 
                                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500 tw-transition-colors tw-bg-gray-50" readonly>
                        </div>
                        
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="mail" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Email Address
                            </label>
                            <input type="email" name="email" value="<?= e($user->email ?? '') ?>" 
                                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500 tw-transition-colors tw-bg-gray-50" readonly>
                        </div>
                        
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="phone" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Phone Number
                            </label>
                            <input type="tel" name="phone" value="<?= e($user->phone ?? '') ?>" 
                                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500 tw-transition-colors tw-bg-gray-50" readonly>
                        </div>
                    </div>
                    
                    <div class="tw-hidden tw-pt-4 tw-border-t tw-border-gray-200" id="personalInfoActions">
                        <div class="tw-flex tw-space-x-3">
                            <button type="button" onclick="savePersonalInfo()" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Save Changes
                            </button>
                            <button type="button" onclick="cancelEdit()" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-gray-100 tw-text-gray-700 tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-gray-200 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="x" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Vehicle Information Tab -->
        <div id="vehicleContent" class="tab-content tw-hidden">
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                        <i data-feather="truck" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-green-600"></i>
                        Vehicle Information
                    </h3>
                    <button onclick="editVehicleInfo()" class="tw-px-4 tw-py-2 tw-bg-green-50 tw-text-green-600 tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-green-100 tw-transition-colors">
                        <i data-feather="edit-2" class="tw-h-4 tw-w-4 tw-mr-1 tw-inline"></i>
                        Edit
                    </button>
                </div>
                
                <form id="vehicleInfoForm" class="tw-space-y-4">
                    <div class="tw-grid tw-grid-cols-1 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="truck" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Vehicle Type
                            </label>
                            <select name="vehicle_type" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-green-500 tw-focus:tw-border-green-500 tw-transition-colors tw-bg-gray-50" disabled>
                                <option value="motorcycle">🏍️ Motorcycle</option>
                                <option value="bicycle">🚲 Bicycle</option>
                                <option value="car">🚗 Car</option>
                                <option value="scooter">🛵 Scooter</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="hash" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                License Plate
                            </label>
                            <input type="text" name="license_plate" value="ABC-123-XY" 
                                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-green-500 tw-focus:tw-border-green-500 tw-transition-colors tw-bg-gray-50" readonly>
                        </div>
                        
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="award" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Vehicle Model
                            </label>
                            <input type="text" name="vehicle_model" value="Honda CB 125" 
                                   class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-text-sm tw-focus:tw-ring-2 tw-focus:tw-ring-green-500 tw-focus:tw-border-green-500 tw-transition-colors tw-bg-gray-50" readonly>
                        </div>
                        
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2 tw-flex tw-items-center">
                                <i data-feather="shield" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Insurance Status
                            </label>
                            <div class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-xl">
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                    <i data-feather="check" class="tw-h-3 tw-w-3 tw-mr-1"></i>
                                    Active
                                </span>
                                </div>
                                <span class="tw-text-xs tw-text-gray-500">Expires: Dec 2024</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tw-hidden tw-pt-4 tw-border-t tw-border-gray-200" id="vehicleInfoActions">
                        <div class="tw-flex tw-space-x-3">
                            <button type="button" onclick="saveVehicleInfo()" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-green-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-green-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Save Changes
                            </button>
                            <button type="button" onclick="cancelVehicleEdit()" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-gray-100 tw-text-gray-700 tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-gray-200 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="x" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Preferences Tab -->
        <div id="preferencesContent" class="tab-content tw-hidden">
            <div class="tw-space-y-4">
        <!-- Delivery Preferences -->
                <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center tw-mb-6">
                        <i data-feather="settings" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-purple-600"></i>
                        Delivery Preferences
                    </h3>
                    
                <div class="tw-space-y-6">
                    <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3 tw-flex tw-items-center">
                                <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Maximum Delivery Distance
                            </label>
                            <div class="tw-bg-gray-50 tw-rounded-xl tw-p-4">
                        <div class="tw-flex tw-items-center tw-space-x-4">
                            <input type="range" id="maxDistance" min="1" max="20" value="10" 
                                           class="tw-flex-1 tw-h-3 tw-bg-gradient-to-r tw-from-blue-200 tw-to-purple-200 tw-rounded-lg tw-appearance-none tw-cursor-pointer tw-slider">
                                    <span id="distanceValue" class="tw-text-lg tw-font-bold tw-text-blue-600 tw-w-16 tw-text-center">10 km</span>
                                </div>
                                <div class="tw-flex tw-justify-between tw-text-xs tw-text-gray-500 tw-mt-2">
                                    <span>1 km</span>
                                    <span>20 km</span>
                                </div>
                            </div>
                    </div>
                    
                    <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-4 tw-flex tw-items-center">
                                <i data-feather="bell" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-400"></i>
                                Notification Preferences
                            </label>
                            <div class="tw-space-y-4">
                                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                                    <div class="tw-flex tw-items-center tw-space-x-3">
                                        <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-blue-500"></i>
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-900">New order notifications</span>
                                    </div>
                                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                        <input type="checkbox" id="newOrderNotif" checked class="tw-sr-only tw-peer">
                                        <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all tw-peer-checked:tw-bg-blue-600"></div>
                                </label>
                            </div>
                                
                                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                                    <div class="tw-flex tw-items-center tw-space-x-3">
                                        <i data-feather="gift" class="tw-h-5 tw-w-5 tw-text-green-500"></i>
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-900">Promotion notifications</span>
                                    </div>
                                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                        <input type="checkbox" id="promotionNotif" checked class="tw-sr-only tw-peer">
                                        <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all tw-peer-checked:tw-bg-blue-600"></div>
                                </label>
                            </div>
                                
                                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                                    <div class="tw-flex tw-items-center tw-space-x-3">
                                        <i data-feather="message-circle" class="tw-h-5 tw-w-5 tw-text-purple-500"></i>
                                        <span class="tw-text-sm tw-font-medium tw-text-gray-900">Message notifications</span>
                                    </div>
                                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                        <input type="checkbox" id="messageNotif" checked class="tw-sr-only tw-peer">
                                        <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all tw-peer-checked:tw-bg-blue-600"></div>
                                </label>
                                </div>
                        </div>
                    </div>
                    
                    <div class="tw-pt-4 tw-border-t tw-border-gray-200">
                            <button onclick="savePreferences()" class="tw-w-full tw-px-4 tw-py-3 tw-bg-purple-600 tw-text-white tw-rounded-xl tw-text-sm tw-font-medium hover:tw-bg-purple-700 tw-transition-colors tw-flex tw-items-center tw-justify-center">
                                <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                            Save Preferences
                        </button>
                </div>
            </div>
        </div>
        
        <!-- Account Actions -->
                <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6">
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center tw-mb-6">
                        <i data-feather="user-check" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-orange-600"></i>
                        Account Actions
                    </h3>
                    
                    <div class="tw-space-y-3">
                        <button onclick="changePassword()" class="tw-w-full tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl hover:tw-bg-gray-100 tw-transition-colors">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                                    <i data-feather="lock" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                </div>
                                <div class="tw-text-left">
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-block">Change Password</span>
                                    <span class="tw-text-xs tw-text-gray-500">Update your account security</span>
            </div>
                        </div>
                            <i data-feather="chevron-right" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </button>
                    
                        <button onclick="downloadData()" class="tw-w-full tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl hover:tw-bg-gray-100 tw-transition-colors">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-green-100 tw-rounded-lg">
                                    <i data-feather="download" class="tw-h-5 tw-w-5 tw-text-green-600"></i>
                                </div>
                                <div class="tw-text-left">
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-block">Download My Data</span>
                                    <span class="tw-text-xs tw-text-gray-500">Export your account data</span>
                                </div>
                        </div>
                            <i data-feather="chevron-right" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </button>
                    
                        <button onclick="contactSupport()" class="tw-w-full tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl hover:tw-bg-gray-100 tw-transition-colors">
                        <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                                    <i data-feather="help-circle" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                                </div>
                                <div class="tw-text-left">
                                    <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-block">Contact Support</span>
                                    <span class="tw-text-xs tw-text-gray-500">Get help from our team</span>
                                </div>
                        </div>
                            <i data-feather="chevron-right" class="tw-h-5 tw-w-5 tw-text-gray-400"></i>
                    </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
feather.replace();

// Mobile-first tab functionality
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.add('tw-hidden');
    });
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('[id$="Tab"]');
    tabs.forEach(tab => {
        tab.classList.remove('tw-bg-blue-600', 'tw-text-white');
        tab.classList.add('tw-text-gray-600', 'hover:tw-bg-gray-100');
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById(tabName + 'Content');
    if (selectedContent) {
        selectedContent.classList.remove('tw-hidden');
    }
    
    // Add active class to selected tab
    const selectedTab = document.getElementById(tabName + 'Tab');
    if (selectedTab) {
        selectedTab.classList.remove('tw-text-gray-600', 'hover:tw-bg-gray-100');
        selectedTab.classList.add('tw-bg-blue-600', 'tw-text-white');
    }
    
    // Re-initialize Feather icons for the new content
    feather.replace();
}

// Distance slider with mobile optimization
document.getElementById('maxDistance').addEventListener('input', function() {
    const value = this.value;
    document.getElementById('distanceValue').textContent = value + ' km';
    
    // Add haptic feedback for mobile
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
});

// Enhanced availability toggle with mobile feedback
function toggleAvailability() {
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<div class="tw-w-3 tw-h-3 tw-rounded-full tw-bg-white tw-animate-pulse"></div><span class="tw-ml-2">Updating...</span>';
    button.disabled = true;
    
    fetch('<?= url('/rider/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            available: !button.textContent.includes('Online'),
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            const isOnline = data.is_available;
            button.innerHTML = `
                <div class="tw-w-3 tw-h-3 tw-rounded-full ${isOnline ? 'tw-bg-green-400' : 'tw-bg-red-400'} tw-animate-pulse"></div>
                <span class="tw-ml-2">${isOnline ? 'Online' : 'Offline'}</span>
            `;
            
            // Show success notification
            showNotification(data.message || 'Status updated successfully', 'success');
        } else {
            // Restore original state
            button.innerHTML = originalText;
            showNotification(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling availability:', error);
        button.innerHTML = originalText;
        showNotification('Failed to update status. Please try again.', 'error');
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Enhanced form editing with mobile feedback
function editPersonalInfo() {
    const form = document.getElementById('personalInfoForm');
    const inputs = form.querySelectorAll('input');
    const actions = document.getElementById('personalInfoActions');
    
    inputs.forEach(input => {
        if (input.name !== 'email') { // Email usually shouldn't be editable
            input.removeAttribute('readonly');
            input.classList.remove('tw-bg-gray-50');
            input.classList.add('tw-bg-white', 'tw-border-blue-300', 'tw-ring-2', 'tw-ring-blue-100');
        }
    });
    
    actions.classList.remove('tw-hidden');
    
    // Add haptic feedback
    if (navigator.vibrate) {
        navigator.vibrate(100);
    }
    
    showNotification('Edit mode activated', 'info');
}

function cancelEdit() {
    // Smooth transition back to read-only
    const form = document.getElementById('personalInfoForm');
    const inputs = form.querySelectorAll('input');
    const actions = document.getElementById('personalInfoActions');
    
    inputs.forEach(input => {
        input.setAttribute('readonly', 'readonly');
        input.classList.remove('tw-bg-white', 'tw-border-blue-300', 'tw-ring-2', 'tw-ring-blue-100');
        input.classList.add('tw-bg-gray-50');
    });
    
    actions.classList.add('tw-hidden');
    showNotification('Changes cancelled', 'info');
}

function savePersonalInfo() {
    const formData = new FormData(document.getElementById('personalInfoForm'));
    const data = Object.fromEntries(formData.entries());
    const saveBtn = document.querySelector('#personalInfoActions button[onclick="savePersonalInfo()"]');
    const originalText = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Saving...';
    saveBtn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/profile/update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            cancelEdit(); // Return to read-only mode
        } else {
            showNotification('Failed to update profile: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        showNotification('Failed to update profile. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        feather.replace();
    });
}

// Vehicle info functions with mobile enhancements
function editVehicleInfo() {
    const form = document.getElementById('vehicleInfoForm');
    const inputs = form.querySelectorAll('input, select');
    const actions = document.getElementById('vehicleInfoActions');
    
    inputs.forEach(input => {
        input.removeAttribute('readonly');
        input.removeAttribute('disabled');
        input.classList.remove('tw-bg-gray-50');
        input.classList.add('tw-bg-white', 'tw-border-green-300', 'tw-ring-2', 'tw-ring-green-100');
    });
    
    actions.classList.remove('tw-hidden');
    
    if (navigator.vibrate) {
        navigator.vibrate(100);
    }
    
    showNotification('Vehicle edit mode activated', 'info');
}

function cancelVehicleEdit() {
    const form = document.getElementById('vehicleInfoForm');
    const inputs = form.querySelectorAll('input, select');
    const actions = document.getElementById('vehicleInfoActions');
    
    inputs.forEach(input => {
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('disabled', 'disabled');
        input.classList.remove('tw-bg-white', 'tw-border-green-300', 'tw-ring-2', 'tw-ring-green-100');
        input.classList.add('tw-bg-gray-50');
    });
    
    actions.classList.add('tw-hidden');
    showNotification('Vehicle changes cancelled', 'info');
}

function saveVehicleInfo() {
    const formData = new FormData(document.getElementById('vehicleInfoForm'));
    const data = Object.fromEntries(formData.entries());
    const saveBtn = document.querySelector('#vehicleInfoActions button[onclick="saveVehicleInfo()"]');
    const originalText = saveBtn.innerHTML;
    
    saveBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Saving...';
    saveBtn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/profile/vehicle') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Vehicle information updated successfully!', 'success');
            cancelVehicleEdit();
        } else {
            showNotification('Failed to update vehicle information: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error updating vehicle info:', error);
        showNotification('Failed to update vehicle information. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        feather.replace();
    });
}

// Enhanced preferences saving
function savePreferences() {
    const preferences = {
        max_distance: document.getElementById('maxDistance').value,
        notifications: {
            new_orders: document.getElementById('newOrderNotif').checked,
            promotions: document.getElementById('promotionNotif').checked,
            messages: document.getElementById('messageNotif').checked
        }
    };
    
    const saveBtn = document.querySelector('button[onclick="savePreferences()"]');
    const originalText = saveBtn.innerHTML;
    
    saveBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Saving...';
    saveBtn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/profile/preferences') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify(preferences)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Preferences saved successfully!', 'success');
        } else {
            showNotification('Failed to save preferences: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error saving preferences:', error);
        showNotification('Failed to save preferences. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        feather.replace();
    });
}

// Enhanced password change with mobile modal
function changePassword() {
    // Create mobile-friendly modal
    const modal = document.createElement('div');
    modal.className = 'tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-z-50 tw-flex tw-items-center tw-justify-center tw-p-4';
    modal.innerHTML = `
        <div class="tw-bg-white tw-rounded-2xl tw-p-6 tw-w-full tw-max-w-sm tw-shadow-2xl">
            <h3 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4 tw-flex tw-items-center">
                <i data-feather="lock" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-blue-600"></i>
                Change Password
            </h3>
            <form id="passwordChangeForm" class="tw-space-y-4">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Current Password</label>
                    <input type="password" id="currentPassword" required class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500">
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">New Password</label>
                    <input type="password" id="newPassword" required minlength="6" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500">
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Confirm Password</label>
                    <input type="password" id="confirmPassword" required minlength="6" class="tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-200 tw-rounded-xl tw-focus:tw-ring-2 tw-focus:tw-ring-blue-500 tw-focus:tw-border-blue-500">
                </div>
                <div class="tw-flex tw-space-x-3 tw-pt-4">
                    <button type="button" onclick="closePasswordModal()" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-gray-100 tw-text-gray-700 tw-rounded-xl tw-font-medium">Cancel</button>
                    <button type="submit" class="tw-flex-1 tw-px-4 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-xl tw-font-medium">Change</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    feather.replace();
    
    // Handle form submission
    document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            showNotification('Passwords do not match!', 'error');
            return;
        }
        
        if (newPassword.length < 6) {
            showNotification('Password must be at least 6 characters long!', 'error');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Changing...';
        submitBtn.disabled = true;
        feather.replace();
        
            fetch('<?= url('/rider/profile/password') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                },
            body: JSON.stringify({ 
                current_password: currentPassword,
                new_password: newPassword 
            })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                showNotification('Password changed successfully!', 'success');
                closePasswordModal();
                } else {
                showNotification('Failed to change password: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error changing password:', error);
            showNotification('Failed to change password. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            feather.replace();
        });
    });
}

function closePasswordModal() {
    const modal = document.querySelector('.tw-fixed.tw-inset-0');
    if (modal) {
        modal.remove();
    }
}

function downloadData() {
    showNotification('Preparing your data download...', 'info');
    window.open('<?= url('/rider/profile/download-data') ?>', '_blank');
}

function contactSupport() {
    showNotification('Opening support email...', 'info');
    window.open('mailto:support@time2eat.com?subject=Rider Support Request', '_blank');
}

// Mobile-optimized notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `tw-fixed tw-top-4 tw-left-4 tw-right-4 tw-px-4 tw-py-3 tw-rounded-xl tw-shadow-lg tw-z-50 tw-transition-all tw-duration-300 tw-transform tw-translate-y-0 ${
        type === 'success' ? 'tw-bg-green-500 tw-text-white' : 
        type === 'error' ? 'tw-bg-red-500 tw-text-white' : 
        type === 'info' ? 'tw-bg-blue-500 tw-text-white' :
        'tw-bg-gray-500 tw-text-white'
    }`;
    
    notification.innerHTML = `
        <div class="tw-flex tw-items-center tw-space-x-3">
            <i data-feather="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="tw-w-5 tw-h-5 tw-flex-shrink-0"></i>
            <span class="tw-text-sm tw-font-medium tw-flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="tw-text-white tw-opacity-70 hover:tw-opacity-100">
                <i data-feather="x" class="tw-w-4 tw-h-4"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    feather.replace();
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.transform = 'translateY(-100%)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 4000);
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Show personal tab by default
    showTab('personal');
    
    // Add touch feedback to buttons
    const buttons = document.querySelectorAll('button');
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
