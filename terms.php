<?php
require_once 'includes/db.php';

$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'terms_conditions'");
$dynamic_terms = $stmt->fetchColumn();

$pageTitle = 'Terms & Conditions';
include 'includes/header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-3xl font-bold text-dark mb-6 border-b pb-4">Terms & Conditions</h1>
        <div class="prose max-w-none text-gray-700 space-y-4">
            <?php if(!empty($dynamic_terms)): ?>
                <?= $dynamic_terms ?>
            <?php else: ?>
                <?php 
                    require_once 'includes/default_terms.php';
                    echo $default_terms; 
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
