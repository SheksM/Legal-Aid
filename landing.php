<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Aid Beyond Bars - Empowering Justice for Imprisoned Women</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .cta-btn-primary {
            background: white;
            color: var(--primary-color);
        }
        
        .cta-btn-primary:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .cta-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .cta-btn-secondary:hover {
            background: white;
            color: var(--primary-color);
        }
        
        .features-section {
            padding: 4rem 0;
            background: #f8f9fa;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .about-section {
            padding: 4rem 0;
        }
        
        .stats-section {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">Legal Aid Beyond Bars</div>
            <ul class="nav-links">
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="auth/login.php" class="btn btn-outline">Login</a></li>
                <li><a href="auth/register.php" class="btn btn-primary">Register</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Legal Aid Beyond Bars</h1>
            <p class="hero-subtitle">
                Connecting imprisoned women with pro bono lawyers to ensure access to justice and legal representation
            </p>
            <div class="cta-buttons">
                <a href="auth/register.php" class="cta-btn cta-btn-primary">Get Started</a>
                <a href="auth/login.php" class="cta-btn cta-btn-secondary">Login</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem; color: var(--primary-color);">
                How We Help
            </h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">⚖️</div>
                    <h3 class="feature-title">Legal Representation</h3>
                    <p>Connect with qualified pro bono lawyers who specialize in criminal, civil, and family law cases.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏛️</div>
                    <h3 class="feature-title">Prison Integration</h3>
                    <p>Seamless case submission and verification process through prison wardens for security and authenticity.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3 class="feature-title">Case Management</h3>
                    <p>Track your legal case progress from submission to completion with real-time updates and notifications.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3 class="feature-title">Legal Resources</h3>
                    <p>Access comprehensive legal information, rights, procedures, and frequently asked questions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3 class="feature-title">Secure Platform</h3>
                    <p>Your information is protected with enterprise-level security and confidentiality measures.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3 class="feature-title">Community Support</h3>
                    <p>Join a network of advocates, lawyers, and support staff dedicated to justice and rehabilitation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <h2 style="font-size: 2.5rem; margin-bottom: 2rem; color: var(--primary-color);">Our Mission</h2>
                <p style="font-size: 1.2rem; line-height: 1.8; margin-bottom: 2rem;">
                    Legal Aid Beyond Bars is dedicated to ensuring that imprisoned women have access to quality legal representation 
                    and support. We bridge the gap between incarcerated individuals and the legal system by providing a secure, 
                    efficient platform that connects clients with pro bono lawyers.
                </p>
                <p style="font-size: 1.1rem; line-height: 1.7; color: #666;">
                    Our platform facilitates case submission, verification through prison authorities, and ongoing legal support 
                    to ensure that justice is accessible to all, regardless of circumstances. We believe that every person 
                    deserves proper legal representation and the opportunity for rehabilitation and reintegration into society.
                </p>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Making a Difference</h2>
            <div class="stats-grid">
                <div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Cases Handled</div>
                </div>
                <div>
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Pro Bono Lawyers</div>
                </div>
                <div>
                    <div class="stat-number">15+</div>
                    <div class="stat-label">Partner Prisons</div>
                </div>
                <div>
                    <div class="stat-number">85%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section style="padding: 4rem 0; background: #f8f9fa; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary-color);">Ready to Get Started?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; color: #666;">
                Join our platform today and take the first step towards accessing quality legal representation.
            </p>
            <div class="cta-buttons">
                <a href="auth/register.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                    Register Now
                </a>
                <a href="auth/login.php" class="btn btn-outline" style="padding: 1rem 2rem; font-size: 1.1rem;">
                    Login to Account
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" style="background: var(--dark-color); color: white; padding: 3rem 0; text-align: center;">
        <div class="container">
            <h3 style="margin-bottom: 1rem;">Legal Aid Beyond Bars</h3>
            <p style="margin-bottom: 2rem; opacity: 0.8;">
                Empowering justice and rehabilitation through accessible legal representation
            </p>
            <div style="margin-bottom: 2rem;">
                <p><strong>Contact Information:</strong></p>
                <p>Email: info@legalaidbeyondbars.ke</p>
                <p>Phone: +254-700-123456</p>
                <p>Address: Nairobi, Kenya</p>
            </div>
            <p style="opacity: 0.6; font-size: 0.9rem;">
                &copy; <?php echo date('Y'); ?> Legal Aid Beyond Bars. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
