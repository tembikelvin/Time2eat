<?php
/**
 * Customer Role Change Request Page
 */

$currentPage = 'role-request';
?>

<!-- Page Header -->
<div class="tw-mb-8">
    <div class="tw-flex tw-items-center tw-justify-between">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Request Role Change</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-600">
                Apply to become a vendor or delivery rider on Time2Eat
            </p>
        </div>
    </div>
</div>

<?php if ($pendingRequest): ?>
<!-- Pending Request Notice -->
<div class="tw-bg-orange-50 tw-border tw-border-orange-200 tw-rounded-xl tw-p-6 tw-mb-8">
    <div class="tw-flex tw-items-center">
        <div class="tw-flex-shrink-0">
            <i data-feather="clock" class="tw-h-6 tw-w-6 tw-text-orange-600"></i>
        </div>
        <div class="tw-ml-3">
            <h3 class="tw-text-lg tw-font-medium tw-text-orange-800">Request Pending Review</h3>
            <div class="tw-mt-2 tw-text-sm tw-text-orange-700">
                <p>You have a pending role change request to become a <strong><?= ucfirst($pendingRequest['requested_role']) ?></strong>.</p>
                <p class="tw-mt-1">Submitted on: <?= date('F j, Y', strtotime($pendingRequest['created_at'])) ?></p>
                <p class="tw-mt-1">Status: <span class="tw-font-semibold"><?= ucfirst($pendingRequest['status']) ?></span></p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Role Options -->
<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-8 tw-mb-8">
    <!-- Become a Vendor -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-overflow-hidden">
        <div class="tw-p-6">
            <div class="tw-flex tw-items-center tw-mb-4">
                <div class="tw-p-3 tw-bg-orange-100 tw-rounded-full">
                    <i data-feather="coffee" class="tw-h-8 tw-w-8 tw-text-orange-600"></i>
                </div>
                <div class="tw-ml-4">
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900">Become a Vendor</h3>
                    <p class="tw-text-sm tw-text-gray-600">Start your own restaurant</p>
                </div>
            </div>
            
            <div class="tw-space-y-3 tw-mb-6">
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Create and manage your restaurant profile
                </div>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Add menu items and manage inventory
                </div>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Receive and manage orders
                </div>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Track earnings and analytics
                </div>
            </div>
            
            <button onclick="selectRole('vendor')" 
                    class="tw-w-full tw-bg-orange-600 tw-text-white tw-py-3 tw-px-4 tw-rounded-lg tw-font-medium hover:tw-bg-orange-700 tw-transition-colors">
                Apply to Become Vendor
            </button>
        </div>
    </div>

    <!-- Become a Rider -->
    <div class="tw-bg-white tw-rounded-xl tw-shadow-lg tw-border tw-border-gray-200 tw-overflow-hidden">
        <div class="tw-p-6">
            <div class="tw-flex tw-items-center tw-mb-4">
                <div class="tw-p-3 tw-bg-blue-100 tw-rounded-full">
                    <i data-feather="truck" class="tw-h-8 tw-w-8 tw-text-blue-600"></i>
                </div>
                <div class="tw-ml-4">
                    <h3 class="tw-text-xl tw-font-bold tw-text-gray-900">Become a Rider</h3>
                    <p class="tw-text-sm tw-text-gray-600">Deliver food to customers</p>
                </div>
            </div>
            
            <div class="tw-space-y-3 tw-mb-6">
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Flexible working hours
                </div>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Accept delivery requests
                </div>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Track your deliveries and earnings
                </div>
                <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-600">
                    <i data-feather="check" class="tw-h-4 tw-w-4 tw-text-green-500 tw-mr-2"></i>
                    Performance metrics and bonuses
                </div>
            </div>
            
            <button onclick="selectRole('rider')" 
                    class="tw-w-full tw-bg-blue-600 tw-text-white tw-py-3 tw-px-4 tw-rounded-lg tw-font-medium hover:tw-bg-blue-700 tw-transition-colors">
                Apply to Become Rider
            </button>
        </div>
    </div>
</div>

