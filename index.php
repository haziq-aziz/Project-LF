<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nabihah Ishak & CO. - Legal Document Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero text-white text-center d-flex align-items-center">
        <nav class="navbar navbar-expand-lg w-100 position-absolute top-0">
            <div class="container">
                <a class="navbar-brand text-uppercase" href="#">Nabihah Ishak & CO.</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                        <li class="nav-item"><a class="nav-link" href="#lawyers">Our Lawyers</a></li>
                        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="auth/login.php">Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container hero-heading">
            <h1>Your Trusted Legal Partner</h1>
            <p class="lead">We are dedicated team of legal professionals providing expert services for all your legal needs. We are comitted to working hard for you and ensuring successful outcomes.</p>
            <a href="auth/login.php" class="btn">Get Started</a>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="mb-8 text-center section-title">About Us</h2>
            <div class="row align-items-center">
                <!-- Image Column -->
                <div class="col-lg-6">
                    <img src="../assets/images/landing/office.jpeg" class="img-fluid rounded shadow" alt="Company Office">
                </div>
                <!-- Text Column -->
                <div class="col-lg-5">
                    <p class="lead">
                        Nabihah Ishak & Co, located at No. 36A (Front) Tingkat 1, Jalan Kemuja, Of Jalan Bangsac, 59000, Kuala Lumpur, was established in May 2022. 
                        The firm has grown dynamically, offering comprehensive legal services. 
                        Legal documents form the foundation of its operations, serving as formal records that define the rights and obligations of all parties.
                        From contracts and agreements to court filings and client records, Nabihah Ishak & Co ensures meticulous handling of critical documents 
                        to support client interactions, case strategies, and litigation processes. 
                        The firm is committed to professionalism and precision in delivering effective legal solutions.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Lawyers Section -->
    <section id="lawyers" class="bg-light py-5">
        <div class="container">
            <h2 class="mb-8 text-center section-title">Meet Our Lawyers</h2>
            <div class="row text-center">
                <div class="col-md-4">
                    <img src="../assets/images/landing/lawyer1.png" class="rounded-circle mb-3" alt="Lawyer 1">
                    <h5>Nabihah binti Ishak  </h5>
                    <p>LL.B (Hons), DLSA UiTM Shah Alam</p>
                </div>
                <div class="col-md-4">
                    <img src="../assets/images/landing/lawyer2.png" class="rounded-circle mb-3" alt="Lawyer 2">
                    <h5>Rozainasha binti Mohd Omar</h5>
                    <p>LL.B (Hons) UiTM Shah Alam</p>
                </div>
                <div class="col-md-4">
                    <img src="../assets/images/landing/lawyer3.png" class="rounded-circle mb-3" alt="Lawyer 3">
                    <h5>Hanis Shakirin binti Ahmad Saleh </h5>
                    <p>LL.B (Hons) UiTM Shah Alam</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <h2 class="mb-8 text-center section-title">Our Services</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <img src="../assets/images/landing/personal_injury.jpg" class="card-img-top" alt="Personal Injury">
                        <div class="card-body">
                            <h5 class="card-title">Personal Injury</h5>
                            <p class="card-text">Legal representation for personal injury claims and compensation.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="../assets/images/landing/criminal_law.jpeg" class="card-img-top" alt="Criminal Law">
                        <div class="card-body">
                            <h5 class="card-title">Criminal Law</h5>
                            <p class="card-text">Defense and legal support for criminal cases and investigations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="../assets/images/landing/conveyancing.jpg" class="card-img-top" alt="Conveyancing">
                        <div class="card-body">
                            <h5 class="card-title">Conveyancing</h5>
                            <p class="card-text">Handling property transactions, ownership transfers, and agreements.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
        <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-8 text-center section-title">Contact Us</h2>
            <div class="row align-items-center">
                <!-- Left Side - Google Map -->
                <div class="col-md-6">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7967.942244073371!2d101.66828879316697!3d3.127547153588727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc48b2c951b039%3A0xa726c2902e4b5a49!2sJalan%20Kemuja%2C%20Bangsar%2C%2059000%20Kuala%20Lumpur%2C%20Wilayah%20Persekutuan%20Kuala%20Lumpur!5e0!3m2!1sen!2smy!4v1695897890123!5m2!1sen!2smy" 
                            width="100%" 
                            height="350" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
                <!-- Right Side - Contact Info -->
                <div class="col-md-6">
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> No. 36A (Front) Tingkat 1, Jalan Kemuja, Off Jalan Bangsar, 59000 Kuala Lumpur, W.P. Kuala Lumpur</p>
                        <p><i class="fas fa-phone"></i> 013-9269519 / 017-2154801</p>
                        <p><i class="fas fa-envelope"></i> <a href="mailto:nabihahishakco@gmail.com">nabihahishakco@gmail.com</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p>&copy; 2024 Nabihah Ishak & CO. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
