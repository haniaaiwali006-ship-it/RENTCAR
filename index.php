<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'rsoa_rsoa278_6');
define('DB_PASS', '654321#');
define('DB_NAME', 'rsoa_rsoa278_6');
 
// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
 
    return $conn;
}
 
// Get featured cars
$conn = getConnection();
$featured_query = "SELECT * FROM cars WHERE available = 1 ORDER BY rating DESC LIMIT 6";
$featured_result = $conn->query($featured_query);
 
// Get locations
$locations_query = "SELECT DISTINCT location FROM cars ORDER BY location";
$locations_result = $conn->query($locations_query);
$locations = [];
while($row = $locations_result->fetch_assoc()) {
    $locations[] = $row['location'];
}
 
// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $pickup_location = $_POST['pickup_location'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? date('Y-m-d');
    $return_date = $_POST['return_date'] ?? date('Y-m-d', strtotime('+3 days'));
 
    header("Location: cars.php?location=" . urlencode($pickup_location) . 
           "&pickup_date=" . urlencode($pickup_date) . 
           "&return_date=" . urlencode($return_date));
    exit();
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEasy Rentals - Your Perfect Car Awaits</title>
 
    <style>
        /* CSS Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --gray-dark: #374151;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 8px;
            --radius-lg: 12px;
            --transition: all 0.3s ease;
        }
 
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }
 
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
 
        /* Navigation */
        .navbar {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
 
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
        }
 
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
 
        .logo span {
            color: var(--dark);
        }
 
        .nav-menu {
            display: flex;
            gap: 2rem;
        }
 
        .nav-link {
            color: var(--gray-dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
 
        .nav-link:hover {
            color: var(--primary);
        }
 
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 4rem 0;
            text-align: center;
        }
 
        .hero-title {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
 
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
 
        /* Search Form */
        .search-form {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            max-width: 800px;
            margin: 0 auto;
        }
 
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
 
        .form-group {
            margin-bottom: 0;
        }
 
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }
 
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 1rem;
        }
 
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }
 
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
 
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
 
        .btn-search {
            height: 56px;
            font-size: 1.1rem;
            width: 100%;
        }
 
        /* Features Section */
        .features {
            padding: 4rem 0;
            background-color: var(--white);
        }
 
        .section-title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark);
        }
 
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
 
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: var(--radius);
            transition: var(--transition);
        }
 
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
 
        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
        }
 
        /* Cars Grid */
        .featured-cars {
            padding: 4rem 0;
        }
 
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
 
        .car-card {
            background-color: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
 
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
 
        .car-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
 
        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
 
        .car-card:hover .car-image img {
            transform: scale(1.05);
        }
 
        .car-type {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background-color: var(--primary);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
 
        .car-info {
            padding: 1.5rem;
        }
 
        .car-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }
 
        .car-specs {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            color: var(--gray);
            font-size: 0.875rem;
        }
 
        .car-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }
 
        .car-price {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
        }
 
        .car-price .price {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }
 
        /* Footer */
        .footer {
            background-color: var(--dark);
            color: var(--white);
            padding: 3rem 0;
            margin-top: 4rem;
        }
 
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
 
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid var(--gray-dark);
            color: var(--gray);
        }
 
        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
 
            .nav-menu {
                display: none;
            }
 
            .hero-title {
                font-size: 2rem;
            }
 
            .cars-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i>
                <span>DriveEasy</span>Rentals
            </a>
 
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="cars.php" class="nav-link">Cars</a>
                <a href="#" class="nav-link">About</a>
                <a href="#" class="nav-link">Contact</a>
            </div>
 
            <a href="booking.php" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> My Bookings
            </a>
        </div>
    </nav>
 
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">Find Your Perfect Ride</h1>
            <p class="hero-subtitle">Choose from our wide selection of premium vehicles at unbeatable prices</p>
 
            <!-- Search Form -->
            <form class="search-form" method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="pickup_location"><i class="fas fa-map-marker-alt"></i> Pickup Location</label>
                        <select id="pickup_location" name="pickup_location" required>
                            <option value="">Select Location</option>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location); ?>">
                                    <?php echo htmlspecialchars($location); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
 
                    <div class="form-group">
                        <label for="pickup_date"><i class="fas fa-calendar-plus"></i> Pickup Date</label>
                        <input type="date" id="pickup_date" name="pickup_date" required 
                               value="<?php echo date('Y-m-d'); ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
 
                    <div class="form-group">
                        <label for="return_date"><i class="fas fa-calendar-minus"></i> Return Date</label>
                        <input type="date" id="return_date" name="return_date" required 
                               value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
 
                    <div class="form-group">
                        <button type="submit" name="search" class="btn btn-primary btn-search">
                            <i class="fas fa-search"></i> Search Cars
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
 
    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose DriveEasy?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Fully Insured</h3>
                    <p>All our vehicles come with comprehensive insurance coverage</p>
                </div>
 
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3>No Hidden Fees</h3>
                    <p>Transparent pricing with no surprise charges</p>
                </div>
 
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer service assistance</p>
                </div>
 
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Wide Selection</h3>
                    <p>From economy to luxury, we have the perfect car for you</p>
                </div>
            </div>
        </div>
    </section>
 
    <!-- Featured Cars -->
    <section class="featured-cars">
        <div class="container">
            <h2 class="section-title">Featured Vehicles</h2>
 
            <div class="cars-grid">
                <?php while($car = $featured_result->fetch_assoc()): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <img src="assets/images/<?php echo $car['image_url']; ?>" 
                                 alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                            <span class="car-type"><?php echo $car['type']; ?></span>
                        </div>
 
                        <div class="car-info">
                            <h3 class="car-title"><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                            <div class="car-specs">
                                <span><i class="fas fa-users"></i> <?php echo $car['seats']; ?> seats</span>
                                <span><i class="fas fa-gas-pump"></i> <?php echo $car['fuel_type']; ?></span>
                                <span><i class="fas fa-cog"></i> <?php echo $car['transmission']; ?></span>
                            </div>
                            <p><?php echo $car['description']; ?></p>
 
                            <div class="car-footer">
                                <div class="car-price">
                                    <span class="price">$<?php echo $car['price_per_day']; ?></span>
                                    <span class="period">/day</span>
                                </div>
                                <a href="booking.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-calendar-alt"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
 
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="logo" style="color: white;">
                        <i class="fas fa-car"></i>
                        <span>DriveEasy</span>Rentals
                    </h3>
                    <p>Your trusted partner for premium car rental services across the country.</p>
                </div>
 
                <div>
                    <h4>Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="index.php" style="color: var(--gray-light); text-decoration: none;">Home</a></li>
                        <li><a href="cars.php" style="color: var(--gray-light); text-decoration: none;">Browse Cars</a></li>
                        <li><a href="#" style="color: var(--gray-light); text-decoration: none;">About Us</a></li>
                        <li><a href="#" style="color: var(--gray-light); text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
 
                <div>
                    <h4>Contact Info</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Street, City, Country</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope"></i> info@driveeasy.com</li>
                    </ul>
                </div>
            </div>
 
            <div class="footer-bottom">
                <p>&copy; 2024 DriveEasy Rentals. All rights reserved.</p>
            </div>
        </div>
    </footer>
 
    <script>
        // JavaScript for interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Date validation
            const pickupDate = document.getElementById('pickup_date');
            const returnDate = document.getElementById('return_date');
 
            pickupDate.addEventListener('change', function() {
                returnDate.min = this.value;
                if (new Date(returnDate.value) < new Date(this.value)) {
                    returnDate.value = this.value;
                }
            });
 
            returnDate.addEventListener('change', function() {
                if (new Date(this.value) < new Date(pickupDate.value)) {
                    alert('Return date must be after pickup date');
                    this.value = pickupDate.value;
                }
            });
 
            // Initialize date inputs
            const today = new Date().toISOString().split('T')[0];
            const threeDaysLater = new Date();
            threeDaysLater.setDate(threeDaysLater.getDate() + 3);
            const threeDaysLaterStr = threeDaysLater.toISOString().split('T')[0];
 
            if (!pickupDate.value) pickupDate.value = today;
            if (!returnDate.value) returnDate.value = threeDaysLaterStr;
 
            // Search form validation
            const searchForm = document.querySelector('.search-form');
            searchForm.addEventListener('submit', function(e) {
                const location = document.getElementById('pickup_location').value;
                const pickup = document.getElementById('pickup_date').value;
                const returnD = document.getElementById('return_date').value;
 
                if (!location) {
                    e.preventDefault();
                    alert('Please select a pickup location');
                    return false;
                }
 
                if (new Date(returnD) <= new Date(pickup)) {
                    e.preventDefault();
                    alert('Return date must be after pickup date');
                    return false;
                }
            });
 
            // Add hover effects to car cards
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
 
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
 
            // Make logo clickable to home
            const logos = document.querySelectorAll('.logo');
            logos.forEach(logo => {
                logo.style.cursor = 'pointer';
            });
        });
    </script>
</body>
</html>
 
<?php 
// Close connection
$conn->close();
?>
