<?php
require_once 'includes/db.php';

$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'privacy_policy'");
$dynamic_privacy = $stmt->fetchColumn();

$pageTitle = 'Privacy Policy';
include 'includes/header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-3xl font-bold text-dark mb-6 border-b pb-4">Privacy Policy</h1>
        <div class="prose max-w-none text-gray-700 space-y-4">
            <?php if(!empty($dynamic_privacy)): ?>
                <?= $dynamic_privacy ?>
            <?php else: ?>
                <?php 
                    require_once 'includes/default_privacy.php';
                    echo $default_privacy; 
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
