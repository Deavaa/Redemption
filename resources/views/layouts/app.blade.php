<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'School of Redemption')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
</head>

<body class="flex min-h-screen flex-col bg-slate-50 text-slate-950">
    <nav class="bg-cyan-500 text-black shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="space-y-1">
                <div class="text-xs uppercase tracking-[0.3em] text-amber-400">School of</div>
                <div class="text-2xl font-semibold">REDEMPTION</div>
            </a>
            <div class="hidden items-center gap-4 md:flex">
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('/') }}">Home</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('about') }}">About</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('gallery') }}">Gallery</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('contact') }}">Contact</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('team') }}">Team</a>
            </div>
            <a href="{{ url('login') }}"
                class="rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400">Login</a>
        </div>
    </nav>
    <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    <footer class="bg-slate-950 text-slate-200">
        <div class="mx-auto max-w-7xl space-y-10 px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-4">
                    <h5 class="text-lg font-semibold text-white">School of Redemption</h5>
                    <p class="text-sm leading-7 text-slate-300">Nurturing minds and building futures with excellence in
                        education and character development since our founding.</p>
                    <div class="flex gap-3 text-slate-300">
                        <a href="#" class="hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-white">Quick Links</h5>
                    <ul class="mt-4 space-y-2 text-sm text-slate-300">
                        <li><a href="{{ url('/') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ url('about') }}" class="hover:text-white">About</a></li>
                        <li><a href="{{ url('gallery') }}" class="hover:text-white">Gallery</a></li>
                        <li><a href="{{ url('contact') }}" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-white">Academics</h5>
                    <ul class="mt-4 space-y-2 text-sm text-slate-300">
                        <li><a href="#" class="hover:text-white">Programs</a></li>
                        <li><a href="#" class="hover:text-white">Admissions</a></li>
                        <li><a href="#" class="hover:text-white">Calendar</a></li>
                        <li><a href="#" class="hover:text-white">Results</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-white">Contact Us</h5>
                    <ul class="mt-4 space-y-3 text-sm text-slate-300">
                        <li><i class="fas fa-map-marker-alt text-amber-400"></i> 123 Education St, City</li>
                        <li><i class="fas fa-phone text-amber-400"></i> +251-XXX-XXXXXX</li>
                        <li><i class="fas fa-envelope text-amber-400"></i> info@schoolofredemption.com</li>
                        <li><i class="fas fa-clock text-amber-400"></i> Mon-Fri: 8AM-5PM</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-6 text-center text-sm text-slate-400">&copy; {{ date('Y') }}
                School of Redemption. All rights reserved.</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
