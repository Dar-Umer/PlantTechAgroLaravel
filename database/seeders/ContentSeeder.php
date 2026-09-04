<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\ImpactStat;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedServices();
        $this->seedPostCategories();
        $this->seedHomeSections();
        $this->seedImpactStats();
        $this->seedFaqEntries();
        $this->seedTestimonials();
        $this->seedSamplePosts();
    }

    private function seedServices(): void
    {
        $services = [
            ['name' => 'Book an Orchard', 'slug' => 'book-orchard', 'category' => 'orchard-development', 'description' => 'Generate ESTIMATE based on your desired material, spacing and land area. Download Estimate and Book Your Orchard online.', 'image' => '/images/orchard.jpg', 'book_url' => '/services/orchard-development/book-orchard', 'sort_order' => 1],
            ['name' => 'Book Plants', 'slug' => 'book-plants', 'category' => 'orchard-development', 'description' => 'Choose from a variety of plants to suit your orchard\'s needs. Our selection includes high-quality, disease-resistant varieties for optimal growth and yield.', 'image' => '/images/drip.jpg', 'book_url' => '/services/orchard-development/book-plants', 'sort_order' => 2],
            ['name' => 'Book a Soil Test', 'slug' => 'book-soil-test', 'category' => 'soil-health-management', 'description' => 'Before investing your earnings into soil, we let you know the properties and composition your soil carries. Book soil sample collection and testing services here.', 'image' => '/images/soil_test.jpg', 'book_url' => '/services/soil-health-management/book-soil-test', 'sort_order' => 3],
            ['name' => 'Book an Expert Call', 'slug' => 'book-expert-call', 'category' => 'orchard-consultation', 'description' => 'Get personalized advice from our seasoned experts on orchard management, soil health, and sustainable practices. Book your consultation today.', 'image' => '/images/expert.jpg', 'book_url' => '/services/orchard-consultation/book-expert-call', 'sort_order' => 4],
            ['name' => 'Book Ground Water Detection', 'slug' => 'book-ground-water-detection', 'category' => 'ground-water-detection', 'description' => 'Planning a borewell? Our ground water detection service helps identify water availability and optimal depth in advance.', 'image' => '/images/borewell.jpg', 'book_url' => '/services/ground-water-detection/book-ground-water-detection', 'sort_order' => 5],
            ['name' => 'Book Hail Protection & Safety Net Installation', 'slug' => 'book-hail-protection', 'category' => 'hail-protection', 'description' => 'Protect your crops from hail damage. Our safety net installation service provides reliable coverage to shield your orchard and farmland from unpredictable weather.', 'image' => '/images/hailnet.jpg', 'book_url' => '/services/hail-protection/book-hail-protection', 'sort_order' => 6],
        ];

        foreach ($services as $data) {
            Service::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }

    private function seedPostCategories(): void
    {
        $categories = [
            ['name' => 'Orchard Management', 'slug' => 'orchard-management', 'description' => 'Expert tips on orchard planning and care', 'sort_order' => 1],
            ['name' => 'Soil Health', 'slug' => 'soil-health', 'description' => 'Soil testing and improvement guides', 'sort_order' => 2],
            ['name' => 'Modern Farming', 'slug' => 'modern-farming', 'description' => 'Advanced agricultural techniques', 'sort_order' => 3],
            ['name' => 'Apple Cultivation', 'slug' => 'apple-cultivation', 'description' => 'Apple growing guides for Kashmir', 'sort_order' => 4],
        ];

        foreach ($categories as $data) {
            PostCategory::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }

    private function seedHomeSections(): void
    {
        $sections = [
            [
                'section_key' => 'hero',
                'title' => 'Innovation Built to Elevate Agriculture',
                'subtitle' => 'Driving agricultural excellence with advanced technologies, sustainable farming models, and data-driven solutions for higher productivity and farmer prosperity.',
                'sort_order' => 1,
            ],
            [
                'section_key' => 'about_preview',
                'title' => 'Leading Innovation in Kashmir\'s Agriculture',
                'subtitle' => 'Equipped with decades of agricultural expertise, deep local knowledge, and a commitment to sustainable farming.',
                'content' => ['description' => 'Plant Tech Agro is Kashmir\'s most trusted partner for orchard management, seed quality, and field productivity. We assess your land\'s soil health and seasonal conditions, provide expert-guided orchard planning and setup, deliver premium seeds and produce in a timely manner, and support your goals, budget, and long-term harvest targets.'],
                'sort_order' => 2,
            ],
            [
                'section_key' => 'home_gallery',
                'title' => 'From Kashmir\'s Fields',
                'sort_order' => 3,
            ],
            [
                'section_key' => 'cta',
                'title' => 'Ready to Transform Your Orchard?',
                'subtitle' => 'From high-density orchard planning to drip irrigation — let our experts guide every step.',
                'sort_order' => 4,
            ],
        ];

        foreach ($sections as $data) {
            HomeSection::firstOrCreate(['section_key' => $data['section_key']], $data);
        }
    }

    private function seedImpactStats(): void
    {
        $stats = [
            ['label' => 'Years of Experience', 'value' => '15', 'suffix' => '+', 'icon' => 'calendar', 'sort_order' => 1],
            ['label' => 'Farms Served', 'value' => '500', 'suffix' => '+', 'icon' => 'farm', 'sort_order' => 2],
            ['label' => 'Orchards Planted', 'value' => '1000', 'suffix' => '+', 'icon' => 'tree', 'sort_order' => 3],
            ['label' => 'Happy Farmers', 'value' => '2000', 'suffix' => '+', 'icon' => 'users', 'sort_order' => 4],
        ];

        foreach ($stats as $data) {
            ImpactStat::firstOrCreate(['label' => $data['label']], $data);
        }
    }

    private function seedFaqEntries(): void
    {
        $faqs = [
            ['question' => 'What services does your company offer?', 'answer' => 'We offer a range of services including High Density Orchard Installation, Soil Testing and Soil Health Management, Trellis Infrastructure Services, Micro Irrigation Services, Sale of Plants, and Technical Services for Orchard Management and Precision Farming across Jammu & Kashmir.', 'category' => 'general', 'sort_order' => 1],
            ['question' => 'How do you ensure quality?', 'answer' => 'We follow rigorous quality standards at every step — from sourcing certified rootstock and premium plants to conducting thorough soil tests and providing scientifically backed orchard plans. Our team of agronomists and field experts ensures each orchard is set up for long-term success.', 'category' => 'general', 'sort_order' => 2],
            ['question' => 'Are imported plants better than homegrown plants?', 'answer' => 'Imported plants often carry superior genetics for disease resistance, fruit quality, and yield potential. When grafted on compatible rootstock and managed well, they outperform traditional varieties in profitability and shelf life.', 'category' => 'plants', 'sort_order' => 3],
            ['question' => 'Is drip irrigation important for high-density plants?', 'answer' => 'Absolutely. Drip irrigation delivers precise water and nutrient quantities directly to the root zone, which is critical for the dense spacing of modern orchards. It minimizes water waste and maximizes growth uniformity.', 'category' => 'irrigation', 'sort_order' => 4],
            ['question' => 'Does MM111 rootstock eliminate the need for irrigation?', 'answer' => 'No rootstock completely eliminates the need for irrigation, especially in the establishment years. MM111 is drought-tolerant but still benefits greatly from controlled irrigation for consistent fruit production.', 'category' => 'plants', 'sort_order' => 5],
            ['question' => 'Can apple trees produce fruit within a year?', 'answer' => 'With high-density planting using precocious rootstocks like M9 or MM106, trees can start producing fruit as early as the second year. However, full commercial yield is typically achieved by years 4–5.', 'category' => 'cultivation', 'sort_order' => 6],
            ['question' => 'What is the most important component of a high-density orchard?', 'answer' => 'The rootstock choice is the single most critical factor. It determines tree size, disease resistance, soil adaptability, and overall productivity of the orchard for decades to come.', 'category' => 'orchard', 'sort_order' => 7],
            ['question' => 'What are rootstock plants and how are they different from seedling plants?', 'answer' => 'Rootstock plants are grafted combinations where a vigorous, disease-resistant root system supports a scion of a fruiting variety. Seedling plants are grown from seeds and are genetically variable, making them unpredictable in fruit quality and growth habit.', 'category' => 'plants', 'sort_order' => 8],
        ];

        foreach ($faqs as $data) {
            Faq::firstOrCreate(['question' => $data['question']], $data);
        }
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            ['name' => 'Farooq Ahmad', 'role' => 'Apple Grower, Srinagar', 'content' => 'Plant Tech Agro transformed our old orchard into a high-density powerhouse. The yield increased threefold in just four years. Their team is knowledgeable and truly cares about results.', 'rating' => 5, 'sort_order' => 1],
            ['name' => 'Aisha Bano', 'role' => 'Farmer, Pulwama', 'content' => 'Their soil testing service was eye-opening. We discovered nutrient deficiencies that were holding back our crop. After their recommendations, our apple quality improved dramatically.', 'rating' => 5, 'sort_order' => 2],
            ['name' => 'Rashid Hussain', 'role' => 'Orchard Owner, Anantnag', 'content' => 'The drip irrigation setup they installed saved us thousands in water costs while improving tree health. Professional team with excellent after-sales support.', 'rating' => 4, 'sort_order' => 3],
        ];

        foreach ($testimonials as $data) {
            Testimonial::firstOrCreate(['name' => $data['name']], $data);
        }
    }

    private function seedSamplePosts(): void
    {
        $category = PostCategory::where('slug', 'orchard-management')->first();
        if ($category) {
            Post::firstOrCreate(['slug' => 'getting-started-with-high-density-apple-orchards'], [
                'category_id' => $category->id,
                'title' => 'Getting Started with High Density Apple Orchards',
                'excerpt' => 'High-density apple farming is revolutionizing orchards in Kashmir. Learn the fundamentals of planning, rootstock selection, and spacing for maximum yield.',
                'content' => '<p>High-density orchards represent the future of apple cultivation in Kashmir. With proper planning, rootstock selection, and management practices, farmers can achieve significantly higher yields from smaller land areas.</p><h2>Why High Density?</h2><p>Traditional orchards in Kashmir use spacious planting distances that limit yield per acre. High-density systems pack more trees per hectare, using dwarfing rootstocks and trellis support to manage tree size while maximizing fruit production.</p><h2>Choosing the Right Rootstock</h2><p>The rootstock is the foundation of your orchard. Popular choices include M9 T337 for maximum dwarfing and early bearing, and MM106 for semi-vigorous growth with good disease resistance.</p>',
                'featured_image' => '/images/orchard-development1.jpg',
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }
}
