<!-- success-stories.php -->
<?php include 'includes/header.php'; ?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold text-center text-dark mb-12" data-aos="fade-up">Success Stories</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php 
            $stories = [
                ['Rahul & Priya', 'Mumbai', 'We found each other through Jain Digambar Matrimony. Our families were happy with the match!', 'https://images.unsplash.com/photo-1516589091380-5d8e87f6999f?w=400'],
                ['Amit & Neha', 'Delhi', 'The platform helped us find our soulmate within the community. Highly recommended!', 'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=400'],
                ['Sanjay & Meera', 'Jaipur', 'Excellent service with genuine profiles. Thank you for making our dream come true.', 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=400']
            ];
            foreach($stories as $story): ?>
            <div class="bg-light rounded-lg overflow-hidden shadow-lg" data-aos="fade-up"><img src="<?php echo $story[3]; ?>" alt="Couple" class="w-full h-64 object-cover"><div class="p-6"><h3 class="text-xl font-bold"><?php echo $story[0]; ?></h3><p class="text-primary text-sm mb-2"><?php echo $story[1]; ?></p><p class="text-gray-600">"<?php echo $story[2]; ?>"</p></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>