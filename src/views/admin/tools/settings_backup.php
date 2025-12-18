<?php
/**
 * Admin Site Settings Management
 * Edit contact information, social media, and site configuration
 */

$title = $title ?? 'Site Settings - Time2Eat Admin';
$user = $user ?? [];
$settings = $settings ?? [];

// Set current page for sidebar highlighting
$currentPage = 'settings';

// Helper function for social media icons
function getSocialIcon($key) {
    $icons = [
        'facebook_url' => 'facebook',
        'twitter_url' => 'twitter', 
        'instagram_url' => 'instagram',
        'youtube_url' => 'youtube',
        'linkedin_url' => 'linkedin',
        'tiktok_url' => 'music',
        'whatsapp_number' => 'message-circle'
    ];
    return $icons[$key] ?? 'link';
}
?>

<!-- Mobile-First Page Header -->
<div class="tw-mb-6 sm:tw-mb-8">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-text-gray-900">Site Settings</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage site configuration, contact information, and social media links
            </p>
        </div>
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2 sm:tw-gap-3">
            <button type="button" class="tw-w-full sm:tw-w-auto tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-transition-colors">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Export Settings
            </button>
            <button id="saveAllSettings" class="tw-w-full sm:tw-w-auto tw-bg-primary-600 tw-border tw-border-transparent tw-rounded-lg tw-px-4 tw-py-2.5 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-primary-700 tw-transition-colors">
                <i data-feather="save" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                Save All Changes
            </button>
        </div>
    </div>
</div>

