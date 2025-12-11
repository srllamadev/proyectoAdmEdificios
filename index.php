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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* 🎨 Paleta Verde Esmeralda */
            --color-emerald: #009B77;
            --color-lime: #7ED957;
            --color-white: #FFFFFF;
            --color-graphite: #2F2F2F;
            --color-gold: #D4AF37;
            --color-navy: #001F54;
            
            /* Variaciones */
            --emerald-light: rgba(0, 155, 119, 0.1);
            --emerald-medium: rgba(0, 155, 119, 0.5);
            --lime-light: rgba(126, 217, 87, 0.1);
            --navy-light: rgba(0, 31, 84, 0.1);
            --gold-light: rgba(212, 175, 55, 0.1);
            
            /* Glass */
            --glass-white: rgba(255, 255, 255, 0.15);
            --glass-strong: rgba(255, 255, 255, 0.25);
            
            /* Sombras */
            --shadow-sm: 0 2px 8px rgba(47, 47, 47, 0.08);
            --shadow-md: 0 4px 16px rgba(47, 47, 47, 0.12);
            --shadow-lg: 0 8px 32px rgba(47, 47, 47, 0.16);
            --glow-emerald: 0 0 30px rgba(0, 155, 119, 0.4);
            --glow-lime: 0 0 25px rgba(126, 217, 87, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #E8F5F1 0%, #D4F1E8 30%, #C8EFE0 60%, #E1F0FF 100%);
            background-attachment: fixed;
            color: var(--color-graphite);
            overflow-x: hidden;
            line-height: 1.6;
            position: relative;
        }

        /* Fondo animado con gradientes */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 50%, var(--emerald-light) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, var(--lime-light) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, var(--navy-light) 0%, transparent 50%);
            opacity: 0.6;
            z-index: -1;
            animation: gradientShift 15s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.6; }
            50% { transform: scale(1.1) rotate(5deg); opacity: 0.8; }
        }

        /* 🍃 ANIMACIÓN DE HOJAS CAYENDO */
        .leaves-container {
            position: fixed;
            top: -100px;
            left: 0;
            width: 100%;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .leaf {
            position: absolute;
            width: 30px;
            height: 30px;
            opacity: 0;
            animation: fall linear infinite;
        }

        @keyframes fall {
            0% {
                opacity: 0;
                transform: translateY(-100px) rotate(0deg);
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                opacity: 0;
                transform: translateY(100vh) rotate(360deg);
            }
        }

        @keyframes sway {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(20px); }
            75% { transform: translateX(-20px); }
        }

        /* Glassmorphism */
        .glass {
            background: var(--glass-white);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        /* Navegación */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1.2rem 2rem;
            background: var(--glass-strong);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 155, 119, 0.2);
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md), var(--glow-emerald);
        }

        .navbar .logo {
            font-family: 'Inter', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            filter: drop-shadow(var(--glow-emerald));
        }

        .navbar .logo i {
            font-size: 2rem;
            color: var(--color-emerald);
        }

        .navbar .login-btn {
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime));
            border: none;
            border-radius: 50px;
            color: var(--color-white);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-md), var(--glow-emerald);
            font-size: 1rem;
        }

        .navbar .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg), 0 0 40px rgba(0, 155, 119, 0.6);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 140px 2rem 4rem;
            position: relative;
        }

        .hero-content {
            max-width: 900px;
            animation: fadeInUp 1s ease-out;
            position: relative;
            z-index: 2;
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
            font-family: 'Inter', sans-serif;
            font-size: clamp(2.5rem, 8vw, 5.5rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime), var(--color-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(var(--glow-emerald));
            line-height: 1.1;
        }

        .hero-subtitle {
            font-size: clamp(1.1rem, 3vw, 1.6rem);
            color: var(--color-graphite);
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2.5rem;
        }

        .btn-primary, .btn-secondary {
            padding: 1.2rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime));
            color: var(--color-white);
            box-shadow: var(--shadow-md), var(--glow-emerald);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg), 0 0 40px rgba(0, 155, 119, 0.6);
        }

        .btn-secondary {
            background: var(--glass-white);
            backdrop-filter: blur(12px);
            color: var(--color-emerald);
            border-color: var(--color-emerald);
        }

        .btn-secondary:hover {
            background: var(--color-emerald);
            color: var(--color-white);
            transform: translateY(-3px);
            box-shadow: var(--glow-emerald);
        }

        /* Stats Section */
        .stats {
            padding: 5rem 2rem;
            background: var(--glass-white);
            backdrop-filter: blur(20px);
        }

        .stats-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: var(--glass-white);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 2.5rem;
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
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--color-emerald), var(--color-lime), transparent);
            transition: left 0.6s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg), var(--glow-emerald);
            border-color: rgba(0, 155, 119, 0.5);
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 900;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
            filter: drop-shadow(var(--glow-emerald));
        }

        .stat-label {
            font-size: 1rem;
            color: var(--color-graphite);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Services Section */
        .services {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(0, 155, 119, 0.05) 100%);
        }

        .services-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .services-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--color-navy), var(--color-emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
        }

        .service-card {
            background: var(--glass-white);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-emerald), var(--color-lime));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .service-card:hover::after {
            transform: scaleX(1);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl), var(--glow-emerald);
            border-color: rgba(0, 155, 119, 0.5);
        }

        .service-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--color-emerald), var(--color-lime));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: white;
            box-shadow: var(--glow-emerald);
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        .service-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--color-graphite);
        }

        .service-description {
            color: var(--color-graphite);
            line-height: 1.7;
            opacity: 0.8;
        }

        /* Gallery Section */
        .gallery {
            padding: 5rem 2rem;
        }

        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .gallery-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--color-gold), var(--color-emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 2.5rem;
        }

        .apartment-card {
            background: var(--glass-white);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .apartment-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-xl), var(--glow-lime);
        }

        .apartment-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .apartment-card:hover .apartment-image {
            transform: scale(1.1);
        }

        .apartment-content {
            padding: 2rem;
        }

        .apartment-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--color-graphite);
        }

        .apartment-price {
            font-size: 1.75rem;
            font-weight: 900;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--color-gold), var(--color-emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .apartment-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .feature-tag {
            background: linear-gradient(135deg, var(--emerald-light), rgba(126, 217, 87, 0.1));
            color: var(--color-emerald);
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            border: 1px solid rgba(0, 155, 119, 0.3);
            font-weight: 500;
        }

        .apartment-description {
            color: var(--color-graphite);
            font-size: 0.95rem;
            line-height: 1.6;
            opacity: 0.8;
        }

        .view-details-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--color-lime), var(--color-emerald));
            color: var(--color-white);
            padding: 0.9rem 1.75rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1.25rem;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
            box-shadow: var(--glow-lime);
        }

        .view-details-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg), 0 0 30px rgba(126, 217, 87, 0.5);
        }

        /* Footer */
        footer {
            background: var(--glass-white);
            backdrop-filter: blur(20px);
            padding: 3rem 2rem;
            border-top: 1px solid rgba(0, 155, 119, 0.2);
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .footer-text {
            color: var(--color-graphite);
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--color-emerald);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links a:hover {
            color: var(--color-lime);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .hero {
                padding: 120px 1rem 2rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                max-width: 320px;
                justify-content: center;
            }

            .stats-grid, .services-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
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
            background: var(--color-emerald);
            border-radius: 50%;
            animation: particleFloat 6s linear infinite;
            box-shadow: var(--glow-emerald);
        }

        .particle:nth-child(2n) {
            background: var(--color-lime);
            animation-duration: 8s;
        }

        .particle:nth-child(3n) {
            background: var(--color-gold);
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
    <!-- 🍃 Hojas Cayendo -->
    <div class="leaves-container" id="leavesContainer"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            SLH
        </div>
        <a href="login.php" class="login-btn">
            <i class="fas fa-sign-in-alt"></i>
            Acceder al Sistema
        </a>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">EL FUTURO VERDE DE LA VIDA URBANA</h1>
            <p class="hero-subtitle">
                Tecnología sustentable para la gestión inteligente de comunidades residenciales.
                Donde la innovación se encuentra con el respeto al medio ambiente.
            </p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary">
                    <i class="fas fa-seedling"></i>
                    Comenzar Ahora
                </a>
                <a href="#servicios" class="btn-secondary">
                    <i class="fas fa-leaf"></i>
                    Conocer Más
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-container">
            <h2 class="stats-title">
                <i class="fas fa-chart-line"></i>
                Nuestros Números
            </h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-building"></i>
                        <?php echo $edificio_stats['departamentos']; ?>+
                    </div>
                    <div class="stat-label">Departamentos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-users"></i>
                        <?php echo $edificio_stats['residentes']; ?>+
                    </div>
                    <div class="stat-label">Residentes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-tree"></i>
                        <?php echo $edificio_stats['areas_comunes']; ?>+
                    </div>
                    <div class="stat-label">Áreas Verdes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-heart"></i>
                        <?php echo $edificio_stats['satisfaccion']; ?>%
                    </div>
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
                <i class="fas fa-leaf"></i>
                © 2025 SLH - Sistema de Administración de Edificios Sustentable.
                Tecnología verde para un futuro mejor.
            </p>
            <div class="footer-links">
                <a href="login.php">
                    <i class="fas fa-sign-in-alt"></i>
                    Acceso
                </a>
                <a href="#servicios">
                    <i class="fas fa-seedling"></i>
                    Servicios
                </a>
                <a href="mailto:contacto@slh.com">
                    <i class="fas fa-envelope"></i>
                    Contacto
                </a>
            </div>
        </div>
    </footer>

    <!-- PWA Service Worker Registration -->
    <script>
        // 🍃 ANIMACIÓN DE HOJAS CAYENDO
        function createLeaves() {
            const leavesContainer = document.getElementById('leavesContainer');
            const numberOfLeaves = 15; // Cantidad de hojas
            
            // Array de SVG de hojas con diferentes estilos
            const leafSVGs = [
                // Hoja 1 - Maple
                `<svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/>
                </svg>`,
                // Hoja 2 - Simple
                `<svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12,3L2,12L5,15L12,21L19,15L22,12L12,3M12,5.7L17.6,12L16,13.6L12,10.3L8,13.6L6.4,12L12,5.7Z"/>
                </svg>`,
                // Hoja 3 - Oak
                `<svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11,2.5C6.5,2.5 3,5.5 3,9.5C3,11.5 4,13.5 5,14.5C5.5,15 6,15.5 6,16C6,16.5 5.5,17 5,17.5C4,18.5 3,20 3,22H21C21,20 20,18.5 19,17.5C18.5,17 18,16.5 18,16C18,15.5 18.5,15 19,14.5C20,13.5 21,11.5 21,9.5C21,5.5 17.5,2.5 13,2.5H11Z"/>
                </svg>`,
                // Hoja 4 - Rounded
                `<svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12,2C6.5,2 2,6.5 2,12C2,17.5 6.5,22 12,22C12,19 12,16 12,13C12,10 12,7 12,4C12,3.33 12,2.67 12,2M12,2C17.5,2 22,6.5 22,12C22,17.5 17.5,22 12,22"/>
                </svg>`
            ];
            
            const leafColors = [
                'rgba(0, 155, 119, 0.7)',   // Verde Esmeralda
                'rgba(126, 217, 87, 0.7)',  // Verde Claro
                'rgba(212, 175, 55, 0.6)',  // Dorado
                'rgba(0, 155, 119, 0.5)',   // Verde Esmeralda transparente
                'rgba(126, 217, 87, 0.5)'   // Verde Claro transparente
            ];
            
            for (let i = 0; i < numberOfLeaves; i++) {
                const leaf = document.createElement('div');
                leaf.className = 'leaf';
                
                // Seleccionar SVG y color aleatorio
                const randomSVG = leafSVGs[Math.floor(Math.random() * leafSVGs.length)];
                const randomColor = leafColors[Math.floor(Math.random() * leafColors.length)];
                
                leaf.innerHTML = randomSVG;
                leaf.style.color = randomColor;
                leaf.style.left = Math.random() * 100 + '%';
                leaf.style.animationDuration = (Math.random() * 10 + 15) + 's'; // Entre 15-25 segundos
                leaf.style.animationDelay = Math.random() * 5 + 's';
                
                // Añadir efecto de balanceo aleatorio
                const swayAnimation = Math.random() * 3 + 2; // Entre 2-5 segundos
                leaf.style.animation = `fall ${leaf.style.animationDuration} linear infinite, sway ${swayAnimation}s ease-in-out infinite`;
                
                leavesContainer.appendChild(leaf);
            }
        }
        
        // Inicializar hojas cuando cargue la página
        window.addEventListener('load', createLeaves);

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
