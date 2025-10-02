<?php
require_once 'includes/functions.php';

// Si ya está logueado, redirigir a su dashboard
if (isLoggedIn()) {
    redirectToRolePage();
}

// Datos dinámicos del edificio (podrían venir de BD en el futuro)
$edificio_stats = [
    'departamentos' => 150,
    'residentes' => 420,
    'areas_comunes' => 12,
    'años_operacion' => 8,
    'satisfaccion' => 98
];

$servicios = [
    [
        'icon' => 'fas fa-home',
        'titulo' => 'Gestión Residencial',
        'descripcion' => 'Administración completa de departamentos, alquileres y residentes con tecnología de vanguardia.'
    ],
    [
        'icon' => 'fas fa-users',
        'titulo' => 'Comunidad Conectada',
        'descripcion' => 'Sistema de comunicación integrado que mantiene a toda la comunidad informada y conectada.'
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'titulo' => 'Seguridad Avanzada',
        'descripcion' => 'Protocolos de seguridad digital y física para garantizar la tranquilidad de nuestros residentes.'
    ],
    [
        'icon' => 'fas fa-leaf',
        'titulo' => 'Sustentabilidad',
        'descripcion' => 'Comprometidos con el medio ambiente a través de tecnologías verdes y eficiencia energética.'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EdiTech Tower - El Futuro de la Vida Urbana</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-cyan: #00ffff;
            --primary-blue: #0066ff;
            --dark-bg: #0a0a0a;
            --darker-bg: #000000;
            --accent-purple: #6600cc;
            --accent-pink: #ff0066;
            --neon-green: #00ff88;
            --text-light: #ffffff;
            --text-gray: #cccccc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--dark-bg);
            color: var(--text-light);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, #000428, #004e92, #000428, #009ffd);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23ffffff" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: gridMove 20s linear infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--primary-cyan);
            border-radius: 50%;
            opacity: 0.6;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 6s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 1s; animation-duration: 8s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 2s; animation-duration: 7s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 3s; animation-duration: 9s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 4s; animation-duration: 6s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; animation-duration: 8s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 1.5s; animation-duration: 7s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 2.5s; animation-duration: 9s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 3.5s; animation-duration: 6s; }

        @keyframes float {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            25% { opacity: 0.6; }
            50% { transform: translateY(50vh) rotate(180deg); opacity: 1; }
            75% { opacity: 0.6; }
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 0;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .header.scrolled {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary-cyan);
            text-shadow: 0 0 10px var(--primary-cyan);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 800px;
            z-index: 10;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 20px;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue), var(--accent-purple));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 50px rgba(0, 255, 255, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        .hero .subtitle {
            font-size: 1.3rem;
            color: var(--text-gray);
            margin-bottom: 30px;
            opacity: 0;
            animation: fadeInUp 1s ease-out 0.5s forwards;
        }

        .hero .description {
            font-size: 1.1rem;
            color: var(--text-gray);
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeInUp 1s ease-out 1s forwards;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 20px rgba(0, 255, 255, 0.3)); }
            to { filter: drop-shadow(0 0 40px rgba(0, 255, 255, 0.6)); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CTA Buttons */
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            opacity: 0;
            animation: fadeInUp 1s ease-out 1.5s forwards;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            color: var(--darker-bg);
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 255, 255, 0.5);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-cyan);
            border: 2px solid var(--primary-cyan);
        }

        .btn-secondary:hover {
            background: var(--primary-cyan);
            color: var(--darker-bg);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 255, 255, 0.3);
        }

        /* Building Visualization */
        .building-visual {
            position: absolute;
            right: -200px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.15;
            z-index: 1;
        }

        .building {
            width: 300px;
            height: 600px;
            position: relative;
        }

        .building-floor {
            width: 100%;
            height: 60px;
            background: linear-gradient(45deg, rgba(0, 255, 255, 0.1), rgba(0, 102, 255, 0.1));
            border: 1px solid rgba(0, 255, 255, 0.3);
            margin-bottom: 5px;
            position: relative;
            animation: buildingGlow 3s ease-in-out infinite alternate;
        }

        .building-floor:nth-child(odd) {
            animation-delay: 0.5s;
        }

        .building-floor::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 10px;
            width: 8px;
            height: 8px;
            background: var(--neon-green);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--neon-green);
            animation: windowBlink 2s ease-in-out infinite;
        }

        .building-floor::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 10px;
            width: 8px;
            height: 8px;
            background: var(--neon-green);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--neon-green);
            animation: windowBlink 2s ease-in-out infinite 1s;
        }

        @keyframes buildingGlow {
            from { border-color: rgba(0, 255, 255, 0.3); }
            to { border-color: rgba(0, 255, 255, 0.8); }
        }

        @keyframes windowBlink {
            0%, 50% { opacity: 1; }
            25%, 75% { opacity: 0.3; }
        }

        /* Stats Section */
        .stats-section {
            padding: 100px 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }

        .stat-card {
            text-align: center;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(0, 255, 255, 0.3);
            transition: all 0.3s ease;
            animation: statsAnimation 0.8s ease-out forwards;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-cyan);
            box-shadow: 0 20px 40px rgba(0, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @keyframes statsAnimation {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Services Section */
        .services-section {
            padding: 100px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(45deg, var(--primary-cyan), var(--accent-purple));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--text-gray);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .service-card {
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(0, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .service-card:hover::before {
            left: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-cyan);
            box-shadow: 0 20px 40px rgba(0, 255, 255, 0.2);
        }

        .service-icon {
            font-size: 3rem;
            color: var(--primary-cyan);
            margin-bottom: 20px;
            text-shadow: 0 0 20px var(--primary-cyan);
        }

        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .service-card p {
            color: var(--text-gray);
            line-height: 1.8;
        }

        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.8);
            padding: 50px 0;
            text-align: center;
            border-top: 1px solid rgba(0, 255, 255, 0.3);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer p {
            color: var(--text-gray);
            margin-bottom: 20px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--primary-cyan);
            border-radius: 50%;
            color: var(--primary-cyan);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--primary-cyan);
            color: var(--darker-bg);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 255, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .building-visual {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Scroll indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--primary-cyan);
            font-size: 2rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg"></div>
    
    <!-- Floating Particles -->
    <div class="particles">
        <?php for ($i = 0; $i < 9; $i++): ?>
            <div class="particle"></div>
        <?php endfor; ?>
    </div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-building"></i> EdiTech Tower
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#servicios">Servicios</a></li>
                    <li><a href="#estadisticas">Estadísticas</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="inicio">
        <div class="hero-content">
            <h1>EdiTech Tower</h1>
            <p class="subtitle">El Futuro de la Vida Urbana Inteligente</p>
            <p class="description">
                Bienvenido al ecosistema residencial más avanzado del siglo XXI. 
                Donde la tecnología, la sostenibilidad y la comunidad se fusionan 
                para crear la experiencia de vida perfecta.
            </p>
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-rocket"></i> Acceder al Sistema
                </a>
                <a href="#servicios" class="btn btn-secondary">
                    <i class="fas fa-info-circle"></i> Descubrir Más
                </a>
            </div>
        </div>
        
        <!-- Building Visualization -->
        <div class="building-visual">
            <div class="building">
                <?php for ($floor = 0; $floor < 10; $floor++): ?>
                    <div class="building-floor"></div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section" id="estadisticas">
        <div class="container">
            <div class="section-title">
                <h2>Números que Nos Definen</h2>
                <p>La excelencia se refleja en cada cifra</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $edificio_stats['departamentos']; ?></span>
                    <div class="stat-label">Departamentos</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $edificio_stats['residentes']; ?></span>
                    <div class="stat-label">Residentes</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $edificio_stats['areas_comunes']; ?></span>
                    <div class="stat-label">Áreas Comunes</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $edificio_stats['años_operacion']; ?></span>
                    <div class="stat-label">Años de Excelencia</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $edificio_stats['satisfaccion']; ?>%</span>
                    <div class="stat-label">Satisfacción</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="servicios">
        <div class="container">
            <div class="section-title">
                <h2>Servicios de Vanguardia</h2>
                <p>Tecnología que transforma la experiencia residencial</p>
            </div>
            <div class="services-grid">
                <?php foreach ($servicios as $servicio): ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="<?php echo $servicio['icon']; ?>"></i>
                        </div>
                        <h3><?php echo $servicio['titulo']; ?></h3>
                        <p><?php echo $servicio['descripcion']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contacto">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> EdiTech Tower. Redefiniendo el futuro urbano.</p>
            <p>Sistema de Administración Inteligente | Versión 2.0</p>
            
            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

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

        // Stats counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200;

            counters.forEach(counter => {
                const target = parseInt(counter.innerText);
                const count = +counter.innerText;

                const increment = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(() => animateCounters(), 1);
                } else {
                    counter.innerText = target;
                }
            });
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('stats-section')) {
                        animateCounters();
                    }
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe sections for scroll animations
        document.querySelectorAll('.stats-section, .services-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(50px)';
            section.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            observer.observe(section);
        });

        // Dynamic particle generation
        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
            particle.style.animationDelay = Math.random() * 2 + 's';
            
            document.querySelector('.particles').appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 10000);
        }

        // Generate particles periodically
        setInterval(createParticle, 2000);
    </script>
</body>
</html>