<?php
require_once 'includes/db.php';

$pageTitle = 'Frequently Asked Questions';
include 'includes/header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-3xl font-bold text-dark mb-6 border-b pb-4">Frequently Asked Questions</h1>
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-bold text-primary mb-2">How do I register a profile?</h3>
                <p class="text-gray-700">Click on the "Register" button on the top menu, fill in the required details, and submit. Your profile will be reviewed by our admins before it becomes visible.</p>
            </div>
            <div>
                <h3 class="text-lg font-bold text-primary mb-2">Is registration free?</h3>
                <p class="text-gray-700">Yes, creating a basic profile is completely free.</p>
            </div>
            <div>
                <h3 class="text-lg font-bold text-primary mb-2">Why can't I see full photos?</h3>
                <p class="text-gray-700">To protect member privacy, clear photos and full contact details are only visible to registered members whose accounts have been approved by our admins.</p>
            </div>
            <div>
                <h3 class="text-lg font-bold text-primary mb-2">How do I contact another member?</h3>
                <p class="text-gray-700">Once you are logged in and approved, you can view the contact number and address of other profiles.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
