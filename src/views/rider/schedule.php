<?php
$title = $title ?? 'My Schedule - Time2Eat';
$user = $user ?? null;
$currentPage = $currentPage ?? 'schedule';
$schedule = $schedule ?? [];
$todaySchedule = $todaySchedule ?? null;
$upcomingDeliveries = $upcomingDeliveries ?? [];
$weeklyStats = $weeklyStats ?? [];
?>

<!-- Mobile-First Header -->
<div class="tw-bg-gradient-to-r tw-from-blue-600 tw-to-indigo-600 tw-rounded-2xl tw-p-6 tw-mb-6 tw-text-white">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-mb-1">My Schedule</h1>
            <p class="tw-text-blue-100 tw-text-sm">Manage your availability and working hours</p>
        </div>
        <div class="tw-p-3 tw-bg-white tw-bg-opacity-20 tw-backdrop-blur-sm tw-rounded-xl">
            <i data-feather="calendar" class="tw-h-8 tw-w-8"></i>
        </div>
    </div>
    
    <!-- Current Status Badge -->
    <div class="tw-flex tw-items-center tw-justify-between tw-bg-white tw-bg-opacity-10 tw-backdrop-blur-sm tw-rounded-xl tw-p-4">
        <div class="tw-flex tw-items-center tw-space-x-3">
            <div class="tw-p-2 tw-bg-white tw-bg-opacity-20 tw-rounded-lg">
                <i data-feather="clock" class="tw-h-5 tw-w-5"></i>
            </div>
            <div>
                <div class="tw-text-sm tw-font-medium">Current Status</div>
                <div class="tw-text-xs tw-text-blue-100"><?= date('l, F j, Y') ?></div>
            </div>
        </div>
        <div class="tw-flex tw-items-center tw-space-x-3">
            <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium <?= ($user->is_available ?? false) ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-red-100 tw-text-red-800' ?>">
                <i data-feather="power" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <?= ($user->is_available ?? false) ? 'Online' : 'Offline' ?>
            </span>
            <button onclick="toggleAvailability()" 
                    class="tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all tw-duration-200 tw-shadow-lg <?= ($user->is_available ?? false) ? 'tw-bg-red-500 hover:tw-bg-red-600 tw-text-white' : 'tw-bg-green-500 hover:tw-bg-green-600 tw-text-white' ?>"
                    title="<?= ($user->is_available ?? false) ? 'Go Offline' : 'Go Online' ?>">
                <i data-feather="<?= ($user->is_available ?? false) ? 'power' : 'power' ?>" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                <?= ($user->is_available ?? false) ? 'Go Offline' : 'Go Online' ?>
            </button>
        </div>
    </div>
</div>