<!-- Mobile-First Settings Form -->
<div class="tw-space-y-6 sm:tw-space-y-8">
    <form id="settingsForm" class="tw-space-y-6 sm:tw-space-y-8">
        <!-- General Settings -->
        <?php if (isset($settings['general'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow-sm tw-rounded-xl tw-border tw-border-gray-200">
            <div class="tw-px-4 tw-py-5 sm:tw-px-6 sm:tw-py-6">
                <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-mb-6">
                    <div>
                        <h3 class="tw-text-lg sm:tw-text-xl tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                            <i data-feather="settings" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-primary-600"></i>
                            General Settings
                        </h3>
                        <p class="tw-mt-1 tw-text-sm tw-text-gray-600">Basic site information and branding</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 tw-px-3 tw-py-1.5 tw-rounded-lg hover:tw-bg-gray-50 tw-transition-colors" data-group="general">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-gap-6 lg:tw-grid-cols-2">
                    <?php foreach ($settings['general'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <?php if ($setting['type'] === 'text'): ?>
                                    <textarea id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>" rows="3"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['value'] ?? '') ?></textarea>
                                <?php else: ?>
                                    <input type="<?= $setting['type'] === 'boolean' ? 'checkbox' : 'text' ?>" 
                                           id="<?= $key ?>" 
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                           <?= $setting['type'] === 'boolean' && $setting['value'] === 'true' ? 'checked' : '' ?>
                                           class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                           placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contact Settings -->
        <?php if (isset($settings['contact'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Contact Information</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Business contact details and support information</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="contact">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <?php foreach ($settings['contact'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <?php if ($setting['type'] === 'text'): ?>
                                    <textarea id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>" rows="3"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['value'] ?? '') ?></textarea>
                                <?php else: ?>
                                    <input type="text" 
                                           id="<?= $key ?>" 
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                           class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                           placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Social Media Settings -->
        <?php if (isset($settings['social'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Social Media Links</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Social media profiles and contact channels</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="social">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <?php foreach ($settings['social'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <i data-feather="<?= getSocialIcon($key) ?>" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                <?= ucwords(str_replace(['_', 'url'], [' ', ''], $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <input type="url" 
                                       id="<?= $key ?>" 
                                       name="<?= htmlspecialchars($key) ?>"
                                       value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                       class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                       placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Business Settings -->
        <?php if (isset($settings['business'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Business Settings</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Pricing, fees, and business configuration</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="business">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <?php foreach ($settings['business'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <input type="<?= $setting['type'] === 'integer' ? 'number' : ($setting['type'] === 'float' ? 'number' : 'text') ?>" 
                                       id="<?= $key ?>" 
                                       name="<?= htmlspecialchars($key) ?>"
                                       value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                       <?= $setting['type'] === 'float' ? 'step="0.01"' : '' ?>
                                       class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                       placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SEO Settings -->
        <?php if (isset($settings['seo'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">SEO & Analytics</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Search engine optimization and tracking</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="seo">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6">
                    <?php foreach ($settings['seo'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <?php if ($key === 'meta_keywords'): ?>
                                    <textarea id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>" rows="3"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['value'] ?? '') ?></textarea>
                                <?php else: ?>
                                    <input type="text" 
                                           id="<?= $key ?>" 
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                           class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                           placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Maps & Location Settings -->
        <?php if (isset($settings['maps'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Maps & Location</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Map API keys and location tracking settings</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="maps">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <?php foreach ($settings['maps'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <i data-feather="map-pin" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <?php if ($setting['type'] === 'boolean'): ?>
                                    <select id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md">
                                        <option value="true" <?= $setting['value'] === 'true' ? 'selected' : '' ?>>Enabled</option>
                                        <option value="false" <?= $setting['value'] === 'false' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= $setting['type'] === 'integer' ? 'number' : 'text' ?>"
                                           id="<?= $key ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                           class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                           placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment Gateway Settings -->
        <?php if (isset($settings['payment'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Payment Gateways</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Payment methods and gateway configurations</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="payment">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <?php foreach ($settings['payment'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <i data-feather="credit-card" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <?php if ($setting['type'] === 'boolean'): ?>
                                    <select id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md">
                                        <option value="true" <?= $setting['value'] === 'true' ? 'selected' : '' ?>>Enabled</option>
                                        <option value="false" <?= $setting['value'] === 'false' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                <?php elseif ($setting['type'] === 'json'): ?>
                                    <textarea id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>" rows="3"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                        placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['value'] ?? '') ?></textarea>
                                <?php else: ?>
                                    <input type="<?= $setting['type'] === 'integer' ? 'number' : ($setting['type'] === 'float' ? 'number' : 'text') ?>"
                                           id="<?= $key ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                           <?= $setting['type'] === 'float' ? 'step="0.001"' : '' ?>
                                           class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                           placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Currency & Pricing Settings -->
        <?php if (isset($settings['currency'])): ?>
        <div class="tw-bg-white tw-overflow-hidden tw-shadow tw-rounded-lg">
            <div class="tw-px-4 tw-py-5 sm:tw-p-6">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900">Currency & Pricing</h3>
                        <p class="tw-mt-1 tw-max-w-2xl tw-text-sm tw-text-gray-500">Currency formatting and exchange rate settings</p>
                    </div>
                    <button type="button" class="reset-group tw-text-sm tw-text-gray-500 hover:tw-text-gray-700" data-group="currency">
                        <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-inline tw-mr-1"></i>
                        Reset to defaults
                    </button>
                </div>
                <div class="tw-grid tw-grid-cols-1 tw-gap-6 sm:tw-grid-cols-2">
                    <?php foreach ($settings['currency'] as $key => $setting): ?>
                        <div>
                            <label for="<?= $key ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">
                                <i data-feather="dollar-sign" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>
                                <?= ucwords(str_replace('_', ' ', $key)) ?>
                            </label>
                            <div class="tw-mt-1">
                                <?php if ($setting['type'] === 'boolean'): ?>
                                    <select id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md">
                                        <option value="true" <?= $setting['value'] === 'true' ? 'selected' : '' ?>>Enabled</option>
                                        <option value="false" <?= $setting['value'] === 'false' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                <?php elseif ($key === 'currency_position'): ?>
                                    <select id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>"
                                        class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md">
                                        <option value="before" <?= $setting['value'] === 'before' ? 'selected' : '' ?>>Before amount (FCFA 1,000)</option>
                                        <option value="after" <?= $setting['value'] === 'after' ? 'selected' : '' ?>>After amount (1,000 FCFA)</option>
                                    </select>
                                <?php else: ?>
                                    <input type="<?= $setting['type'] === 'integer' ? 'number' : ($setting['type'] === 'float' ? 'number' : 'text') ?>"
                                           id="<?= $key ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                           <?= $setting['type'] === 'float' ? 'step="0.01"' : '' ?>
                                           class="tw-shadow-sm focus:tw-ring-primary-500 focus:tw-border-primary-500 tw-block tw-w-full sm:tw-text-sm tw-border-gray-300 tw-rounded-md"
                                           placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($setting['description'])): ?>
                                <p class="tw-mt-2 tw-text-sm tw-text-gray-500"><?= htmlspecialchars($setting['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>

<script>
// Settings management functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settingsForm');
    const saveButton = document.getElementById('saveAllSettings');
    const resetButtons = document.querySelectorAll('.reset-group');

    // Save all settings
    saveButton.addEventListener('click', function() {
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        saveButton.disabled = true;
        saveButton.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-inline tw-mr-2 tw-animate-spin"></i>Saving...';

        fetch('<?= url('/admin/tools/settings/update') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Settings saved successfully!', 'success');
            } else {
                showNotification(data.message || 'Failed to save settings', 'error');
            }
        })
        .catch(error => {
            showNotification('Error saving settings', 'error');
        })
        .finally(() => {
            saveButton.disabled = false;
            saveButton.innerHTML = '<i data-feather="save" class="tw-h-4 tw-w-4 tw-inline tw-mr-2"></i>Save All Changes';
            feather.replace();
        });
    });

    // Reset group settings
    resetButtons.forEach(button => {
        button.addEventListener('click', function() {
            const group = this.dataset.group;

            if (confirm(`Are you sure you want to reset all ${group} settings to defaults?`)) {
                fetch('<?= url('/admin/tools/settings/reset') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ group: group })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification(data.message || 'Failed to reset settings', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error resetting settings', 'error');
                });
            }
        });
    });

    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `tw-fixed tw-top-4 tw-right-4 tw-p-4 tw-rounded-lg tw-shadow-lg tw-z-50 ${
            type === 'success' ? 'tw-bg-green-500 tw-text-white' :
            type === 'error' ? 'tw-bg-red-500 tw-text-white' :
            'tw-bg-blue-500 tw-text-white'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Initialize Feather icons
    feather.replace();
});
</script>
