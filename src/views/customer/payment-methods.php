<?php
/**
 * Customer Payment Methods Page
 * Customers can VIEW available payment methods and SAVE mobile money numbers
 * Customers CANNOT edit or change payment methods (read-only)
 */

$user = $user ?? null;
$paymentMethods = $paymentMethods ?? [];
$errors = $errors ?? [];
$success = $success ?? '';

// Get saved mobile money numbers from user profile or payment methods
$savedMomoNumbers = [];
foreach ($paymentMethods as $method) {
    if (in_array($method['type'], ['mobile_money']) && in_array($method['provider'], ['mtn_momo', 'orange_money'])) {
        $details = json_decode($method['details'], true);
        $savedMomoNumbers[] = [
            'id' => $method['id'],
            'provider' => $method['provider'],
            'phone_number' => $details['phone_number'] ?? '',
            'name' => $method['name'],
            'is_default' => $method['is_default']
        ];
    }
}
?>

<!-- Page Header -->
<div class="tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Payment Methods</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                View available payment methods and save your mobile money numbers
            </p>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success): ?>
    <div class="tw-mb-6 tw-p-4 tw-bg-green-50 tw-border tw-border-green-200 tw-rounded-lg">
        <div class="tw-flex tw-items-center">
            <svg class="tw-w-5 tw-h-5 tw-text-green-600 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="tw-text-sm tw-font-medium tw-text-green-800"><?= htmlspecialchars($success) ?></span>
        </div>
    </div>
