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
    $stmt = $pdo->prepare("SELECT * FROM users WHERE profile_id = ?");
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
                $address = trim(!empty($user['current_address']) ? $user['current_address'] : ($user['permanent_address'] ?? ''));
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, full_name, phone_number, email, address, dob, membership_id, amount, transaction_id, payment_method, payment_remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'verified')");
                $transaction_id = 'MANUAL-' . time();
                $stmt->execute([$user_id, $user['full_name'], $user['mobile'], $user['email'], $address, $user['birth_date'], $membership_id, $amount, $transaction_id, $payment_method, $payment_remarks]);
                
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

// Handle Screenshot Approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve_screenshot') {
        try {
            $user_id = (int)$_POST['user_id'];
            $stmt = $pdo->prepare("UPDATE users SET payment_status = 'approved' WHERE id = ?");
            $stmt->execute([$user_id]);
            
            // Update payments table if exists, else insert
            $check = $pdo->prepare("SELECT id FROM payments WHERE user_id = ? AND payment_method = 'Screenshot'");
            $check->execute([$user_id]);
            if ($check->rowCount() > 0) {
                $update = $pdo->prepare("UPDATE payments SET status = 'verified' WHERE user_id = ? AND payment_method = 'Screenshot'");
                $update->execute([$user_id]);
            } else {
                $insert = $pdo->prepare("INSERT INTO payments (user_id, full_name, phone_number, email, address, dob, transaction_id, payment_method, payment_screenshot, status) SELECT id, full_name, mobile, email, COALESCE(current_address, permanent_address, ''), CASE WHEN birth_date = '' OR birth_date = '0000-00-00' THEN NULL ELSE birth_date END, payment_transaction_id, 'Screenshot', payment_screenshot, 'verified' FROM users WHERE id = ?");
                $insert->execute([$user_id]);
            }
            
            $success_msg = "Screenshot approved successfully.";
        } catch (Exception $e) {
            $error_msg = "Error approving screenshot: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'reject_screenshot') {
        try {
            $user_id = (int)$_POST['user_id'];
            $stmt = $pdo->prepare("UPDATE users SET payment_status = 'rejected' WHERE id = ?");
            $stmt->execute([$user_id]);
            
            // Update payments table if exists, else insert
            $check = $pdo->prepare("SELECT id FROM payments WHERE user_id = ? AND payment_method = 'Screenshot'");
            $check->execute([$user_id]);
            if ($check->rowCount() > 0) {
                $update = $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE user_id = ? AND payment_method = 'Screenshot'");
                $update->execute([$user_id]);
            } else {
                $insert = $pdo->prepare("INSERT INTO payments (user_id, full_name, phone_number, email, address, dob, transaction_id, payment_method, payment_screenshot, status) SELECT id, full_name, mobile, email, COALESCE(current_address, permanent_address, ''), CASE WHEN birth_date = '' OR birth_date = '0000-00-00' THEN NULL ELSE birth_date END, payment_transaction_id, 'Screenshot', payment_screenshot, 'rejected' FROM users WHERE id = ?");
                $insert->execute([$user_id]);
            }
            
            $success_msg = "Screenshot rejected.";
        } catch (Exception $e) {
            $error_msg = "Error rejecting screenshot: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'bulk_approve_screenshot') {
        try {
            $user_ids = $_POST['user_ids'] ?? [];
            if(!empty($user_ids)) {
                $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE users SET payment_status = 'approved' WHERE id IN ($placeholders)");
                $stmt->execute($user_ids);
                
                $update = $pdo->prepare("UPDATE payments SET status = 'verified' WHERE user_id IN ($placeholders) AND payment_method = 'Screenshot'");
                $update->execute($user_ids);
                
                // Insert missing
                $insert = $pdo->prepare("INSERT INTO payments (user_id, full_name, phone_number, email, address, dob, transaction_id, payment_method, payment_screenshot, status) SELECT id, full_name, mobile, email, COALESCE(current_address, permanent_address, ''), CASE WHEN birth_date = '' OR birth_date = '0000-00-00' THEN NULL ELSE birth_date END, payment_transaction_id, 'Screenshot', payment_screenshot, 'verified' FROM users WHERE id IN ($placeholders) AND id NOT IN (SELECT user_id FROM payments WHERE payment_method = 'Screenshot' AND user_id IS NOT NULL)");
                $insert->execute(array_merge($user_ids));
                
                $success_msg = count($user_ids) . " screenshot(s) approved successfully.";
            }
        } catch (Exception $e) {
            $error_msg = "Error in bulk approval: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'bulk_reject_screenshot') {
        try {
            $user_ids = $_POST['user_ids'] ?? [];
            if(!empty($user_ids)) {
                $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE users SET payment_status = 'rejected' WHERE id IN ($placeholders)");
                $stmt->execute($user_ids);
                
                $update = $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE user_id IN ($placeholders) AND payment_method = 'Screenshot'");
                $update->execute($user_ids);
                
                // Insert missing
                $insert = $pdo->prepare("INSERT INTO payments (user_id, full_name, phone_number, email, address, dob, transaction_id, payment_method, payment_screenshot, status) SELECT id, full_name, mobile, email, COALESCE(current_address, permanent_address, ''), CASE WHEN birth_date = '' OR birth_date = '0000-00-00' THEN NULL ELSE birth_date END, payment_transaction_id, 'Screenshot', payment_screenshot, 'rejected' FROM users WHERE id IN ($placeholders) AND id NOT IN (SELECT user_id FROM payments WHERE payment_method = 'Screenshot' AND user_id IS NOT NULL)");
                $insert->execute(array_merge($user_ids));
                
                $success_msg = count($user_ids) . " screenshot(s) rejected.";
            }
        } catch (Exception $e) {
            $error_msg = "Error in bulk rejection: " . $e->getMessage();
        }
    }
}

$limit = 10;
// Pagination logic for History
$history_page = isset($_GET['h_page']) ? (int)$_GET['h_page'] : 1;
if ($history_page < 1) $history_page = 1;
$history_offset = ($history_page - 1) * $limit;

$paymentsCountStmt = $pdo->query("SELECT COUNT(*) FROM payments");
$total_history = $paymentsCountStmt->fetchColumn();
$total_history_pages = max(1, ceil($total_history / $limit));

$paymentsStmt = $pdo->prepare("SELECT p.*, u.profile_id as user_profile_id, m.plan_name FROM payments p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN memberships m ON p.membership_id = m.id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset");
$paymentsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$paymentsStmt->bindValue(':offset', $history_offset, PDO::PARAM_INT);
$paymentsStmt->execute();
$payments = $paymentsStmt->fetchAll();

// Fetch Active Memberships for dropdown
$plansStmt = $pdo->query("SELECT * FROM memberships WHERE status = 1");
$active_plans = $plansStmt->fetchAll();

// Pagination logic for Approvals
$approvals_page = isset($_GET['a_page']) ? (int)$_GET['a_page'] : 1;
if ($approvals_page < 1) $approvals_page = 1;
$approvals_offset = ($approvals_page - 1) * $limit;

$approvalsCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE payment_screenshot IS NOT NULL AND payment_status = 'pending'");
$total_approvals = $approvalsCountStmt->fetchColumn();
$total_approvals_pages = max(1, ceil($total_approvals / $limit));

$pendingScreenshotsStmt = $pdo->prepare("SELECT id, full_name, profile_id, payment_screenshot, payment_transaction_id, created_at FROM users WHERE payment_screenshot IS NOT NULL AND payment_status = 'pending' ORDER BY updated_at DESC LIMIT :limit OFFSET :offset");
$pendingScreenshotsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$pendingScreenshotsStmt->bindValue(':offset', $approvals_offset, PDO::PARAM_INT);
$pendingScreenshotsStmt->execute();
$pending_screenshots = $pendingScreenshotsStmt->fetchAll();

// To remember which tab to open
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'history';
if (isset($_POST['action']) && strpos($_POST['action'], 'screenshot') !== false) {
    $active_tab = 'approvals';
}

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
<div class="bg-white rounded-t-xl border-b border-gray-200 px-6 pt-4 flex space-x-6 overflow-x-auto">
    <button class="pb-3 text-sm font-bold text-primary border-b-2 border-primary whitespace-nowrap" onclick="switchTab('history', event)">Payment History</button>
    <button class="pb-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition whitespace-nowrap" onclick="switchTab('approvals', event)">
        Screenshot Approvals
        <?php if($total_approvals > 0): ?>
            <span class="ml-1 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $total_approvals ?></span>
        <?php endif; ?>
    </button>
    <button class="pb-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition whitespace-nowrap" onclick="switchTab('manual', event)">Manual Payment</button>
    <button class="pb-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition whitespace-nowrap" onclick="switchTab('settings', event)">Payment Methods</button>
</div>

<!-- Tab Content: History -->
<div id="tab-history" class="bg-white p-6 rounded-b-xl shadow-sm border border-t-0 border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <h4 class="font-bold text-gray-800">Recent Transactions</h4>
        <div class="relative w-full md:w-1/3">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </span>
            <input type="text" id="paymentSearch" placeholder="Search by name, phone, email, Txn ID..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm transition">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="py-3 px-4 font-semibold">User Details</th>
                    <th class="py-3 px-4 font-semibold">Contact Info</th>
                    <th class="py-3 px-4 font-semibold">Txn ID / Date</th>
                    <th class="py-3 px-4 font-semibold">Plan & Amount</th>
                    <th class="py-3 px-4 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php if(empty($payments)): ?>
                    <tr><td colspan="5" class="py-3 px-4 text-center text-gray-500">No payment history found.</td></tr>
                <?php else: ?>
                    <?php foreach($payments as $payment): ?>
                    <tr class="hover:bg-gray-50 transition payment-row">
                        <td class="py-3 px-4 text-gray-800">
                            <div class="font-bold searchable"><?= htmlspecialchars($payment['full_name'] ?? 'N/A') ?> (<?= htmlspecialchars($payment['user_profile_id'] ?? 'N/A') ?>)</div>
                            <div class="text-xs text-gray-500 mt-1">DOB: <span class="searchable"><?= !empty($payment['dob']) ? date('d M Y', strtotime($payment['dob'])) : 'N/A' ?></span></div>
                            <div class="text-xs text-gray-500 max-w-xs truncate searchable" title="<?= htmlspecialchars($payment['address'] ?? 'N/A') ?>"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($payment['address'] ?? 'N/A') ?></div>
                        </td>
                        <td class="py-3 px-4 text-gray-600">
                            <div class="text-sm searchable"><i class="fas fa-phone-alt text-gray-400 w-4"></i> <?= htmlspecialchars($payment['phone_number'] ?? 'N/A') ?></div>
                            <div class="text-sm searchable"><i class="fas fa-envelope text-gray-400 w-4"></i> <?= htmlspecialchars($payment['email'] ?? 'N/A') ?></div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-800 searchable"><?= htmlspecialchars($payment['transaction_id'] ?? 'N/A') ?></div>
                            <div class="text-xs text-gray-500"><?= date('M d, Y', strtotime($payment['created_at'])) ?></div>
                            <div class="text-xs text-gray-500 searchable"><?= htmlspecialchars($payment['payment_method']) ?></div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-800 searchable"><?= htmlspecialchars($payment['plan_name'] ?? 'Custom') ?></div>
                            <div class="font-bold text-primary">₹<?= number_format($payment['amount'], 2) ?></div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="searchable hidden"><?= $payment['status'] ?></div>
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
    
    <!-- Pagination for History -->
    <?php if ($total_history_pages > 1): ?>
    <div class="flex justify-center mt-6 space-x-1 flex-wrap gap-y-2">
        <?php 
        $start_page = max(1, $history_page - 2);
        $end_page = min($total_history_pages, $history_page + 2);
        
        if ($start_page > 1): ?>
            <a href="payment.php?tab=history&h_page=1&a_page=<?= $approvals_page ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">1</a>
            <?php if ($start_page > 2): ?>
                <span class="px-2 py-1 text-gray-500">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i == $history_page): ?>
                <button class="px-3 py-1 border border-primary bg-primary text-white rounded text-sm font-medium"><?= $i ?></button>
            <?php else: ?>
                <a href="payment.php?tab=history&h_page=<?= $i ?>&a_page=<?= $approvals_page ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end_page < $total_history_pages): ?>
            <?php if ($end_page < $total_history_pages - 1): ?>
                <span class="px-2 py-1 text-gray-500">...</span>
            <?php endif; ?>
            <a href="payment.php?tab=history&h_page=<?= $total_history_pages ?>&a_page=<?= $approvals_page ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $total_history_pages ?></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Tab Content: Approvals -->
