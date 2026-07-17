<?php
require_once '../includes/db.php';
$current_page = 'payment.php';

$success_msg = '';
$error_msg = '';

// Handle Manual Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manual_payment') {
    $profile_id = trim($_POST['profile_id']);
    $membership_id = (int)$_POST['membership_id'];
    $amount = (float)$_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $payment_remarks = trim($_POST['payment_remarks']);
    
    // Clean up Profile ID (remove # if user typed it)
    $profile_id = ltrim($profile_id, '#');

    // Find User
    $stmt = $pdo->prepare("SELECT id FROM users WHERE profile_id = ?");
    $stmt->execute([$profile_id]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['id'];
        
        // Find Plan Duration
        $planStmt = $pdo->prepare("SELECT duration_days FROM memberships WHERE id = ?");
        $planStmt->execute([$membership_id]);
        $plan = $planStmt->fetch();
        
        if ($plan) {
            try {
                $pdo->beginTransaction();
                
                // Insert Payment
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, membership_id, amount, transaction_id, payment_method, payment_remarks, status) VALUES (?, ?, ?, ?, ?, ?, 'verified')");
                $transaction_id = 'MANUAL-' . time();
                $stmt->execute([$user_id, $membership_id, $amount, $transaction_id, $payment_method, $payment_remarks]);
                
                // Update or Insert User Membership
                // Check if user already has active membership
                $checkMembership = $pdo->prepare("SELECT id FROM user_memberships WHERE user_id = ? AND status = 'active'");
                $checkMembership->execute([$user_id]);
                $existing = $checkMembership->fetch();
                
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+" . $plan['duration_days'] . " days"));
                
                if ($existing) {
                    // Update existing
                    $updateMem = $pdo->prepare("UPDATE user_memberships SET membership_id = ?, start_date = ?, end_date = ? WHERE id = ?");
                    $updateMem->execute([$membership_id, $start_date, $end_date, $existing['id']]);
                } else {
                    // Insert new
                    $insertMem = $pdo->prepare("INSERT INTO user_memberships (user_id, membership_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
                    $insertMem->execute([$user_id, $membership_id, $start_date, $end_date]);
                }
                
                $pdo->commit();
                $success_msg = "Manual payment recorded and membership activated successfully.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = "Error recording payment: " . $e->getMessage();
            }
        } else {
            $error_msg = "Invalid membership plan selected.";
        }
    } else {
        $error_msg = "User with Profile ID $profile_id not found.";
    }
}

// Fetch Payment History
$paymentsStmt = $pdo->query("SELECT p.*, u.full_name, u.profile_id as user_profile_id, m.plan_name FROM payments p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN memberships m ON p.membership_id = m.id ORDER BY p.created_at DESC");
$payments = $paymentsStmt->fetchAll();

// Fetch Active Memberships for dropdown
$plansStmt = $pdo->query("SELECT * FROM memberships WHERE status = 1");
$active_plans = $plansStmt->fetchAll();

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Page Header -->
<div class="mb-6">
    <h3 class="text-2xl font-bold text-gray-800">Payment Management</h3>
    <p class="text-gray-500 text-sm">View transactions, record manual payments, and configure gateways.</p>
</div>

<?php if ($success_msg): ?>
<div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
    <?= htmlspecialchars($success_msg) ?>
</div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
    <?= htmlspecialchars($error_msg) ?>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="bg-white rounded-t-xl border-b border-gray-200 px-6 pt-4 flex space-x-6">
    <button class="pb-3 text-sm font-bold text-primary border-b-2 border-primary" onclick="switchTab('history')">Payment History</button>
    <button class="pb-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition" onclick="switchTab('manual')">Manual Payment</button>
    <button class="pb-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition" onclick="switchTab('settings')">Payment Methods</button>
</div>

