@extends('layouts.website')

@push('styles')
    <style>
        /* ========== Hero Slider Section ========== */
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
            will-change: transform;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: transparent;
        }

        /* Classical pattern overlay */
        .hero-dot-grid {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 30px 30px;
            z-index: 1;
            pointer-events: none;
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
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50%;
            top: 50%;
            opacity: 0;
            transition: all 0.4s ease;
        }

        .hero-slider:hover .carousel-control-prev,
        .hero-slider:hover .carousel-control-next {
            opacity: 1;
        }

        .hero-slider .carousel-control-prev:hover,
        .hero-slider .carousel-control-next:hover {
            background: rgba(212, 160, 23, 0.35);
            border-color: var(--secondary-color);
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
            background: rgba(255, 255, 255, 0.4);
            border: none;
            transition: all 0.4s ease;
        }

        .hero-slider .carousel-indicators button.active {
            background: var(--secondary-color);
            width: 36px;
            border-radius: 6px;
        }

        .hero-content {
            color: var(--white);
            padding: 4rem 0;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(212, 160, 23, 0.12);
            border: 1px solid rgba(212, 160, 23, 0.5);
            color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        /* Animated text reveal */
        .hero-text-reveal {
            overflow: hidden;
        }

        .hero-text-reveal > * {
            transform: translateY(100%);
            opacity: 0;
            animation: textReveal 0.8s cubic-bezier(0.4,0,0.2,1) forwards;
        }

        .hero-text-reveal > *:nth-child(1) { animation-delay: 0.2s; }
        .hero-text-reveal > *:nth-child(2) { animation-delay: 0.4s; }
        .hero-text-reveal > *:nth-child(3) { animation-delay: 0.6s; }

        @keyframes textReveal {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .hero h1 {
            font-size: 3.5rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--white);
            text-shadow: 0 2px 12px rgba(0,0,0,0.3);
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
            background: #E8B82E;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(212, 160, 23, 0.5);
        }

        .btn-hero-secondary {
            background: transparent;
            color: var(--white);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            border: 2px solid rgba(255,255,255,0.4);
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
            border-top: 1px solid rgba(255,255,255,0.15);
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

        /* ========== Section Dividers (clip-path) ========== */
        .section-divider-top {
            clip-path: polygon(0 0, 100% 40px, 100% 100%, 0 100%);
            margin-top: -40px;
        }

        .section-divider-bottom {
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 40px), 0 100%);
            margin-bottom: -40px;
        }

        /* ========== Section Headers ========== */
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-block;
            background: rgba(212, 160, 23, 0.1);
            color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid rgba(212, 160, 23, 0.2);
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
        }

        .section-header h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: var(--secondary-color);
            margin: 0.75rem auto 0;
        }

        .section-header p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== Animated Counters Section ========== */
        .counters-section {
            padding: 5rem 0;
            background: var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .counters-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(212, 160, 23, 0.04) 1px, transparent 1px);
            background-size: 25px 25px;
        }

        .counter-item {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .counter-item .counter-icon {
            width: 70px;
            height: 70px;
            background: rgba(212, 160, 23, 0.12);
            border: 1px solid rgba(212, 160, 23, 0.35);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .counter-item .counter-icon i {
            font-size: 1.75rem;
            color: var(--secondary-color);
        }

        .counter-item h3 {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
            font-weight: 800;
            font-family: 'Playfair Display', serif;
        }

        .counter-item p {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            margin: 0;
        }

        /* ========== Features Section (Glassmorphic Cards) ========== */
        .features {
            padding: 6rem 0;
            background: var(--white);
            position: relative;
        }

        .feature-card {
            background: var(--white);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color), var(--secondary-color));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            border-color: transparent;
        }

        /* Hover glow effect */
        .feature-card::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(212, 160, 23, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.5s ease;
            z-index: 0;
        }

        .feature-card:hover::after {
            width: 300px;
            height: 300px;
        }

        .feature-card > * {
            position: relative;
            z-index: 1;
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
            border: 2px solid rgba(212, 160, 23, 0.2);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-color: var(--secondary-color);
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

        /* ========== About Section (Split Layout) ========== */
        .about {
            padding: 6rem 0;
            background: var(--light-bg);
        }

        .about-image-col {
            position: relative;
        }

        .about-image-wrapper {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }

        .about-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            min-height: 400px;
        }

        .about-image-badge {
            position: absolute;
            bottom: 24px;
            left: 24px;
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 1rem 2rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .about-image-badge h3 {
            font-size: 2.5rem;
            margin: 0;
            line-height: 1;
        }

        .about-image-badge p {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .about-text-col {
            display: flex;
            flex-direction: column;
            justify-content: center;
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

        /* ========== Programs Section (Horizontal Scroll) ========== */
        .programs {
            padding: 6rem 0;
            background: var(--white);
        }

        .programs-scroll-wrapper {
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 1rem;
        }

        .programs-scroll-wrapper::-webkit-scrollbar {
            display: none;
        }

        .programs-scroll-track {
            display: flex;
            gap: 1.5rem;
            padding: 0.5rem 0;
        }

        .program-card {
            min-width: 340px;
            max-width: 340px;
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            scroll-snap-align: start;
            flex-shrink: 0;
        }

        .program-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
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
            background: linear-gradient(to top, rgba(0, 0, 0, 0.5), transparent);
        }

        .program-content {
            padding: 2rem;
        }

        .program-tag {
            display: inline-block;
            background: rgba(212, 160, 23, 0.1);
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
            transition: gap 0.3s ease;
        }

        .program-link:hover {
            gap: 0.75rem;
            color: var(--primary-color);
        }

        .programs-scroll-nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .programs-scroll-nav button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-color);
            color: var(--secondary-color);
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .programs-scroll-nav button:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: scale(1.1);
        }

        /* ========== Gallery Section (Masonry) ========== */
        .gallery {
            padding: 6rem 0;
            background: var(--light-bg);
        }

        .gallery-masonry {
            columns: 3;
            column-gap: 1rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            cursor: pointer;
            margin-bottom: 1rem;
            break-inside: avoid;
            display: inline-block;
            width: 100%;
        }

        .gallery-item img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.45) 100%);
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

        @media (max-width: 991px) {
            .gallery-masonry { columns: 2; }
        }

        @media (max-width: 575px) {
            .gallery-masonry { columns: 1; }
        }

        /* ========== Video Section ========== */
        .video-section {
            padding: 6rem 0;
            background: var(--white);
        }

        .video-card {
            border-radius: 20px;
            overflow: hidden;
            background: var(--white);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            height: 100%;
            cursor: pointer;
        }

        .video-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .video-thumb {
            position: relative;
            padding-bottom: 56.25%;
            background: var(--primary-color);
            overflow: hidden;
        }

        .video-thumb img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .video-card:hover .video-thumb img {
            transform: scale(1.05);
        }

        .video-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 68px;
            height: 68px;
            background: rgba(212, 160, 23, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(212, 160, 23, 0.5);
        }

        .video-play-btn i {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-left: 4px;
        }

        .video-card:hover .video-play-btn {
            transform: translate(-50%, -50%) scale(1.1);
            background: var(--secondary-color);
        }

        .video-info {
            padding: 1.25rem;
        }

        .video-info h5 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .video-info .video-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .video-info .video-meta .video-category {
            background: rgba(212, 160, 23, 0.1);
            color: var(--secondary-color);
            padding: 0.15rem 0.6rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Video Modal */
        .video-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            padding: 2rem;
        }

        .video-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .video-modal-content {
            width: 100%;
            max-width: 900px;
            position: relative;
        }

        .video-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.75rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .video-modal-close:hover {
            transform: rotate(90deg);
        }

        .video-modal-content .ratio {
            border-radius: 16px;
            overflow: hidden;
        }

        /* ========== Team Section (Rounded Avatar) ========== */
        .team {
            padding: 6rem 0;
            background: var(--light-bg);
        }

        .team-card {
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            text-align: center;
            padding: 2.5rem 1.5rem 2rem;
            height: 100%;
            position: relative;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 1.5rem;
            border: 4px solid rgba(212, 160, 23, 0.25);
            position: relative;
            transition: border-color 0.3s ease;
        }

        .team-card:hover .team-avatar {
            border-color: var(--secondary-color);
        }

        .team-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .team-card:hover .team-avatar img {
            transform: scale(1.08);
        }

        /* Social overlay on hover */
        .team-social-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .team-card:hover .team-social-overlay {
            opacity: 1;
        }

        .team-social-overlay a {
            width: 30px;
            height: 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }

        .team-social-overlay a:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: scale(1.15);
        }

        .team-content h4 {
            font-size: 1.15rem;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }

        .team-content p {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin: 0;
        }

        /* ========== CTA Section (Animated Gradient) ========== */
        .cta {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a1a2e 50%, var(--primary-color) 100%);
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
            color: var(--white);
            text-align: center;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(212, 160, 23, 0.2);
            border-bottom: 1px solid rgba(212, 160, 23, 0.2);
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .cta::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 160, 23, 0.06) 0%, transparent 50%);
            animation: ctaGlow 6s ease-in-out infinite;
        }

        @keyframes ctaGlow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(5%, 5%); }
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--white);
            position: relative;
        }

        .cta p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.8);
            max-width: 600px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .cta .btn { position: relative; }

        /* ========== Contact Section (Two-Column) ========== */
        .contact-section {
            padding: 6rem 0;
            background: var(--white);
        }

        .contact-form-wrapper {
            background: var(--white);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            height: 100%;
        }

        .contact-form-wrapper .form-control,
        .contact-form-wrapper .form-select {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .contact-form-wrapper .form-control:focus,
        .contact-form-wrapper .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.12);
        }

        .contact-info-cards {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            height: 100%;
            justify-content: center;
        }

        .contact-info-card {
            background: var(--light-bg);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .contact-info-card:hover {
            transform: translateX(8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .contact-info-card .info-icon {
            width: 50px;
            height: 50px;
            min-width: 50px;
            background: var(--primary-color);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(212, 160, 23, 0.2);
        }

        .contact-info-card .info-icon i {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .contact-info-card h5 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .contact-info-card p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

        /* ========== Footer (Modern Layout) ========== */
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
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }

        .footer-links a:hover {
            color: var(--secondary-color);
            transform: translateX(4px);
        }

        .social-links {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
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
            border-color: var(--secondary-color);
        }

        /* Newsletter */
        .newsletter-form {
            display: flex;
            gap: 0.5rem;
        }

        .newsletter-form input {
            flex: 1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 0.65rem 1rem;
            color: var(--white);
            font-size: 0.9rem;
        }

        .newsletter-form input::placeholder {
            color: rgba(255,255,255,0.4);
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }

        .newsletter-form button {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .newsletter-form button:hover {
            background: #E8B82E;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 2rem;
            margin-top: 3rem;
        }

        .footer-bottom p {
            margin: 0;
            font-size: 0.85rem;
        }

        /* ========== Back to Top Button ========== */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            border-radius: 14px;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.4s ease;
            box-shadow: 0 6px 20px rgba(212, 160, 23, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: #E8B82E;
            transform: translateY(-3px);
        }

        /* ========== Scroll Reveal ========== */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-40px);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(40px);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal-scale.revealed {
            opacity: 1;
            transform: scale(1);
        }

        /* ========== Responsive ========== */
        @media (max-width: 991px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .cta h2 {
                font-size: 2rem;
            }

            .about-image-wrapper img {
                min-height: 300px;
            }

            .counter-item h3 {
                font-size: 2.25rem;
            }
        }

        @media (max-width: 575px) {
            .hero h1 {
                font-size: 2rem;
            }

            .section-header h2 {
                font-size: 1.75rem;
            }

            .about-image-badge {
                bottom: 12px;
                left: 12px;
                padding: 0.75rem 1.25rem;
            }

            .about-image-badge h3 {
                font-size: 1.75rem;
            }

            .counter-item h3 {
                font-size: 2rem;
            }

            .newsletter-form {
                flex-direction: column;
            }
        }

        /* ========== Animations ========== */
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

    </style>
@endpush

@section('before-nav')
    <!-- Announcement Ticker Bar -->
    <div id="announcementTicker" style="display:none;background:linear-gradient(135deg,#1B5E20 0%,#0D3B12 100%);color:#fff;overflow:hidden;position:fixed;top:0;left:0;right:0;z-index:9999;">
        <div style="display:flex;align-items:center;height:36px;">
            <div style="background:rgba(255,255,255,0.18);padding:0 14px;height:100%;display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;flex-shrink:0;border-right:1px solid rgba(255,255,255,0.15);">
                <i class="fas fa-bullhorn" style="font-size:13px;"></i> <span>Announcements</span>
            </div>
            <div style="flex:1;overflow:hidden;position:relative;height:100%;">
                <div id="tickerTrack" style="display:flex;align-items:center;height:100%;white-space:nowrap;"></div>
            </div>
            <button id="tickerClose" style="background:rgba(255,255,255,0.12);border:none;color:#fff;width:36px;height:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;border-left:1px solid rgba(255,255,255,0.15);" title="Dismiss"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <style>
    @keyframes ticker-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    .ticker-item { display:inline-flex;align-items:center;gap:8px;padding:0 24px;font-size:13px;font-weight:500;white-space:nowrap; }
    .ticker-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }
    .ticker-cat { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;opacity:0.7; }
    .ticker-date { font-size:11px;opacity:0.6; }
    #tickerTrack.scrolling { animation: ticker-scroll 120s linear infinite; }
    #tickerTrack.scrolling:hover { animation-play-state:paused; }
    @media(max-width:768px) { .ticker-item { padding:0 14px;font-size:12px; } }
    </style>
    <script>
    // Adjust navbar top when ticker is visible
    (function() {
        var navbar = document.getElementById('navbar');
        var tickerEl = document.getElementById('announcementTicker');
        if (navbar && tickerEl) {
            // Check if ticker will be shown (handled by ticker script below)
            // Default to offset position
            navbar.style.top = '36px';
        }
    })();
    </script>
@endsection

@section('content')
    {{-- Latest News Banner --}}
    @isset($latestNews)
    @if($latestNews->count() > 0)
    <div style="background:linear-gradient(135deg,#0D3B12 0%,#1B5E20 100%);color:#fff;padding:12px 0;position:relative;z-index:100">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <span style="font-weight:700;font-size:.75rem;background:rgba(255,255,255,.2);padding:4px 12px;border-radius:4px;white-space:nowrap;flex-shrink:0"><i class="fas fa-newspaper me-1"></i>NEWS</span>
                <div style="overflow:hidden;flex:1">
                    <div style="display:flex;gap:40px;animation:newsScroll 30s linear infinite;white-space:nowrap">
                        @foreach($latestNews as $newsItem)
                            <span style="font-size:.9rem;font-weight:500">
                                <strong>{{ $newsItem->title }}</strong>
                                @if($newsItem->content)<span style="opacity:.8"> — {{ Str::limit(strip_tags($newsItem->content), 120) }}</span>@endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    @keyframes newsScroll { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }
    </style>
    @endif
    @endisset

    <!-- ========== Hero Slider Section ========== -->
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
                        <div class="hero-slide" data-parallax style="background-image: url('{{ asset('storage/' . $slider->image_path) }}')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content hero-text-reveal">
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
                        <div class="hero-slide" data-parallax style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content hero-text-reveal">
                                            <span class="hero-badge">
                                                <i class="fas fa-graduation-cap me-2"></i>Excellence in Education
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
                        <div class="hero-slide" data-parallax style="background-image: url('https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content hero-text-reveal">
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
                        <div class="hero-slide" data-parallax style="background-image: url('https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="hero-content hero-text-reveal">
                                            <span class="hero-badge">
                                                <i class="fas fa-users me-2"></i>Join Our Community
                                            </span>
                                            <h1>Building <span>Tomorrow's Leaders</span></h1>
                                            <p>With {{ $settings['total_students'] }} students, {{ $settings['total_teachers'] }} expert teachers, and a {{ $settings['university_acceptance'] }} university acceptance rate, {{ $settings['school_name'] }} is where excellence meets opportunity.</p>
                                            <div class="hero-stats">
                                                <div class="stat-item">
                                                    <h3><span class="counter" data-target="{{ $settings['total_students'] }}">0</span>+</h3>
                                                    <p>Students</p>
                                                </div>
                                                <div class="stat-item">
                                                    <h3><span class="counter" data-target="{{ $settings['total_teachers'] }}">0</span>+</h3>
                                                    <p>Expert Teachers</p>
                                                </div>
                                                <div class="stat-item">
                                                    <h3><span class="counter" data-target="{{ preg_replace('/[^0-9]/', '', $settings['university_acceptance']) }}">0</span>%</h3>
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

    <!-- ========== Animated Counters Section ========== -->
    <section class="counters-section section-divider-top" id="counters">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ $settings['total_students'] }}">0</span>+</h3>
                        <p>Students</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ $settings['total_teachers'] }}">0</span>+</h3>
                        <p>Expert Teachers</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ $settings['years_of_excellence'] }}">0</span>+</h3>
                        <p>Years of Excellence</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ preg_replace('/[^0-9]/', '', $settings['university_acceptance']) }}">0</span>%</h3>
                        <p>University Acceptance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Features Section (Glassmorphic Cards) ========== -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Why Choose Us</span>
                <h2>{{ $settings['wcu_section_title'] }}</h2>
                <p>{{ $settings['wcu_section_subtitle'] }}</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_1_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_1_title'] }}</h4>
                        <p>{{ $settings['wcu_1_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_2_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_2_title'] }}</h4>
                        <p>{{ $settings['wcu_2_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_3_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_3_title'] }}</h4>
                        <p>{{ $settings['wcu_3_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_4_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_4_title'] }}</h4>
                        <p>{{ $settings['wcu_4_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_5_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_5_title'] }}</h4>
                        <p>{{ $settings['wcu_5_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_6_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_6_title'] }}</h4>
                        <p>{{ $settings['wcu_6_description'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== About Section (Split Layout) ========== -->
    <section class="about" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 about-image-col reveal-left">
                    <div class="about-image-wrapper">
                        <img src="{{ isset($settings['about_image']) && $settings['about_image'] && file_exists(public_path('storage/' . $settings['about_image'])) ? asset('storage/' . $settings['about_image']) : 'https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}" alt="About {{ $settings['school_name'] }}">
                        <div class="about-image-badge">
                            <h3>{{ $settings['years_of_excellence'] }}</h3>
                            <p>Years of<br>Excellence</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 about-text-col reveal-right">
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
                        <div class="mt-3">
                            <a href="#contact" class="btn btn-hero-primary">
                                <i class="fas fa-arrow-right me-2"></i>Discover More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Programs Section (Horizontal Scroll) ========== -->
    <section class="programs" id="programs">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Academic Programs</span>
                <h2>{{ $settings['programs_section_title'] }}</h2>
                <p>{{ $settings['programs_section_subtitle'] }}</p>
            </div>
            <div class="programs-scroll-wrapper reveal" id="programsScrollWrapper">
                <div class="programs-scroll-track">
                    @for ($i = 1; $i <= 4; $i++)
                        @php
                            $pImage = $settings["program_{$i}_image"] ?? '';
                            $pTag = $settings["program_{$i}_tag"] ?? '';
                            $pTitle = $settings["program_{$i}_title"] ?? '';
                            $pDesc = $settings["program_{$i}_description"] ?? '';
                            $hasImage = !empty($pImage) && file_exists(public_path('storage/' . $pImage));
                        @endphp
                        <div class="program-card">
                            <div class="program-image" style="background-image: url('{{ $hasImage ? asset('storage/' . $pImage) : 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80' }}')"></div>
                            <div class="program-content">
                                <span class="program-tag">{{ $pTag }}</span>
                                <h4>{{ $pTitle }}</h4>
                                <p>{{ $pDesc }}</p>
                                <a href="#" class="program-link">Learn More <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="programs-scroll-nav">
                <button id="programsScrollLeft" aria-label="Scroll left"><i class="fas fa-chevron-left"></i></button>
                <button id="programsScrollRight" aria-label="Scroll right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- ========== Gallery Section (Masonry) ========== -->
    <section class="gallery" id="gallery">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Photo Gallery</span>
                <h2>Campus Life & Moments</h2>
                <p>Explore our vibrant campus through these captured moments of learning, creativity, and achievement.</p>
            </div>
            <div class="gallery-masonry reveal">
                @forelse($galleryImages as $image)
                    <div class="gallery-item">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title ?? 'Gallery Image' }}" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                @empty
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Students in classroom" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Science laboratory" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="School library" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Sports activities" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Art class" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Graduation ceremony" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                @endforelse
            </div>
            @if($galleryImages->count() > 0)
            <div class="text-center mt-4 reveal">
                <a href="{{ route('gallery') }}" class="btn btn-hero-primary" style="padding:0.75rem 2rem;">View Full Gallery</a>
            </div>
            @endif
        </div>
    </section>

    <!-- ========== Video Section (NEW) ========== -->
    @isset($videos)
    @if($videos->count() > 0)
    <section class="video-section" id="videos">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Video Gallery</span>
                <h2>Video Highlights</h2>
                <p>Watch our school events, educational content, and student achievements come to life.</p>
            </div>
            <div class="row g-4">
                @foreach($videos as $video)
                <div class="col-lg-4 col-md-6">
                    <div class="video-card reveal" data-video-id="{{ $video->youtube_video_id }}" onclick="openVideoModal('{{ $video->youtube_video_id }}')">
                        <div class="video-thumb">
                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail }}" alt="{{ $video->title }}" loading="lazy">
                            @else
                                <img src="https://img.youtube.com/vi/{{ $video->youtube_video_id }}/hqdefault.jpg" alt="{{ $video->title }}" loading="lazy">
                            @endif
                            <div class="video-play-btn">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h5>{{ $video->title }}</h5>
                            <div class="video-meta">
                                @if($video->channel_name)
                                    <span><i class="fab fa-youtube me-1" style="color:#ff0000;"></i>{{ $video->channel_name }}</span>
                                @endif
                                @if($video->category)
                                    <span class="video-category">{{ $video->category }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endisset

    {{-- Legacy video section for backward compat --}}
    @isset($websiteVideos)
    @if(isset($videos) && $videos->count() > 0)
    @elseif($websiteVideos->count() > 0 || (isset($galleryVideos) && $galleryVideos->count() > 0))
    <section class="video-section" id="videos">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Video Gallery</span>
                <h2>Video Highlights</h2>
                <p>Watch our school events, educational content, and student achievements.</p>
            </div>
            <div class="row g-4">
                @foreach($websiteVideos as $video)
                <div class="col-lg-4 col-md-6">
                    <div class="video-card reveal" onclick="openVideoModal('{{ $video->youtube_video_id }}')">
                        <div class="video-thumb">
                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail }}" alt="{{ $video->title }}" loading="lazy">
                            @else
                                <img src="https://img.youtube.com/vi/{{ $video->youtube_video_id }}/hqdefault.jpg" alt="{{ $video->title }}" loading="lazy">
                            @endif
                            <div class="video-play-btn">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h5>{{ $video->title }}</h5>
                            <div class="video-meta">
                                @if($video->channel_name)
                                    <span><i class="fab fa-youtube me-1" style="color:#ff0000;"></i>{{ $video->channel_name }}</span>
                                @endif
                                @if($video->category)
                                    <span class="video-category">{{ $video->category }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                @isset($galleryVideos)
                @foreach($galleryVideos as $gv)
                @php
                    $gvVideoId = null;
                    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $gv->video_url, $m)) {
                        $gvVideoId = $m[1];
                    }
                @endphp
                @if($gvVideoId)
                <div class="col-lg-4 col-md-6">
                    <div class="video-card reveal" onclick="openVideoModal('{{ $gvVideoId }}')">
                        <div class="video-thumb">
                            <img src="https://img.youtube.com/vi/{{ $gvVideoId }}/hqdefault.jpg" alt="{{ $gv->title }}" loading="lazy">
                            <div class="video-play-btn">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h5>{{ $gv->title }}</h5>
                            @if($gv->description)
                            <div class="video-meta">
                                <span>{{ Str::limit($gv->description, 60) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                @endisset
            </div>
        </div>
    </section>
    @endif
    @endisset

    <!-- Video Modal -->
    <div class="video-modal-overlay" id="videoModal">
        <div class="video-modal-content">
            <button class="video-modal-close" id="videoModalClose" aria-label="Close video">
                <i class="fas fa-times"></i>
            </button>
            <div class="ratio ratio-16x9">
                <iframe id="videoModalIframe" src="" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        </div>
    </div>

    <!-- ========== Team Section (Rounded Avatar) ========== -->
    <section class="team" id="team">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Leadership Team</span>
                <h2>Meet Our Educators</h2>
                <p>Our dedicated team of experienced educators and administrators is committed to nurturing each student's potential.</p>
            </div>
            <div class="row g-4">
                @forelse($teamMembers as $member)
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}">
                                <div class="team-social-overlay">
                                    @if($member->email)
                                        <a href="mailto:{{ $member->email }}" title="Email"><i class="fas fa-envelope"></i></a>
                                    @endif
                                    @if($member->phone)
                                        <a href="tel:{{ $member->phone }}" title="Call"><i class="fas fa-phone"></i></a>
                                    @endif
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>{{ $member->name }}</h4>
                                <p>{{ $member->designation }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Dr. Sarah Johnson">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Dr. Sarah Johnson</h4>
                                <p>Principal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Prof. Michael Chen">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Prof. Michael Chen</h4>
                                <p>Vice Principal - Academics</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Ms. Emily Davis">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Ms. Emily Davis</h4>
                                <p>Head of Student Affairs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Mr. David Wilson">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Mr. David Wilson</h4>
                                <p>Athletics Director</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ========== CTA Section (Animated Gradient) ========== -->
    <section class="cta section-divider-top" id="cta">
        <div class="container">
            <h2 class="reveal">Visit Us in Person</h2>
            <p class="reveal">Applications are accepted in person only. Visit our campus to enroll and begin your educational journey with us.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap reveal">
                <a href="#contact" class="btn btn-hero-primary">
                    <i class="fas fa-map-marker-alt me-2"></i>Find Our Campus
                </a>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings['school_phone']) }}" class="btn btn-hero-secondary">
                    <i class="fas fa-phone me-2"></i>Call Us
                </a>
            </div>
        </div>
    </section>

    <!-- ========== Contact Section (Two-Column) ========== -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Get in Touch</span>
                <h2>Contact Us</h2>
                <p>We would love to hear from you. Reach out and let us help you on your educational journey.</p>
            </div>
            <div class="row g-5">
                <div class="col-lg-7 reveal-left">
                    <div class="contact-form-wrapper">
                        <h4 style="font-family:'Montserrat',sans-serif;font-weight:700;margin-bottom:1.5rem;color:var(--primary-color);">Send Us a Message</h4>
                        <form action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Full Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+1 234 567 890">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Subject</label>
                                    <select name="subject" class="form-select">
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Admissions">Admissions</option>
                                        <option value="Academics">Academics</option>
                                        <option value="Fee Structure">Fee Structure</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Message</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-hero-primary w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 reveal-right">
                    <div class="contact-info-cards">
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5>Our Location</h5>
                                <p>{{ $settings['school_address'] }}</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <h5>Call Us</h5>
                                <p>{{ $settings['school_phone'] }}</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h5>Email Us</h5>
                                <p>{{ $settings['school_email'] }}</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h5>Working Hours</h5>
                                <p>Mon - Fri: 7:30 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        // ========== Smooth Scrolling for Anchor Links ==========
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    var offset = 80;
                    var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                }
                // Close mobile drawer if open
                var mobileDrawer = document.getElementById('mobileDrawer');
                var mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('active');
                    mobileDrawerOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // ========== Counter Animation ==========
        (function() {
            var counters = document.querySelectorAll('.counter');
            var counterObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var el = entry.target;
                        var target = parseInt(el.getAttribute('data-target'));
                        var duration = 2000;
                        var start = 0;
                        var startTime = null;

                        function animate(currentTime) {
                            if (!startTime) startTime = currentTime;
                            var progress = Math.min((currentTime - startTime) / duration, 1);
                            // Ease out
                            var ease = 1 - Math.pow(1 - progress, 3);
                            var current = Math.floor(ease * target);
                            el.textContent = current;
                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                el.textContent = target;
                            }
                        }

                        requestAnimationFrame(animate);
                        counterObserver.unobserve(el);
                    }
                });
            }, {
                threshold: 0.5
            });

            counters.forEach(function(counter) {
                counterObserver.observe(counter);
            });
        })();

        // ========== Video Modal ==========
        function openVideoModal(videoId) {
            var modal = document.getElementById('videoModal');
            var iframe = document.getElementById('videoModalIframe');
            iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        (function() {
            var modal = document.getElementById('videoModal');
            if (!modal) return;
            var closeBtn = document.getElementById('videoModalClose');
            var iframe = document.getElementById('videoModalIframe');

            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
                iframe.src = '';
                document.body.style.overflow = '';
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                    iframe.src = '';
                    document.body.style.overflow = '';
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    modal.classList.remove('active');
                    iframe.src = '';
                    document.body.style.overflow = '';
                }
            });
        })();

        // ========== Programs Horizontal Scroll Nav ==========
        (function() {
            var wrapper = document.getElementById('programsScrollWrapper');
            var leftBtn = document.getElementById('programsScrollLeft');
            var rightBtn = document.getElementById('programsScrollRight');

            if (wrapper && leftBtn && rightBtn) {
                leftBtn.addEventListener('click', function() {
                    wrapper.scrollBy({ left: -360, behavior: 'smooth' });
                });
                rightBtn.addEventListener('click', function() {
                    wrapper.scrollBy({ left: 360, behavior: 'smooth' });
                });
            }
        })();

        // ========== Hero Parallax Effect ==========
        (function() {
            var heroSlides = document.querySelectorAll('[data-parallax]');
            window.addEventListener('scroll', function() {
                var scrollY = window.pageYOffset;
                heroSlides.forEach(function(slide) {
                    var rect = slide.getBoundingClientRect();
                    if (rect.bottom > 0 && rect.top < window.innerHeight) {
                        slide.style.backgroundPositionY = (scrollY * 0.3) + 'px';
                    }
                });
            }, { passive: true });
        })();

        // ========== Announcement Ticker Script ==========
        (function() {
            var tickerEl = document.getElementById('announcementTicker');
            var trackEl = document.getElementById('tickerTrack');
            var closeBtn = document.getElementById('tickerClose');
            var navbar = document.getElementById('navbar');
            if (!tickerEl || !trackEl) return;

            if (sessionStorage.getItem('ticker_dismissed')) {
                tickerEl.style.display = 'none';
                if (navbar) navbar.style.top = '0';
                return;
            }

            closeBtn.addEventListener('click', function() {
                tickerEl.style.display = 'none';
                if (navbar) navbar.style.top = '0';
                sessionStorage.setItem('ticker_dismissed', '1');
            });

            var categoryLabels = {
                'holiday': 'Holiday', 'exam': 'Exam', 'event': 'Event',
                'meeting': 'Meeting', 'deadline': 'Deadline', 'other': 'Info'
            };

            fetch('/api/public/announcements', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data || data.length === 0) {
                    tickerEl.style.display = 'none';
                    if (navbar) navbar.style.top = '0';
                    return;
                }

                var html = '';
                data.forEach(function(item) {
                    var dotColor = item.color || '#fff';
                    var cat = categoryLabels[item.category] || item.category || '';
                    html += '<span class="ticker-item">';
                    html += '<span class="ticker-dot" style="background:' + dotColor + '"></span>';
                    html += '<span class="ticker-cat">' + cat + '</span>';
                    html += ' ' + item.title;
                    if (item.start_date) html += ' <span class="ticker-date">(' + item.start_date + ')</span>';
                    html += '</span>';
                });

                var contentWidth = trackEl.scrollWidth;
                var containerWidth = trackEl.parentElement.offsetWidth;

                if (contentWidth > containerWidth) {
                    trackEl.innerHTML = html + html;
                    trackEl.classList.add('scrolling');
                    var totalWidth = trackEl.scrollWidth / 2;
                    var speed = Math.max(60, totalWidth / 5);
                    trackEl.style.animationDuration = speed + 's';
                } else {
                    trackEl.innerHTML = html;
                }

                tickerEl.style.display = 'block';
            })
            .catch(function() {
                tickerEl.style.display = 'none';
                if (navbar) navbar.style.top = '0';
            });
        })();
    </script>
@endpush