<?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="tw-mx-4 tw-mt-4 tw-p-4 tw-bg-red-100 tw-border tw-border-red-200 tw-rounded-lg">
            <div class="tw-flex tw-items-start">
                <i data-feather="alert-circle" class="tw-w-5 tw-h-5 tw-text-red-600 tw-mr-2 tw-mt-0.5"></i>
                <div class="tw-text-red-800">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payment Methods List -->
    <div class="tw-px-4 tw-py-6">
        <!-- Available Payment Methods (Read-Only) -->
        <div class="tw-mb-8">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Available Payment Methods</h3>
                <span class="tw-text-xs tw-text-gray-500 tw-bg-gray-100 tw-px-3 tw-py-1 tw-rounded-full">Read Only</span>
            </div>

            <div class="tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-lg tw-p-4 tw-mb-4">
                <div class="tw-flex tw-items-start">
                    <i data-feather="info" class="tw-w-5 tw-h-5 tw-text-blue-600 tw-mr-2 tw-mt-0.5 tw-flex-shrink-0"></i>
                    <div class="tw-text-sm tw-text-blue-800">
                        <p class="tw-font-medium tw-mb-1">Payment methods are managed by the system</p>
                        <p>You can choose any of these methods during checkout. For mobile money, save your number below for faster checkout.</p>
                    </div>
                </div>
            </div>

            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <!-- Cash on Delivery -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-5 tw-transition-all hover:tw-shadow-md">
                    <div class="tw-flex tw-items-start tw-space-x-4">
                        <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-green-400 tw-to-green-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="dollar-sign" class="tw-w-7 tw-h-7 tw-text-white"></i>
                        </div>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-1">Cash on Delivery</h4>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-2">Pay with cash when your order arrives</p>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-green-100 tw-text-green-800">
                                    <span class="tw-w-1.5 tw-h-1.5 tw-bg-green-600 tw-rounded-full tw-mr-1.5"></span>
                                    Available
                                </span>
                                <span class="tw-text-xs tw-text-gray-500">No setup required</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MTN Mobile Money -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-5 tw-transition-all hover:tw-shadow-md">
                    <div class="tw-flex tw-items-start tw-space-x-4">
                        <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-yellow-400 tw-to-yellow-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="smartphone" class="tw-w-7 tw-h-7 tw-text-white"></i>
                        </div>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-1">MTN Mobile Money</h4>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-2">Pay with MTN MoMo</p>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-yellow-100 tw-text-yellow-800">
                                    <span class="tw-w-1.5 tw-h-1.5 tw-bg-yellow-600 tw-rounded-full tw-mr-1.5"></span>
                                    Available
                                </span>
                                <span class="tw-text-xs tw-text-gray-500">Save number below</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orange Money -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-5 tw-transition-all hover:tw-shadow-md">
                    <div class="tw-flex tw-items-start tw-space-x-4">
                        <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-orange-400 tw-to-orange-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="smartphone" class="tw-w-7 tw-h-7 tw-text-white"></i>
                        </div>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-1">Orange Money</h4>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-2">Pay with Orange Money</p>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-orange-100 tw-text-orange-800">
                                    <span class="tw-w-1.5 tw-h-1.5 tw-bg-orange-600 tw-rounded-full tw-mr-1.5"></span>
                                    Available
                                </span>
                                <span class="tw-text-xs tw-text-gray-500">Save number below</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Online Payment (Tranzak) -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-5 tw-transition-all hover:tw-shadow-md">
                    <div class="tw-flex tw-items-start tw-space-x-4">
                        <div class="tw-w-14 tw-h-14 tw-bg-gradient-to-br tw-from-blue-400 tw-to-blue-600 tw-rounded-xl tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                            <i data-feather="credit-card" class="tw-w-7 tw-h-7 tw-text-white"></i>
                        </div>
                        <div class="tw-flex-1">
                            <h4 class="tw-font-semibold tw-text-gray-900 tw-mb-1">Online Payment</h4>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-2">Card, Mobile Money via Tranzak</p>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">
                                    <span class="tw-w-1.5 tw-h-1.5 tw-bg-blue-600 tw-rounded-full tw-mr-1.5"></span>
                                    Available
                                </span>
                                <span class="tw-text-xs tw-text-gray-500">Secure checkout</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saved Mobile Money Numbers -->
        <div>
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Saved Mobile Money Numbers</h3>
                <button onclick="openMomoModal()" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-orange-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-orange-700 tw-transition-colors">
                    <i data-feather="plus" class="tw-w-4 tw-h-4 tw-mr-2"></i>
                    Add Number
                </button>
            </div>

            <div class="tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg tw-p-4 tw-mb-4">
                <div class="tw-flex tw-items-start">
                    <i data-feather="smartphone" class="tw-w-5 tw-h-5 tw-text-yellow-600 tw-mr-2 tw-mt-0.5 tw-flex-shrink-0"></i>
                    <div class="tw-text-sm tw-text-yellow-800">
                        <p class="tw-font-medium tw-mb-1">Save your mobile money numbers for faster checkout</p>
                        <p>Your saved numbers will be pre-filled during checkout, making payments quicker and easier.</p>
                    </div>
                </div>
            </div>

            <?php if (empty($savedMomoNumbers)): ?>
                <!-- Empty State -->
                <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-8 tw-text-center">
                    <div class="tw-w-20 tw-h-20 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                        <i data-feather="smartphone" class="tw-w-10 tw-h-10 tw-text-gray-400"></i>
                    </div>
                    <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">No Saved Numbers</h3>
                    <p class="tw-text-gray-600 tw-mb-6">Save your MTN MoMo or Orange Money number for faster checkout</p>
                    <button onclick="openMomoModal()" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-orange-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-orange-700 tw-transition-colors">
                        <i data-feather="plus" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                        Add Mobile Money Number
                    </button>
                </div>
            <?php else: ?>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <?php foreach ($savedMomoNumbers as $momo): ?>
                        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-5 tw-transition-all hover:tw-shadow-md">
                            <div class="tw-flex tw-items-start tw-justify-between">
                                <div class="tw-flex tw-items-start tw-space-x-4 tw-flex-1">
                                    <!-- Provider Icon -->
                                    <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0 <?= $momo['provider'] === 'mtn_momo' ? 'tw-bg-yellow-100' : 'tw-bg-orange-100' ?>">
                                        <i data-feather="smartphone" class="tw-w-6 tw-h-6 <?= $momo['provider'] === 'mtn_momo' ? 'tw-text-yellow-600' : 'tw-text-orange-600' ?>"></i>
                                    </div>

                                    <!-- Details -->
                                    <div class="tw-flex-1">
                                        <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-1">
                                            <h4 class="tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($momo['name']) ?></h4>
                                            <?php if ($momo['is_default']): ?>
                                                <span class="tw-px-2 tw-py-0.5 tw-bg-green-100 tw-text-green-800 tw-text-xs tw-font-medium tw-rounded-full">
                                                    Default
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="tw-text-sm tw-text-gray-600 tw-mb-2">
                                            <?= htmlspecialchars($momo['phone_number']) ?>
                                        </p>
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium <?= $momo['provider'] === 'mtn_momo' ? 'tw-bg-yellow-100 tw-text-yellow-800' : 'tw-bg-orange-100 tw-text-orange-800' ?>">
                                            <?= $momo['provider'] === 'mtn_momo' ? 'MTN MoMo' : 'Orange Money' ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="tw-flex tw-flex-col tw-space-y-2 tw-ml-2">
                                    <?php if (!$momo['is_default']): ?>
                                        <button onclick="setDefaultMomo(<?= $momo['id'] ?>)" class="tw-p-2 tw-text-green-600 hover:tw-bg-green-50 tw-rounded-lg tw-transition-colors" title="Set as Default">
                                            <i data-feather="star" class="tw-w-4 tw-h-4"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteMomo(<?= $momo['id'] ?>)" class="tw-p-2 tw-text-red-600 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors" title="Delete">
                                        <i data-feather="trash-2" class="tw-w-4 tw-h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mobile Money Modal -->