<!-- Tab Content: History -->
<div id="tab-history" class="bg-white p-6 rounded-b-xl shadow-sm border border-t-0 border-gray-100">
    <div class="flex justify-between items-center mb-4">
        <h4 class="font-bold text-gray-800">Recent Transactions</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="py-3 px-4 font-semibold">Transaction ID</th>
                    <th class="py-3 px-4 font-semibold">Profile</th>
                    <th class="py-3 px-4 font-semibold">Plan</th>
                    <th class="py-3 px-4 font-semibold">Amount</th>
                    <th class="py-3 px-4 font-semibold">Method</th>
                    <th class="py-3 px-4 font-semibold">Date</th>
                    <th class="py-3 px-4 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php if(empty($payments)): ?>
                    <tr><td colspan="7" class="py-3 px-4 text-center text-gray-500">No payment history found.</td></tr>
                <?php else: ?>
                    <?php foreach($payments as $payment): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 text-gray-800 font-medium"><?= htmlspecialchars($payment['transaction_id'] ?? 'N/A') ?></td>
                        <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($payment['full_name']) ?> (<?= htmlspecialchars($payment['user_profile_id'] ?? 'N/A') ?>)</td>
                        <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($payment['plan_name'] ?? 'Custom') ?></td>
                        <td class="py-3 px-4 text-gray-800 font-bold">₹<?= number_format($payment['amount'], 2) ?></td>
                        <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($payment['payment_method']) ?></td>
                        <td class="py-3 px-4 text-gray-600"><?= date('M d, Y', strtotime($payment['created_at'])) ?></td>
                        <td class="py-3 px-4">
                            <?php if($payment['status'] === 'verified' || $payment['status'] === 'success'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700">Success</span>
                            <?php elseif($payment['status'] === 'pending'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-700">Pending</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700"><?= ucfirst($payment['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tab Content: Manual Payment -->
<div id="tab-manual" class="bg-white p-6 rounded-b-xl shadow-sm border border-t-0 border-gray-100 hidden">
    <div class="max-w-xl">
        <h4 class="font-bold text-gray-800 mb-4">Record Manual Payment</h4>
        <p class="text-sm text-gray-500 mb-6">Use this form to grant membership to users who paid offline (Cash, Cheque, Direct Bank Transfer).</p>
        
        <form method="POST" action="payment.php" class="space-y-4">
            <input type="hidden" name="action" value="manual_payment">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Profile ID</label>
                <input type="text" name="profile_id" required placeholder="e.g. JDM1045" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Membership Plan</label>
                <select name="membership_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white text-sm">
                    <option value="">Select a plan</option>
                    <?php foreach($active_plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>"><?= htmlspecialchars($plan['plan_name']) ?> (₹<?= number_format($plan['price'], 2) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid (₹)</label>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 5000" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white text-sm">
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference / Notes</label>
                <textarea name="payment_remarks" rows="3" placeholder="Cheque number or any specific details" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm"></textarea>
            </div>
            
            <button type="submit" class="bg-primary text-white py-2 px-6 rounded-lg font-bold shadow-md hover:bg-opacity-90 transition mt-2">
                Record Payment & Activate Plan
            </button>
        </form>
    </div>
</div>

<!-- Tab Content: Settings -->
<div id="tab-settings" class="bg-white p-6 rounded-b-xl shadow-sm border border-t-0 border-gray-100 hidden">
    <div class="max-w-3xl">
        <h4 class="font-bold text-gray-800 mb-4">Payment Gateways</h4>
        <p class="text-sm text-gray-500 mb-6">Configure the active payment methods shown to users during registration or plan upgrades. <em>(Note: Configuration forms need to be implemented)</em></p>
        
        <div class="space-y-4">
            <!-- Razorpay -->
            <div class="border border-gray-200 rounded-xl p-4 flex items-center justify-between bg-gray-50">
                <div class="flex items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" alt="Razorpay" class="h-6 w-auto mr-4">
                    <div>
                        <h5 class="font-bold text-gray-800">Razorpay (UPI, Cards, NetBanking)</h5>
                        <p class="text-xs text-gray-500">Currently active Gateway.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="text-sm text-primary font-medium hover:underline">Edit Keys</button>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching logic
    function switchTab(tabId) {
        // Hide all tabs
        document.getElementById('tab-history').classList.add('hidden');
        document.getElementById('tab-manual').classList.add('hidden');
        document.getElementById('tab-settings').classList.add('hidden');
        
        // Show selected tab
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        
        // Update button styles
        const buttons = document.querySelectorAll('.bg-white.rounded-t-xl button');
        buttons.forEach(btn => {
            btn.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
            btn.classList.add('text-gray-500', 'font-medium');
        });
        
        // Highlight active button
        const activeBtn = event.currentTarget;
        activeBtn.classList.remove('text-gray-500', 'font-medium');
        activeBtn.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
    }
    
    <?php if ($success_msg || $error_msg): ?>
    // Auto-switch to manual tab if there was a submission
    switchTab('manual');
    const tabs = document.querySelectorAll('.bg-white.rounded-t-xl button');
    tabs.forEach(btn => {
        if(btn.textContent.includes('Manual Payment')) {
            btn.classList.remove('text-gray-500', 'font-medium');
            btn.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
        } else {
            btn.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
            btn.classList.add('text-gray-500', 'font-medium');
        }
    });
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
