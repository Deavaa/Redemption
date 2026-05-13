e down the schoond
cd /c/xampp/htdocs/school-of-redemption && echo "PD9waHAKCm5hbWVzcGFjZSBEYXRhYmFzZVxTZWVkZXJzOwoKdXNlIElsbHVtaW5hdGVcRGF0YWJhc2VcU2VlZGVyOwp1c2UgSWxsdW1pbmF0ZVxTdXBwb3J0XEZhY2FkZXNcSGFzaDsKdXNlIEFwcFxNb2RlbHNcVXNlcjsKCmNsYXNzIERlbW9Vc2VyU2VlZGVyIGV4dGVuZHMgU2VlZGVyCnsKICAgIHB1YmxpYyBmdW5jdGlvbiBydW4oKTogdm9pZAogICAgewogICAgICAgICR1c2VycyA9IFsKICAgICAgICAgICAgWydOYW1lJyA9PiAnU3lzdGVtIEFkbWluaXN0cmF0b3InLCAnZW1haWwnID0+ICdhZG1pbkBzY2hvb2xvZnJlZGVtcHRpb24uY29tJywgJ3Bhc3N3b3JkJyA9PiBIYXNoOjptYWtlKCdwYXNzd29yZCcpLCAncm9sZScgPT4gJ2FkbWluJ10sCiAgICAgICAgICAgIFsnTmFtZScgPT4gJ01yLiBKb2huIFNtaXRoJywgJ2VtYWlsJyA9PiAnam9obi5zbWl0aEBzY2hvb2xvZnJlZGVtcHRpb24uY29tJywgJ3Bhc3N3b3JkJyA9PiBIYXNoOjptYWtlKCdwYXNzd29yZCcpLCAncm9sZScgPT4gJ3RlYWNoZXInXSwKICAgICAgICAgICAgWydOYW1lJyA9PiAnTXMuIE1hcnkgSm9obnNvbicsICdlbWFpbCcgPT4gJ21hcnkuam9obnNvbkBzY2hvb2xvZnJlZGVtcHRpb24uY29tJywgJ3Bhc3N3b3JkJyA9PiBIYXNoOjptYWtlKCdwYXNzd29yZCcpLCAncm9sZScgPT4gJ3RlYWNoZXInXSwKICAgICAgICAgICAgWydOYW1lJyA9PiAnTXIuIFBldGVyIFdpbGxpYW1zJywgJ2VtYWlsJyA9PiAncGV0ZXIud2lsbGlhbXNAc2Nob29sb2ZyZWRlbXB0aW9uLmNvbScsICdwYXNzd29yZCcgPT4gSGFzaDo6bWFrZSgncGFzc3dvcmQnKSwgJ3JvbGUnID0+ICdzdGFmZiddLAogICAgICAgIF07CgogICAgICAgIGZvcmVhY2ggKCR1c2VycyBhcyAkdSkgewogICAgICAgICAgICBVc2VyOjpmaXJzdE9yQ3JlYXRlKFsnZW1haWwnID0+ICR1WydlbWFpbCddXSwgJHUpOwogICAgICAgIH0KCiAgICAgICAgJHRoaXMtPmNvbW1hbmQtPmluZm8oJ0RlbW8gdXNlcnMgc2VlZGVkIScpOwogICAgICAgICR0aGlzLT5jb21tYW5kLT5pbmZvKCdBZG1pbjogIGFkbWluQHNjaG9vbG9mcmVkZW1wdGlvbi5jb20gLyBwYXNzd29yZCcpOwogICAgICAgICR0aGlzLT5jb21tYW5kLT5pbmZvKCdUZWFjaGVyOiBqb2huLnNtaXRoQHNjaG9vbG9mcmVkZW1wdGlvbi5jb20gLyBwYXNzd29yZCcpOwogICAgICAgICR0aGlzLT5jb21tYW5kLT5pbmZvKCdUZWFjaGVyOiBtYXJ5LmpvaG5zb25Ac2Nob29sb2ZyZWRlbXB0aW9uLmNvbSAvIHBhc3N3b3JkJyk7CiAgICAgICAgJHRoaXMtPmNvbW1hbmQtPmluZm8oJ1N0YWZmOiAgIHBldGVyLndpbGxpYW1zQHNjaG9vbG9mcmVkZW1wdGlvbi5jb20gLyBwYXNzd29yZCcpOwogICAgfQp9Cg==" | base64 -d > database/seeders/DemoUserSeeder.php<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $settings['school_name'] }} - {{ $settings['school_tagline'] }}">
    <title>{{ $settings['school_name'] }} - {{ $settings['school_tagline'] }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $settings['primary_color'] ?? '#0d0d2b' }};
            --secondary-color: {{ $settings['secondary_color'] ?? '#c9a84c' }};
            --accent-color: #198754;
            --text-dark: #1a1a2e;
            --text-light: #6c757d;
            --white: #ffffff;
            --light-bg: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }
        
        /* Navigation */
        .navbar {
            background: rgba(13, 13, 43, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1;
        }
        
        .navbar-brand .brand-pre {
            color: var(--white);
            font-weight: 400;
            font-size: 0.7rem;
            display: block;
            line-height: 1;
        }
        
        .navbar-brand .brand-name {
            color: var(--secondary-color);
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--secondary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 80%;
        }
        
        .btn-nav-portal {
            background: var(--secondary-color);
            color: var(--primary-color) !important;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-nav-portal:hover {
            background: #e0c060;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(201, 168, 76, 0.4);
        }
        
        /* Hero Slider Section */
        .hero-slider {
            position: relative;
            min-height: 100vh;
        }
        
        .hero-slider .carousel,
        .hero-slider .carousel-inner,
        .hero-slider .carousel-item {
            height: 100vh;
            min-height: 600px;
        }
        
        .hero-slide {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(13, 13, 43, 0.85) 0%, rgba(13, 13, 43, 0.65) 100%);
        }
        
        .hero-slider .carousel-item {
            position: relative;
        }
        
        .hero-slider .carousel-item .container {
            position: relative;
            z-index: 2;
        }
        
        .hero-slider .carousel-control-prev,
        .hero-slider .carousel-control-next {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            top: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .hero-slider:hover .carousel-control-prev,
        .hero-slider:hover .carousel-control-next {
            opacity: 1;
        }
        
        .hero-slider .carousel-control-prev {
            left: 30px;
        }
        
        .hero-slider .carousel-control-next {
            right: 30px;
        }
        
        .hero-slider .carousel-control-prev-icon,
        .hero-slider .carousel-control-next-icon {
            width: 24px;
            height: 24px;
        }
        
        .hero-slider .carousel-indicators {
            bottom: 30px;
        }
        
        .hero-slider .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 6px;
            background: rgba(255, 255, 255, 0.5);
            border: none;
            transition: all 0.3s ease;
        }
        
        .hero-slider .carousel-indicators button.active {
            background: var(--secondary-color);
            width: 30px;
            border-radius: 6px;
        }
        
        .hero-content {
            color: var(--white);
            padding: 4rem 0;
        }
        
        .hero-badge {
            display: inline-block;
            background: rgba(201, 168, 76, 0.2);
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--white);
        }
        
        .hero h1 span {
            color: var(--secondary-color);
        }
        
        .hero p {
            font-size: 1.2rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-hero-primary {
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-hero-primary:hover {
            background: #e0c060;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(201, 168, 76, 0.4);
        }
        
        .btn-hero-secondary {
            background: transparent;
            color: var(--white);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            border: 2px solid rgba(255,255,255,0.5);
            transition: all 0.3s ease;
        }
        
        .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--white);
            color: var(--white);
        }
        
        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
        }
        
        .stat-item p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            margin: 0;
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
            background: var(--white);
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-badge {
            display: inline-block;
            background: rgba(201, 168, 76, 0.1);
            color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .section-header p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #eee;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: transparent;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), #1a1a4e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .feature-icon i {
            font-size: 2rem;
            color: var(--secondary-color);
        }
        
        .feature-card h4 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .feature-card p {
            color: var(--text-light);
            font-size: 0.95rem;
        }
        
        /* About Section */
        .about {
            padding: 6rem 0;
            background: var(--light-bg);
        }
        
        .about-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }
        
        .about-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }
        
        .about-badge {
            position: absolute;
            bottom: -20px;
            right: -20px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            min-width: 150px;
        }
        
        .about-badge h3 {
            font-size: 2.5rem;
            margin: 0;
            line-height: 1;
        }
        
        .about-badge p {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .about-content h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .about-content p {
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }
        
        .about-features {
            list-style: none;
            padding: 0;
        }
        
        .about-features li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .about-features li i {
            color: var(--secondary-color);
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        /* Programs Section */
        .programs {
            padding: 6rem 0;
            background: var(--white);
        }
        
        .program-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .program-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }
        
        .program-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .program-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(13,13,43,0.5), transparent);
        }
        
        .program-content {
            padding: 2rem;
        }
        
        .program-tag {
            display: inline-block;
            background: rgba(201, 168, 76, 0.1);
            color: var(--secondary-color);
            padding: 0.25rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .program-content h4 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }
        
        .program-content p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        
        .program-link {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .program-link:hover {
            color: var(--primary-color);
        }
        
        /* Gallery Section */
        .gallery {
            padding: 6rem 0;
            background: var(--light-bg);
        }
        
        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            cursor: pointer;
            aspect-ratio: 4/3;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(13, 13, 43, 0.8) 0%, rgba(201, 168, 76, 0.8) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-overlay i {
            font-size: 2rem;
            color: var(--white);
        }
        
        /* Team Section */
        .team {
            padding: 6rem 0;
            background: var(--white);
        }
        
        .team-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }
        
        .team-image {
            position: relative;
            overflow: hidden;
            height: 280px;
        }
        
        .team-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .team-card:hover .team-image img {
            transform: scale(1.05);
        }
        
        .team-content {
            padding: 1.5rem;
            background: var(--white);
        }
        
        .team-content h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }
        
        .team-content p {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .team-social {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .team-social a {
            width: 35px;
            height: 35px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            transition: all 0.3s ease;
        }
        
        .team-social a:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
        }
        
        /* CTA Section */
        .cta {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a1a4e 100%);
            color: var(--white);
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--white);
        }
        
        .cta p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.8);
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        
        /* Footer */
        .footer {
            background: var(--primary-color);
            color: var(--white);
            padding: 4rem 0 2rem;
        }
        
        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .footer-brand .brand-pre {
            font-weight: 400;
        }
        
        .footer-brand .brand-name {
            color: var(--secondary-color);
            font-weight: 700;
        }
        
        .footer p {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
        
        .footer h5 {
            color: var(--white);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.75rem;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: var(--secondary-color);
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 2rem;
            margin-top: 3rem;
            text-align: center;
        }
        
        .footer-bottom p {
            margin: 0;
            font-size: 0.85rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .about-badge {
                position: static;
                margin-top: 1rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .cta h2 {
                font-size: 2rem;
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
        
        .animate-fade-up {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                @if($settings['school_logo'] && file_exists(public_path('storage/' . $settings['school_logo'])))
                    <img src="{{ asset('storage/' . $settings['school_logo']) }}" alt="{{ $settings['school_name'] }}" style="height: 45px; margin-right: 10px;">
                @endif
                <span class="brand-pre">{{ Str::beforeLast($settings['school_name'], ' ') }}<br></span>
                <span class="brand-name">{{ Str::afterLast($settings['school_name'], ' ') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#team">Our Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-nav-portal" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Slider Section -->
    <section class="hero-slider" id="home">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-pause="false">
            <div class="carousel-indicators">
                @forelse($sliders as $index => $slider)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" @if($index === 0) class="active" @endif></button>
                @empty
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                @endforelse
            </div>
            <div class="carousel-inner">
                @forelse($sliders as $index => $slider)
                    <div class="carousel-item @if($index === 0) active @endif" data-bs-interval="6000">
                        <div class="hero-slide" style="background-image: url('{{ asset('storage/' . $slider->image_path) }}')">
                            <div class="hero-overlay"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content">
                                            @if($slider->title)
                                                <span class="hero-badge">
                                                    <i class="fas fa-graduation-cap me-2"></i>{{ $slider->title }}
                                                </span>
                                            @endif
                                            @if($slider->subtitle)
                                                <h1>{!! $slider->subtitle !!}</h1>
                                            @endif
                                            @if($slider->description ?? null)
                                                <p>{{ $slider->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Default Slide 1 -->
                    <div class="carousel-item active" data-bs-interval="6000">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content">
                                            <span class="hero-badge">
                                                <i class="fas fa-graduation-cap me-2"></i>Admissions Open 2026
                                            </span>
                                            <h1>Empowering Minds, <span>Shaping Futures</span></h1>
                                            <p>{{ $settings['school_description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Default Slide 2 -->
                    <div class="carousel-item" data-bs-interval="6000">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content">
                                            <span class="hero-badge">
                                                <i class="fas fa-book-reader me-2"></i>Excellence in Education
                                            </span>
                                            <h1>Where Learning <span>Becomes Discovery</span></h1>
                                            <p>Our innovative curriculum combines academic rigor with creative exploration, preparing students to think critically and solve real-world problems with confidence.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Default Slide 3 -->
                    <div class="carousel-item" data-bs-interval="6000">
                        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content">
                                            <span class="hero-badge">
                                                <i class="fas fa-users me-2"></i>Join Our Community
                                            </span>
                                            <h1>Building <span>Tomorrow's Leaders</span></h1>
                                            <p>With {{ $settings['total_students'] }} students, {{ $settings['total_teachers'] }} expert teachers, and a {{ $settings['university_acceptance'] }} university acceptance rate, {{ $settings['school_name'] }} is where excellence meets opportunity.</p>
                                            <div class="hero-stats">
                                                <div class="stat-item">
                                                    <h3>{{ $settings['total_students'] }}</h3>
                                                    <p>Students</p>
                                                </div>
                                                <div class="stat-item">
                                                    <h3>{{ $settings['total_teachers'] }}</h3>
                                                    <p>Expert Teachers</p>
                                                </div>
                                                <div class="stat-item">
                                                    <h3>{{ $settings['university_acceptance'] }}</h3>
                                                    <p>University Acceptance</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Why Choose Us</span>
                <h2>Excellence in Every Aspect</h2>
                <p>We provide a comprehensive educational experience that goes beyond academics, focusing on holistic development and character building.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4>Expert Faculty</h4>
                        <p>Our teachers are highly qualified professionals with years of experience, dedicated to nurturing each student's unique potential.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <h4>Modern Facilities</h4>
                        <p>State-of-the-art laboratories, libraries, sports facilities, and technology-enabled classrooms provide the perfect learning environment.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Small Class Sizes</h4>
                        <p>Limited student-teacher ratio ensures personalized attention and customized learning approaches for every student.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4>Holistic Development</h4>
                        <p>Beyond academics, we focus on arts, sports, leadership, and character development to create well-rounded individuals.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h4>Global Perspective</h4>
                        <p>International curriculum standards and exchange programs prepare students for success in a globalized world.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h4>Proven Track Record</h4>
                        <p>Our students consistently achieve top scores in national examinations and gain admission to prestigious universities.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="School Campus">
                        <div class="about-badge">
                            <h3>{{ $settings['years_of_excellence'] }}</h3>
                            <p>Years of Excellence</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content">
                        <span class="section-badge">About Our School</span>
                        <h2>A Legacy of Academic Excellence</h2>
                        <p>{{ $settings['about_description'] }}</p>
                        <p>{{ $settings['about_mission'] }}</p>
                        <ul class="about-features">
                            <li><i class="fas fa-check-circle"></i> Accredited by National Education Board</li>
                            <li><i class="fas fa-check-circle"></i> Award-winning STEM programs</li>
                            <li><i class="fas fa-check-circle"></i> Comprehensive extracurricular activities</li>
                            <li><i class="fas fa-check-circle"></i> Strong alumni network worldwide</li>
                        </ul>
                        <a href="#contact" class="btn btn-hero-primary mt-3">
                            <i class="fas fa-arrow-right me-2"></i>Discover More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="programs" id="programs">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Academic Programs</span>
                <h2>Pathways to Success</h2>
                <p>Our comprehensive curriculum is designed to challenge and inspire students at every stage of their educational journey.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="program-card">
                        <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80')"></div>
                        <div class="program-content">
                            <span class="program-tag">Ages 3-5</span>
                            <h4>Early Childhood Education</h4>
                            <p>A nurturing environment where young learners develop foundational skills through play-based learning and creative exploration.</p>
                            <a href="#" class="program-link">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-card">
                        <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80')"></div>
                        <div class="program-content">
                            <span class="program-tag">Grades 1-8</span>
                            <h4>Primary & Middle School</h4>
                            <p>Building strong academic foundations while fostering curiosity, critical thinking, and social-emotional development.</p>
                            <a href="#" class="program-link">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="program-card">
                        <div class="program-image" style="background-image: url('https://images.unsplash.com/photo-1509062522246-3755977927d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80')"></div>
                        <div class="program-content">
                            <span class="program-tag">Grades 9-12</span>
                            <h4>High School</h4>
                            <p>Rigorous college-preparatory curriculum with advanced placement courses and specialized tracks in sciences, arts, and humanities.</p>
                            <a href="#" class="program-link">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Photo Gallery</span>
                <h2>Campus Life & Moments</h2>
                <p>Explore our vibrant campus through these captured moments of learning, creativity, and achievement.</p>
            </div>
            <div class="row g-3">
                @forelse($galleryImages as $image)
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title ?? 'Gallery Image' }}">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Students in classroom">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Science laboratory">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="School library">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Sports activities">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Art class">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Graduation ceremony">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team" id="team">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Leadership Team</span>
                <h2>Meet Our Educators</h2>
                <p>Our dedicated team of experienced educators and administrators is committed to nurturing each student's potential.</p>
            </div>
            <div class="row g-4">
                @forelse($teamMembers as $member)
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-image">
                                <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}">
                            </div>
                            <div class="team-content">
                                <h4>{{ $member->name }}</h4>
                                <p>{{ $member->designation }}</p>
                                <div class="team-social">
                                    @if($member->email)
                                        <a href="mailto:{{ $member->email }}"><i class="fas fa-envelope"></i></a>
                                    @endif
                                    @if($member->phone)
                                        <a href="tel:{{ $member->phone }}"><i class="fas fa-phone"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-image">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Dr. Sarah Johnson">
                            </div>
                            <div class="team-content">
                                <h4>Dr. Sarah Johnson</h4>
                                <p>Principal</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-image">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Prof. Michael Chen">
                            </div>
                            <div class="team-content">
                                <h4>Prof. Michael Chen</h4>
                                <p>Vice Principal - Academics</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-image">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Ms. Emily Davis">
                            </div>
                            <div class="team-content">
                                <h4>Ms. Emily Davis</h4>
                                <p>Head of Student Affairs</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-image">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Mr. David Wilson">
                            </div>
                            <div class="team-content">
                                <h4>Mr. David Wilson</h4>
                                <p>Athletics Director</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="contact">
        <div class="container">
            <h2>{{ $settings['cta_title'] }}</h2>
            <p>{{ $settings['cta_description'] }}</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ $settings['cta_button_url'] }}" class="btn btn-hero-primary">
                    <i class="fas fa-user-plus me-2"></i>{{ $settings['cta_button_text'] }}
                </a>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings['school_phone']) }}" class="btn btn-hero-secondary">
                    <i class="fas fa-phone me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        @if($settings['school_logo'] && file_exists(public_path('storage/' . $settings['school_logo'])))
                            <img src="{{ asset('storage/' . $settings['school_logo']) }}" alt="{{ $settings['school_name'] }}" style="height: 40px; margin-bottom: 0.5rem;">
                        @endif
                        <span class="brand-pre">{{ Str::beforeLast($settings['school_name'], ' ') }}</span>
                        <span class="brand-name"> {{ Str::afterLast($settings['school_name'], ' ') }}</span>
                    </div>
                    <p>{{ $settings['about_mission'] }}</p>
                    <div class="social-links">
                        @if($settings['facebook_url'])<a href="{{ $settings['facebook_url'] }}" target="_blank"><i class="fab fa-facebook-f"></i></a>@endif
                        @if($settings['twitter_url'])<a href="{{ $settings['twitter_url'] }}" target="_blank"><i class="fab fa-twitter"></i></a>@endif
                        @if($settings['instagram_url'])<a href="{{ $settings['instagram_url'] }}" target="_blank"><i class="fab fa-instagram"></i></a>@endif
                        @if($settings['linkedin_url'])<a href="{{ $settings['linkedin_url'] }}" target="_blank"><i class="fab fa-linkedin-in"></i></a>@endif
                        @if($settings['youtube_url'])<a href="{{ $settings['youtube_url'] }}" target="_blank"><i class="fab fa-youtube"></i></a>@endif
                        @if($settings['telegram_url'])<a href="{{ $settings['telegram_url'] }}" target="_blank"><i class="fab fa-telegram-plane"></i></a>@endif
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#programs">Programs</a></li>
                        <li><a href="#features">Why Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Programs</h5>
                    <ul class="footer-links">
                        <li><a href="#">Early Childhood</a></li>
                        <li><a href="#">Primary School</a></li>
                        <li><a href="#">Middle School</a></li>
                        <li><a href="#">High School</a></li>
                        <li><a href="#">Extracurricular</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contact Info</h5>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt me-2"></i>{{ $settings['school_address'] }}</li>
                        <li><i class="fas fa-phone me-2"></i>{{ $settings['school_phone'] }}</li>
                        <li><i class="fas fa-envelope me-2"></i>{{ $settings['school_email'] }}</li>
                        <li><i class="fas fa-clock me-2"></i>Mon - Fri: 7:30 AM - 4:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $settings['footer_text'] }}</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
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

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .program-card, .section-header').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    </script>
</body>
</html>