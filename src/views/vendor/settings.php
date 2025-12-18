<?php
$title = $title ?? 'Settings - Time2Eat';
$currentPage = $currentPage ?? 'settings';
$user = $user ?? null;
$restaurant = $restaurant ?? null;
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Settings</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage your account settings, preferences, and security options
            </p>
        </div>
    </div>
</div>

<!-- Settings Content -->
<div class="tw-space-y-8">
    <!-- Account Settings -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-flex tw-items-center">
                <i data-feather="user" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-blue-600"></i>
                Account Information
            </h2>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Update your personal information and contact details</p>
        </div>
        
        <div class="tw-p-6">
            <form id="accountForm" class="tw-space-y-6">
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <div class="tw-space-y-2">
                        <label for="first_name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="user" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            First Name
                            <span class="tw-text-red-500 tw-ml-1">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name" value="<?= e($user->first_name ?? '') ?>"
                               class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                               placeholder="Enter your first name" required>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Your given name as it appears on official documents</div>
                    </div>
                    <div class="tw-space-y-2">
                        <label for="last_name" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="user" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Last Name
                            <span class="tw-text-red-500 tw-ml-1">*</span>
                        </label>
                        <input type="text" id="last_name" name="last_name" value="<?= e($user->last_name ?? '') ?>"
                               class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                               placeholder="Enter your last name" required>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Your family name or surname</div>
                    </div>
                </div>
                
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <div class="tw-space-y-2">
                        <label for="email" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="mail" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Email Address
                            <span class="tw-text-red-500 tw-ml-1">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="<?= e($user->email ?? '') ?>"
                               class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                               placeholder="Enter your email address" required>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">This will be used for account notifications and login</div>
                    </div>
                    <div class="tw-space-y-2">
                        <label for="phone" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="phone" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone" value="<?= e($user->phone ?? '') ?>"
                               class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                               placeholder="+237 123 456 789">
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Include country code for international numbers</div>
                    </div>
                </div>
                
                <div class="tw-flex tw-justify-end">
                    <button type="submit" class="tw-bg-gradient-to-r tw-from-orange-600 tw-to-orange-700 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-text-sm tw-font-semibold hover:tw-from-orange-700 hover:tw-to-orange-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-orange-500 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="save" class="tw-h-4 tw-w-4"></i>
                        <span>Update Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-flex tw-items-center">
                <i data-feather="shield" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-green-600"></i>
                Security
            </h2>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Manage your password and security preferences</p>
        </div>
        
        <div class="tw-p-6">
            <form id="passwordForm" class="tw-space-y-6">
                <div class="tw-space-y-2">
                    <label for="current_password" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                        <i data-feather="lock" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                        Current Password
                        <span class="tw-text-red-500 tw-ml-1">*</span>
                    </label>
                    <div class="tw-relative">
                        <input type="password" id="current_password" name="current_password"
                               class="tw-w-full tw-px-4 tw-py-3 tw-pr-12 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                               placeholder="Enter your current password" required>
                        <button type="button" onclick="togglePassword('current_password')" 
                                class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-gray-600">
                            <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                        </button>
                    </div>
                    <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Enter your current password to verify your identity</div>
                </div>
                
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <div class="tw-space-y-2">
                        <label for="new_password" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="key" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            New Password
                            <span class="tw-text-red-500 tw-ml-1">*</span>
                        </label>
                        <div class="tw-relative">
                            <input type="password" id="new_password" name="new_password"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-pr-12 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                                   placeholder="Enter new password" required>
                            <button type="button" onclick="togglePassword('new_password')" 
                                    class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-gray-600">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Must be at least 8 characters long</div>
                    </div>
                    <div class="tw-space-y-2">
                        <label for="confirm_password" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="check-circle" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Confirm New Password
                            <span class="tw-text-red-500 tw-ml-1">*</span>
                        </label>
                        <div class="tw-relative">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-pr-12 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400"
                                   placeholder="Confirm new password" required>
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                    class="tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-text-gray-400 hover:tw-text-gray-600">
                                <i data-feather="eye" class="tw-h-4 tw-w-4"></i>
                            </button>
                        </div>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Must match the new password above</div>
                    </div>
                </div>
                
                <div class="tw-flex tw-justify-end">
                    <button type="submit" class="tw-bg-gradient-to-r tw-from-green-600 tw-to-green-700 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-text-sm tw-font-semibold hover:tw-from-green-700 hover:tw-to-green-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-green-500 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="key" class="tw-h-4 tw-w-4"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-flex tw-items-center">
                <i data-feather="bell" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-purple-600"></i>
                Notifications
            </h2>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Choose how you want to be notified about orders and updates</p>
        </div>
        
        <div class="tw-p-6">
            <form id="notificationsForm" class="tw-space-y-6">
                <div class="tw-space-y-6">
                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-xl tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-orange-100 tw-rounded-lg">
                                    <i data-feather="shopping-bag" class="tw-h-5 tw-w-5 tw-text-orange-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900">New Orders</h3>
                                    <p class="tw-text-sm tw-text-gray-600">Get notified when you receive new orders</p>
                                </div>
                            </div>
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="notifications[]" value="new_orders" checked
                                       class="tw-sr-only tw-peer">
                                <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-rounded-full tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-orange-300 tw-peer-checked:tw-bg-orange-600 tw-after:tw-content-[''] tw-after:tw-absolute tw-after:tw-top-[2px] tw-after:tw-left-[2px] tw-after:tw-bg-white tw-after:tw-border-gray-300 tw-after:tw-border tw-after:tw-rounded-full tw-after:tw-h-5 tw-after:tw-w-5 tw-after:tw-transition-all tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-xl tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-blue-100 tw-rounded-lg">
                                    <i data-feather="refresh-cw" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900">Order Updates</h3>
                                    <p class="tw-text-sm tw-text-gray-600">Get notified about order status changes</p>
                                </div>
                            </div>
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="notifications[]" value="order_updates" checked
                                       class="tw-sr-only tw-peer">
                                <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-rounded-full tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-orange-300 tw-peer-checked:tw-bg-orange-600 tw-after:tw-content-[''] tw-after:tw-absolute tw-after:tw-top-[2px] tw-after:tw-left-[2px] tw-after:tw-bg-white tw-after:tw-border-gray-300 tw-after:tw-border tw-after:tw-rounded-full tw-after:tw-h-5 tw-after:tw-w-5 tw-after:tw-transition-all tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-xl tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-yellow-100 tw-rounded-lg">
                                    <i data-feather="star" class="tw-h-5 tw-w-5 tw-text-yellow-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900">Reviews</h3>
                                    <p class="tw-text-sm tw-text-gray-600">Get notified when customers leave reviews</p>
                                </div>
                            </div>
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="notifications[]" value="reviews" checked
                                       class="tw-sr-only tw-peer">
                                <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-rounded-full tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-orange-300 tw-peer-checked:tw-bg-orange-600 tw-after:tw-content-[''] tw-after:tw-absolute tw-after:tw-top-[2px] tw-after:tw-left-[2px] tw-after:tw-bg-white tw-after:tw-border-gray-300 tw-after:tw-border tw-after:tw-rounded-full tw-after:tw-h-5 tw-after:tw-w-5 tw-after:tw-transition-all tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-xl tw-border tw-border-gray-200">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-purple-100 tw-rounded-lg">
                                    <i data-feather="message-circle" class="tw-h-5 tw-w-5 tw-text-purple-600"></i>
                                </div>
                                <div>
                                    <h3 class="tw-text-sm tw-font-semibold tw-text-gray-900">Messages</h3>
                                    <p class="tw-text-sm tw-text-gray-600">Get notified about new customer messages</p>
                                </div>
                            </div>
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" name="notifications[]" value="messages" checked
                                       class="tw-sr-only tw-peer">
                                <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-rounded-full tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-orange-300 tw-peer-checked:tw-bg-orange-600 tw-after:tw-content-[''] tw-after:tw-absolute tw-after:tw-top-[2px] tw-after:tw-left-[2px] tw-after:tw-bg-white tw-after:tw-border-gray-300 tw-after:tw-border tw-after:tw-rounded-full tw-after:tw-h-5 tw-after:tw-w-5 tw-after:tw-transition-all tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white"></div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="tw-space-y-2">
                    <label for="delivery_method" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                        <i data-feather="send" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                        Delivery Method
                    </label>
                    <select id="delivery_method" name="delivery_method"
                            class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-pr-10"
                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-position: right 12px center; background-size: 12px;">
                        <option value="email">📧 Email Only</option>
                        <option value="sms">📱 SMS Only</option>
                        <option value="both">📧📱 Both Email and SMS</option>
                    </select>
                    <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Choose how you want to receive notifications</div>
                </div>
                
                <div class="tw-flex tw-justify-end">
                    <button type="submit" class="tw-bg-gradient-to-r tw-from-purple-600 tw-to-purple-700 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-text-sm tw-font-semibold hover:tw-from-purple-700 hover:tw-to-purple-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-purple-500 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="bell" class="tw-h-4 tw-w-4"></i>
                        <span>Update Notifications</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preferences -->
    <div class="tw-bg-white tw-shadow tw-rounded-lg tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-flex tw-items-center">
                <i data-feather="settings" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-indigo-600"></i>
                Preferences
            </h2>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Customize your dashboard and application preferences</p>
        </div>
        
        <div class="tw-p-6">
            <form id="preferencesForm" class="tw-space-y-6">
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <div class="tw-space-y-2">
                        <label for="timezone" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="clock" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Timezone
                        </label>
                        <select id="timezone" name="timezone"
                                class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-pr-10"
                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-position: right 12px center; background-size: 12px;">
                            <option value="Africa/Douala">🌍 Africa/Douala (GMT+1)</option>
                            <option value="UTC">🌐 UTC (GMT+0)</option>
                        </select>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Used for displaying times and dates</div>
                    </div>
                    <div class="tw-space-y-2">
                        <label for="language" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="globe" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Language
                        </label>
                        <select id="language" name="language"
                                class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-pr-10"
                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-position: right 12px center; background-size: 12px;">
                            <option value="en">🇺🇸 English</option>
                            <option value="fr">🇫🇷 Français</option>
                        </select>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">Interface language preference</div>
                    </div>
                </div>
                
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <div class="tw-space-y-2">
                        <label for="currency" class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Currency
                        </label>
                        <select id="currency" name="currency"
                                class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500 tw-transition-all tw-duration-200 tw-bg-white hover:tw-border-gray-400 tw-appearance-none tw-bg-no-repeat tw-bg-right tw-pr-10"
                                style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-position: right 12px center; background-size: 12px;">
                            <option value="XAF">💰 Central African Franc (XAF)</option>
                            <option value="USD">💵 US Dollar (USD)</option>
                            <option value="EUR">💶 Euro (EUR)</option>
                        </select>
                        <div class="tw-text-xs tw-text-gray-500 tw-mt-1">For displaying prices and earnings</div>
                    </div>
                    <div class="tw-space-y-2">
                        <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-800 tw-flex tw-items-center">
                            <i data-feather="zap" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-600"></i>
                            Order Management
                        </label>
                        <div class="tw-bg-gray-50 tw-p-4 tw-rounded-xl tw-border tw-border-gray-200">
                            <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                                <input type="checkbox" id="auto_accept_orders" name="auto_accept_orders"
                                       class="tw-sr-only tw-peer">
                                <div class="tw-w-11 tw-h-6 tw-bg-gray-200 tw-peer-focus:tw-outline-none tw-peer-focus:tw-ring-4 tw-peer-focus:tw-ring-orange-300 tw-rounded-full tw-peer tw-peer-checked:after:tw-translate-x-full tw-peer-checked:after:tw-border-white tw-after:tw-content-[''] tw-after:tw-absolute tw-after:tw-top-[2px] tw-after:tw-left-[2px] tw-after:tw-bg-white tw-after:tw-border-gray-300 tw-after:tw-border tw-after:tw-rounded-full tw-after:tw-h-5 tw-after:tw-w-5 tw-after:tw-transition-all tw-peer-checked:tw-bg-orange-600"></div>
                                <span class="tw-ml-3 tw-text-sm tw-font-medium tw-text-gray-700">Auto-accept orders</span>
                            </label>
                            <div class="tw-text-xs tw-text-gray-500 tw-mt-2">Automatically accept incoming orders without manual approval</div>
                        </div>
                    </div>
                </div>
                
                <div class="tw-flex tw-justify-end">
                    <button type="submit" class="tw-bg-gradient-to-r tw-from-indigo-600 tw-to-indigo-700 tw-text-white tw-px-6 tw-py-3 tw-rounded-xl tw-text-sm tw-font-semibold hover:tw-from-indigo-700 hover:tw-to-indigo-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-indigo-500 tw-transition-all tw-duration-200 tw-shadow-lg hover:tw-shadow-xl tw-flex tw-items-center tw-space-x-2">
                        <i data-feather="settings" class="tw-h-4 tw-w-4"></i>
                        <span>Update Preferences</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="settingsMessages" class="tw-fixed tw-top-4 tw-right-4 tw-z-50 tw-hidden">
    <div id="successMessage" class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg tw-p-4 tw-mb-4 tw-hidden">
        <div class="tw-flex">
            <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-400 tw-mr-3 tw-mt-0.5"></i>
            <div>
                <h3 class="tw-text-sm tw-font-medium tw-text-green-800">Success</h3>
                <p class="tw-mt-1 tw-text-sm tw-text-green-700" id="successText"></p>
            </div>
        </div>
    </div>
    <div id="errorMessage" class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg tw-p-4 tw-mb-4 tw-hidden">
        <div class="tw-flex">
            <i data-feather="alert-circle" class="tw-h-5 tw-w-5 tw-text-red-400 tw-mr-3 tw-mt-0.5"></i>
            <div>
                <h3 class="tw-text-sm tw-font-medium tw-text-red-800">Error</h3>
                <p class="tw-mt-1 tw-text-sm tw-text-red-700" id="errorText"></p>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-feather', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-feather', 'eye');
    }
    
    // Re-render the icon
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Account form submission
document.getElementById('accountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/vendor/settings/account') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Account updated successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to update account', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
});

