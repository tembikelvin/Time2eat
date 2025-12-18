<?php
/**
 * Admin Payment Settings Page
 * Comprehensive payment gateway configuration
 */

// Helper functions
function getSettingValue($settings, $key, $default = '') {
    return $settings[$key]['value'] ?? $default;
}

function isSettingEnabled($settings, $key) {
    $value = getSettingValue($settings, $key, 'false');
    return $value === 'true' || $value === '1' || $value === 1;
}

$paymentMethods = json_decode(getSettingValue($settings, 'payment_methods_enabled', '[]'), true) ?: [];
?>

<!-- Page Header -->
<div class="tw-mb-6 md:tw-mb-8">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center tw-gap-4">
            <div class="tw-w-12 tw-h-12 md:tw-w-14 md:tw-h-14 tw-rounded-2xl tw-bg-gradient-to-r tw-from-green-500 tw-to-emerald-600 tw-flex tw-items-center tw-justify-center tw-shadow-lg">
                <i data-feather="credit-card" class="tw-h-6 tw-w-6 md:tw-h-7 md:tw-w-7 tw-text-white"></i>
            </div>
            <div>
                <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-gray-900">Payment Settings</h1>
                <p class="tw-text-sm tw-text-gray-600 tw-mt-1">Configure payment gateways and methods</p>
            </div>
        </div>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
            <button onclick="saveSettings()" class="tw-px-4 md:tw-px-6 tw-py-2.5 md:tw-py-3 tw-bg-gradient-to-r tw-from-green-500 tw-to-emerald-600 tw-text-white tw-rounded-xl tw-font-medium tw-shadow-lg hover:tw-shadow-xl tw-transition-all tw-duration-200 tw-flex tw-items-center tw-gap-2">
                <i data-feather="save" class="tw-h-4 tw-w-4"></i>
                <span class="tw-hidden md:tw-inline">Save Settings</span>
                <span class="md:tw-hidden">Save</span>
            </button>
        </div>
    </div>
</div>

<!-- Payment Statistics -->
<?php if (!empty($stats['transactions'])): ?>
<div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4 tw-mb-6">
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-text-gray-600">Total Transactions</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-900 tw-mt-1"><?= number_format($stats['transactions']['total_transactions'] ?? 0) ?></p>
            </div>
            <div class="tw-w-10 tw-h-10 md:tw-w-12 md:tw-h-12 tw-rounded-xl tw-bg-blue-100 tw-flex tw-items-center tw-justify-center">
                <i data-feather="activity" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-text-gray-600">Successful</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-green-600 tw-mt-1"><?= number_format($stats['transactions']['successful_transactions'] ?? 0) ?></p>
            </div>
            <div class="tw-w-10 tw-h-10 md:tw-w-12 md:tw-h-12 tw-rounded-xl tw-bg-green-100 tw-flex tw-items-center tw-justify-center">
                <i data-feather="check-circle" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-text-gray-600">Failed</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-red-600 tw-mt-1"><?= number_format($stats['transactions']['failed_transactions'] ?? 0) ?></p>
            </div>
            <div class="tw-w-10 tw-h-10 md:tw-w-12 md:tw-h-12 tw-rounded-xl tw-bg-red-100 tw-flex tw-items-center tw-justify-center">
                <i data-feather="x-circle" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-red-600"></i>
            </div>
        </div>
    </div>
    
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-p-4 md:tw-p-6">
        <div class="tw-flex tw-items-center tw-justify-between">
            <div>
                <p class="tw-text-xs md:tw-text-sm tw-text-gray-600">Pending</p>
                <p class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-yellow-600 tw-mt-1"><?= number_format($stats['transactions']['pending_transactions'] ?? 0) ?></p>
            </div>
            <div class="tw-w-10 tw-h-10 md:tw-w-12 md:tw-h-12 tw-rounded-xl tw-bg-yellow-100 tw-flex tw-items-center tw-justify-center">
                <i data-feather="clock" class="tw-h-5 tw-w-5 md:tw-h-6 md:tw-w-6 tw-text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Settings Form -->
