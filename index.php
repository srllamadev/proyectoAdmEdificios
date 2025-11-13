<?php
require_once 'includes/functions.php';

// Si ya está logueado, redirigir a su dashboard
if (isLoggedIn()) {
    redirectToRolePage();
}

// Datos dinámicos del edificio
$edificio_stats = [
    'departamentos' => 150,
    'residentes' => 420,
    'areas_comunes' => 12,
    'años_operacion' => 8,
    'satisfaccion' => 98
];

$servicios = [
    [
        'icon' => 'fas fa-rocket',
        'titulo' => 'Tecnología Avanzada',
        'descripcion' => 'Sistema inteligente con IA integrada para gestión predictiva y automatización.'
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'titulo' => 'Seguridad Total',
        'descripcion' => 'Protección de datos de nivel empresarial con encriptación cuántica.'
    ],
    [
        'icon' => 'fas fa-leaf',
        'titulo' => 'Sustentabilidad',
        'descripcion' => 'Comprometidos con el medio ambiente y eficiencia energética máxima.'
    ]
];

$departamentos = [
    [
        'imagen' => 'departamento1.jpg',
        'titulo' => 'Departamento Tipo A',
        'precio' => '$1,200/mes',
        'caracteristicas' => ['2 Dormitorios', '1 Baño', '60m²', 'Balcón'],
        'descripcion' => 'Amplio departamento con excelente iluminación natural y vistas panorámicas.'
    ],
    [
        'imagen' => 'departamento2.jpg',
        'titulo' => 'Departamento Tipo B',
        'precio' => '$1,450/mes',
        'caracteristicas' => ['3 Dormitorios', '2 Baños', '85m²', 'Cocina Equipada'],
        'descripcion' => 'Espacioso departamento familiar con acabados de primera calidad.'
    ],
    [
        'imagen' => 'departamento3.webp',
        'titulo' => 'Loft Moderno',
        'precio' => '$1,800/mes',
        'caracteristicas' => ['1 Dormitorio', '1 Baño', '45m²', 'Altura Doble'],
        'descripcion' => 'Loft contemporáneo perfecto para profesionales jóvenes.'
    ],
    [
        'imagen' => 'departamento4.webp',
        'titulo' => 'Penthouse Premium',
        'precio' => '$2,500/mes',
        'caracteristicas' => ['4 Dormitorios', '3 Baños', '120m²', 'Terraza Privada'],
        'descripcion' => 'Exclusivo penthouse con terraza privada y acabados de lujo.'
    ],
    [
        'imagen' => 'departamento5.webp',
        'titulo' => 'Estudio Compacto',
        'precio' => '$950/mes',
        'caracteristicas' => ['1 Ambiente', '1 Baño', '35m²', 'Equipado'],
        'descripcion' => 'Estudio funcional ideal para estudiantes o profesionales.'
    ],
    [
        'imagen' => 'departamento6.jpeg',
        'titulo' => 'Departamento Familiar',
        'precio' => '$1,650/mes',
        'caracteristicas' => ['3 Dormitorios', '2 Baños', '90m²', 'Jardín'],
        'descripcion' => 'Amplio departamento familiar con acceso directo a áreas verdes.'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#00ffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Edificio Admin">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#00ffff">
    <meta name="msapplication-tap-highlight" content="no">
    <!-- PWA Links -->
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/img/icon-192.svg">
    <title>SLH - El Futuro de la Vida Urbana</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-cyan: #00ffff;
            --primary-magenta: #ff00ff;
            --primary-purple: #8a2be2;
            --dark-bg: #0a0a0a;
            --darker-bg: #050505;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --text-accent: #00ffff;
            --shadow-glow: 0 0 20px rgba(0, 255, 255, 0.3);
            --shadow-strong: 0 0 40px rgba(255, 0, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(0, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 0, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(138, 43, 226, 0.1) 0%, transparent 50%);
            animation: backgroundShift 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes backgroundShift {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(180deg); }
        }

        /* Glassmorphism Effect */
        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1rem 2rem;
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar .login-btn {
            padding: 0.5rem 1.5rem;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta));
            border: none;
            border-radius: 25px;
            color: var(--dark-bg);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .navbar .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 2rem 4rem;
            position: relative;
        }

        .hero-content {
            max-width: 800px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta), var(--primary-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: var(--shadow-strong);
            animation: textGlow 2s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { filter: brightness(1); }
            to { filter: brightness(1.2); }
        }

        .hero-subtitle {
            font-size: clamp(1rem, 3vw, 1.5rem);
            color: var(--text-secondary);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta));
            color: var(--dark-bg);
            box-shadow: var(--shadow-glow);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-cyan);
            border-color: var(--primary-cyan);
        }

        .btn-secondary:hover {
            background: var(--primary-cyan);
            color: var(--dark-bg);
            transform: translateY(-3px);
        }

        /* Stats Section */
        .stats {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.8) 0%, rgba(5, 5, 5, 0.9) 100%);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 3rem;
            color: var(--primary-cyan);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-cyan), transparent);
            transition: left 0.5s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-glow);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            font-family: 'Orbitron', monospace;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Services Section */
        .services {
            padding: 4rem 2rem;
        }

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .services-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            background: linear-gradient(45deg, var(--primary-magenta), var(--primary-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-strong);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-magenta));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-glow);
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .service-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Gallery Section */
        .gallery {
            padding: 4rem 2rem;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            background: linear-gradient(45deg, var(--primary-magenta), var(--primary-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .apartment-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .apartment-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-strong);
        }

        .apartment-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .apartment-card:hover .apartment-image {
            transform: scale(1.05);
        }

        .apartment-content {
            padding: 1.5rem;
        }

        .apartment-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .apartment-price {
            font-size: 1.5rem;
            font-weight: 900;
            font-family: 'Orbitron', monospace;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .apartment-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .feature-tag {
            background: rgba(0, 255, 255, 0.1);
            color: var(--primary-cyan);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            border: 1px solid rgba(0, 255, 255, 0.3);
        }

        .apartment-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .view-details-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-magenta));
            color: var(--dark-bg);
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
        }

        .view-details-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }

        .footer-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .footer-text {
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .footer-links a {
            color: var(--primary-cyan);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-magenta);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .hero {
                padding: 100px 1rem 2rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .stats-grid, .services-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Floating Particles Animation */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary-cyan);
            border-radius: 50%;
            animation: particleFloat 6s linear infinite;
        }

        .particle:nth-child(2n) {
            background: var(--primary-magenta);
            animation-duration: 8s;
        }

        .particle:nth-child(3n) {
            background: var(--primary-purple);
            animation-duration: 10s;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">SLH</div>
        <a href="login.php" class="login-btn">
            <i class="fas fa-sign-in-alt"></i>
            Acceder al Sistema
        </a>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">EL FUTURO DE LA VIDA URBANA</h1>
            <p class="hero-subtitle">
                Tecnología avanzada para la gestión inteligente de comunidades residenciales.
                Donde la innovación se encuentra con el confort moderno.
            </p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary">
                    <i class="fas fa-rocket"></i>
                    Comenzar Ahora
                </a>
                <a href="#servicios" class="btn-secondary">
                    <i class="fas fa-info-circle"></i>
                    Conocer Más
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-container">
            <h2 class="stats-title">Nuestros Números</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $edificio_stats['departamentos']; ?>+</div>
                    <div class="stat-label">Departamentos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $edificio_stats['residentes']; ?>+</div>
                    <div class="stat-label">Residentes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $edificio_stats['areas_comunes']; ?>+</div>
                    <div class="stat-label">Áreas Comunes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $edificio_stats['satisfaccion']; ?>%</div>
                    <div class="stat-label">Satisfacción</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="services">
        <div class="services-container">
            <h2 class="services-title">Tecnologías del Futuro</h2>
            <div class="services-grid">
                <?php foreach ($servicios as $servicio): ?>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="<?php echo $servicio['icon']; ?>"></i>
                    </div>
                    <h3 class="service-title"><?php echo $servicio['titulo']; ?></h3>
                    <p class="service-description"><?php echo $servicio['descripcion']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery">
        <div class="gallery-container">
            <h2 class="gallery-title">Nuestros Departamentos</h2>
            <div class="gallery-grid">
                <?php foreach ($departamentos as $dept): ?>
                <div class="apartment-card">
                    <img src="assets/img/<?php echo $dept['imagen']; ?>" alt="<?php echo $dept['titulo']; ?>" class="apartment-image">
                    <div class="apartment-content">
                        <h3 class="apartment-title"><?php echo $dept['titulo']; ?></h3>
                        <div class="apartment-price"><?php echo $dept['precio']; ?></div>
                        <div class="apartment-features">
                            <?php foreach ($dept['caracteristicas'] as $feature): ?>
                            <span class="feature-tag"><?php echo $feature; ?></span>
                            <?php endforeach; ?>
                        </div>
                        <p class="apartment-description"><?php echo $dept['descripcion']; ?></p>
                        <a href="login.php" class="view-details-btn">
                            <i class="fas fa-eye"></i>
                            Ver Detalles
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p class="footer-text">
                © 2025 SLH - Sistema de Administración de Edificios.
                Tecnología del futuro, hoy.
            </p>
            <div class="footer-links">
                <a href="login.php">Acceso</a>
                <a href="#servicios">Servicios</a>
                <a href="mailto:contacto@slh.com">Contacto</a>
            </div>
        </div>
    </footer>

    <!-- Floating Particles -->
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 20%; animation-delay: 1s;"></div>
    <div class="particle" style="left: 30%; animation-delay: 2s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 3s;"></div>
    <div class="particle" style="left: 50%; animation-delay: 4s;"></div>
    <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 80%; animation-delay: 7s;"></div>
    <div class="particle" style="left: 90%; animation-delay: 8s;"></div>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registrado correctamente:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('Error al registrar ServiceWorker:', error);
                    });
            });
        }

        // Smooth scrolling for anchor links
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

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards for animation
        document.querySelectorAll('.stat-card, .service-card, .apartment-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(50px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
