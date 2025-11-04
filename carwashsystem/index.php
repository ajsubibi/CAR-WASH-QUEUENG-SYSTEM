<?php
// Home page for public customers
require_once 'config/session_config.php';
require_once 'classes/Service.php';

$service = new Service();
$services = $service->getAllServiceTypes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Pro - Professional Car Washing Services</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <main>
        <section class="hero" style="background-image: url('assets/images/Mercedes.jpg');">
            <div class="login-link">
                <a href="login/login.php" class="btn btn-small">Login</a>
            </div>
            <div class="hero-overlay">
                <div class="container">
                    <h2>Welcome to Car Wash Pro</h2>
                    <p>Professional car washing services with modern technology and expert care.</p>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn btn-primary">Register Now</a>
                        <a href="queue.php" class="btn btn-secondary">View Queue</a>
                        <a href="classes/search_customer.php" class="btn btn-secondary">My Record</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h3 style="font-size: 2.2rem; position: relative; text-align: center; margin-bottom: 2rem;">
                    Why Choose Us?
                    <div style="width: 100px; height: 3px; background-color: #FFD700; margin: 0.5rem auto 0;"></div>
                </h3>
                <div class="features-grid">
                    <div class="feature-card" style="background-image: url('assets/images/car1.jpg');">
                        <div class="feature-overlay">
                            <h4>Expert Service</h4>
                            <p>Professional technicians using premium products and equipment.</p>
                        </div>
                    </div>
                    <div class="feature-card" style="background-image: url('assets/images/car2.jpg');">
                        <div class="feature-overlay">
                            <h4>Quick & Efficient</h4>
                            <p>Fast service without compromising on quality.</p>
                        </div>
                    </div>
                    <div class="feature-card" style="background-image: url('assets/images/car 3.jpg');">
                        <div class="feature-overlay">
                            <h4>Affordable Prices</h4>
                            <p>Competitive pricing for all our services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="services">
            <div class="container">
                <h3 style="font-size: 2.2rem; position: relative; text-align: center; margin-bottom: 2rem;">
                    Our Services
                    <div style="width: 100px; height: 3px; background-color: #FFD700; margin: 0.5rem auto 0;"></div>
                </h3>
                <div class="services-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; justify-items: center; max-width: 1200px; margin: 0 auto;">
                    <div class="service-card" style="width: 100%; max-width: 250px; background: rgba(255, 255, 255, 0.95); border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 1.5rem; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';">
                        <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Quick Wash</h4>
                        <p style="font-size: 14px; font-weight: 500; color: #666; margin-bottom: 1rem;">Fast exterior wash</p>
                        <div class="service-details" style="display: flex; justify-content: space-between; font-size: 12px; color: #888;">
                            <span class="duration">15 min</span>
                            <span class="price">₱150.00</span>
                        </div>
                    </div>
                    <div class="service-card" style="width: 100%; max-width: 250px; background: rgba(255, 255, 255, 0.95); border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 1.5rem; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';">
                        <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Basic Wash</h4>
                        <p style="font-size: 14px; font-weight: 500; color: #666; margin-bottom: 1rem;">Exterior wash and dry</p>
                        <div class="service-details" style="display: flex; justify-content: space-between; font-size: 12px; color: #888;">
                            <span class="duration">30 min</span>
                            <span class="price">₱250.00</span>
                        </div>
                    </div>
                    <div class="service-card" style="width: 100%; max-width: 250px; background: rgba(255, 255, 255, 0.95); border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 1.5rem; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';">
                        <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Full Service Wash</h4>
                        <p style="font-size: 14px; font-weight: 500; color: #666; margin-bottom: 1rem;">Interior and exterior cleaning</p>
                        <div class="service-details" style="display: flex; justify-content: space-between; font-size: 12px; color: #888;">
                            <span class="duration">60 min</span>
                            <span class="price">₱450.00</span>
                        </div>
                    </div>
                    <div class="service-card" style="width: 100%; max-width: 250px; background: rgba(255, 255, 255, 0.95); border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); padding: 1.5rem; text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)';">
                        <h4 style="font-size: 18px; font-weight: 600; margin-bottom: 0.5rem; color: #333;">Deluxe Detailing</h4>
                        <p style="font-size: 14px; font-weight: 500; color: #666; margin-bottom: 1rem;">Complete detailing service</p>
                        <div class="service-details" style="display: flex; justify-content: space-between; font-size: 12px; color: #888;">
                            <span class="duration">120 min</span>
                            <span class="price">₱800.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Car Wash Pro. All rights reserved. <span class="footer-line">|</span> <span class="footer-text">Login</span></p>
        </div>
    </footer>
</body>
</html>