// Password form submission
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/vendor/settings/password') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Password updated successfully!', 'success');
            this.reset();
        } else {
            showMessage(data.message || 'Failed to update password', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
});

// Notifications form submission
document.getElementById('notificationsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/vendor/settings/notifications') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Notification preferences updated successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to update notifications', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
});

// Preferences form submission
document.getElementById('preferencesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= url('/vendor/settings/preferences') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Preferences updated successfully!', 'success');
        } else {
            showMessage(data.message || 'Failed to update preferences', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
});

function showMessage(message, type = 'info') {
    const messagesContainer = document.getElementById('settingsMessages');
    const successDiv = document.getElementById('successMessage');
    const errorDiv = document.getElementById('errorMessage');

    // Hide all messages first
    successDiv.classList.add('tw-hidden');
    errorDiv.classList.add('tw-hidden');

    if (type === 'success') {
        document.getElementById('successText').textContent = message;
        successDiv.classList.remove('tw-hidden');
    } else if (type === 'error') {
        document.getElementById('errorText').textContent = message;
        errorDiv.classList.remove('tw-hidden');
    }

    messagesContainer.classList.remove('tw-hidden');

    // Auto-hide after 5 seconds
    setTimeout(() => {
        messagesContainer.classList.add('tw-hidden');
    }, 5000);
}
</script>

<style>
/* Toggle Switch Styling */
input[type="checkbox"].tw-peer {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

input[type="checkbox"].tw-peer:checked ~ div {
    background-color: #f97316;
}

input[type="checkbox"].tw-peer:checked ~ div::after {
    transform: translateX(1.25rem);
}

input[type="checkbox"].tw-peer ~ div::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 1.25rem;
    height: 1.25rem;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 9999px;
    transition: all 0.3s ease;
}

input[type="checkbox"].tw-peer:focus ~ div {
    outline: none;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
}
</style>
