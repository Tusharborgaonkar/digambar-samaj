<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
require_once '../includes/Mailer.php';
$current_page = 'bulk-email.php';

// Fetch users for selection
$stmt = $pdo->query("SELECT id, full_name, email FROM users WHERE email IS NOT NULL AND email != '' ORDER BY full_name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_bulk_email'])) {
    $subject = trim($_POST['subject'] ?? '');
    $messageBody = trim($_POST['message'] ?? '');
    $selected_users = $_POST['users'] ?? [];
    
    if (empty($subject) || empty($messageBody) || empty($selected_users)) {
        $error = "Please fill in all fields and select at least one user.";
    } else {
        $mailer = new Mailer();
        $successCount = 0;
        $failCount = 0;
        
        foreach ($selected_users as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Personalization could be added here if needed
                if ($mailer->send($email, $subject, $messageBody)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                // Optional: add a small delay to avoid spam filters/rate limiting
                usleep(500000); // 0.5 seconds
            }
        }
        
        $success = "Successfully sent {$successCount} emails. Failed: {$failCount}.";
    }
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Bulk Email</h3>
        <p class="text-gray-500 text-sm">Send emails to multiple registered users at once.</p>
    </div>
</div>

<?php if(isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-6">
        <form action="" method="POST" class="space-y-6">
            
            <!-- Subject -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject <span class="text-red-500">*</span></label>
                <input type="text" id="subject" name="subject" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="Enter email subject">
            </div>
            
            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Email Body (HTML supported) <span class="text-red-500">*</span></label>
                <textarea id="message" name="message" rows="8" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="Write your message here... You can use HTML tags."></textarea>
            </div>
            
            <!-- User Selection -->
            <div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Select Recipients <span class="text-red-500">*</span></label>
                        <button type="button" id="selectAllBtn" class="text-sm text-primary font-bold hover:underline">Select All Visible</button>
                    </div>
                    <div class="relative w-full sm:w-80 flex">
                        <div class="relative w-full">
                            <input type="text" id="searchInput" placeholder="Search name or email..." class="w-full pl-10 pr-4 py-2 border border-gray-300 border-r-0 rounded-l-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                        </div>
                        <button type="button" id="searchBtn" class="bg-primary text-white px-4 py-2 rounded-r-lg text-sm font-bold hover:bg-opacity-90 transition whitespace-nowrap border border-primary">
                            Search
                        </button>
                    </div>
                </div>
                
                <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto p-4 bg-gray-50">
                    <?php if (empty($users)): ?>
                        <p class="text-gray-500 text-sm">No users found with valid email addresses.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php foreach ($users as $user): ?>
                                <label class="user-label flex items-center space-x-3 bg-white p-2 border border-gray-200 rounded shadow-sm hover:bg-gray-50 cursor-pointer transition">
                                    <input type="checkbox" name="users[]" value="<?= htmlspecialchars($user['email']) ?>" class="user-checkbox form-checkbox h-4 w-4 text-primary rounded border-gray-300 focus:ring-primary">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-800"><?= htmlspecialchars(trim($user['full_name'])) ?: 'Unknown Name' ?></span>
                                        <span class="text-xs text-gray-500 truncate" title="<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-500 mt-2">Selected: <span id="selectedCount">0</span> users</p>
            </div>
            
            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" name="send_bulk_email" id="submitBtn" class="bg-primary text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-opacity-90 transition shadow-sm flex items-center" onclick="return confirm('Are you sure you want to send this email to all selected users? This may take some time depending on the number of recipients.');">
                    <i class="fas fa-paper-plane mr-2"></i> Send Bulk Email
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const userLabels = document.querySelectorAll('.user-label');
    
    function performSearch() {
        if(!searchInput) return;
        const term = searchInput.value.toLowerCase();
        userLabels.forEach(label => {
            const text = label.textContent.toLowerCase();
            if(text.includes(term)) {
                label.style.display = 'flex';
            } else {
                label.style.display = 'none';
            }
        });
    }

    if(searchInput) {
        searchInput.addEventListener('keyup', performSearch);
    }
    if(searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }

    const selectAllBtn = document.getElementById('selectAllBtn');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    let allSelected = false;
    
    function updateCount() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        selectedCountSpan.textContent = checkedCount;
    }
    
    selectAllBtn.addEventListener('click', function() {
        allSelected = !allSelected;
        
        userLabels.forEach(label => {
            if (label.style.display !== 'none') {
                const cb = label.querySelector('.user-checkbox');
                if (cb) cb.checked = allSelected;
            }
        });
        
        selectAllBtn.textContent = allSelected ? 'Deselect All Visible' : 'Select All Visible';
        updateCount();
    });
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateCount();
            if (!this.checked) {
                allSelected = false;
                selectAllBtn.textContent = 'Select All';
            } else {
                const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
                if (checkedCount === checkboxes.length) {
                    allSelected = true;
                    selectAllBtn.textContent = 'Deselect All';
                }
            }
        });
    });
    
    // Add loading state to button on submit
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        if(document.querySelectorAll('.user-checkbox:checked').length > 0) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            // We don't disable the button because it prevents the form submission value
            btn.style.pointerEvents = 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
