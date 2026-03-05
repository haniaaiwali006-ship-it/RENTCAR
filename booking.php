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

// Get car details
$car_id = $_GET['car_id'] ?? 0;
$pickup_date = $_GET['pickup_date'] ?? date('Y-m-d');
$return_date = $_GET['return_date'] ?? date('Y-m-d', strtotime('+3 days'));

// Fetch car details
$car_query = "SELECT * FROM cars WHERE id = ?";
$stmt = $conn->prepare($car_query);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$car_result = $stmt->get_result();
$car = $car_result->fetch_assoc();

if (!$car) {
    header("Location: cars.php");
    exit();
}

// Calculate rental details
$date1 = new DateTime($pickup_date);
$date2 = new DateTime($return_date);
$total_days = $date2->diff($date1)->days;
$total_days = $total_days > 0 ? $total_days : 1;
$total_price = $car['price_per_day'] * $total_days;

// Handle booking submission
$booking_success = false;
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $user_phone = trim($_POST['user_phone']);
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    
    // Validate inputs
    if (empty($user_name) || empty($user_email) || empty($user_phone)) {
        $booking_error = "Please fill in all required fields";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $booking_error = "Please enter a valid email address";
    } else {
        // Recalculate total price based on submitted dates
        $date1 = new DateTime($pickup_date);
        $date2 = new DateTime($return_date);
        $total_days = $date2->diff($date1)->days;
        $total_days = $total_days > 0 ? $total_days : 1;
        $total_price = $car['price_per_day'] * $total_days;
        
        // Insert booking
        $insert_query = "INSERT INTO bookings (car_id, user_name, user_email, user_phone, pickup_date, return_date, total_price) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isssssd", $car_id, $user_name, $user_email, $user_phone, $pickup_date, $return_date, $total_price);
        
        if ($stmt->execute()) {
            $booking_id = $stmt->insert_id;
            
            // Update car availability
            $update_query = "UPDATE cars SET available = 0 WHERE id = ?";
            $stmt2 = $conn->prepare($update_query);
            $stmt2->bind_param("i", $car_id);
            $stmt2->execute();
            $stmt2->close();
            
            // Redirect to confirmation
            header("Location: confirmation.php?booking_id=" . $booking_id);
            exit();
        } else {
            $booking_error = "Booking failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Booking - DriveEasy Rentals</title>
    
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
            --danger: #ef4444;
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

        /* Booking Container */
        .booking-container {
            padding: 2rem 0;
        }

        .page-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .booking-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        /* Booking Summary */
        .summary-card {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .summary-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .summary-car {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .car-image-summary {
            width: 120px;
            height: 80px;
            border-radius: var(--radius);
            overflow: hidden;
        }

        .car-image-summary img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .summary-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-row.total {
            padding-top: 1rem;
            border-top: 2px solid var(--gray-light);
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Booking Form */
        .booking-form-container {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .form-title {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: inherit;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .terms-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .btn-book {
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
            padding: 1rem;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Footer */
        .footer {
            background-color: var(--dark);
            color: var(--white);
            padding: 3rem 0;
            margin-top: 4rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .booking-content {
                grid-template-columns: 1fr;
            }
            
            .nav-menu {
                display: none;
            }
            
            .form-row {
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

    <div class="container booking-container">
        <h1 class="page-title">Complete Your Booking</h1>
        
        <?php if ($booking_error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $booking_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="booking-content">
            <!-- Booking Summary -->
            <div class="booking-summary">
                <div class="summary-card">
                    <div class="summary-header">
                        <h3>Booking Summary</h3>
                    </div>
                    
                    <div class="summary-car">
                        <div class="car-image-summary">
                            <img src="assets/images/<?php echo $car['image_url']; ?>" 
                                 alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>">
                        </div>
                        <div>
                            <h4><?php echo $car['brand'] . ' ' . $car['model']; ?> (<?php echo $car['year']; ?>)</h4>
                            <div style="display: flex; gap: 1rem; margin-top: 0.5rem; font-size: 0.9rem; color: var(--gray);">
                                <span><i class="fas fa-users"></i> <?php echo $car['seats']; ?> seats</span>
                                <span><i class="fas fa-gas-pump"></i> <?php echo $car['fuel_type']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="summary-details">
                        <div class="detail-row">
                            <span>Pickup Date</span>
                            <strong><?php echo date('M d, Y', strtotime($pickup_date)); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Return Date</span>
                            <strong><?php echo date('M d, Y', strtotime($return_date)); ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>Rental Duration</span>
                            <strong><?php echo $total_days; ?> days</strong>
                        </div>
                        <div class="detail-row">
                            <span>Daily Rate</span>
                            <strong>$<?php echo $car['price_per_day']; ?>/day</strong>
                        </div>
                        <div class="detail-row total">
                            <span>Total Amount</span>
                            <strong style="color: var(--primary);">$<?php echo number_format($total_price, 2); ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="summary-card">
                    <h4><i class="fas fa-info-circle"></i> Important Information</h4>
                    <ul style="padding-left: 1.5rem; margin-top: 1rem; color: var(--gray);">
                        <li>Minimum age requirement: 21 years</li>
                        <li>Valid driver's license required</li>
                        <li>Credit card required for security deposit</li>
                        <li>Free cancellation up to 24 hours before pickup</li>
                    </ul>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="booking-form-container">
                <form method="POST" class="booking-form">
                    <h3 class="form-title">Your Information</h3>
                    
                    <div class="form-group">
                        <label for="user_name"><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" id="user_name" name="user_name" required 
                               placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="user_email"><i class="fas fa-envelope"></i> Email Address *</label>
                        <input type="email" id="user_email" name="user_email" required 
                               placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="user_phone"><i class="fas fa-phone"></i> Phone Number *</label>
                        <input type="tel" id="user_phone" name="user_phone" required 
                               placeholder="Enter your phone number">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pickup_date"><i class="fas fa-calendar-plus"></i> Pickup Date *</label>
                            <input type="date" id="pickup_date" name="pickup_date" required 
                                   value="<?php echo $pickup_date; ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="return_date"><i class="fas fa-calendar-minus"></i> Return Date *</label>
                            <input type="date" id="return_date" name="return_date" required 
                                   value="<?php echo $return_date; ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests"><i class="fas fa-comment"></i> Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" 
                                  placeholder="Any special requests or notes" rows="3"></textarea>
                    </div>
                    
                    <div class="terms-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="#">Terms & Conditions</a> and 
                            <a href="#">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                    
                    <button type="submit" name="book_now" class="btn btn-primary btn-book">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div style="text-align: center;">
                <h3 class="logo" style="color: white; justify-content: center; margin-bottom: 1rem;">
                    <i class="fas fa-car"></i>
                    <span>DriveEasy</span>Rentals
                </h3>
                <p>Your trusted partner for premium car rental services across the country.</p>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 DriveEasy Rentals. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update total price when dates change
            const pickupDateInput = document.getElementById('pickup_date');
            const returnDateInput = document.getElementById('return_date');
            const dailyRate = <?php echo $car['price_per_day']; ?>;
            
            function updateTotalPrice() {
                const pickupDate = new Date(pickupDateInput.value);
                const returnDate = new Date(returnDateInput.value);
                
                if (pickupDate && returnDate && returnDate > pickupDate) {
                    const days = Math.ceil((returnDate - pickupDate) / (1000 * 60 * 60 * 24));
                    const totalPrice = days * dailyRate;
                    
                    // Update display
                    const totalElement = document.querySelector('.detail-row.total strong');
                    const daysElement = document.querySelector('.detail-row:nth-child(3) strong');
                    
                    if (totalElement) {
                        totalElement.textContent = '$' + totalPrice.toFixed(2);
                    }
                    if (daysElement) {
                        daysElement.textContent = days + ' days';
                    }
                }
            }
            
            if (pickupDateInput && returnDateInput) {
                pickupDateInput.addEventListener('change', function() {
                    returnDateInput.min = this.value;
                    if (new Date(returnDateInput.value) < new Date(this.value)) {
                        returnDateInput.value = this.value;
                    }
                    updateTotalPrice();
                });
                
                returnDateInput.addEventListener('change', function() {
                    if (new Date(this.value) < new Date(pickupDateInput.value)) {
                        alert('Return date must be after pickup date');
                        this.value = pickupDateInput.value;
                    }
                    updateTotalPrice();
                });
            }
            
            // Form validation
            const bookingForm = document.querySelector('.booking-form');
            bookingForm.addEventListener('submit', function(e) {
                const userEmail = document.getElementById('user_email').value;
                const userPhone = document.getElementById('user_phone').value;
                const pickupDate = document.getElementById('pickup_date').value;
                const returnDate = document.getElementById('return_date').value;
                const terms = document.getElementById('terms').checked;
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(userEmail)) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return false;
                }
                
                // Phone validation (basic)
                if (userPhone.length < 10) {
                    e.preventDefault();
                    alert('Please enter a valid phone number');
                    return false;
                }
                
                // Date validation
                if (new Date(returnDate) <= new Date(pickupDate)) {
                    e.preventDefault();
                    alert('Return date must be after pickup date');
                    return false;
                }
                
                // Terms validation
                if (!terms) {
                    e.preventDefault();
                    alert('Please agree to the terms and conditions');
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