<div id="momo-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-p-4 tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-w-full tw-max-w-md">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900">Add Mobile Money Number</h3>
            <button onclick="closeMomoModal()" class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 tw-rounded-lg">
                <i data-feather="x" class="tw-w-5 tw-h-5"></i>
            </button>
        </div>

        <form id="momo-form" class="tw-space-y-4">
            <!-- Provider Selection -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Provider</label>
                <div class="tw-grid tw-grid-cols-2 tw-gap-3">
                    <label class="tw-relative tw-flex tw-items-center tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-lg tw-cursor-pointer hover:tw-border-yellow-500 tw-transition-colors">
                        <input type="radio" name="provider" value="mtn_momo" class="tw-sr-only" required onchange="updateProviderUI()">
                        <div class="tw-flex tw-flex-col tw-items-center tw-w-full">
                            <div class="tw-w-12 tw-h-12 tw-bg-yellow-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-2">
                                <i data-feather="smartphone" class="tw-w-6 tw-h-6 tw-text-yellow-600"></i>
                            </div>
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900">MTN MoMo</span>
                        </div>
                    </label>

                    <label class="tw-relative tw-flex tw-items-center tw-p-4 tw-border-2 tw-border-gray-200 tw-rounded-lg tw-cursor-pointer hover:tw-border-orange-500 tw-transition-colors">
                        <input type="radio" name="provider" value="orange_money" class="tw-sr-only" required onchange="updateProviderUI()">
                        <div class="tw-flex tw-flex-col tw-items-center tw-w-full">
                            <div class="tw-w-12 tw-h-12 tw-bg-orange-100 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-mb-2">
                                <i data-feather="smartphone" class="tw-w-6 tw-h-6 tw-text-orange-600"></i>
                            </div>
                            <span class="tw-text-sm tw-font-medium tw-text-gray-900">Orange Money</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Name (Optional)</label>
                <input type="text" id="momo-name" name="name" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" placeholder="e.g., My MTN Number">
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Leave blank to use phone number as name</p>
            </div>

            <!-- Phone Number -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Phone Number *</label>
                <input type="tel" id="momo-phone" name="phone_number" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" placeholder="+237 6XX XXX XXX" required>
                <p class="tw-text-xs tw-text-gray-500 tw-mt-1">Enter your mobile money number</p>
            </div>

            <!-- Set as Default -->
            <div class="tw-flex tw-items-center tw-p-3 tw-bg-gray-50 tw-rounded-lg">
                <input type="checkbox" id="momo-default" name="is_default" class="tw-rounded tw-border-gray-300 tw-text-orange-600 tw-mr-3">
                <label for="momo-default" class="tw-text-sm tw-text-gray-700">Set as default mobile money number</label>
            </div>

            <!-- Action Buttons -->
            <div class="tw-flex tw-space-x-3 tw-pt-4">
                <button type="button" onclick="closeMomoModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-200 tw-text-gray-800 tw-rounded-lg tw-font-medium hover:tw-bg-gray-300 tw-transition-colors">
                    Cancel
                </button>
                <button type="submit" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-orange-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-orange-700 tw-transition-colors">
                    <span id="momo-submit-text">Save Number</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Feather Icons
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Mobile Money Modal Functions
function openMomoModal() {
    const modal = document.getElementById('momo-modal');
    const form = document.getElementById('momo-form');

    // Reset form
    form.reset();

    modal.classList.remove('tw-hidden');

    // Re-initialize feather icons for modal
    setTimeout(() => {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 100);
}

function closeMomoModal() {
    document.getElementById('momo-modal').classList.add('tw-hidden');
}

function updateProviderUI() {
    const radios = document.querySelectorAll('input[name="provider"]');
    radios.forEach(radio => {
        const label = radio.closest('label');
        if (radio.checked) {
            if (radio.value === 'mtn_momo') {
                label.classList.add('tw-border-yellow-500', 'tw-bg-yellow-50');
                label.classList.remove('tw-border-gray-200', 'tw-border-orange-500', 'tw-bg-orange-50');
            } else {
                label.classList.add('tw-border-orange-500', 'tw-bg-orange-50');
                label.classList.remove('tw-border-gray-200', 'tw-border-yellow-500', 'tw-bg-yellow-50');
            }
        } else {
            label.classList.remove('tw-border-yellow-500', 'tw-bg-yellow-50', 'tw-border-orange-500', 'tw-bg-orange-50');
            label.classList.add('tw-border-gray-200');
        }
    });
}

// Handle Mobile Money Form Submission
document.getElementById('momo-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const submitText = document.getElementById('momo-submit-text');
    const originalText = submitText.textContent;

    // Disable button and show loading
    submitBtn.disabled = true;
    submitText.textContent = 'Saving...';

    const formData = new FormData(this);
    const provider = formData.get('provider');
    const phoneNumber = formData.get('phone_number');
    let name = formData.get('name');
    const isDefault = formData.get('is_default') === 'on';

    // If no name provided, use phone number
    if (!name || name.trim() === '') {
        name = phoneNumber;
    }

    const data = {
        type: 'mobile_money',
        provider: provider,
        name: name,
        phone_number: phoneNumber,
        is_default: isDefault
    };

    fetch('/customer/payment-methods/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to save mobile money number');
            submitBtn.disabled = false;
            submitText.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error saving mobile money number:', error);
        alert('Failed to save mobile money number');
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    });
});

function setDefaultMomo(paymentId) {
    if (confirm('Set this as your default mobile money number?')) {
        fetch(`/customer/payment-methods/${paymentId}/set-default`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to set default');
            }
        })
        .catch(error => {
            console.error('Error setting default:', error);
            alert('Failed to set default');
        });
    }
}

function deleteMomo(paymentId) {
    if (confirm('Are you sure you want to delete this mobile money number?')) {
        fetch(`/customer/payment-methods/${paymentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete mobile money number');
            }
        })
        .catch(error => {
            console.error('Error deleting mobile money number:', error);
            alert('Failed to delete mobile money number');
        });
    }
}

// Format phone number input
document.getElementById('momo-phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9+]/g, '');
    e.target.value = value;
});
        console.error('Error saving payment method:', error);
        alert('Failed to save payment method');
    });
});
</script>
