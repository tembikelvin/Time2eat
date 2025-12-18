<?php
/**
 * Customer Addresses Page
 */

$user = $user ?? null;
$addresses = $addresses ?? [];
$errors = $errors ?? [];
$success = $success ?? '';
?>

<!-- Page Header -->
<div class="tw-mb-6">
    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">My Addresses</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Manage your delivery addresses
            </p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <button onclick="openAddressModal()" class="tw-bg-orange-600 tw-border tw-border-transparent tw-rounded-lg tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white hover:tw-bg-orange-700 tw-transition-colors tw-flex tw-items-center">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Address
            </button>
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

    <!-- Addresses List -->
    <div class="tw-px-4 tw-py-6">
        <?php if (empty($addresses)): ?>
            <!-- Empty State -->
            <div class="tw-text-center tw-py-12">
                <div class="tw-w-24 tw-h-24 tw-mx-auto tw-mb-4 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center">
                    <i data-feather="map-pin" class="tw-w-12 tw-h-12 tw-text-gray-400"></i>
                </div>
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-2">No Addresses Added</h3>
                <p class="tw-text-gray-600 tw-mb-6">Add your delivery addresses for faster checkout</p>
                <button onclick="openAddressModal()" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                    <i data-feather="plus" class="tw-w-5 tw-h-5 tw-mr-2"></i>
                    Add Your First Address
                </button>
            </div>
        <?php else: ?>
            <div class="tw-space-y-4">
                <?php foreach ($addresses as $address): ?>
                    <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-4">
                        <div class="tw-flex tw-items-start tw-justify-between">
                            <div class="tw-flex-1">
                                <div class="tw-flex tw-items-center tw-space-x-2 tw-mb-2">
                                    <h3 class="tw-font-semibold tw-text-gray-900"><?= htmlspecialchars($address['label'] ?? 'Address') ?></h3>
                                    <?php if (!empty($address['is_default'])): ?>
                                        <span class="tw-px-2 tw-py-1 tw-bg-blue-100 tw-text-blue-800 tw-rounded-full tw-text-xs tw-font-medium">Default</span>
                                    <?php endif; ?>
                                </div>
                                <p class="tw-text-gray-600 tw-text-sm tw-mb-2"><?= htmlspecialchars($address['address_line_1'] ?? $address['address'] ?? '') ?></p>
                                <p class="tw-text-gray-600 tw-text-sm"><?= htmlspecialchars($address['city'] ?? '') ?><?= !empty($address['state']) ? ', ' . htmlspecialchars($address['state']) : '' ?> <?= htmlspecialchars($address['postal_code'] ?? '') ?></p>
                                <?php if (!empty($address['phone'])): ?>
                                    <p class="tw-text-gray-600 tw-text-sm tw-mt-1">
                                        <i data-feather="phone" class="tw-w-4 tw-h-4 tw-inline tw-mr-1"></i>
                                        <?= htmlspecialchars($address['phone']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($address['delivery_instructions'])): ?>
                                    <p class="tw-text-gray-500 tw-text-sm tw-mt-1 tw-italic"><?= htmlspecialchars($address['delivery_instructions']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="tw-flex tw-flex-col tw-space-y-2 tw-ml-4">
                                <button onclick="editAddress('<?= htmlspecialchars($address['id'] ?? '') ?>')" class="tw-p-2 tw-text-blue-600 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors">
                                    <i data-feather="edit-2" class="tw-w-4 tw-h-4"></i>
                                </button>
                                <?php if (empty($address['is_default'])): ?>
                                    <button onclick="setDefaultAddress('<?= htmlspecialchars($address['id'] ?? '') ?>')" class="tw-p-2 tw-text-green-600 hover:tw-bg-green-50 tw-rounded-lg tw-transition-colors" title="Set as Default">
                                        <i data-feather="star" class="tw-w-4 tw-h-4"></i>
                                    </button>
                                <?php endif; ?>
                                <button onclick="deleteAddress('<?= htmlspecialchars($address['id'] ?? '') ?>')" class="tw-p-2 tw-text-red-600 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors">
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

<!-- Address Modal -->
<div id="address-modal" class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-p-4 tw-hidden tw-z-50">
    <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-w-full tw-max-w-md tw-max-h-screen tw-overflow-y-auto">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h3 id="modal-title" class="tw-text-lg tw-font-semibold tw-text-gray-900">Add New Address</h3>
            <button onclick="closeAddressModal()" class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 tw-rounded-lg">
                <i data-feather="x" class="tw-w-5 tw-h-5"></i>
            </button>
        </div>
        
        <form id="address-form" class="tw-space-y-4">
            <input type="hidden" id="address-id" name="address_id">
            
            <!-- Address Label -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Address Label</label>
                <select id="address-label" name="label" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" required>
                    <option value="">Select Label</option>
                    <option value="Home">Home</option>
                    <option value="Work">Work</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Street Address -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Street Address</label>
                <textarea id="address-street" name="address" rows="2" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" placeholder="Enter your full address" required></textarea>
            </div>

            <!-- City -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">City</label>
                <input type="text" id="address-city" name="city" value="Bamenda" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" required>
            </div>

            <!-- State and Postal Code -->
            <div class="tw-grid tw-grid-cols-2 tw-gap-3">
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">State/Region</label>
                    <input type="text" id="address-state" name="state" value="North West" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" required>
                </div>
                <div>
                    <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Postal Code</label>
                    <input type="text" id="address-postal" name="postal_code" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm">
                </div>
            </div>

            <!-- Phone Number -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Phone Number</label>
                <input type="tel" id="address-phone" name="phone" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" placeholder="e.g., +237 6XX XXX XXX">
            </div>

            <!-- Delivery Instructions -->
            <div>
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Delivery Instructions (Optional)</label>
                <textarea id="address-instructions" name="delivery_instructions" rows="2" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm" placeholder="e.g., Ring the doorbell, Leave at gate"></textarea>
            </div>

            <!-- Set as Default -->
            <div class="tw-flex tw-items-center">
                <input type="checkbox" id="address-default" name="is_default" class="tw-rounded tw-border-gray-300 tw-text-blue-600 tw-mr-2">
                <label for="address-default" class="tw-text-sm tw-text-gray-700">Set as default address</label>
            </div>

            <!-- Action Buttons -->
            <div class="tw-flex tw-space-x-3 tw-pt-4">
                <button type="button" onclick="closeAddressModal()" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-gray-200 tw-text-gray-800 tw-rounded-lg tw-font-medium hover:tw-bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-lg tw-font-medium hover:tw-bg-blue-700">
                    <span id="submit-text">Save Address</span>
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

let currentAddressId = null;

function openAddressModal(addressData = null) {
    const modal = document.getElementById('address-modal');
    const title = document.getElementById('modal-title');
    const form = document.getElementById('address-form');
    const submitText = document.getElementById('submit-text');
    
    // Reset form
    form.reset();
    currentAddressId = null;
    
    if (addressData) {
        // Edit mode
        title.textContent = 'Edit Address';
        submitText.textContent = 'Update Address';
        currentAddressId = addressData.id;
        
        // Populate form
        document.getElementById('address-id').value = addressData.id || '';
        document.getElementById('address-label').value = addressData.label || '';
        document.getElementById('address-street').value = addressData.address_line_1 || addressData.address || '';
        document.getElementById('address-city').value = addressData.city || '';
        document.getElementById('address-state').value = addressData.state || '';
        document.getElementById('address-postal').value = addressData.postal_code || '';
        document.getElementById('address-phone').value = addressData.phone || '';
        document.getElementById('address-instructions').value = addressData.delivery_instructions || '';
        document.getElementById('address-default').checked = addressData.is_default || false;
    } else {
        // Add mode
        title.textContent = 'Add New Address';
        submitText.textContent = 'Save Address';
    }
    
    modal.classList.remove('tw-hidden');
}

function closeAddressModal() {
    document.getElementById('address-modal').classList.add('tw-hidden');
    currentAddressId = null;
}

function editAddress(addressId) {
    // Fetch address data and open modal
    fetch(`/api/addresses/${addressId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openAddressModal(data.address);
            } else {
                alert(data.message || 'Failed to load address');
            }
        })
        .catch(error => {
            console.error('Error loading address:', error);
            alert('Failed to load address');
        });
}

function setDefaultAddress(addressId) {
    if (confirm('Set this address as your default delivery address?')) {
        fetch(`/api/addresses/${addressId}/set-default`, {
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
                alert(data.message || 'Failed to set default address');
            }
        })
        .catch(error => {
            console.error('Error setting default address:', error);
            alert('Failed to set default address');
        });
    }
}

function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        fetch(`/api/addresses/${addressId}`, {
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
                alert(data.message || 'Failed to delete address');
            }
        })
        .catch(error => {
            console.error('Error deleting address:', error);
            alert('Failed to delete address');
        });
    }
}

// Handle form submission
document.getElementById('address-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Convert checkbox to boolean
    data.is_default = document.getElementById('address-default').checked;
    
    const url = currentAddressId ? `/api/addresses/${currentAddressId}` : '/api/addresses';
    const method = currentAddressId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
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
            alert(data.message || 'Failed to save address');
        }
    })
    .catch(error => {
        console.error('Error saving address:', error);
        alert('Failed to save address');
    });
});
</script>