<!-- Application Form Modal -->
<div id="applicationModal" class="tw-fixed tw-inset-0 tw-bg-gray-600 tw-bg-opacity-50 tw-overflow-y-auto tw-h-full tw-w-full tw-z-50" style="display: none;">
    <div class="tw-relative tw-top-10 tw-mx-auto tw-p-5 tw-border tw-w-full tw-max-w-2xl tw-shadow-lg tw-rounded-md tw-bg-white">
        <div class="tw-mt-3">
            <!-- Modal Header -->
            <div class="tw-flex tw-items-center tw-justify-between tw-pb-3 tw-border-b tw-border-gray-200">
                <h3 id="modalTitle" class="tw-text-lg tw-font-medium tw-text-gray-900">Role Change Application</h3>
                <button onclick="closeApplicationModal()" class="tw-text-gray-400 hover:tw-text-gray-600">
                    <i data-feather="x" class="tw-h-5 tw-w-5"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <form id="roleRequestForm" class="tw-mt-6">
                <input type="hidden" id="requestedRole" name="requested_role" value="">
                
                <div class="tw-mb-6">
                    <label for="reason" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Why do you want to become a <span id="roleLabel"></span>? *
                    </label>
                    <textarea id="reason" 
                              name="reason" 
                              required
                              class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500" 
                              rows="4" 
                              placeholder="Please provide a detailed explanation of why you want to change your role. Include your experience, motivation, and how you plan to contribute to the platform..."></textarea>
                    <p class="tw-mt-1 tw-text-xs tw-text-gray-500">Minimum 20 characters required</p>
                </div>
                
                <div class="tw-mb-6">
                    <label for="documents" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
                        Supporting Documents (Optional)
                    </label>
                    <input type="file" 
                           id="documents" 
                           name="documents[]" 
                           multiple
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500">
                    <p class="tw-mt-1 tw-text-xs tw-text-gray-500">
                        Upload relevant documents such as ID, certificates, or experience proof. Max 5MB per file.
                    </p>
                </div>
                
                <div id="formError" class="tw-mb-4 tw-p-3 tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-rounded" style="display: none;">
                </div>
                
                <div id="formSuccess" class="tw-mb-4 tw-p-3 tw-bg-green-100 tw-border tw-border-green-400 tw-text-green-700 tw-rounded" style="display: none;">
                </div>
            </form>
            
            <!-- Modal Footer -->
            <div class="tw-flex tw-items-center tw-justify-end tw-pt-4 tw-border-t tw-border-gray-200 tw-space-x-3">
                <button onclick="closeApplicationModal()" 
                        class="tw-px-4 tw-py-2 tw-bg-gray-300 tw-text-gray-700 tw-rounded-md hover:tw-bg-gray-400 tw-transition-colors">
                    Cancel
                </button>
                <button onclick="submitApplication()" 
                        id="submitBtn"
                        class="tw-px-6 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded-md hover:tw-bg-blue-700 tw-transition-colors">
                    Submit Application
                </button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
feather.replace();

let selectedRole = null;

function selectRole(role) {
    selectedRole = role;
    
    document.getElementById('requestedRole').value = role;
    document.getElementById('roleLabel').textContent = role;
    document.getElementById('modalTitle').textContent = `Apply to Become ${role.charAt(0).toUpperCase() + role.slice(1)}`;
    
    // Clear form
    document.getElementById('reason').value = '';
    document.getElementById('documents').value = '';
    document.getElementById('formError').style.display = 'none';
    document.getElementById('formSuccess').style.display = 'none';
    
    document.getElementById('applicationModal').style.display = 'block';
    document.getElementById('reason').focus();
}

function closeApplicationModal() {
    document.getElementById('applicationModal').style.display = 'none';
    selectedRole = null;
}

function submitApplication() {
    const reason = document.getElementById('reason').value.trim();
    const submitBtn = document.getElementById('submitBtn');
    const errorDiv = document.getElementById('formError');
    const successDiv = document.getElementById('formSuccess');
    
    // Clear previous messages
    errorDiv.style.display = 'none';
    successDiv.style.display = 'none';
    
    // Validation
    if (!selectedRole) {
        showError('Please select a role');
        return;
    }
    
    if (reason.length < 20) {
        showError('Please provide a more detailed reason (at least 20 characters)');
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('requested_role', selectedRole);
    formData.append('reason', reason);
    
    // Add documents if any
    const documentsInput = document.getElementById('documents');
    if (documentsInput.files.length > 0) {
        for (let i = 0; i < documentsInput.files.length; i++) {
            formData.append('documents[]', documentsInput.files[i]);
        }
    }
    
    // Submit request
    fetch('<?= url('/customer/role-request/submit') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.textContent = data.message;
            successDiv.style.display = 'block';
            
            // Close modal after success
            setTimeout(() => {
                closeApplicationModal();
                location.reload();
            }, 2000);
        } else {
            showError(data.message || 'An error occurred while submitting your request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Network error. Please check your connection and try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Application';
    });
}

function showError(message) {
    const errorDiv = document.getElementById('formError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Close modal when clicking outside
document.getElementById('applicationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApplicationModal();
    }
});

// Handle Enter key in textarea
document.getElementById('reason').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.ctrlKey) {
        submitApplication();
    }
});
</script>
