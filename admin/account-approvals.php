<?php
require_once '../includes/db.php';
$current_page = 'account-approvals.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $action = $_POST['action'];
    $userId = (int)$_POST['user_id'];
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'account_approved' WHERE id = ?");
        $stmt->execute([$userId]);

        // Send approval email
        $uStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $uStmt->execute([$userId]);
        $u = $uStmt->fetch();
        if ($u && !empty($u['email'])) {
            require_once '../includes/Mailer.php';
            $mailer = new Mailer();
            $html = "
            <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden'>
              <div style='background:#7c3aed;padding:20px;text-align:center'>
                <h2 style='color:#fff;margin:0'>Digambar Jain Matrimony</h2>
              </div>
              <div style='padding:30px'>
                <h3 style='color:#16a34a;margin:0 0 12px'>&#127881; Congratulations, " . htmlspecialchars($u['full_name']) . "!</h3>
                <p style='font-size:15px;color:#374151;'>Your account has been <strong>successfully approved</strong> by the admin.</p>
                <p style='font-size:14px;color:#6b7280;'>You can now log in and complete your matrimonial profile.</p>
                <div style='text-align:center;margin:24px 0'>
                  <a href='http://" . $_SERVER['HTTP_HOST'] . "/digambar-samaj/login.php' style='background:#7c3aed;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>Login Now</a>
                </div>
                <p style='font-size:12px;color:#9ca3af;text-align:center;margin-top:20px;'>Digambar Jain Matrimony &mdash; Trusted Community Platform</p>
              </div>
            </div>";
            $mailer->send($u['email'], 'Account Approved - Digambar Jain Matrimony', $html);
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    header("Location: account-approvals.php?page=" . (isset($_GET['page']) ? $_GET['page'] : 1));
    exit;
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereConditions = ["status = 'account_pending'"];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

$countSql = "SELECT COUNT(*) FROM users $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total_records = $countStmt->fetchColumn();
$total_pages = max(1, ceil($total_records / $limit));

$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

$start_result = $offset + 1;
$end_result = min($offset + $limit, $total_records);
if ($total_records == 0) {
    $start_result = 0;
    $end_result = 0;
}

if (!function_exists('buildQueryString')) {
    function buildQueryString($page_num) {
        $query = $_GET;
        $query['page'] = $page_num;
        return '?' . http_build_query($query);
    }
}
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Account Approvals (Stage 1)</h3>
        <p class="text-gray-500 text-sm">Review and approve new basic account requests before they fill out full profiles.</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="account-approvals.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name, Email or Mobile" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
        </div>
        <div>
            <button type="submit" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200 transition">
                Search
            </button>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                    <th class="py-4 px-6 font-semibold w-16">
                        <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-4 px-6 font-semibold">User Info</th>
                    <th class="py-4 px-6 font-semibold">Contact Info</th>
                    <th class="py-4 px-6 font-semibold">Request Date</th>
                    <th class="py-4 px-6 font-semibold">Status</th>
                    <th class="py-4 px-6 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php foreach ($members as $member): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-4 px-6">
                        <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($member['full_name']) ?>" class="w-10 h-10 rounded-full object-cover mr-3 border border-gray-200" alt="Profile Photo">
                            <div>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($member['full_name']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <p class="text-gray-800"><i class="fas fa-envelope text-gray-400 w-4"></i> <?= htmlspecialchars($member['email'] ?? 'N/A') ?></p>
                        <p class="text-gray-500 text-xs mt-1"><i class="fas fa-phone text-gray-400 w-4"></i> <?= htmlspecialchars($member['mobile'] ?? 'N/A') ?></p>
                    </td>
                    <td class="py-4 px-6 text-gray-600">
                        <?= date('M d, Y', strtotime($member['created_at'])) ?><br>
                        <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($member['created_at'])) ?></span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Account Pending</span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <form method="POST" class="inline-block m-0 p-0">
                            <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                            <button type="submit" name="action" value="approve" class="text-green-600 hover:text-green-900 mx-1 p-1 tooltip" title="Approve Account" onclick="event.preventDefault(); Swal.fire({title: 'Approve Account?', text: 'They will now be able to log in and complete their profile.', icon: 'success', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, approve it!'}).then((result) => { if (result.isConfirmed) { const input = document.createElement('input'); input.type = 'hidden'; input.name = this.name; input.value = this.value; this.form.appendChild(input); this.form.submit(); } });"><i class="fas fa-check-circle"></i></button>
                            <button type="submit" name="action" value="reject" class="text-red-500 hover:text-red-700 mx-1 p-1 tooltip" title="Deny & Delete" onclick="event.preventDefault(); Swal.fire({title: 'Deny & Delete?', text: 'You are about to completely delete this account request.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!'}).then((result) => { if (result.isConfirmed) { const input = document.createElement('input'); input.type = 'hidden'; input.name = this.name; input.value = this.value; this.form.appendChild(input); this.form.submit(); } });"><i class="fas fa-times-circle"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($members)): ?>
                <tr>
                    <td colspan="6" class="py-4 px-6 text-center text-gray-500">No account requests pending.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between">
        <div class="text-sm text-gray-500 mb-4 sm:mb-0">
            Showing <span class="font-medium text-gray-800"><?= $start_result ?></span> to <span class="font-medium text-gray-800"><?= $end_result ?></span> of <span class="font-medium text-gray-800"><?= $total_records ?></span> results
        </div>
        <div class="flex space-x-1">
            <?php if ($page > 1): ?>
                <a href="<?= buildQueryString($page - 1) ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">Previous</a>
            <?php else: ?>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <?php endif; ?>

            <?php 
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, max(1, $page + 2));
            
            if ($start_page > 1) {
                echo '<a href="' . buildQueryString(1) . '" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">1</a>';
                if ($start_page > 2) {
                    echo '<span class="px-3 py-1 text-gray-500 flex items-center justify-center">...</span>';
                }
            }
            
            for ($i = $start_page; $i <= $end_page; $i++): 
                if ($i == $page): ?>
                    <button class="px-3 py-1 border border-primary bg-primary text-white rounded text-sm font-medium"><?= $i ?></button>
                <?php else: ?>
                    <a href="<?= buildQueryString($i) ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50"><?= $i ?></a>
                <?php endif; 
            endfor; 
            
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<span class="px-3 py-1 text-gray-500 flex items-center justify-center">...</span>';
                }
                echo '<a href="' . buildQueryString($total_pages) . '" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">' . $total_pages . '</a>';
            }
            ?>

            <?php if ($page < $total_pages): ?>
                <a href="<?= buildQueryString($page + 1) ?>" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">Next</a>
            <?php else: ?>
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Next</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