<!-- Today's Schedule Overview -->
<?php if ($todaySchedule): ?>
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-p-6 tw-mb-6">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
            <i data-feather="sun" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-yellow-500"></i>
            Today's Schedule
        </h2>
        <span class="tw-text-sm tw-text-gray-500"><?= date('l, M j') ?></span>
    </div>
    
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-4">
        <div class="tw-bg-gradient-to-r tw-from-green-50 tw-to-green-100 tw-rounded-xl tw-p-4">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-p-2 tw-bg-green-500 tw-rounded-lg">
                    <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-white"></i>
                </div>
                <div>
                    <div class="tw-text-sm tw-font-medium tw-text-green-700">Start Time</div>
                    <div class="tw-text-lg tw-font-bold tw-text-green-900"><?= $todaySchedule['start'] ?? 'Not Set' ?></div>
                </div>
            </div>
        </div>
        
        <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-blue-100 tw-rounded-xl tw-p-4">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-p-2 tw-bg-blue-500 tw-rounded-lg">
                    <i data-feather="clock" class="tw-h-5 tw-w-5 tw-text-white"></i>
                </div>
                <div>
                    <div class="tw-text-sm tw-font-medium tw-text-blue-700">End Time</div>
                    <div class="tw-text-lg tw-font-bold tw-text-blue-900"><?= $todaySchedule['end'] ?? 'Not Set' ?></div>
                </div>
            </div>
        </div>
        
        <div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-purple-100 tw-rounded-xl tw-p-4">
            <div class="tw-flex tw-items-center tw-space-x-3">
                <div class="tw-p-2 tw-bg-purple-500 tw-rounded-lg">
                    <i data-feather="zap" class="tw-h-5 tw-w-5 tw-text-white"></i>
                </div>
                <div>
                    <div class="tw-text-sm tw-font-medium tw-text-purple-700">Status</div>
                    <div class="tw-text-lg tw-font-bold tw-text-purple-900"><?= $todaySchedule['available'] ? 'Available' : 'Unavailable' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Weekly Schedule Management -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-mb-6">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-space-y-4 sm:tw-space-y-0">
            <div>
                <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                    <i data-feather="calendar" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-indigo-500"></i>
                    Weekly Schedule
                </h2>
                <p class="tw-text-sm tw-text-gray-500 tw-mt-1">Set your preferred working hours for each day</p>
            </div>
            <div class="tw-flex tw-space-x-2">
                <button onclick="resetSchedule()" 
                        class="tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-gray-200 tw-transition-colors tw-flex tw-items-center">
                    <i data-feather="rotate-ccw" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Reset
                </button>
                <button onclick="saveSchedule()" 
                        class="tw-px-4 tw-py-2 tw-bg-indigo-600 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium hover:tw-bg-indigo-700 tw-transition-colors tw-flex tw-items-center">
                    <i data-feather="save" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                    Save Schedule
                </button>
            </div>
        </div>
    </div>
    
    <div class="tw-p-6">
        <form id="scheduleForm">
            <div class="tw-space-y-4">
                <?php
                $days = [
                    'monday' => ['name' => 'Monday', 'icon' => 'briefcase', 'color' => 'blue'],
                    'tuesday' => ['name' => 'Tuesday', 'icon' => 'briefcase', 'color' => 'blue'],
                    'wednesday' => ['name' => 'Wednesday', 'icon' => 'briefcase', 'color' => 'blue'],
                    'thursday' => ['name' => 'Thursday', 'icon' => 'briefcase', 'color' => 'blue'],
                    'friday' => ['name' => 'Friday', 'icon' => 'briefcase', 'color' => 'blue'],
                    'saturday' => ['name' => 'Saturday', 'icon' => 'sun', 'color' => 'orange'],
                    'sunday' => ['name' => 'Sunday', 'icon' => 'sun', 'color' => 'orange']
                ];
                
                foreach ($days as $dayKey => $dayInfo):
                    $daySchedule = $schedule[$dayKey] ?? ['available' => false, 'start' => '09:00', 'end' => '17:00'];
                    $isToday = strtolower(date('l')) === $dayKey;
                ?>
                    <div class="tw-border tw-border-gray-200 tw-rounded-xl tw-p-4 hover:tw-border-gray-300 tw-transition-colors <?= $isToday ? 'tw-bg-blue-50 tw-border-blue-200' : '' ?>">
                        <!-- Day Header -->
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <div class="tw-flex tw-items-center tw-space-x-3">
                                <div class="tw-p-2 tw-bg-<?= $dayInfo['color'] ?>-100 tw-rounded-lg">
                                    <i data-feather="<?= $dayInfo['icon'] ?>" class="tw-h-5 tw-w-5 tw-text-<?= $dayInfo['color'] ?>-600"></i>
                                </div>
                                <div>
                                    <label for="<?= $dayKey ?>_available" class="tw-text-base tw-font-semibold tw-text-gray-900 tw-cursor-pointer tw-flex tw-items-center">
                                        <input type="checkbox" 
                                               id="<?= $dayKey ?>_available" 
                                               name="schedule[<?= $dayKey ?>][available]"
                                               <?= $daySchedule['available'] ? 'checked' : '' ?>
                                               onchange="toggleDaySchedule('<?= $dayKey ?>')"
                                               class="tw-h-5 tw-w-5 tw-text-indigo-600 tw-focus:ring-indigo-500 tw-border-gray-300 tw-rounded tw-mr-3">
                                        <?= $dayInfo['name'] ?>
                                        <?php if ($isToday): ?>
                                            <span class="tw-ml-2 tw-px-2 tw-py-1 tw-bg-blue-100 tw-text-blue-800 tw-text-xs tw-font-medium tw-rounded-full">Today</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="tw-text-right">
                                <div class="tw-text-sm tw-text-gray-500 tw-font-medium" id="<?= $dayKey ?>_duration">
                                    <?php
                                    if ($daySchedule['available']) {
                                        $start = strtotime($daySchedule['start']);
                                        $end = strtotime($daySchedule['end']);
                                        $hours = ($end - $start) / 3600;
                                        echo number_format($hours, 1) . ' hrs';
                                    } else {
                                        echo 'Off';
                                    }
                                    ?>
                                </div>
                                <?php if ($daySchedule['available']): ?>
                                    <div class="tw-text-xs tw-text-green-600 tw-font-medium">Available</div>
                                <?php else: ?>
                                    <div class="tw-text-xs tw-text-gray-400 tw-font-medium">Unavailable</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Time Inputs -->
                        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-4" id="<?= $dayKey ?>_times" style="<?= $daySchedule['available'] ? '' : 'opacity: 0.5; pointer-events: none;' ?>">
                            <div class="tw-space-y-2">
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-flex tw-items-center">
                                    <i data-feather="sunrise" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-yellow-500"></i>
                                    Start Time
                                </label>
                                <input type="time" 
                                       name="schedule[<?= $dayKey ?>][start]" 
                                       value="<?= $daySchedule['start'] ?>"
                                       class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-base tw-focus:ring-2 tw-focus:ring-indigo-500 tw-focus:border-indigo-500 tw-transition-colors tw-bg-white">
                            </div>
                            <div class="tw-space-y-2">
                                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-flex tw-items-center">
                                    <i data-feather="sunset" class="tw-h-4 tw-w-4 tw-mr-2 tw-text-orange-500"></i>
                                    End Time
                                </label>
                                <input type="time" 
                                       name="schedule[<?= $dayKey ?>][end]" 
                                       value="<?= $daySchedule['end'] ?>"
                                       class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-px-4 tw-py-3 tw-text-base tw-focus:ring-2 tw-focus:ring-indigo-500 tw-focus:border-indigo-500 tw-transition-colors tw-bg-white">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="tw-mt-6 tw-bg-gray-50 tw-rounded-xl tw-p-6">
                <div class="tw-mb-4">
                    <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
                        <i data-feather="zap" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-yellow-500"></i>
                        Quick Actions
                    </h3>
                    <p class="tw-text-sm tw-text-gray-500 tw-mt-1">Apply common schedule patterns</p>
                </div>
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-3">
                    <button type="button" onclick="applyWeekdaySchedule()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-blue-700 tw-bg-blue-100 tw-rounded-lg hover:tw-bg-blue-200 tw-transition-colors tw-border tw-border-blue-200">
                        <i data-feather="briefcase" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Weekdays Only
                    </button>
                    <button type="button" onclick="applyFullWeekSchedule()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-green-700 tw-bg-green-100 tw-rounded-lg hover:tw-bg-green-200 tw-transition-colors tw-border tw-border-green-200">
                        <i data-feather="calendar" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Full Week
                    </button>
                    <button type="button" onclick="applyWeekendsSchedule()" class="tw-flex tw-items-center tw-justify-center tw-px-4 tw-py-3 tw-text-sm tw-font-medium tw-text-purple-700 tw-bg-purple-100 tw-rounded-lg hover:tw-bg-purple-200 tw-transition-colors tw-border tw-border-purple-200">
                        <i data-feather="sun" class="tw-h-4 tw-w-4 tw-mr-2"></i>
                        Weekends Only
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Summary -->
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-mb-6">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
            <i data-feather="bar-chart-2" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-green-500"></i>
            Schedule Summary
        </h2>
    </div>
    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-6">
            <div class="tw-bg-gradient-to-r tw-from-blue-50 tw-to-blue-100 tw-rounded-xl tw-p-6 tw-text-center">
                <div class="tw-text-3xl tw-font-bold tw-text-blue-900 tw-mb-2" id="totalHours">0</div>
                <div class="tw-text-sm tw-font-medium tw-text-blue-700 tw-mb-1">Total Hours/Week</div>
                <div class="tw-text-xs tw-text-blue-600">Across all working days</div>
            </div>
            <div class="tw-bg-gradient-to-r tw-from-green-50 tw-to-green-100 tw-rounded-xl tw-p-6 tw-text-center">
                <div class="tw-text-3xl tw-font-bold tw-text-green-900 tw-mb-2" id="workingDays">0</div>
                <div class="tw-text-sm tw-font-medium tw-text-green-700 tw-mb-1">Working Days</div>
                <div class="tw-text-xs tw-text-green-600">Days you're available</div>
            </div>
            <div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-purple-100 tw-rounded-xl tw-p-6 tw-text-center">
                <div class="tw-text-3xl tw-font-bold tw-text-purple-900 tw-mb-2" id="avgHours">0</div>
                <div class="tw-text-sm tw-font-medium tw-text-purple-700 tw-mb-1">Avg Hours/Day</div>
                <div class="tw-text-xs tw-text-purple-600">Average per working day</div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Deliveries (if any) -->
