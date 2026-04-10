<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once __DIR__ . '/src/Service/RecommendationService.php';
use App\Service\RecommendationService;

// Redirect admin to admin dashboard
if(isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get featured cars by category to ensure all categories are represented
$categories = $db->query("SELECT DISTINCT category FROM cars WHERE status = 'available' AND category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
$featured_cars = [];
$limitPerCat = ceil(6 / max(count($categories), 1));
foreach ($categories as $cat) {
    $stmt = $db->prepare("SELECT * FROM cars WHERE status = 'available' AND category = ? LIMIT $limitPerCat");
    $stmt->execute([$cat]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $car) {
        $featured_cars[] = $car;
    }
}
$featured_cars = array_slice($featured_cars, 0, 6);

// Get features for each car
$featureQuery = "SELECT features FROM cars WHERE id = ?";
$featureStmt = $db->prepare($featureQuery);
foreach ($featured_cars as &$car) {
    $featureStmt->execute([$car['id']]);
    $features = $featureStmt->fetchColumn();
    $car['features'] = $features ? explode(',', $features) : [];
    
    // Get review stats
    $reviewQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                    FROM reviews 
                    WHERE car_id = ?";
    $reviewStmt = $db->prepare($reviewQuery);
    $reviewStmt->execute([$car['id']]);
    $reviewStats = $reviewStmt->fetch(PDO::FETCH_ASSOC);
    $car['avg_rating'] = $reviewStats['avg_rating'] ?? 0;
    $car['review_count'] = $reviewStats['review_count'] ?? 0;
}
unset($car); // Break the reference


// Get car categories
$query = "SELECT DISTINCT category FROM cars WHERE category IS NOT NULL";
$categories = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);

// Banner images array for hero section
$banner_images = [
    'assets/images/banner1.jpg',
    'assets/images/banner2.jpg',
    'assets/images/banner3.jpg'
];
$current_banner = $banner_images[0]; // Default first image

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarEase - Premium Car Rental Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a1a1a;
            --secondary: #333333;
            --accent: #0066cc;
            --accent-hover: #0052a3;
            --accent-light: #e6f0ff;
            --text: #1a1a1a;
            --text-light: #666666;
            --background: #ffffff;
            --background-alt: #f8f9fa;
            --border: #e5e5e5;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
            --radius: 12px;
            --font-primary: 'Inter', sans-serif;
            --font-secondary: 'Poppins', sans-serif;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Hero Banner Styles */
        .hero-banner {
            position: relative;
            height: 90vh;
            min-height: 700px;
            overflow: hidden;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
        }

        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
        }

        .banner-slide.active {
            opacity: 1;
        }

        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: #fff;
            padding: 0 1rem;
            max-width: 800px;
        }

        .hero-title {
            font-size: 4.5rem;
            color: #fff;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-family: var(--font-secondary);
            letter-spacing: -0.02em;
            line-height: 1.1;
            text-shadow: 0 4px 24px rgba(0,0,0,0.7);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 400;
            font-family: var(--font-primary);
        }

        .hero-btn {
            display: inline-block;
            padding: 1.2rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-transform: none;
            letter-spacing: 0.5px;
            background: var(--accent);
            color: white;
            border: none;
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.3);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .hero-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 102, 204, 0.4);
            color: white;
        }

        .hero-btn:active {
            transform: translateY(-1px);
        }

        /* Section Styles */
        .section {
            padding: 6rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            text-align: center;
            color: var(--text);
            font-family: var(--font-secondary);
            position: relative;
        }

        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--accent);
            margin: 1rem auto 0;
            border-radius: 2px;
        }

        /* Card Styles */
        .car-card {
            background: var(--background);
            border-radius: var(--radius);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .car-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .car-image-container {
            position: relative;
            overflow: hidden;
            height: 240px;
        }

        .car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .car-card:hover .car-image {
            transform: scale(1.05);
        }

        .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(5px);
        }

        .car-details {
            padding: 1.75rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .car-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-family: var(--font-primary);
        }

        .car-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            margin: 1rem 0;
        }

        .car-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-tag {
            background: var(--accent-light);
            color: var(--accent);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .rating {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .stars {
            color: #ffc107;
            margin-right: 0.5rem;
        }

        .rating-count {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.85rem 1.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: none;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 102, 204, 0.3);
        }

        /* Why Choose Us Section */
        .why-us-section {
            background-color: var(--background-alt);
            padding: 6rem 0;
        }

        .why-us-section .section-title {
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: var(--shadow);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
            display: inline-block;
            padding: 1rem;
            border-radius: 50%;
            background: var(--accent-light);
        }

        .feature-card h4 {
            font-family: var(--font-secondary);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text);
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Map Section */
        .map-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--background) 0%, var(--background-alt) 100%);
        }

        .map-section h2 {
            font-family: var(--font-secondary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .map-section .lead {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .map-section p {
            margin-bottom: 1rem;
            color: var(--text);
            display: flex;
            align-items: center;
        }

        .map-section i {
            color: var(--accent);
            width: 24px;
            margin-right: 10px;
        }

        .map-container {
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-hover);
        }

        /* FAQ Styles */
        .faq-section {
            background-color: var(--background-alt);
            padding: 6rem 0;
        }

        .faq-item {
            margin-bottom: 1.5rem;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            background: white;
        }

        .faq-question {
            padding: 1.5rem;
            background: white;
            cursor: pointer;
            font-weight: 600;
            color: var(--text);
            transition: all 0.3s ease;
            font-family: var(--font-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question:hover {
            background: var(--accent-light);
        }

        .faq-question:after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            transition: transform 0.3s ease;
        }

        .faq-question.active:after {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 1.5rem;
            background: white;
            color: var(--text-light);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }

        .faq-answer.open {
            padding: 0 1.5rem 1.5rem;
            max-height: 500px;
        }

        /* Footer Styles */
        footer {
            background: var(--primary);
            color: white;
            padding: 5rem 0 2rem;
        }

        footer h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-family: var(--font-primary);
            position: relative;
            display: inline-block;
        }

        footer h5:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 40px;
            height: 3px;
            background: var(--accent);
        }

        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            margin-bottom: 0.8rem;
        }

        footer a:hover {
            color: white;
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--accent);
            transform: translateY(-3px);
        }

        /* Recommended Cars Section */
        .recommended-section {
            margin-bottom: 4rem;
            position: relative;
        }
        
        .recommended-section:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--accent-light) 0%, rgba(230, 240, 255, 0.5) 100%);
            z-index: -1;
        }
        
        .recommended-section .section-title {
            color: var(--text);
        }
        
        .recommended-section .car-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            padding: 0;
        }
        
        .recommended-section .car-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }
        
        .recommended-section .car-image-container {
            height: 220px;
        }
        
        .recommended-section .car-details {
            padding: 1.5rem;
        }
        
        .recommended-section .car-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-family: var(--font-secondary);
        }
        
        .recommended-section .car-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent);
            margin: 1rem 0;
        }
        
        .recommended-section .btn {
            background: var(--accent);
            color: #fff;
            border-radius: 50px;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
            transition: background 0.3s, transform 0.3s;
            margin-top: auto;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        }
        
        .recommended-section .btn:hover {
            background: var(--accent-hover);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 102, 204, 0.3);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .hero-banner {
                height: 70vh;
                min-height: 500px;
            }

            .hero-title {
                font-size: 2.75rem;
            }

            .hero-subtitle {
                font-size: 1.25rem;
                margin-bottom: 2rem;
            }

            .section {
                padding: 4rem 0;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 2.5rem;
            }

            .why-us-section {
                padding: 4rem 0;
            }

            .map-section {
                padding: 4rem 0;
            }
            
            .feature-card {
                margin-bottom: 1.5rem;
            }
        }

        /* Animation for elements */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Banner Section -->
    <section class="hero-banner">
        <?php foreach ($banner_images as $index => $image): ?>
        <div class="banner-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
             style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('<?php echo htmlspecialchars($image); ?>')">
        </div>
        <?php endforeach; ?>
        
        <div class="hero-content">
            <h1 class="hero-title">Find Your Perfect Ride</h1>
            <p class="hero-subtitle">Discover our premium collection of vehicles for your next adventure. Quality, comfort, and reliability guaranteed.</p>
            
            <div class="hero-buttons">
                <a href="cars.php" class="hero-btn">Explore Our Fleet</a>
            </div>
        </div>
    </section>

    <?php
    // Show recommendations only if user is logged in and has at least one booking
    if (isset($_SESSION['user_id'])) {
        $stmtB = $db->prepare("SELECT car_id FROM bookings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmtB->execute([$_SESSION['user_id']]);
        $lastBooking = $stmtB->fetch(PDO::FETCH_ASSOC);
        if ($lastBooking) {
            $recService = new RecommendationService($db);
            $recCars = $recService->getSimilarCars($lastBooking['car_id'], 4);
            if (!empty($recCars)) {
    ?>
    <section class="section recommended-section">
        <div class="container">
            <h2 class="section-title">Recommended For You</h2>
            <div class="row">
                <?php foreach ($recCars as $r): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="car-card">
                        <div class="car-image-container">
                            <img src="assets/images/<?php echo htmlspecialchars($r['image'] ?: 'car-placeholder.png'); ?>" class="car-image" alt="<?php echo htmlspecialchars($r['brand'].' '.$r['model']); ?>">
                            <span class="category-badge"><?php echo htmlspecialchars($r['category']); ?></span>
                        </div>
                        <div class="car-details">
                            <h3 class="car-title"><?php echo htmlspecialchars($r['brand'].' '.$r['model']); ?></h3>
                            <div class="car-price">Rs. <?php echo number_format($r['price_per_day'],2); ?>/day</div>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="car-details.php?id=<?php echo $r['id']; ?>" class="btn">View Details</a>
                            <?php else: ?>
                                <a href="book-car.php?id=<?php echo $r['id']; ?>" class="btn">Book Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
            }
        }
    }
    ?>

    <!-- Featured Cars Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Cars</h2>
            <div class="row">
                <?php foreach($featured_cars as $car): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="car-card">
                            <div class="car-image-container">
                                <img src="assets/images/<?php echo !empty($car['image']) ? htmlspecialchars($car['image']) : 'car-placeholder.png'; ?>" class="car-image" alt="<?php echo htmlspecialchars($car['brand'].' '.$car['model']); ?>">
                                <span class="category-badge"><?php echo htmlspecialchars($car['category']); ?></span>
                            </div>
                            <div class="car-details">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['brand'].' '.$car['model']); ?></h3>
                                <div class="rating">
                                    <div class="stars">
                                        <?php
                                        $avg_rating = $car['avg_rating'];
                                        $full_stars = floor($avg_rating);
                                        $has_half_star = ($avg_rating - $full_stars) >= 0.5;
                                        
                                        for ($i = 0; $i < $full_stars; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        
                                        if ($has_half_star) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                            $full_stars++;
                                        }
                                        
                                        for ($i = $full_stars; $i < 5; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-count">(<?php echo $car['review_count']; ?>)</span>
                                </div>
                                <?php if (!empty($car['features'])): ?>
                                <div class="car-features">
                                    <?php 
                                    $display_features = array_slice($car['features'], 0, 3);
                                    foreach ($display_features as $feature): 
                                    ?>
                                        <span class="feature-tag"><?php echo htmlspecialchars(trim($feature)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <div class="car-price">Rs. <?php echo number_format($car['price_per_day'],2); ?>/day</div>
                                <div class="d-grid">
                                    <?php if (!isset($_SESSION['user_id'])): ?>
                                        <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-primary"><i class="fas fa-eye me-2"></i> View Details</a>
                                    <?php else: ?>
                                        <a href="book-car.php?id=<?php echo $car['id']; ?>" class="btn btn-primary"><i class="fas fa-calendar-check me-2"></i> Book Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="cars.php" class="btn btn-primary">View All Cars</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-us-section">
        <div class="container">
            <h2 class="section-title">Why Choose CarEase</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h4>Safe & Secure</h4>
                        <p>All our vehicles undergo regular maintenance and comprehensive safety checks. Fully insured for your peace of mind.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-hand-holding-usd feature-icon"></i>
                        <h4>Best Prices</h4>
                        <p>Competitive rates with no hidden charges. Enjoy special discounts for long-term rentals and loyal customers.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-headset feature-icon"></i>
                        <h4>24/7 Support</h4>
                        <p>Our dedicated support team is available round the clock to assist you with any queries or emergencies.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2>Our Location in Kathmandu</h2>
                    <p class="lead">Visit our office in the heart of Kathmandu for the best car rental experience with personalized service.</p>
                    <div class="mt-4">
                        <p><i class="fas fa-map-marker-alt"></i> Thamel, Kathmandu, Nepal</p>
                        <p><i class="fas fa-phone"></i> +977-1-1234567</p>
                        <p><i class="fas fa-envelope"></i> info@carease.com</p>
                        <p><i class="fas fa-clock"></i> Open 7 days a week, 8:00 AM - 8:00 PM</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="map-container ratio ratio-16x9">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3532.462444447567!2d85.3142483150621!3d27.709031982793!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb1980%3A0x93a1a1c1!2sThamel%2C%20Kathmandu%2044600!5e0!3m2!1sen!2snp!4v1620000000000!5m2!1sen!2snp" 
                                style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <div class="faq-question">
                            What documents do I need to rent a car?
                        </div>
                        <div class="faq-answer">
                            You'll need a valid driver's license and a government-issued ID (citizenship card or passport). For international visitors, an international driving permit is required along with your passport.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            What is included in the rental price?
                        </div>
                        <div class="faq-answer">
                            The rental price includes unlimited mileage, comprehensive insurance coverage, 24/7 roadside assistance, and vehicle maintenance. Additional services like GPS navigation, child seats, or additional drivers can be added for a small fee.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            Can I modify or cancel my booking?
                        </div>
                        <div class="faq-answer">
                            Yes, you can modify or cancel your booking up to 24 hours before the rental start time free of charge. For cancellations within 24 hours, a small fee may apply. Please contact our support team for assistance with modifications.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            What is your fuel policy?
                        </div>
                        <div class="faq-answer">
                            We provide the car with a full tank of fuel and expect it to be returned with a full tank. Alternatively, you can choose our pre-paid fuel option where you pay for a full tank upfront and return the car at any fuel level.
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            Do you offer long-term rentals?
                        </div>
                        <div class="faq-answer">
                            Yes, we offer special discounted rates for rentals longer than one week. Contact our team for customized long-term rental packages that can include additional benefits and savings.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>CarEase</h5>
                    <p>Your trusted partner for premium car rentals in Kathmandu. Experience comfort, reliability, and exceptional service with every journey.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <a href="index.php">Home</a>
                    <a href="cars.php">Our Fleet</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Our Services</h5>
                    <a href="cars.php?category=Economy">Economy Cars</a>
                    <a href="cars.php?category=SUV">SUV Rentals</a>
                    <a href="cars.php?category=Luxury">Luxury Vehicles</a>
                    <a href="cars.php?category=Family">Family Cars</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Thamel, Kathmandu, Nepal</p>
                    <p><i class="fas fa-phone me-2"></i> +977-1-1234567</p>
                    <p><i class="fas fa-envelope me-2"></i> info@carease.com</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> CarEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Banner auto-scroll functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.banner-slide');
            let currentSlide = 0;

            function nextSlide() {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }

            // Change slide every 5 seconds
            setInterval(nextSlide, 5000);

            // FAQ Accordion
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    const isOpen = answer.classList.contains('open');
                    
                    // Close all answers
                    document.querySelectorAll('.faq-answer').forEach(ans => {
                        ans.classList.remove('open');
                    });
                    document.querySelectorAll('.faq-question').forEach(q => {
                        q.classList.remove('active');
                    });
                    
                    // Toggle current answer
                    if (!isOpen) {
                        answer.classList.add('open');
                        question.classList.add('active');
                    }
                });
            });

            // Fade in animation on scroll
            const fadeElements = document.querySelectorAll('.car-card, .feature-card');
            
            const fadeInOnScroll = () => {
                fadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.classList.add('visible');
                    }
                });
            };
            
            // Set initial state
            fadeElements.forEach(element => {
                element.classList.add('fade-in');
            });
            
            // Check on load
            window.addEventListener('load', fadeInOnScroll);
            // Check on scroll
            window.addEventListener('scroll', fadeInOnScroll);
        });
    </script>
</body>
</html>