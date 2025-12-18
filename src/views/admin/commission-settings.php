<?php
/**
 * Admin Commission Settings Page
 * This content is rendered within the dashboard layout
 */

// Set current page for sidebar highlighting
$currentPage = 'affiliates';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Commission Settings</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage global and individual affiliate commission rates
            </p>
        </div>
        <div class="tw-flex tw-space-x-3">
            <a href="<?= url('/admin/affiliate/dashboard') ?>" class="tw-bg-gray-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-gray-700 tw-transition-colors">
                <i data-feather="arrow-left" class="tw-h-4 tw-w-4 tw-mr-2 tw-inline"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Global Commission Rates -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6 tw-mb-8">
    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Global Commission Rates</h2>
    <p class="tw-text-sm tw-text-gray-600 tw-mb-6">These rates apply to all new affiliates by default</p>
    
    <form id="global-rates-form" class="tw-space-y-4">
        <?php foreach ($settings as $setting): ?>
        <div class="tw-flex tw-items-center tw-space-x-4">
            <label class="tw-w-48 tw-text-sm tw-font-medium tw-text-gray-700">
                <?= ucwords(str_replace('_', ' ', $setting['setting_name'])) ?>:
            </label>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <input 
                    type="number" 
                    name="<?= $setting['setting_name'] ?>" 
                    value="<?= number_format($setting['setting_value'] * 100, 2) ?>" 
                    min="0" 
                    max="100" 
                    step="0.01"
                    class="tw-w-24 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500"
                >
                <span class="tw-text-sm tw-text-gray-500">%</span>
            </div>
            <span class="tw-text-xs tw-text-gray-500"><?= $setting['description'] ?></span>
        </div>
        <?php endforeach; ?>
        
        <div class="tw-pt-4">
            <button 
                type="submit" 
                class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-primary-700 tw-transition-colors"
            >
                <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2 tw-inline"></i>
                Update Global Rates
            </button>
        </div>
    </form>
</div>

<!-- Individual Affiliate Rates -->
<div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-p-6">
    <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Individual Affiliate Rates</h2>
    <p class="tw-text-sm tw-text-gray-600 tw-mb-6">Override global rates for specific affiliates</p>
    
    <form id="affiliate-rates-form" class="tw-space-y-4">
        <?php foreach ($affiliates as $affiliate): ?>
        <div class="tw-flex tw-items-center tw-space-x-4 tw-p-3 tw-bg-gray-50 tw-rounded-lg">
            <div class="tw-flex-1">
                <div class="tw-font-medium tw-text-gray-900">
                    <?= e($affiliate['first_name'] . ' ' . $affiliate['last_name']) ?>
                </div>
                <div class="tw-text-sm tw-text-gray-500">
                    <?= e($affiliate['email']) ?> • 
                    <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium
                        <?= $affiliate['status'] === 'active' ? 'tw-bg-green-100 tw-text-green-800' : 
                           ($affiliate['status'] === 'suspended' ? 'tw-bg-red-100 tw-text-red-800' : 'tw-bg-gray-100 tw-text-gray-800') ?>">
                        <?= ucfirst($affiliate['status']) ?>
                    </span>
                </div>
            </div>
            <div class="tw-flex tw-items-center tw-space-x-2">
                <input 
                    type="number" 
                    name="affiliate_rates[<?= $affiliate['id'] ?>]" 
                    value="<?= number_format($affiliate['commission_rate'] * 100, 2) ?>" 
                    min="0" 
                    max="100" 
                    step="0.01"
                    class="tw-w-24 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-primary-500"
                >
                <span class="tw-text-sm tw-text-gray-500">%</span>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="tw-pt-4">
            <button 
                type="submit" 
                class="tw-bg-primary-600 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-font-medium hover:tw-bg-primary-700 tw-transition-colors"
            >
                <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2 tw-inline"></i>
                Update Affiliate Rates
            </button>
        </div>
    </form>
</div>

<!-- Success/Error Messages -->
<div id="message-container" class="tw-mt-4 tw-hidden">
    <div id="message" class="tw-p-4 tw-rounded-lg tw-font-medium"></div>
</div>

<script>
// Global rates form submission
document.getElementById('global-rates-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = parseFloat(value);
    }
    
    fetch('<?= url('/admin/affiliates/commission-settings/update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        showMessage(result.success ? 'success' : 'error', result.message);
        if (result.success) {
            // Optionally reload the page to show updated values
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', 'An error occurred while updating settings');
    });
});

// Affiliate rates form submission
document.getElementById('affiliate-rates-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = { affiliate_rates: {} };
    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('affiliate_rates[')) {
            const affiliateId = key.match(/\[(\d+)\]/)[1];
            data.affiliate_rates[affiliateId] = parseFloat(value);
        }
    }
    
    fetch('<?= url('/admin/affiliates/commission-settings/update') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        showMessage(result.success ? 'success' : 'error', result.message);
        if (result.success) {
            // Optionally reload the page to show updated values
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', 'An error occurred while updating affiliate rates');
    });
});

// Show success/error messages
function showMessage(type, message) {
    const container = document.getElementById('message-container');
    const messageEl = document.getElementById('message');
    
    messageEl.textContent = message;
    messageEl.className = `tw-p-4 tw-rounded-lg tw-font-medium ${
        type === 'success' 
            ? 'tw-bg-green-100 tw-text-green-800 tw-border tw-border-green-200' 
            : 'tw-bg-red-100 tw-text-red-800 tw-border tw-border-red-200'
    }`;
    
    container.classList.remove('tw-hidden');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        container.classList.add('tw-hidden');
    }, 5000);
}

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

