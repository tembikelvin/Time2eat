<?php
/**
 * Admin Site Settings Management
 * Mobile-first design matching customer profile page style
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

// Helper function for section icons
function getSectionIcon($section) {
    $icons = [
        'general' => 'settings',
        'contact' => 'phone',
        'social' => 'share-2',
        'business' => 'briefcase',
        'seo' => 'search',
        'auth' => 'shield',
        'system' => 'server',
        'maps' => 'map-pin',
        'payment' => 'credit-card',
        'currency' => 'dollar-sign'
    ];
    return $icons[$section] ?? 'folder';
}

// Helper function for section gradient colors
function getSectionGradient($section) {
    $gradients = [
        'general' => 'tw-from-blue-500 tw-to-blue-600',
        'contact' => 'tw-from-purple-500 tw-to-purple-600',
        'social' => 'tw-from-pink-500 tw-to-pink-600',
        'business' => 'tw-from-green-500 tw-to-green-600',
        'seo' => 'tw-from-orange-500 tw-to-orange-600',
        'auth' => 'tw-from-red-500 tw-to-red-600',
        'system' => 'tw-from-gray-500 tw-to-gray-600',
        'maps' => 'tw-from-teal-500 tw-to-teal-600',
        'payment' => 'tw-from-indigo-500 tw-to-indigo-600',
        'currency' => 'tw-from-yellow-500 tw-to-yellow-600'
    ];
    return $gradients[$section] ?? 'tw-from-blue-500 tw-to-blue-600';
}

?>

<!-- Page Header -->
<div class="tw-mb-6 md:tw-mb-8">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center">
            <div class="tw-p-3 md:tw-p-4 tw-rounded-2xl tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 tw-mr-3 md:tw-mr-4 tw-shadow-lg">
                <i data-feather="settings" class="tw-h-6 tw-w-6 md:tw-h-8 md:tw-w-8 tw-text-white"></i>
            </div>
            <div>
                <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-gray-900">Site Settings</h1>
                <p class="tw-mt-1 tw-text-xs md:tw-text-sm tw-text-gray-500 tw-flex tw-items-center">
                    <i data-feather="shield" class="tw-h-3 tw-w-3 md:tw-h-4 md:tw-w-4 tw-mr-1"></i>
                    Manage site configuration and system settings
                </p>
            </div>
        </div>
        <div class="tw-flex tw-items-center tw-gap-2 md:tw-gap-3">
            <button type="button" onclick="exportSettings()"
                    class="tw-flex-1 md:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-px-4 md:tw-px-6 tw-py-2 md:tw-py-3 tw-border tw-border-gray-300 tw-rounded-xl tw-shadow-sm tw-text-xs md:tw-text-sm tw-font-semibold tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200">
                <i data-feather="download" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <span class="tw-hidden md:tw-inline">Export</span>
            </button>
            <button id="saveAllSettings"
                    class="tw-flex-1 md:tw-flex-none tw-inline-flex tw-items-center tw-justify-center tw-px-4 md:tw-px-6 tw-py-2 md:tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-lg tw-text-xs md:tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 hover:tw-from-blue-600 hover:tw-to-blue-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200 tw-ease-in-out tw-transform hover:tw-scale-105 active:tw-scale-95">
                <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                Save All
            </button>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="settingsMessages" class="tw-mb-6 tw-hidden">
    <div id="successMessage" class="tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-xl tw-shadow-sm tw-p-4 tw-mb-4 tw-hidden">
        <div class="tw-flex tw-items-center">
            <i data-feather="check-circle" class="tw-w-5 tw-h-5 tw-text-green-600 tw-mr-3 tw-flex-shrink-0"></i>
            <div>
                <h3 class="tw-text-sm tw-font-semibold tw-text-green-800">Settings Updated</h3>
                <p class="tw-mt-1 tw-text-xs md:tw-text-sm tw-text-green-700" id="successText"></p>
            </div>
        </div>
    </div>
    <div id="errorMessage" class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-shadow-sm tw-p-4 tw-mb-4 tw-hidden">
        <div class="tw-flex tw-items-start">
            <i data-feather="alert-circle" class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-3 tw-mt-0.5 tw-flex-shrink-0"></i>
            <div>
                <h3 class="tw-text-sm tw-font-semibold tw-text-red-800">Error</h3>
                <p class="tw-mt-1 tw-text-xs md:tw-text-sm tw-text-red-700" id="errorText"></p>
            </div>
        </div>
    </div>
</div>

<!-- Settings Form -->
<form id="settingsForm" class="tw-space-y-6 md:tw-space-y-8">

    <?php foreach ($settings as $sectionKey => $sectionSettings): ?>
    <!-- <?= ucfirst($sectionKey) ?> Settings Section -->
    <div class="tw-bg-white tw-rounded-2xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-4 md:tw-p-8">
        <!-- Section Header -->
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-mb-6 tw-gap-4">
            <div class="tw-flex tw-items-center">
                <div class="tw-w-10 tw-h-10 md:tw-w-12 md:tw-h-12 tw-bg-gradient-to-r <?= getSectionGradient($sectionKey) ?> tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-mr-3 md:tw-mr-4 tw-shadow-lg tw-flex-shrink-0">
                    <i data-feather="<?= getSectionIcon($sectionKey) ?>" class="tw-w-5 tw-h-5 md:tw-w-6 md:tw-h-6 tw-text-white"></i>
                </div>
                <div>
                    <h3 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-gray-900">
                        <?= ucfirst($sectionKey) ?> Settings
                    </h3>
                    <p class="tw-text-xs md:tw-text-sm tw-text-gray-600 tw-mt-0.5">
                        <?php
                        $descriptions = [
                            'general' => 'Basic site information and branding',
                            'contact' => 'Business contact information and hours',
                            'social' => 'Social media links and profiles',
                            'business' => 'Business settings and configuration',
                            'seo' => 'Search engine optimization settings',
                            'auth' => 'Authentication and security settings',
                            'system' => 'System maintenance and configuration',
                            'maps' => 'Map services and location settings',
                            'payment' => 'Payment gateway and method configuration',
                            'currency' => 'Currency formatting and exchange rates'
                        ];
                        echo $descriptions[$sectionKey] ?? 'Configuration settings';
                        ?>
                    </p>
                </div>
            </div>
            <button type="button"
                    class="reset-group tw-self-start sm:tw-self-auto tw-text-xs md:tw-text-sm tw-text-gray-500 hover:tw-text-gray-700 tw-px-3 tw-py-2 tw-rounded-lg hover:tw-bg-gray-100 tw-transition-colors tw-flex tw-items-center tw-gap-2 tw-border tw-border-gray-200"
                    data-group="<?= $sectionKey ?>">
                <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4"></i>
                <span>Reset</span>
            </button>
        </div>

        <!-- Settings Fields -->
        <div class="tw-grid tw-grid-cols-1 <?= in_array($sectionKey, ['general', 'contact', 'business', 'maps']) ? 'md:tw-grid-cols-2' : '' ?> tw-gap-4 md:tw-gap-6">
            <?php foreach ($sectionSettings as $key => $setting): ?>
                <div class="tw-space-y-2">
                    <label for="<?= $key ?>" class="tw-block tw-text-xs md:tw-text-sm tw-font-semibold tw-text-gray-700 tw-flex tw-items-center">
                        <?php if ($sectionKey === 'social'): ?>
                            <i data-feather="<?= getSocialIcon($key) ?>" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-gray-500"></i>
                        <?php endif; ?>
                        <?= ucwords(str_replace('_', ' ', $key)) ?>
                    </label>

                    <div>
                        <?php if ($key === 'map_provider'): ?>
                            <!-- Special selector for map provider -->
                            <div class="tw-relative">
                                <select id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>"
                                    class="tw-w-full tw-px-4 tw-py-3 tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200 tw-appearance-none tw-cursor-pointer">
                                    <option value="leaflet" <?= ($setting['value'] ?? 'leaflet') === 'leaflet' ? 'selected' : '' ?>>
                                        🗺️ Leaflet (OpenStreetMap) - Free, No API Key Required
                                    </option>
                                    <option value="google" <?= ($setting['value'] ?? '') === 'google' ? 'selected' : '' ?>>
                                        🌍 Google Maps - Requires API Key
                                    </option>
                                </select>
                                <div class="tw-pointer-events-none tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-px-3 tw-text-gray-500">
                                    <svg class="tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="tw-mt-2 tw-p-3 tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg">
                                <p class="tw-text-xs tw-text-blue-700 tw-flex tw-items-start">
                                    <svg class="tw-w-4 tw-h-4 tw-mr-2 tw-mt-0.5 tw-flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span><strong>Important:</strong> Changing the map provider will instantly switch all maps across the entire application. If you select Google Maps, make sure to enter your API key below.</span>
                                </p>
                            </div>
                        <?php elseif ($setting['type'] === 'boolean'): ?>
                            <div class="tw-flex tw-items-center tw-space-x-3 tw-p-3 tw-bg-gray-50 tw-rounded-xl tw-border tw-border-gray-200">
                                <input type="checkbox"
                                       id="<?= $key ?>"
                                       name="<?= htmlspecialchars($key) ?>"
                                       <?= $setting['value'] === 'true' ? 'checked' : '' ?>
                                       class="tw-h-5 tw-w-5 tw-text-blue-600 tw-border-gray-300 tw-rounded focus:tw-ring-blue-500 tw-transition-all">
                                <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-gray-700">
                                    <?= $setting['value'] === 'true' ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                        <?php elseif ($setting['type'] === 'text' || $key === 'site_description'): ?>
                            <textarea id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>" rows="3"
                                class="tw-w-full tw-px-4 tw-py-3 tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200 tw-resize-none"
                                placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['value'] ?? '') ?></textarea>
                        <?php elseif ($setting['type'] === 'json'): ?>
                            <textarea id="<?= $key ?>" name="<?= htmlspecialchars($key) ?>" rows="4"
                                class="tw-w-full tw-px-4 tw-py-3 tw-text-xs tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200 tw-font-mono"
                                placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>"><?= htmlspecialchars($setting['value'] ?? '') ?></textarea>
                        <?php elseif ($setting['type'] === 'integer' || $setting['type'] === 'float'): ?>
                            <input type="number"
                                   id="<?= $key ?>"
                                   name="<?= htmlspecialchars($key) ?>"
                                   value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                   <?= $setting['type'] === 'float' ? 'step="0.01"' : '' ?>
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200"
                                   placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                        <?php else: ?>
                            <input type="text"
                                   id="<?= $key ?>"
                                   name="<?= htmlspecialchars($key) ?>"
                                   value="<?= htmlspecialchars($setting['value'] ?? '') ?>"
                                   class="tw-w-full tw-px-4 tw-py-3 tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded-xl tw-bg-gray-50 focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500 tw-transition-all tw-duration-200"
                                   placeholder="<?= htmlspecialchars($setting['description'] ?? '') ?>">
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($setting['description'])): ?>
                        <p class="tw-text-xs tw-text-gray-500 tw-leading-relaxed tw-mt-1"><?= htmlspecialchars($setting['description']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

</form>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Handle save button
    const saveButton = document.getElementById('saveAllSettings');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            saveAllSettings();
        });
    }

    // Handle reset buttons
    document.querySelectorAll('.reset-group').forEach(button => {
        button.addEventListener('click', function() {
            const group = this.dataset.group;
            if (confirm(`Reset all ${group} settings to default values?`)) {
                resetGroupSettings(group);
            }
        });
    });

    // Auto-save indicator (mobile-friendly)
    let saveTimeout;
    let hasChanges = false;
    document.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('change', function() {
            hasChanges = true;
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                if (hasChanges) {
                    showMessage('Changes detected. Don\'t forget to save!', 'info');
                }
            }, 1000);
        });
    });

    // Update checkbox labels dynamically
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.parentElement.querySelector('span');
            if (label) {
                label.textContent = this.checked ? 'Enabled' : 'Disabled';
            }
        });
    });
});

function saveAllSettings() {
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);

    // Show loading state
    const button = document.getElementById('saveAllSettings');
    const originalHTML = button ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i><span class="tw-hidden md:tw-inline">Saving...</span><span class="md:tw-hidden">...</span>';
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    fetch('<?= url('/admin/tools/settings/save') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Settings saved successfully!', 'success');
            hasChanges = false;
        } else {
            showMessage(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2"></i>Save All';
        }
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
}

function exportSettings() {
    // Export settings as JSON
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);
    const settings = {};

    for (let [key, value] of formData.entries()) {
        settings[key] = value;
    }

    const dataStr = JSON.stringify(settings, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `settings-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    showMessage('Settings exported successfully!', 'success');
}

function resetGroupSettings(group) {
    // Implementation for resetting group settings
    fetch(`<?= url('/admin/tools/settings/reset') ?>/${group}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(`${group} settings reset to defaults`, 'success');
            // Reload the page to show updated values
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage(data.message || 'Error resetting settings', 'error');
        }
    })
    .catch(error => {
        showMessage('Network error. Please try again.', 'error');
    });
}

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

    // Scroll to top on mobile to show message
    if (window.innerWidth < 640) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Prevent accidental navigation if there are unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// Show mobile save bar when scrolling down
let lastScrollTop = 0;
const mobileSaveBar = document.getElementById('mobileSaveBar');

if (mobileSaveBar) {
    window.addEventListener('scroll', function() {
        if (window.innerWidth < 768) { // Only on mobile
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 200 && hasChanges) {
                mobileSaveBar.style.display = 'block';
            } else if (scrollTop < 100) {
                mobileSaveBar.style.display = 'none';
            }

            lastScrollTop = scrollTop;
        }
    }, false);
}
</script>

<!-- Mobile-friendly sticky save button -->
<div class="tw-fixed tw-bottom-0 tw-left-0 tw-right-0 tw-bg-white tw-border-t tw-border-gray-200 tw-p-4 tw-shadow-lg md:tw-hidden tw-z-50" id="mobileSaveBar" style="display: none;">
    <button onclick="saveAllSettings()"
            class="tw-w-full tw-inline-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-rounded-xl tw-shadow-lg tw-text-sm tw-font-semibold tw-text-white tw-bg-gradient-to-r tw-from-blue-500 tw-to-blue-600 hover:tw-from-blue-600 hover:tw-to-blue-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 tw-transition-all tw-duration-200">
        <i data-feather="save" class="tw-h-5 tw-w-5 tw-mr-2"></i>
        Save All Changes
    </button>
</div>
