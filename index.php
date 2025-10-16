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
    <title>SLH - El Futuro de la Vida Urbana</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/bento-style.css">
    <style>
        /* Estilos específicos del landing page */
        .hero-section {
            background: linear-gradient(135deg, var(--color-dark-blue), var(--color-pink));
            color: var(--text-white);
            padding: var(--spacing-2xl) 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="30" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-lg);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: var(--font-size-xl);
            opacity: 0.9;
            margin-bottom: var(--spacing-xl);
        }

        .hero-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            flex-wrap: wrap;
        }

        .stats-section {
            background: var(--bg-primary);
            padding: var(--spacing-2xl) 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-xl);
            max-width: 1000px;
            margin: 0 auto;
        }

        .stat-card {
            text-align: center;
            padding: var(--spacing-xl);
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .stat-number {
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            color: var(--color-dark-blue);
            margin-bottom: var(--spacing-sm);
        }

        .stat-label {
            font-size: var(--font-size-lg);
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .services-section {
            background: var(--bg-secondary);
            padding: var(--spacing-2xl) 0;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-xl);
            max-width: 1000px;
            margin: 0 auto;
        }

        .service-card {
            background: var(--bg-primary);
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: var(--transition-normal);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .service-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--color-pink), var(--color-dark-blue));
            border-radius: var(--border-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
            color: var(--text-white);
            font-size: var(--font-size-2xl);
        }

        .service-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin-bottom: var(--spacing-md);
        }

        .service-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .gallery-section {
            background: var(--bg-accent);
            padding: var(--spacing-2xl) 0;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-lg);
            max-width: 1000px;
            margin: 0 auto;
        }

        .gallery-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition-normal);
            cursor: pointer;
        }

        .gallery-card:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .gallery-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .gallery-content {
            padding: var(--spacing-lg);
        }

        .gallery-title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }

        .gallery-description {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
        }

        .features-section {
            background: var(--bg-primary);
            padding: var(--spacing-2xl) 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-xl);
            max-width: 1000px;
            margin: 0 auto;
        }

        .feature-card {
            background: linear-gradient(135deg, var(--color-blush), var(--color-beige));
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            font-size: var(--font-size-3xl);
            color: var(--color-dark-blue);
            margin-bottom: var(--spacing-lg);
        }

        .feature-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin-bottom: var(--spacing-md);
        }

        .feature-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--color-dark-blue), var(--color-pink));
            color: var(--text-white);
            padding: var(--spacing-2xl) 0;
            text-align: center;
        }

        .cta-title {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-lg);
        }

        .cta-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }

        .modal-content {
            background-color: var(--bg-primary);
            margin: 5% auto;
            padding: 0;
            border-radius: var(--border-radius-xl);
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-xl);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--color-pink), var(--color-dark-blue));
            color: var(--text-white);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-xl) var(--border-radius-xl) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: var(--spacing-xl);
        }

        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: var(--border-radius-md);
            margin-bottom: var(--spacing-lg);
        }

        .close {
            color: var(--text-white);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .close:hover {
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: var(--font-size-3xl);
            }

            .hero-buttons,
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .stats-grid,
            .services-grid,
            .gallery-grid,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }

        /* Animations */
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

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
    </link>

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

        /* Gallery Section */
        .gallery-section {
            padding: 100px 0;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .apartment-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 255, 0.2);
            transition: all 0.5s ease;
            position: relative;
            height: 400px;
            cursor: pointer;
        }

        .apartment-card:hover {
            transform: translateY(-15px) scale(1.02);
            border-color: var(--primary-cyan);
            box-shadow: 0 25px 50px rgba(0, 255, 255, 0.3);
        }

        .apartment-image {
            width: 100%;
            height: 70%;
            object-fit: cover;
            transition: all 0.5s ease;
            filter: brightness(0.8);
        }

        .apartment-card:hover .apartment-image {
            filter: brightness(1) saturate(1.2);
            transform: scale(1.1);
        }

        .apartment-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
            backdrop-filter: blur(10px);
            padding: 20px;
            border-top: 1px solid rgba(0, 255, 255, 0.3);
        }

        .apartment-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 8px;
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .apartment-features {
            display: flex;
            gap: 15px;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .apartment-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 255, 255, 0.1), rgba(102, 126, 234, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .apartment-card:hover .apartment-overlay {
            opacity: 1;
        }

        .view-btn {
            background: linear-gradient(45deg, var(--primary-cyan), var(--primary-blue));
            color: var(--darker-bg);
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .apartment-card:hover .view-btn {
            transform: translateY(0);
        }

        .view-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 255, 255, 0.4);
        }

        /* Gallery Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
        }

        .modal-content {
            position: relative;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 800px;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 255, 0.3);
        }

        .modal-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .modal-info {
            padding: 30px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
        }

        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: var(--primary-cyan);
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close:hover {
            background: rgba(0, 255, 255, 0.2);
            transform: scale(1.1);
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(0, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-cyan);
            box-shadow: 0 20px 40px rgba(0, 255, 255, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-cyan);
            margin-bottom: 20px;
            text-shadow: 0 0 20px var(--primary-cyan);
        }

        .feature-card h3 {
            color: var(--text-light);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .feature-card p {
            color: var(--text-gray);
            line-height: 1.6;
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

        /* Animations */
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

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
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
    <header class="bento-header">
        <div class="bento-container">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-md">
                    <i class="fas fa-building text-3xl text-white"></i>
                    <span class="bento-title">SLH</span>
                </div>
                <nav class="bento-nav">
                    <ul class="flex">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#departamentos">Departamentos</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#estadisticas">Estadísticas</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                        <li><a href="finanzas.php">Finanzas</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section" id="inicio">
        <div class="bento-container">
            <div class="hero-content">
                <h1 class="hero-title">🏠 Smart Living Hub</h1>
                <p class="hero-subtitle">El Futuro de la Vida Urbana Inteligente</p>
                <p class="text-lg mb-xl text-center max-w-2xl mx-auto">
                    Bienvenido al ecosistema residencial más avanzado del siglo XXI.
                    Donde la tecnología, la sostenibilidad y la comunidad se fusionan
                    para crear la experiencia de vida perfecta.
                </p>
                <div class="hero-buttons">
                    <a href="login.php" class="bento-btn bento-btn-primary">
                        <i class="fas fa-rocket"></i> Acceder al Sistema
                    </a>
                    <a href="#servicios" class="bento-btn bento-btn-secondary">
                        <i class="fas fa-info-circle"></i> Descubrir Más
                    </a>
                </div>
            </div>
        </div>

        <div class="scroll-indicator text-center mt-2xl">
            <i class="fas fa-chevron-down text-2xl animate-bounce"></i>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section" id="estadisticas">
        <div class="bento-container">
            <div class="text-center mb-2xl">
                <h2 class="text-4xl font-bold text-primary mb-md">Números que Nos Definen</h2>
                <p class="text-xl text-secondary">La excelencia se refleja en cada cifra</p>
            </div>
            <div class="bento-stats">
                <div class="bento-stat-card">
                    <div class="bento-stat-number"><?php echo $edificio_stats['departamentos']; ?></div>
                    <div class="bento-stat-label">Departamentos</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number"><?php echo $edificio_stats['residentes']; ?></div>
                    <div class="bento-stat-label">Residentes</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number"><?php echo $edificio_stats['areas_comunes']; ?></div>
                    <div class="bento-stat-label">Áreas Comunes</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number"><?php echo $edificio_stats['años_operacion']; ?></div>
                    <div class="bento-stat-label">Años de Excelencia</div>
                </div>
                <div class="bento-stat-card">
                    <div class="bento-stat-number"><?php echo $edificio_stats['satisfaccion']; ?>%</div>
                    <div class="bento-stat-label">Satisfacción</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery-section" id="departamentos">
        <div class="bento-container">
            <div class="text-center mb-2xl">
                <h2 class="text-4xl font-bold text-primary mb-md">Nuestros Departamentos Exclusivos</h2>
                <p class="text-xl text-secondary">Espacios diseñados para el futuro, donde la comodidad y la tecnología se encuentran</p>
            </div>
            <div class="gallery-grid">
                <?php 
                $departamentos = [
                    [
                        'image' => 'assets/img/departamento1.jpg',
                        'title' => 'Loft Moderno Premium',
                        'bedrooms' => 2,
                        'bathrooms' => 2,
                        'area' => 85,
                        'description' => 'Diseño contemporáneo con amplios ventanales y acabados de lujo. Vista panorámica de la ciudad.'
                    ],
                    [
                        'image' => 'assets/img/departamento2.jpg',
                        'title' => 'Suite Ejecutiva',
                        'bedrooms' => 1,
                        'bathrooms' => 1,
                        'area' => 65,
                        'description' => 'Perfecto para profesionales. Diseño minimalista con tecnología integrada y espacios optimizados.'
                    ],
                    [
                        'image' => 'assets/img/departamento3.webp',
                        'title' => 'Penthouse Deluxe',
                        'bedrooms' => 3,
                        'bathrooms' => 3,
                        'area' => 120,
                        'description' => 'La máxima expresión del lujo urbano. Terraza privada y vistas espectaculares de 360°.'
                    ],
                    [
                        'image' => 'assets/img/departamento4.webp',
                        'title' => 'Apartamento Familiar',
                        'bedrooms' => 3,
                        'bathrooms' => 2,
                        'area' => 95,
                        'description' => 'Espacioso y funcional, diseñado pensando en la comodidad familiar con áreas de juego integradas.'
                    ],
                    [
                        'image' => 'assets/img/departamento5.webp',
                        'title' => 'Studio Smart',
                        'bedrooms' => 1,
                        'bathrooms' => 1,
                        'area' => 45,
                        'description' => 'Inteligente aprovechamiento del espacio con domótica avanzada y diseño multifuncional.'
                    ],
                    [
                        'image' => 'assets/img/departamento6.jpeg',
                        'title' => 'Dúplex Innovador',
                        'bedrooms' => 4,
                        'bathrooms' => 3,
                        'area' => 140,
                        'description' => 'Dos niveles de pura sofisticación. Escalera flotante y doble altura en living principal.'
                    ],
                    [
                        'image' => 'assets/img/departamento7.jpg',
                        'title' => 'Loft Industrial Chic',
                        'bedrooms' => 2,
                        'bathrooms' => 2,
                        'area' => 90,
                        'description' => 'Estilo industrial moderno con techos altos, vigas expuestas y acabados en acero y cristal.'
                    ],
                    [
                        'image' => 'assets/img/departamento8.jpg',
                        'title' => 'Apartamento Zen',
                        'bedrooms' => 2,
                        'bathrooms' => 2,
                        'area' => 80,
                        'description' => 'Diseño inspirado en la filosofía zen. Espacios abiertos y conexión con elementos naturales.'
                    ],
                    [
                        'image' => 'assets/img/departamento9.jpg',
                        'title' => 'Apartamento Zen',
                        'bedrooms' => 2,
                        'bathrooms' => 2,
                        'area' => 80,
                        'description' => 'Diseño inspirado en la filosofía zen. Espacios abiertos y conexión con elementos naturales.'
                    ]
                ];
                ?>
                
                <?php foreach ($departamentos as $index => $depto): ?>
                    <div class="gallery-card" onclick="openModal(<?php echo $index; ?>)">
                        <img src="<?php echo $depto['image']; ?>" alt="<?php echo $depto['title']; ?>" class="gallery-image">
                        <div class="gallery-content">
                            <div class="gallery-title"><?php echo $depto['title']; ?></div>
                            <div class="flex gap-sm text-sm text-secondary mb-sm">
                                <span><i class="fas fa-bed"></i> <?php echo $depto['bedrooms']; ?> hab</span>
                                <span><i class="fas fa-bath"></i> <?php echo $depto['bathrooms']; ?> baños</span>
                                <span><i class="fas fa-ruler-combined"></i> <?php echo $depto['area']; ?>m²</span>
                            </div>
                            <div class="gallery-description"><?php echo $depto['description']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="bento-container">
            <div class="text-center mb-2xl">
                <h2 class="text-4xl font-bold text-primary mb-md">Características Premium</h2>
                <p class="text-xl text-secondary">Amenidades que definen un estilo de vida excepcional</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h3>Internet Ultra-rápido</h3>
                    <p>Conexión de fibra óptica de 1GB en todos los departamentos</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Estacionamiento Inteligente</h3>
                    <p>Sistema automatizado de parqueo con sensores y app móvil</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Gimnasio Premium</h3>
                    <p>Equipamiento de última generación disponible 24/7</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-swimming-pool"></i>
                    </div>
                    <h3>Piscina Climatizada</h3>
                    <p>Área de relajación con sistema de climatización inteligente</p>
                </div>
                
            </div>
        </div>

    <!-- Services Section -->
    <section class="services-section" id="servicios">
        <div class="bento-container">
            <div class="text-center mb-2xl">
                <h2 class="text-4xl font-bold text-primary mb-md">Servicios de Vanguardia</h2>
                <p class="text-xl text-secondary">Tecnología que transforma la experiencia residencial</p>
            </div>
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

    <!-- Footer -->
    <footer class="bento-section bento-footer" id="contacto">
        <div class="bento-container text-center">
            <p class="text-lg mb-md">&copy; <?php echo date('Y'); ?> SLH. Redefiniendo el futuro urbano.</p>
            <p class="text-secondary mb-xl">Sistema de Administración Inteligente | Versión 2.0</p>

            <div class="flex justify-center gap-lg">
                <a href="#" class="text-2xl text-white hover:text-pink transition-fast"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-2xl text-white hover:text-pink transition-fast"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-2xl text-white hover:text-pink transition-fast"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-2xl text-white hover:text-pink transition-fast"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>

    <!-- Modal para galería  -->
    <div id="galleryModal" class="bento-modal">
        <div class="bento-modal-content">
            <span class="bento-modal-close" onclick="closeModal()">&times;</span>
            <img id="modalImage" class="bento-modal-image" src="" alt="">
            <div class="bento-modal-info">
                <h3 id="modalTitle" class="bento-modal-title"></h3>
                <div id="modalFeatures" class="bento-modal-features"></div>
                <p id="modalDescription" class="bento-modal-description"></p>
            </div>
        </div>
    </div>

    <script>
        // Datos de departamentos para el modal
        const departamentos = <?php echo json_encode($departamentos); ?>;

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

        // Gallery Modal Functions
        function openModal(index) {
            const depto = departamentos[index];
            const modal = document.getElementById('galleryModal');
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalFeatures = document.getElementById('modalFeatures');
            const modalDescription = document.getElementById('modalDescription');

            modalImage.src = depto.image;
            modalImage.alt = depto.title;
            modalTitle.textContent = depto.title;
            modalDescription.textContent = depto.description;
            
            modalFeatures.innerHTML = `
                <div class="bento-modal-feature">
                    <i class="fas fa-bed"></i>
                    <span>${depto.bedrooms} Habitaciones</span>
                </div>
                <div class="bento-modal-feature">
                    <i class="fas fa-bath"></i>
                    <span>${depto.bathrooms} Baños</span>
                </div>
                <div class="bento-modal-feature">
                    <i class="fas fa-ruler-combined"></i>
                    <span>${depto.area}m² Área Total</span>
                </div>
            `;

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('galleryModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('galleryModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Stats counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            const speed = 200;

            counters.forEach(counter => {
                const target = parseInt(counter.innerText);
                let count = 0;
                const increment = target / speed;

                function updateCounter() {
                    if (count < target) {
                        count += increment;
                        counter.innerText = Math.ceil(count);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = target;
                    }
                }
                updateCounter();
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
        document.querySelectorAll('.stats-section, .services-section, .gallery-section, .features-section').forEach(section => {
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

        // Parallax effect for building
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const building = document.querySelector('.building-visual');
            if (building) {
                building.style.transform = `translateY(${scrolled * 0.1}px)`;
            }
        });

        // Lazy loading for images
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '1';
                    img.style.transform = 'scale(1)';
                    observer.unobserve(img);
                }
            });
        });

        // Observe apartment images
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.apartment-image').forEach(img => {
                img.style.opacity = '0.7';
                img.style.transform = 'scale(0.95)';
                img.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                imageObserver.observe(img);
            });
        });
    </script>
</body>
</html>

