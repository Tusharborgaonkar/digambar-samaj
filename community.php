<?php
require_once 'includes/db.php';

$pageTitle = 'Community Initiatives';
include 'includes/header.php';
?>

<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl text-center">
        <i class="fas fa-users text-6xl text-primary mb-6"></i>
        <h1 class="text-4xl font-bold text-dark mb-4">Our Community Initiatives</h1>
        <p class="text-gray-600 text-lg mb-8">This page is under development. We will soon update this space with details about our social programs, community gatherings, and upcoming initiatives for the Digambar Jain Samaj.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <i class="fas fa-hand-holding-heart text-3xl text-secondary mb-4"></i>
                <h3 class="text-xl font-bold text-dark mb-2">Charity Drives</h3>
                <p class="text-gray-600 text-sm">Supporting the underprivileged and providing essential resources.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="text-4xl text-primary font-bold mb-3">卐</div>
                <h3 class="text-xl font-bold text-dark mb-2">Spiritual Events</h3>
                <p class="text-gray-600 text-sm">Organizing regular poojas, aartis, and spiritual discourses.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <i class="fas fa-ring text-3xl text-accent mb-4"></i>
                <h3 class="text-xl font-bold text-dark mb-2">Parichay Sammelan</h3>
                <p class="text-gray-600 text-sm">Hosting matchmaking events for prospective brides and grooms.</p>
            </div>
        </div>

        <div class="mt-12">
            <a href="index.php" class="bg-primary text-white px-6 py-3 rounded-md font-semibold hover:bg-opacity-90 transition">Return to Home</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
