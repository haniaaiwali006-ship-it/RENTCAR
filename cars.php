<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'rsoa_rsoa278_6');
define('DB_PASS', '654321#');
define('DB_NAME', 'rsoa_rsoa278_6');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

$conn = getConnection();

// Get search parameters
$location = $_GET['location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? date('Y-m-d');
$return_date = $_GET['return_date'] ?? date('Y-m-d', strtotime('+3 days'));
$type_filter = $_GET['type'] ?? '';
$fuel_filter = $_GET['fuel'] ?? '';
$sort_by = $_GET['sort'] ?? 'price_asc';
$brand_filter = $_GET['brand'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build query
$query = "SELECT * FROM cars WHERE available = 1";
$params = [];

if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($type_filter)) {
    $query .= " AND type = ?";
    $params[] = $type_filter;
}

if (!empty($fuel_filter)) {
    $query .= " AND fuel_type = ?";
    $params[] = $fuel_filter;
}

if (!empty($brand_filter)) {
    $query .= " AND brand = ?";
    $params[] = $brand_filter;
}

if (!empty($min_price)) {
    $query .= " AND price_per_day >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $query .= " AND price_per_day <= ?";
    $params[] = $max_price;
}

// Add sorting
switch($sort_by) {
    case 'price_desc':
        $query .= " ORDER BY price_per_day DESC";
        break;
    case 'rating_desc':
        $query .= " ORDER BY rating DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY brand, model";
        break;
    default:
        $query .= " ORDER BY price_per_day ASC";
}

// Prepare and execute
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// Get filter options
$types_query = "SELECT DISTINCT type FROM cars ORDER BY type";
$types_result = $conn->query($types_query);

$fuels_query = "SELECT DISTINCT fuel_type FROM cars ORDER BY fuel_type";
$fuels_result = $conn->query($fuels_query);

$brands_query = "SELECT DISTINCT brand FROM cars ORDER BY brand";
$brands_result = $conn->query($brands_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - DriveEasy Rentals</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 8px;
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
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-weight: 500;
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
        }

        /* Page Header */
        .page-header {
            background-color: var(--white);
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .page-title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Main Layout */
        .main-container {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
        }

        .sidebar {
            width: 300px;
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .filter-title {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: inherit;
        }

        .price-inputs {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .price-inputs input {
            flex: 1;
        }

        .filter-actions {
            margin-top: 2rem;
        }

        .btn-block {
            width: 100%;
            margin-bottom: 0.5rem;
            justify-content: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
        }

        .sort-bar {
            background-color: var(--white);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            justify-content: flex-end;
            box-shadow: var(--shadow);
        }

        .sort-select {
            padding: 0.5rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: inherit;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
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
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
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
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-menu {
                display: none;
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Available Cars</h1>
            <p><?php echo $result->num_rows; ?> vehicles found</p>
        </div>
    </div>

    <div class="container main-container">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <form method="GET" class="filter-form">
                <h3 class="filter-title"><i class="fas fa-filter"></i> Filters</h3>
                
                <div class="filter-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" 
                           placeholder="Enter city" class="filter-input">
                </div>
                
                <div class="filter-group">
                    <label>Car Type</label>
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <?php while($type = $types_result->fetch_assoc()): ?>
                            <option value="<?php echo $type['type']; ?>"
                                <?php echo ($type_filter == $type['type']) ? 'selected' : ''; ?>>
                                <?php echo $type['type']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Brand</label>
                    <select name="brand" class="filter-select">
                        <option value="">All Brands</option>
                        <?php while($brand = $brands_result->fetch_assoc()): ?>
                            <option value="<?php echo $brand['brand']; ?>"
                                <?php echo ($brand_filter == $brand['brand']) ? 'selected' : ''; ?>>
                                <?php echo $brand['brand']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Fuel Type</label>
                    <select name="fuel" class="filter-select">
                        <option value="">All Fuel Types</option>
                        <?php while($fuel = $fuels_result->fetch_assoc()): ?>
                            <option value="<?php echo $fuel['fuel_type']; ?>"
                                <?php echo ($fuel_filter == $fuel['fuel_type']) ? 'selected' : ''; ?>>
                                <?php echo $fuel['fuel_type']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Price Range (per day)</label>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Min" 
                               value="<?php echo $min_price; ?>" class="price-input">
                        <span>to</span>
                        <input type="number" name="max_price" placeholder="Max" 
                               value="<?php echo $max_price; ?>" class="price-input">
                    </div>
                </div>
                
                <input type="hidden" name="pickup_date" value="<?php echo $pickup_date; ?>">
                <input type="hidden" name="return_date" value="<?php echo $return_date; ?>">
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="cars.php" class="btn btn-block" style="background-color: var(--gray-light);">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Sort Bar -->
            <div class="sort-bar">
                <select name="sort" id="sortSelect" class="sort-select">
                    <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>
                        Price: Low to High
                    </option>
                    <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>
                        Price: High to Low
                    </option>
                    <option value="rating_desc" <?php echo ($sort_by == 'rating_desc') ? 'selected' : ''; ?>>
                        Best Rated
                    </option>
                </select>
            </div>

            <!-- Cars Grid -->
            <div class="cars-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($car = $result->fetch_assoc()): 
                        // Calculate total price
                        $date1 = new DateTime($pickup_date);
                        $date2 = new DateTime($return_date);
                        $days = $date2->diff($date1)->days;
                        $days = $days > 0 ? $days : 1;
                        $total_price = $car['price_per_day'] * $days;
                    ?>
                        <div class="car-card">
                            <div class="car-image">
                                <img src="assets/images/<?php echo $car['image_url']; ?>" 
                                     alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                                <span class="car-type"><?php echo $car['type']; ?></span>
                            </div>
                            
                            <div class="car-info">
                                <h3 class="car-title"><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                                <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $car['location']; ?>
                                </p>
                                
                                <div class="car-specs">
                                    <span><i class="fas fa-users"></i> <?php echo $car['seats']; ?> seats</span>
                                    <span><i class="fas fa-gas-pump"></i> <?php echo $car['fuel_type']; ?></span>
                                    <span><i class="fas fa-cog"></i> <?php echo $car['transmission']; ?></span>
                                </div>
                                
                                <p style="margin: 1rem 0; font-size: 0.9rem;"><?php echo $car['description']; ?></p>
                                
                                <div class="car-footer">
                                    <div>
                                        <div class="car-price">
                                            <span class="price">$<?php echo $car['price_per_day']; ?></span>
                                            <span class="period">/day</span>
                                        </div>
                                        <div style="font-size: 0.9rem; color: var(--gray);">
                                            Total: $<?php echo number_format($total_price, 2); ?>
                                        </div>
                                    </div>
                                    <a href="booking.php?car_id=<?php echo $car['id']; ?>&pickup_date=<?php echo $pickup_date; ?>&return_date=<?php echo $return_date; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-calendar-alt"></i> Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-car-crash" style="font-size: 4rem; color: var(--gray); margin-bottom: 1rem;"></i>
                        <h3>No cars found</h3>
                        <p style="margin-bottom: 1.5rem;">Try adjusting your search criteria</p>
                        <a href="cars.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

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
                        <li><a href="index.php" style="color: #ccc; text-decoration: none;">Home</a></li>
                        <li><a href="cars.php" style="color: #ccc; text-decoration: none;">Browse Cars</a></li>
                        <li><a href="#" style="color: #ccc; text-decoration: none;">About Us</a></li>
                        <li><a href="#" style="color: #ccc; text-decoration: none;">Contact</a></li>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Sort functionality
            document.getElementById('sortSelect').addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', this.value);
                window.location.href = url.toString();
            });
            
            // Date validation
            const minPriceInput = document.querySelector('input[name="min_price"]');
            const maxPriceInput = document.querySelector('input[name="max_price"]');
            
            if (minPriceInput && maxPriceInput) {
                minPriceInput.addEventListener('change', function() {
                    const minValue = parseInt(this.value) || 0;
                    if (maxPriceInput.value && parseInt(maxPriceInput.value) < minValue) {
                        maxPriceInput.value = '';
                    }
                });
            }
            
            // Car card hover effects
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Filter form validation
            const filterForm = document.querySelector('.filter-form');
            filterForm.addEventListener('submit', function(e) {
                const minPrice = document.querySelector('input[name="min_price"]').value;
                const maxPrice = document.querySelector('input[name="max_price"]').value;
                
                if (minPrice && maxPrice && parseInt(minPrice) > parseInt(maxPrice)) {
                    e.preventDefault();
                    alert('Minimum price cannot be greater than maximum price');
                    return false;
                }
            });
        });
    </script>
</body>
</html>

<?php 
$conn->close();
?>