<?php if (!empty($upcomingDeliveries)): ?>
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100 tw-mb-6">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
            <i data-feather="truck" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-orange-500"></i>
            Upcoming Deliveries
        </h2>
    </div>
    <div class="tw-p-6">
        <div class="tw-space-y-3">
            <?php foreach ($upcomingDeliveries as $delivery): ?>
                <div class="tw-flex tw-items-center tw-justify-between tw-p-4 tw-bg-orange-50 tw-rounded-xl tw-border tw-border-orange-200">
                    <div class="tw-flex tw-items-center tw-space-x-3">
                        <div class="tw-p-2 tw-bg-orange-500 tw-rounded-lg">
                            <i data-feather="package" class="tw-h-5 tw-w-5 tw-text-white"></i>
                        </div>
                        <div>
                            <div class="tw-text-sm tw-font-medium tw-text-gray-900">Order #<?= $delivery['order_id'] ?></div>
                            <div class="tw-text-xs tw-text-gray-500"><?= $delivery['restaurant_name'] ?></div>
                        </div>
                    </div>
                    <div class="tw-text-right">
                        <div class="tw-text-sm tw-font-medium tw-text-orange-700"><?= $delivery['scheduled_time'] ?></div>
                        <div class="tw-text-xs tw-text-orange-600"><?= $delivery['status'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Weekly Performance Stats -->
<?php if (!empty($weeklyStats)): ?>
<div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-border tw-border-gray-100">
    <div class="tw-p-6 tw-border-b tw-border-gray-200">
        <h2 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-flex tw-items-center">
            <i data-feather="trending-up" class="tw-h-5 tw-w-5 tw-mr-2 tw-text-green-500"></i>
            This Week's Performance
        </h2>
    </div>
    <div class="tw-p-6">
        <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-4 tw-gap-4">
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $weeklyStats['deliveries'] ?? 0 ?></div>
                <div class="tw-text-sm tw-text-gray-500">Deliveries</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $weeklyStats['earnings'] ?? 0 ?></div>
                <div class="tw-text-sm tw-text-gray-500">Earnings (XAF)</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $weeklyStats['rating'] ?? 0 ?></div>
                <div class="tw-text-sm tw-text-gray-500">Avg Rating</div>
            </div>
            <div class="tw-text-center">
                <div class="tw-text-2xl tw-font-bold tw-text-gray-900"><?= $weeklyStats['hours_worked'] ?? 0 ?></div>
                <div class="tw-text-sm tw-text-gray-500">Hours Worked</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Initialize Feather icons
feather.replace();

function toggleDaySchedule(day) {
    const checkbox = document.getElementById(day + '_available');
    const timesDiv = document.getElementById(day + '_times');
    const durationDiv = document.getElementById(day + '_duration');
    
    if (checkbox.checked) {
        timesDiv.style.opacity = '1';
        timesDiv.style.pointerEvents = 'auto';
        // Calculate and show duration
        const startInput = document.querySelector(`input[name="schedule[${day}][start]"]`);
        const endInput = document.querySelector(`input[name="schedule[${day}][end]"]`);
        if (startInput && endInput) {
            const start = new Date('2000-01-01 ' + startInput.value);
            const end = new Date('2000-01-01 ' + endInput.value);
            const hours = (end - start) / (1000 * 60 * 60);
            durationDiv.textContent = hours.toFixed(1) + ' hrs';
        }
    } else {
        timesDiv.style.opacity = '0.5';
        timesDiv.style.pointerEvents = 'none';
        durationDiv.textContent = 'Off';
    }
    
    updateScheduleSummary();
}

function updateScheduleSummary() {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    let totalHours = 0;
    let workingDays = 0;
    
    days.forEach(day => {
        const checkbox = document.getElementById(day + '_available');
        if (checkbox.checked) {
            workingDays++;
            const startInput = document.querySelector(`input[name="schedule[${day}][start]"]`);
            const endInput = document.querySelector(`input[name="schedule[${day}][end]"]`);
            
            if (startInput && endInput) {
                const start = new Date('2000-01-01 ' + startInput.value);
                const end = new Date('2000-01-01 ' + endInput.value);
                const hours = (end - start) / (1000 * 60 * 60);
                totalHours += hours;
                
                // Update duration display
                const durationDiv = document.getElementById(day + '_duration');
                if (durationDiv) {
                    durationDiv.textContent = hours.toFixed(1) + ' hrs';
                }
            }
        }
    });
    
    document.getElementById('totalHours').textContent = totalHours.toFixed(1);
    document.getElementById('workingDays').textContent = workingDays;
    document.getElementById('avgHours').textContent = workingDays > 0 ? (totalHours / workingDays).toFixed(1) : '0';
}

function applyWeekdaySchedule() {
    const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    const weekends = ['saturday', 'sunday'];
    
    // Enable weekdays
    weekdays.forEach(day => {
        document.getElementById(day + '_available').checked = true;
        document.querySelector(`input[name="schedule[${day}][start]"]`).value = '09:00';
        document.querySelector(`input[name="schedule[${day}][end]"]`).value = '17:00';
        toggleDaySchedule(day);
    });
    
    // Disable weekends
    weekends.forEach(day => {
        document.getElementById(day + '_available').checked = false;
        toggleDaySchedule(day);
    });
    
    updateScheduleSummary();
    showNotification('Weekday schedule applied successfully!', 'success');
}

function applyFullWeekSchedule() {
    const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    allDays.forEach(day => {
        document.getElementById(day + '_available').checked = true;
        document.querySelector(`input[name="schedule[${day}][start]"]`).value = '09:00';
        document.querySelector(`input[name="schedule[${day}][end]"]`).value = '17:00';
        toggleDaySchedule(day);
    });
    
    updateScheduleSummary();
    showNotification('Full week schedule applied successfully!', 'success');
}

function applyWeekendsSchedule() {
    const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    const weekends = ['saturday', 'sunday'];
    
    // Disable weekdays
    weekdays.forEach(day => {
        document.getElementById(day + '_available').checked = false;
        toggleDaySchedule(day);
    });
    
    // Enable weekends
    weekends.forEach(day => {
        document.getElementById(day + '_available').checked = true;
        document.querySelector(`input[name="schedule[${day}][start]"]`).value = '10:00';
        document.querySelector(`input[name="schedule[${day}][end]"]`).value = '18:00';
        toggleDaySchedule(day);
    });
    
    updateScheduleSummary();
    showNotification('Weekend schedule applied successfully!', 'success');
}

function resetSchedule() {
    if (!confirm('Are you sure you want to reset your schedule? This will clear all your current settings.')) {
        return;
    }
    
    const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    allDays.forEach(day => {
        document.getElementById(day + '_available').checked = false;
        document.querySelector(`input[name="schedule[${day}][start]"]`).value = '09:00';
        document.querySelector(`input[name="schedule[${day}][end]"]`).value = '17:00';
        toggleDaySchedule(day);
    });
    
    updateScheduleSummary();
    showNotification('Schedule reset successfully!', 'info');
}

function saveSchedule() {
    const formData = new FormData(document.getElementById('scheduleForm'));
    const scheduleData = {};
    
    // Convert FormData to nested object
    for (let [key, value] of formData.entries()) {
        const matches = key.match(/schedule\[(\w+)\]\[(\w+)\]/);
        if (matches) {
            const day = matches[1];
            const field = matches[2];
            
            if (!scheduleData[day]) {
                scheduleData[day] = {};
            }
            
            if (field === 'available') {
                scheduleData[day][field] = true;
            } else {
                scheduleData[day][field] = value;
            }
        }
    }
    
    // Add unchecked checkboxes as false
    const allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    allDays.forEach(day => {
        if (!scheduleData[day]) {
            scheduleData[day] = {};
        }
        if (!scheduleData[day].hasOwnProperty('available')) {
            scheduleData[day].available = false;
        }
    });
    
    // Show loading state
    const saveBtn = document.querySelector('button[onclick="saveSchedule()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Saving...';
    saveBtn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/schedule') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({
            schedule: JSON.stringify(scheduleData)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Schedule saved successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to save schedule', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving schedule:', error);
        showNotification('Failed to save schedule. Please try again.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
        feather.replace();
    });
}

function toggleAvailability() {
    const btn = document.querySelector('button[onclick="toggleAvailability()"]');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i data-feather="loader" class="tw-h-4 tw-w-4 tw-mr-2 tw-animate-spin"></i>Updating...';
    btn.disabled = true;
    feather.replace();
    
    fetch('<?= url('/rider/toggle-availability') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({
            available: !<?= ($user->is_available ?? false) ? 'true' : 'false' ?>,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Status updated successfully!', 'success');
            // Reload page to update status
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling availability:', error);
        showNotification('Failed to update status. Please try again.', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        feather.replace();
    });
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

// Initialize schedule summary on page load
document.addEventListener('DOMContentLoaded', function() {
    updateScheduleSummary();
    
    // Add event listeners to time inputs
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        input.addEventListener('change', updateScheduleSummary);
    });
    
    // Add touch feedback to buttons
    const buttons = document.querySelectorAll('button, label');
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