<div id="tab-approvals" class="bg-white p-6 rounded-b-xl shadow-sm border border-t-0 border-gray-100 hidden">
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <h4 class="font-bold text-gray-800">Pending Screenshot Approvals</h4>
        <div class="flex items-center gap-2">
            <select id="bulkActionSelect" class="border border-gray-300 rounded px-3 py-1 text-sm outline-none focus:border-primary">
                <option value="">Bulk Actions</option>
                <option value="bulk_approve_screenshot">Approve Selected</option>
                <option value="bulk_reject_screenshot">Reject Selected</option>
            </select>
            <button type="button" onclick="submitBulkAction()" class="bg-gray-800 text-white px-4 py-1 rounded text-sm hover:bg-gray-700 transition shadow-sm">Apply</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="py-3 px-4 font-semibold w-12">
                        <input type="checkbox" id="selectAllApprovals" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-4 font-semibold">User Details</th>
                    <th class="py-3 px-4 font-semibold">Screenshot & Txn ID</th>
                    <th class="py-3 px-4 font-semibold">Date Uploaded</th>
                    <th class="py-3 px-4 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php if(empty($pending_screenshots)): ?>
                    <tr><td colspan="5" class="py-3 px-4 text-center text-gray-500">No pending screenshots.</td></tr>
                <?php else: ?>
                    <?php foreach($pending_screenshots as $screenshot): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <input type="checkbox" value="<?= $screenshot['id'] ?>" class="approval-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                        </td>
                        <td class="py-3 px-4 text-gray-800">
                            <div class="font-bold"><?= htmlspecialchars($screenshot['full_name']) ?> (<?= htmlspecialchars($screenshot['profile_id']) ?>)</div>
                        </td>
                        <td class="py-3 px-4">
                            <a href="../<?= htmlspecialchars($screenshot['payment_screenshot']) ?>" target="_blank" class="text-blue-600 underline text-sm font-medium"><i class="fas fa-external-link-alt"></i> View Upload</a>
                            <?php if(!empty($screenshot['payment_transaction_id'])): ?>
                                <div class="text-xs text-gray-600 mt-1">Txn ID: <strong><?= htmlspecialchars($screenshot['payment_transaction_id']) ?></strong></div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-gray-600">
                            <?= date('M d, Y h:i A', strtotime($screenshot['created_at'])) ?>
                        </td>
                        <td class="py-3 px-4">
                            <form method="POST" action="payment.php" class="inline-block" onsubmit="confirmSubmit(event, 'Approve this payment screenshot?')">
                                <input type="hidden" name="action" value="approve_screenshot">
                                <input type="hidden" name="user_id" value="<?= $screenshot['id'] ?>">
                                <button type="submit" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded text-xs font-bold transition mr-2">Approve</button>
                            </form>
                            <form method="POST" action="payment.php" class="inline-block" onsubmit="confirmSubmit(event, 'Reject this payment screenshot?')">
                                <input type="hidden" name="action" value="reject_screenshot">
                                <input type="hidden" name="user_id" value="<?= $screenshot['id'] ?>">
                                <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded text-xs font-bold transition">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination for Approvals -->
    <?php if ($total_approvals_pages > 1): ?>
    <div class="flex justify-center mt-6 space-x-1 flex-wrap gap-y-2">
        <?php 
        $start_page = max(1, $approvals_page - 2);
        $end_page = min($total_approvals_pages, $approvals_page + 2);
        
        if ($start_page > 1): ?>
            <a href="payment.php?tab=approvals&a_page=1&h_page=<?= $history_page ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">1</a>
            <?php if ($start_page > 2): ?>
                <span class="px-2 py-1 text-gray-500">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i == $approvals_page): ?>
                <button class="px-3 py-1 border border-primary bg-primary text-white rounded text-sm font-medium"><?= $i ?></button>
            <?php else: ?>
                <a href="payment.php?tab=approvals&a_page=<?= $i ?>&h_page=<?= $history_page ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end_page < $total_approvals_pages): ?>
            <?php if ($end_page < $total_approvals_pages - 1): ?>
                <span class="px-2 py-1 text-gray-500">...</span>
            <?php endif; ?>
            <a href="payment.php?tab=approvals&a_page=<?= $total_approvals_pages ?>&h_page=<?= $history_page ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $total_approvals_pages ?></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
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
    function switchTab(tabId, event) {
        // Hide all tabs
        document.getElementById('tab-history').classList.add('hidden');
        document.getElementById('tab-approvals').classList.add('hidden');
        document.getElementById('tab-manual').classList.add('hidden');
        document.getElementById('tab-settings').classList.add('hidden');
        
        // Show selected tab
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        
        if(event) {
            // Update button styles
            const buttons = document.querySelectorAll('.bg-white.rounded-t-xl button');
            buttons.forEach(btn => {
                btn.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
                btn.classList.add('text-gray-500', 'font-medium');
            });
            
            // Style selected button
            const selectedBtn = event.currentTarget;
            selectedBtn.classList.remove('text-gray-500', 'font-medium');
            selectedBtn.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
        }
    }

    // Search functionality
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('paymentSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const term = this.value.toLowerCase();
                const rows = document.querySelectorAll('.payment-row');
                rows.forEach(row => {
                    const textContent = Array.from(row.querySelectorAll('.searchable'))
                                             .map(el => el.textContent.toLowerCase())
                                             .join(' ');
                    if (textContent.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
    
    <?php if ($success_msg || $error_msg): ?>
    document.addEventListener('DOMContentLoaded', () => {
        // We will just let the activeTab logic below handle the tab switching
    });
    <?php endif; ?>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize tab from URL
        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('tab') || '<?= $active_tab ?>';
        // Mock a click on the correct button to handle styling
        const buttons = document.querySelectorAll('.bg-white.rounded-t-xl button');
        buttons.forEach(btn => {
            if (btn.textContent.trim().toLowerCase().includes(activeTab.replace('approvals', 'screenshot'))) {
                // Not ideal, let's just trigger the switchTab directly
            }
        });
        
        switchTab(activeTab, null);
        
        // Also style the active button since event is null
        buttons.forEach(btn => {
            btn.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
            btn.classList.add('text-gray-500', 'font-medium');
            
            const onclick = btn.getAttribute('onclick');
            if (onclick && onclick.includes("'" + activeTab + "'")) {
                btn.classList.remove('text-gray-500', 'font-medium');
                btn.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
            }
        });
        
        // Select All for Approvals
        const selectAll = document.getElementById('selectAllApprovals');
        if(selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.approval-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }
    });

    // Bulk action submission
    function submitBulkAction() {
        const action = document.getElementById('bulkActionSelect').value;
        if (!action) {
            Swal.fire('Error', 'Please select a bulk action.', 'error');
            return;
        }
        
        const allCheckboxes = document.querySelectorAll('.approval-checkbox');
        let checkedValues = [];
        allCheckboxes.forEach(cb => {
            if (cb.checked) {
                checkedValues.push(cb.value);
            }
        });
        
        if (checkedValues.length === 0) {
            Swal.fire('Error', 'Please select at least one screenshot.', 'error');
            return;
        }
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to perform this bulk action on ' + checkedValues.length + ' item(s).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'payment.php?tab=approvals';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = action;
                form.appendChild(actionInput);
                
                checkedValues.forEach(val => {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'user_ids[]';
                    idInput.value = val;
                    form.appendChild(idInput);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmSubmit(event, message) {
        event.preventDefault();
        const form = event.target;
        
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
