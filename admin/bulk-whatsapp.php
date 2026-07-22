<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
$current_page = 'bulk-whatsapp.php';

// Fetch users for selection (only those with mobile numbers)
$stmt = $pdo->query("SELECT id, full_name, mobile FROM users WHERE mobile IS NOT NULL AND mobile != '' ORDER BY full_name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Bulk WhatsApp Messages</h3>
        <p class="text-gray-500 text-sm">Send WhatsApp messages to members directly via WhatsApp Web/App.</p>
    </div>
</div>

<div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
    <p class="font-bold">Note on Bulk Sending:</p>
    <p class="text-sm">Since no third-party WhatsApp API is configured, this tool uses the official WhatsApp application (<a href="https://wa.me" target="_blank" class="underline font-medium">wa.me</a> links). Draft your message below, and click the "Send" button next to each user. It will open WhatsApp with your drafted message ready to send.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Message Drafting Section -->
    <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h4 class="font-bold text-gray-800"><i class="fab fa-whatsapp text-green-500 mr-2"></i> Draft Message</h4>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label for="wa_message" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Message Body</label>
                <textarea id="wa_message" rows="12" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition resize-none" placeholder="Type your WhatsApp message here..."></textarea>
                <p class="text-xs text-gray-500 mt-2">Use *bold* for bold, _italic_ for italic.</p>
            </div>
        </div>
    </div>
    
    <!-- User List Section -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50 gap-4">
            <div class="flex items-center">
                <h4 class="font-bold text-gray-800"><i class="fas fa-users text-primary mr-2"></i> Select Recipients</h4>
                <span class="text-sm text-gray-500 font-medium ml-3">(<?= count($users) ?> users)</span>
            </div>
            <div class="relative w-full sm:w-80 flex">
                <div class="relative w-full">
                    <input type="text" id="searchInput" placeholder="Search name or mobile..." class="w-full pl-10 pr-4 py-2 border border-gray-300 border-r-0 rounded-l-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                </div>
                <button type="button" id="searchBtn" class="bg-primary text-white px-4 py-2 rounded-r-lg text-sm font-bold hover:bg-opacity-90 transition whitespace-nowrap border border-primary">
                    Search
                </button>
            </div>
        </div>
        <div class="p-0">
            <?php if (empty($users)): ?>
                <div class="p-6 text-center text-gray-500">No users found with valid mobile numbers.</div>
            <?php else: ?>
                <div class="overflow-y-auto max-h-[600px]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): 
                                // Clean phone number (remove +, spaces, dashes, etc.)
                                $cleanPhone = preg_replace('/[^0-9]/', '', $user['mobile']);
                                // Make sure it has a country code, assume 91 if it's 10 digits
                                if(strlen($cleanPhone) == 10) {
                                    $cleanPhone = '91' . $cleanPhone;
                                }
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars(trim($user['full_name'])) ?: 'Unknown Name' ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($user['mobile']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="sendWhatsApp('<?= $cleanPhone ?>', this)" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded inline-flex items-center text-xs font-bold transition shadow-sm">
                                        <i class="fab fa-whatsapp mr-1.5"></i> Send Message
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const rows = document.querySelectorAll('tbody tr');
    
    function performSearch() {
        if(!searchInput) return;
        const term = searchInput.value.toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if(text.includes(term)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if(searchInput) {
        searchInput.addEventListener('keyup', performSearch);
    }
    if(searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
});

function sendWhatsApp(phone, btnElement) {
    if (!phone) {
        alert("Invalid phone number.");
        return;
    }
    
    let message = document.getElementById('wa_message').value.trim();
    if (message === "") {
        if(!confirm("Your message body is empty. Do you still want to continue?")) {
            return;
        }
    }
    
    let encodedMessage = encodeURIComponent(message);
    let waUrl = `https://wa.me/${phone}?text=${encodedMessage}`;
    
    // Change button appearance to indicate it was clicked
    btnElement.classList.remove('bg-green-500', 'hover:bg-green-600');
    btnElement.classList.add('bg-gray-400', 'hover:bg-gray-500');
    btnElement.innerHTML = '<i class="fas fa-check mr-1.5"></i> Sent';
    
    // Open WhatsApp Web/App in a new tab
    window.open(waUrl, '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>