<form id="paymentSettingsForm" class="tw-space-y-6">
    
    <!-- General Payment Settings -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-gap-3">
                <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                    <i data-feather="settings" class="tw-h-5 tw-w-5 tw-text-white"></i>
                </div>
                <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">General Settings</h2>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-6">
            <!-- Cash on Delivery -->
            <div class="tw-space-y-4">
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Cash on Delivery</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Allow customers to pay with cash upon delivery</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="cash_on_delivery_enabled" value="true" <?= isSettingEnabled($settings, 'cash_on_delivery_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-blue-600"></div>
                </label>
                </div>

                <!-- Trust-Based COD -->
                <div class="tw-p-5 tw-bg-gradient-to-r tw-from-blue-50 tw-to-indigo-50 tw-rounded-xl tw-border tw-border-blue-200">
                    <div class="tw-flex tw-items-start tw-justify-between tw-mb-4">
                        <div class="tw-flex-1">
                            <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                                <i data-feather="shield-check" class="tw-h-5 tw-w-5 tw-text-blue-600"></i>
                                <label class="tw-text-sm tw-font-semibold tw-text-gray-900">Trust-Based COD</label>
                            </div>
                            <p class="tw-text-xs tw-text-gray-600 tw-leading-relaxed">Only allow COD for trusted users with proven order history and payment reliability</p>
                        </div>
                        <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer tw-ml-4">
                            <input type="checkbox" name="cod_trust_based_enabled" value="true" <?= isSettingEnabled($settings, 'cod_trust_based_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                            <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <!-- Trust Score Requirements -->
                    <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-blue-100 tw-mb-4">
                        <h4 class="tw-text-sm tw-font-semibold tw-text-gray-800 tw-mb-3 tw-flex tw-items-center tw-gap-2">
                            <i data-feather="target" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                            Trust Requirements
                        </h4>
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label class="tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-1 tw-block">Minimum Trust Score</label>
                                <input type="number" name="cod_minimum_score" value="<?= getSettingValue($settings, 'cod_minimum_score', '70') ?>" 
                                       class="tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                                       min="0" max="100" placeholder="70">
                                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Score out of 100 (default: 70)</p>
                            </div>
                            <div>
                                <label class="tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-1 tw-block">Minimum Orders</label>
                                <input type="number" name="cod_minimum_orders" value="<?= getSettingValue($settings, 'cod_minimum_orders', '50') ?>" 
                                       class="tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                                       min="1" max="1000" placeholder="50">
                                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Required completed orders</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trust Check Tools -->
                    <div class="tw-bg-white tw-p-4 tw-rounded-lg tw-border tw-border-blue-100">
                        <div class="tw-mb-4">
                            <h4 class="tw-text-sm tw-font-semibold tw-text-gray-800 tw-mb-1 tw-flex tw-items-center tw-gap-2">
                                <i data-feather="user-check" class="tw-h-4 tw-w-4 tw-text-blue-600"></i>
                                User Trust Checker
                            </h4>
                            <p class="tw-text-xs tw-text-gray-500">Enter a User ID to check their trust score and COD eligibility</p>
                        </div>
                        <div class="tw-flex tw-gap-3 tw-items-end">
                            <div class="tw-flex-1">
                                <label class="tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-1 tw-block">User ID</label>
                                <input type="number" id="checkUserId" placeholder="Enter User ID" 
                                       class="tw-w-full tw-px-3 tw-py-2.5 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                            </div>
                            <button type="button" onclick="checkUserCOD()" 
                                    class="tw-px-6 tw-py-2.5 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-blue-700 tw-transition-colors tw-flex tw-items-center tw-gap-2 tw-whitespace-nowrap tw-shadow-sm">
                                <i data-feather="search" class="tw-h-4 tw-w-4"></i>
                                Check Eligibility
                            </button>
                        </div>
                        <div id="codCheckResult" class="tw-mt-4 tw-hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Minimum Order Amount -->
            <div class="tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex tw-items-center tw-gap-4">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">Minimum Order Amount</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Minimum order value required to place an order</p>
                    </div>
                    <div class="tw-w-32">
                        <input type="number" name="minimum_order_amount" value="<?= getSettingValue($settings, 'minimum_order_amount', '200') ?>" 
                               class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" 
                               min="0" step="50" placeholder="200">
                        <p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-text-center">XAF</p>
                    </div>
                </div>
            </div>

            <!-- Online Payment -->
            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Online Payment</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Enable online payment processing</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="online_payment_enabled" value="true" <?= isSettingEnabled($settings, 'online_payment_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-blue-600"></div>
                </label>
            </div>

            <!-- Payment Processing Fee -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Payment Processing Fee (%)</label>
                <input type="number" name="payment_processing_fee" step="0.001" value="<?= htmlspecialchars(getSettingValue($settings, 'payment_processing_fee', '0')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="0">
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Fee charged for processing online payments (e.g., 0.025 = 2.5%)</p>
            </div>

            <!-- Payment Limits -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Minimum Payment Amount (XAF)</label>
                    <input type="number" name="minimum_payment_amount" value="<?= htmlspecialchars(getSettingValue($settings, 'minimum_payment_amount', '100')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="100">
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Minimum payment amount allowed</p>
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Maximum Payment Amount (XAF)</label>
                    <input type="number" name="maximum_payment_amount" value="<?= htmlspecialchars(getSettingValue($settings, 'maximum_payment_amount', '100000')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="100000">
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Maximum payment amount allowed</p>
                </div>
            </div>

            <!-- Payment Timeout -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Payment Timeout (Minutes)</label>
                <input type="number" name="payment_timeout_minutes" value="<?= htmlspecialchars(getSettingValue($settings, 'payment_timeout_minutes', '30')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="30">
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">How long to wait for payment completion before timing out</p>
            </div>

            <!-- Payment Options -->
            <div class="tw-space-y-3">
                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">Auto Capture Payments</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Automatically capture payments when authorized</p>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="auto_capture_payments" value="true" <?= isSettingEnabled($settings, 'auto_capture_payments') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                        <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-blue-600"></div>
                    </label>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">Require Payment Confirmation</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Require manual confirmation for all payments</p>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="require_payment_confirmation" value="true" <?= isSettingEnabled($settings, 'require_payment_confirmation') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                        <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Configuration -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-green-500 tw-to-green-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="credit-card" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Payment Methods</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">Configure available payment methods</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <!-- Payment Methods Toggles -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">Tranzak Gateway</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Enable Tranzak mobile money payments</p>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="tranzak_enabled" value="true" <?= isSettingEnabled($settings, 'tranzak_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                        <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-green-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-green-600"></div>
                    </label>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">MTN Mobile Money</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Enable MTN Mobile Money payments</p>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="mtn_momo_enabled" value="true" <?= isSettingEnabled($settings, 'mtn_momo_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                        <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-green-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-green-600"></div>
                    </label>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">Orange Money</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Enable Orange Money payments</p>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="orange_money_enabled" value="true" <?= isSettingEnabled($settings, 'orange_money_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                        <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-green-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-green-600"></div>
                    </label>
                </div>

                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                    <div class="tw-flex-1">
                        <label class="tw-text-sm tw-font-medium tw-text-gray-900">PayPal</label>
                        <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Enable PayPal payments</p>
                    </div>
                    <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                        <input type="checkbox" name="paypal_enabled" value="true" <?= isSettingEnabled($settings, 'paypal_enabled') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                        <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-green-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-green-600"></div>
                    </label>
                </div>
            </div>

            <!-- Payment Currency -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Payment Currency</label>
                <select name="payment_currency" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-green-500 focus:tw-border-green-500">
                    <option value="XAF" <?= getSettingValue($settings, 'payment_currency', 'XAF') === 'XAF' ? 'selected' : '' ?>>XAF (Central African Franc)</option>
                    <option value="USD" <?= getSettingValue($settings, 'payment_currency', 'XAF') === 'USD' ? 'selected' : '' ?>>USD (US Dollar)</option>
                    <option value="EUR" <?= getSettingValue($settings, 'payment_currency', 'XAF') === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                </select>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Default currency for all payments</p>
            </div>
        </div>
    </div>

    <!-- Refund Policy Configuration -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-red-500 tw-to-red-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="rotate-ccw" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Refund Policy</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">Configure refund and cancellation policies</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Refund Policy Days</label>
                <input type="number" name="refund_policy_days" value="<?= htmlspecialchars(getSettingValue($settings, 'refund_policy_days', '7')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-red-500 focus:tw-border-red-500" placeholder="7">
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Number of days customers can request refunds after delivery</p>
            </div>

            <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-4">
                <div class="tw-flex tw-gap-3">
                    <i data-feather="info" class="tw-h-5 tw-w-5 tw-text-red-600 tw-flex-shrink-0 tw-mt-0.5"></i>
                    <div class="tw-text-xs tw-text-red-900">
                        <p class="tw-font-medium tw-mb-1">Refund Policy Information</p>
                        <p>This setting determines how long customers have to request refunds after their order is delivered. Refunds are subject to your terms and conditions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tranzak Payment Gateway -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-purple-500 tw-to-purple-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="smartphone" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Tranzak Payment Gateway</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">Mobile Money & Bank Transfers (Cameroon)</p>
                    </div>
                </div>
                <button type="button" onclick="testGateway('tranzak')" class="tw-px-3 tw-py-1.5 tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-text-xs tw-font-medium tw-transition-all">
                    <i data-feather="zap" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>Test
                </button>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-xl tw-p-4">
                <div class="tw-flex tw-gap-3">
                    <i data-feather="check-circle" class="tw-h-5 tw-w-5 tw-text-green-600 tw-flex-shrink-0 tw-mt-0.5"></i>
                    <div class="tw-text-xs tw-text-green-900">
                        <p class="tw-font-medium tw-mb-1">✅ Tranzak Configuration Complete</p>
                           <p><strong>App:</strong> Delta invest - API (aps1rr28n2qxbs) | <strong>Status:</strong> Production & Sandbox Ready</p>
                        <div class="tw-mt-2 tw-flex tw-gap-4 tw-text-xs">
                            <div class="tw-flex tw-items-center tw-gap-1">
                                <div class="tw-w-2 tw-h-2 tw-bg-green-500 tw-rounded-full"></div>
                                <span>Production: PROD_***18BB9F08</span>
                            </div>
                            <div class="tw-flex tw-items-center tw-gap-1">
                                <div class="tw-w-2 tw-h-2 tw-bg-blue-500 tw-rounded-full"></div>
                                <span>Sandbox: SAND_***AC7DA448</span>
                            </div>
                        </div>
                        <p class="tw-mt-2">Tranzak enables seamless payments via MTN Mobile Money, Orange Money, and bank transfers. <a href="https://tranzak.net" target="_blank" class="tw-underline tw-font-medium">Visit Tranzak Dashboard</a></p>
                    </div>
                </div>
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">App ID / Merchant ID</label>
                   <input type="text" name="tranzak_app_id" value="<?= htmlspecialchars(getSettingValue($settings, 'tranzak_app_id', 'aps1rr28n2qxbs')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-purple-500" placeholder="Your Tranzak App ID">
                   <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Current: Delta invest - API (aps1rr28n2qxbs)</p>
            </div>

            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Production API Key</label>
                    <input type="password" name="tranzak_api_key" value="<?= htmlspecialchars(getSettingValue($settings, 'tranzak_api_key')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-purple-500" placeholder="PROD_...">
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Production API key for live payments</p>
                </div>
            <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Sandbox API Key</label>
                    <input type="password" name="tranzak_sandbox_api_key" value="<?= htmlspecialchars(getSettingValue($settings, 'tranzak_sandbox_api_key')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-purple-500" placeholder="SAND_...">
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Sandbox API key for testing</p>
                </div>
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">API Secret (Optional)</label>
                <input type="password" name="tranzak_api_secret" value="<?= htmlspecialchars(getSettingValue($settings, 'tranzak_api_secret')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-purple-500 focus:tw-border-purple-500" placeholder="Your Tranzak API Secret">
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">API secret for enhanced security (if provided by Tranzak)</p>
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Webhook URL</label>
                <div class="tw-flex tw-gap-2">
                    <input type="text" readonly value="<?= url('/api/payments/webhook/tranzak') ?>" class="tw-flex-1 tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-100 tw-text-gray-600" id="tranzak-webhook">
                    <button type="button" onclick="copyToClipboard('tranzak-webhook')" class="tw-px-4 tw-py-3 tw-bg-gray-200 hover:tw-bg-gray-300 tw-rounded-xl tw-transition-colors">
                        <i data-feather="copy" class="tw-h-4 tw-w-4 tw-text-gray-700"></i>
                    </button>
                </div>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Configure this URL in your Tranzak dashboard</p>
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Sandbox Mode</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Use test environment for development</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="tranzak_sandbox_mode" value="true" <?= isSettingEnabled($settings, 'tranzak_sandbox_mode') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-purple-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-purple-600"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- Stripe Payment Gateway -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-indigo-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="credit-card" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Stripe</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">Credit & Debit Cards</p>
                    </div>
                </div>
                <button type="button" onclick="testGateway('stripe')" class="tw-px-3 tw-py-1.5 tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-text-xs tw-font-medium tw-transition-all">
                    <i data-feather="zap" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>Test
                </button>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Publishable Key</label>
                <input type="text" name="stripe_publishable_key" value="<?= htmlspecialchars(getSettingValue($settings, 'stripe_publishable_key')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-border-indigo-500" placeholder="pk_test_...">
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Secret Key</label>
                <input type="password" name="stripe_secret_key" value="<?= htmlspecialchars(getSettingValue($settings, 'stripe_secret_key')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-indigo-500 focus:tw-border-indigo-500" placeholder="sk_test_...">
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Test Mode</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Use test keys for development</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="stripe_test_mode" value="true" <?= isSettingEnabled($settings, 'stripe_test_mode') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-indigo-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-indigo-600"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- PayPal Payment Gateway -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-blue-600 tw-to-blue-700 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="dollar-sign" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">PayPal</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">PayPal & Credit Cards</p>
                    </div>
                </div>
                <button type="button" onclick="testGateway('paypal')" class="tw-px-3 tw-py-1.5 tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-text-xs tw-font-medium tw-transition-all">
                    <i data-feather="zap" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>Test
                </button>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Client ID</label>
                <input type="text" name="paypal_client_id" value="<?= htmlspecialchars(getSettingValue($settings, 'paypal_client_id')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="Your PayPal Client ID">
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Client Secret</label>
                <input type="password" name="paypal_client_secret" value="<?= htmlspecialchars(getSettingValue($settings, 'paypal_client_secret')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" placeholder="Your PayPal Client Secret">
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Sandbox Mode</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Use sandbox for testing</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="paypal_sandbox_mode" value="true" <?= isSettingEnabled($settings, 'paypal_sandbox_mode') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-blue-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- Orange Money -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-orange-500 tw-to-orange-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="smartphone" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">Orange Money</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">Mobile Money (Orange)</p>
                    </div>
                </div>
                <button type="button" onclick="testGateway('orange_money')" class="tw-px-3 tw-py-1.5 tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-text-xs tw-font-medium tw-transition-all">
                    <i data-feather="zap" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>Test
                </button>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">Merchant ID</label>
                <input type="text" name="orange_money_merchant_id" value="<?= htmlspecialchars(getSettingValue($settings, 'orange_money_merchant_id')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500" placeholder="Your Orange Money Merchant ID">
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">API Key</label>
                <input type="password" name="orange_money_api_key" value="<?= htmlspecialchars(getSettingValue($settings, 'orange_money_api_key')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-orange-500 focus:tw-border-orange-500" placeholder="Your Orange Money API Key">
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Sandbox Mode</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Use test environment for development</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="orange_money_sandbox_mode" value="true" <?= isSettingEnabled($settings, 'orange_money_sandbox_mode') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-orange-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-orange-600"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- MTN Mobile Money -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-yellow-500 tw-to-yellow-600 tw-px-6 tw-py-4">
            <div class="tw-flex tw-items-center tw-justify-between">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-white/20 tw-flex tw-items-center tw-justify-center">
                        <i data-feather="smartphone" class="tw-h-5 tw-w-5 tw-text-white"></i>
                    </div>
                    <div>
                        <h2 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-white">MTN Mobile Money</h2>
                        <p class="tw-text-xs tw-text-white/80 tw-mt-0.5">Mobile Money (MTN)</p>
                    </div>
                </div>
                <button type="button" onclick="testGateway('mtn_momo')" class="tw-px-3 tw-py-1.5 tw-bg-white/20 hover:tw-bg-white/30 tw-text-white tw-rounded-lg tw-text-xs tw-font-medium tw-transition-all">
                    <i data-feather="zap" class="tw-h-3 tw-w-3 tw-inline tw-mr-1"></i>Test
                </button>
            </div>
        </div>
        <div class="tw-p-6 tw-space-y-4">
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">API Key</label>
                <input type="password" name="mtn_momo_api_key" value="<?= htmlspecialchars(getSettingValue($settings, 'mtn_momo_api_key')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-yellow-500 focus:tw-border-yellow-500" placeholder="Your MTN MoMo API Key">
            </div>

            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">User ID</label>
                <input type="text" name="mtn_momo_user_id" value="<?= htmlspecialchars(getSettingValue($settings, 'mtn_momo_user_id')) ?>" class="tw-w-full tw-px-4 tw-py-3 tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-yellow-500 focus:tw-border-yellow-500" placeholder="Your MTN MoMo User ID">
            </div>

            <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-gray-50 tw-rounded-xl">
                <div class="tw-flex-1">
                    <label class="tw-text-sm tw-font-medium tw-text-gray-900">Sandbox Mode</label>
                    <p class="tw-text-xs tw-text-gray-600 tw-mt-1">Use test environment for development</p>
                </div>
                <label class="tw-relative tw-inline-flex tw-items-center tw-cursor-pointer">
                    <input type="checkbox" name="mtn_momo_sandbox_mode" value="true" <?= isSettingEnabled($settings, 'mtn_momo_sandbox_mode') ? 'checked' : '' ?> class="tw-sr-only tw-peer">
                    <div class="tw-w-11 tw-h-6 tw-bg-gray-300 peer-focus:tw-outline-none peer-focus:tw-ring-4 peer-focus:tw-ring-yellow-300 tw-rounded-full tw-peer peer-checked:after:tw-translate-x-full peer-checked:after:tw-border-white after:tw-content-[''] after:tw-absolute after:tw-top-[2px] after:tw-left-[2px] after:tw-bg-white after:tw-border-gray-300 after:tw-border after:tw-rounded-full after:tw-h-5 after:tw-w-5 after:tw-transition-all peer-checked:tw-bg-yellow-600"></div>
                </label>
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

function saveSettings() {
    const form = document.getElementById('paymentSettingsForm');
    const formData = new FormData(form);

    // Show loading state
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Saving...';
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    fetch('<?= url('/admin/payment-settings/save') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Payment settings saved successfully!', 'success');
        } else {
            showMessage(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalHTML;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}

function testGateway(gateway) {
    const formData = new FormData();
    formData.append('gateway', gateway);

    showMessage(`Testing ${gateway} connection...`, 'info');

    fetch('<?= url('/admin/payment-settings/test-gateway') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'Gateway connection successful!', 'success');
        } else {
            showMessage(data.message || 'Gateway connection failed', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
}

function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    document.execCommand('copy');
    showMessage('Copied to clipboard!', 'success');
}

function showMessage(message, type) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `tw-fixed tw-top-4 tw-right-4 tw-px-6 tw-py-4 tw-rounded-xl tw-shadow-lg tw-z-50 tw-flex tw-items-center tw-gap-3 tw-transition-all tw-duration-300 ${
        type === 'success' ? 'tw-bg-green-500' :
        type === 'error' ? 'tw-bg-red-500' :
        'tw-bg-blue-500'
    } tw-text-white`;

    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info';
    toast.innerHTML = `
        <i data-feather="${icon}" class="tw-h-5 tw-w-5"></i>
        <span class="tw-font-medium">${message}</span>
    `;

    document.body.appendChild(toast);
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// COD Trust Checking Functions
function checkUserCOD() {
    const userId = document.getElementById('checkUserId').value;
    const resultDiv = document.getElementById('codCheckResult');
    
    if (!userId) {
        showMessage('Please enter a User ID', 'error');
        return;
    }
    
    // Show loading state
    resultDiv.innerHTML = '<div class="tw-flex tw-items-center tw-gap-2 tw-text-blue-600"><i data-feather="loader" class="tw-h-4 tw-w-4 tw-animate-spin"></i>Checking user eligibility...</div>';
    resultDiv.classList.remove('tw-hidden');
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    fetch('<?= url('/admin/payment-settings/check-cod-eligibility') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCODResult(data);
        } else {
            resultDiv.innerHTML = `<div class="tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl">
                <div class="tw-flex tw-items-center tw-gap-2 tw-text-red-600 tw-mb-2">
                    <i data-feather="x-circle" class="tw-h-5 tw-w-5"></i>
                    <span class="tw-font-medium">Error</span>
                </div>
                <p class="tw-text-sm tw-text-red-700">${data.message}</p>
            </div>`;
        }
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `<div class="tw-p-4 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl">
            <div class="tw-flex tw-items-center tw-gap-2 tw-text-red-600 tw-mb-2">
                <i data-feather="x-circle" class="tw-h-5 tw-w-5"></i>
                <span class="tw-font-medium">Network Error</span>
            </div>
            <p class="tw-text-sm tw-text-red-700">Failed to check user eligibility. Please try again.</p>
        </div>`;
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}

function displayCODResult(data) {
    const resultDiv = document.getElementById('codCheckResult');
    const isEligible = data.eligible;
    const trustScore = data.trust_score;
    const summary = data.summary;
    
    const statusColor = isEligible ? 'green' : 'red';
    const statusIcon = isEligible ? 'check-circle' : 'x-circle';
    const statusText = isEligible ? 'Eligible for COD' : 'Not Eligible for COD';
    
    resultDiv.innerHTML = `
        <div class="tw-p-4 tw-bg-${statusColor}-50 tw-border tw-border-${statusColor}-200 tw-rounded-xl">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                <div class="tw-flex tw-items-center tw-gap-2 tw-text-${statusColor}-600">
                    <i data-feather="${statusIcon}" class="tw-h-5 tw-w-5"></i>
                    <span class="tw-font-medium">${statusText}</span>
                </div>
                <div class="tw-text-sm tw-font-bold tw-text-${statusColor}-700">
                    Trust Score: ${trustScore.total_score}/${trustScore.max_possible} (${trustScore.percentage}%)
                </div>
            </div>
            
            <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-4 tw-mb-4">
                <div class="tw-text-center">
                    <div class="tw-text-lg tw-font-bold tw-text-gray-900">${summary.user?.total_orders || 0}</div>
                    <div class="tw-text-xs tw-text-gray-600">Total Orders</div>
                </div>
                <div class="tw-text-center">
                    <div class="tw-text-lg tw-font-bold tw-text-gray-900">${summary.user?.completed_orders || 0}</div>
                    <div class="tw-text-xs tw-text-gray-600">Completed</div>
                </div>
                <div class="tw-text-center">
                    <div class="tw-text-lg tw-font-bold tw-text-gray-900">${summary.user?.avg_rating ? parseFloat(summary.user.avg_rating).toFixed(1) : 'N/A'}</div>
                    <div class="tw-text-xs tw-text-gray-600">Avg Rating</div>
                </div>
            </div>
            
            <div class="tw-mb-3">
                <div class="tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-2">Trust Score Breakdown:</div>
                <div class="tw-grid tw-grid-cols-2 tw-gap-2 tw-text-xs">
                    <div class="tw-flex tw-justify-between">
                        <span>Order History:</span>
                        <span class="tw-font-medium">${trustScore.breakdown.order_history}/100</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Payment Reliability:</span>
                        <span class="tw-font-medium">${trustScore.breakdown.payment_reliability}/100</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Account Age:</span>
                        <span class="tw-font-medium">${trustScore.breakdown.account_age}/100</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Delivery Success:</span>
                        <span class="tw-font-medium">${trustScore.breakdown.delivery_success}/100</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Cancellation Rate:</span>
                        <span class="tw-font-medium">${trustScore.breakdown.cancellation_rate}/100</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Rating Consistency:</span>
                        <span class="tw-font-medium">${trustScore.breakdown.rating_consistency}/100</span>
                    </div>
                </div>
            </div>
            
            <div class="tw-text-sm tw-text-gray-700">
                <div class="tw-font-medium tw-mb-1">Reason:</div>
                <div>${data.reason}</div>
            </div>
            
            ${summary.recommendations && summary.recommendations.length > 0 ? `
                <div class="tw-mt-3 tw-pt-3 tw-border-t tw-border-${statusColor}-200">
                    <div class="tw-text-xs tw-font-medium tw-text-gray-700 tw-mb-1">Recommendations:</div>
                    <ul class="tw-text-xs tw-text-gray-600 tw-space-y-1">
                        ${summary.recommendations.map(rec => `<li>• ${rec}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
    
    resultDiv.classList.remove('tw-hidden');
}

// Load COD trust statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCODTrustStats();
});

function loadCODTrustStats() {
    fetch('<?= url('/admin/payment-settings/cod-trust-stats') ?>')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // You can display these stats somewhere on the page if needed
            console.log('COD Trust Stats:', data.stats);
        }
    })
    .catch(error => {
        console.error('Error loading COD trust stats:', error);
    });
}
</script>

