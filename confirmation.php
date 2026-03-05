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

$booking_id = $_GET['booking_id'] ?? 0;

// Fetch booking details
$query = "SELECT b.*, c.brand, c.model, c.year, c.image_url 
          FROM bookings b 
          JOIN cars c ON b.car_id = c.id 
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - DriveEasy Rentals</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --secondary: #10b981;
            --dark: #1f2937;
            --light: #f9fafb;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 8px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            text-align: center;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        /* Navigation */
        .navbar {
            background-color: var(--white);
            box-shadow: var(--shadow);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
            max-width: 1200px;
            margin: 0 auto;
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

        /* Confirmation Content */
        .confirmation-icon {
            font-size: 4rem;
            color: var(--secondary);
            margin-bottom: 1.5rem;
        }

        .confirmation-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .confirmation-subtitle {
            font-size: 1.125rem;
            color: #666;
            margin-bottom: 3rem;
        }

        /* Confirmation Card */
        .confirmation-card {
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .confirmation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .booking-id {
            background-color: var(--light);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-weight: 600;
        }

        .confirmation-body {
            display: grid;
            gap: 1.5rem;
        }

        .confirmation-row {
            display: grid;
            grid-template-columns: 150px 1fr;
        }

        .confirmation-label {
            font-weight: 600;
            color: #666;
        }

        .confirmation-value {
            font-weight: 500;
        }

        .total-amount {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 700;
        }

        .status-confirmed {
            color: var(--secondary);
            font-weight: 600;
        }

        /* Actions */
        .confirmation-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
            flex-wrap: wrap;
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
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        /* Footer */
        .footer {
            background-color: var(--dark);
            color: var(--white);
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .confirmation-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .confirmation-actions {
                flex-direction: column;
            }
            
            .confirmation-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i>
                <span>DriveEasy</span>Rentals
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Success Icon -->
        <div class="confirmation-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="confirmation-title">Booking Confirmed!</h1>
        <p class="confirmation-subtitle">Your rental has been successfully booked. A confirmation email has been sent to your email address.</p>
        
        <!-- Booking Details Card -->
        <div class="confirmation-card">
            <div class="confirmation-header">
                <h3>Booking Details</h3>
                <span class="booking-id">Booking ID: #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            
            <div class="confirmation-body">
                <div class="confirmation-row">
                    <div class="confirmation-label">Car</div>
                    <div class="confirmation-value">
                        <strong><?php echo $booking['brand'] . ' ' . $booking['model']; ?> (<?php echo $booking['year']; ?>)</strong>
                    </div>
                </div>
                
                <div class="confirmation-row">
                    <div class="confirmation-label">Customer</div>
                    <div class="confirmation-value">
                        <?php echo $booking['user_name']; ?><br>
                        <?php echo $booking['user_email']; ?><br>
                        <?php echo $booking['user_phone']; ?>
                    </div>
                </div>
                
                <div class="confirmation-row">
                    <div class="confirmation-label">Rental Period</div>
                    <div class="confirmation-value">
                        <strong>Pickup:</strong> <?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?><br>
                        <strong>Return:</strong> <?php echo date('M d, Y', strtotime($booking['return_date'])); ?>
                    </div>
                </div>
                
                <div class="confirmation-row">
                    <div class="confirmation-label">Total Amount</div>
                    <div class="confirmation-value total-amount">
                        $<?php echo number_format($booking['total_price'], 2); ?>
                    </div>
                </div>
                
                <div class="confirmation-row">
                    <div class="confirmation-label">Status</div>
                    <div class="confirmation-value status-confirmed">
                        <i class="fas fa-check"></i> <?php echo $booking['status']; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="cars.php" class="btn btn-secondary">
                <i class="fas fa-car"></i> Book Another Car
            </a>
            <button onclick="window.print()" class="btn" style="background-color: #e5e7eb;">
                <i class="fas fa-print"></i> Print Receipt
            </button>
        </div>
        
        <!-- Contact Support -->
        <div style="margin-top: 2rem; padding: 1.5rem; background-color: #f0f9ff; border-radius: var(--radius);">
            <h4 style="margin-bottom: 1rem; color: var(--primary);">
                <i class="fas fa-headset"></i> Need Help?
            </h4>
            <p>Contact our customer support at <strong>+1 234 567 8900</strong> or email <strong>support@driveeasy.com</strong></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 DriveEasy Rentals. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Print button functionality
            const printButton = document.querySelector('button[onclick*="print"]');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }
            
            // Add animation to success icon
            const successIcon = document.querySelector('.confirmation-icon i');
            if (successIcon) {
                successIcon.style.animation = 'bounce 0.5s ease';
                setTimeout(() => {
                    successIcon.style.animation = '';
                }, 500);
            }
            
            // Add CSS animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes bounce {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
            `;
            document.head.appendChild(style);
            
            // Auto-scroll to top
            window.scrollTo(0, 0);
            
            // Display booking time
            const bookingTime = new Date().toLocaleTimeString();
            const timeElement = document.createElement('p');
            timeElement.style.color = '#666';
            timeElement.style.marginTop = '1rem';
            timeElement.innerHTML = `<i class="fas fa-clock"></i> Booked at ${bookingTime}`;
            
            const subtitle = document.querySelector('.confirmation-subtitle');
            if (subtitle) {
                subtitle.parentNode.insertBefore(timeElement, subtitle.nextSibling);
            }
        });
    </script>
</body>
</html>

<?php 
$stmt->close();
$conn->close();
?>
