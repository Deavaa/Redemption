<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('app.school_name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
    <style>
        .gold-text { color: #FF8C00 !important; }
        .btn-gold { background: #FF8C00; color: #fff; border: none; border-radius: 50px; padding: 0.6rem 2rem; font-weight: 600; transition: all 0.3s ease; }
        .btn-gold:hover { background: #FFa520; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(255,140,0,0.4); }
        .hero { background: linear-gradient(135deg, #1E90FF, #1565C0); color: #fff; padding: 100px 0 60px; }
        .section { padding: 60px 0; }
        .stitle { text-align: center; margin-bottom: 2rem; }
        .stitle h2 { font-family: 'Playfair Display', serif; }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-slate-50 text-slate-950">
    <nav class="bg-[#1E90FF] text-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                @php $logoUrl = \App\Models\Setting::getLogoUrl(); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ __('app.school_name') }}" class="h-10 opacity-85">
                @endif
                <div>
                    <div class="text-xs uppercase tracking-[0.3em] text-orange-400">{{ __('app.brand_pre') }}</div>
                    <div class="text-2xl font-semibold">{{ __('app.brand_name') }}</div>
                </div>
            </a>
            <div class="hidden items-center gap-4 md:flex">
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('/') }}">{{ __('app.home') }}</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('about') }}">{{ __('app.about') }}</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('gallery') }}">{{ __('app.gallery') }}</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('contact') }}">{{ __('app.contact') }}</a>
                <a class="text-sm font-medium text-slate-200 hover:text-white" href="{{ url('team') }}">{{ __('app.team') }}</a>
                {{-- Language Switcher --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-sm font-medium text-slate-200 hover:bg-white/20 hover:text-white transition">
                        <i class="fas fa-globe text-xs"></i>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5 z-50">
                        @foreach(config('app.available_locales') as $code => $name)
                            <a href="{{ route('lang.switch', $code) }}"
                               class="flex items-center gap-2 px-4 py-2 text-sm {{ app()->getLocale() === $code ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                @if(app()->getLocale() === $code)
                                    <i class="fas fa-check text-xs text-blue-600"></i>
                                @else
                                    <span class="w-3"></span>
                                @endif
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Mobile Language Switcher --}}
                <div class="md:hidden relative" id="mobileLangSwitch">
                    <a href="{{ route('lang.switch', app()->getLocale() === 'en' ? 'am' : 'en') }}"
                       class="flex items-center gap-1 rounded-full bg-white/10 px-3 py-1.5 text-sm font-medium text-slate-200 hover:text-white transition">
                        <i class="fas fa-globe text-xs"></i>
                        {{ app()->getLocale() === 'en' ? 'አማ' : 'EN' }}
                    </a>
                </div>
                <a href="{{ url('login') }}"
                    class="rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-400">{{ __('app.login') }}</a>
            </div>
        </div>
    </nav>
    <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    <footer class="bg-[#0a1628] text-slate-200">
        <div class="mx-auto max-w-7xl space-y-10 px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-4">
                    <h5 class="text-lg font-semibold text-white flex items-center gap-2">
                        @php $logoUrl = \App\Models\Setting::getLogoUrl(); @endphp
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ __('app.school_name') }}" class="h-8 opacity-85">
                        @endif
                        <span>{{ __('app.school_name') }}</span>
                    </h5>
                    <p class="text-sm leading-7 text-slate-300">{{ __('app.footer_about') }}</p>
                    <div class="flex gap-3 text-slate-300">
                        <a href="#" class="hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="hover:text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-white">{{ __('app.quick_links') }}</h5>
                    <ul class="mt-4 space-y-2 text-sm text-slate-300">
                        <li><a href="{{ url('/') }}" class="hover:text-white">{{ __('app.home') }}</a></li>
                        <li><a href="{{ url('about') }}" class="hover:text-white">{{ __('app.about') }}</a></li>
                        <li><a href="{{ url('gallery') }}" class="hover:text-white">{{ __('app.gallery') }}</a></li>
                        <li><a href="{{ url('contact') }}" class="hover:text-white">{{ __('app.contact') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-white">{{ __('app.academics') }}</h5>
                    <ul class="mt-4 space-y-2 text-sm text-slate-300">
                        <li><a href="#" class="hover:text-white">{{ __('app.programs') }}</a></li>
                        <li><a href="{{ url('contact') }}" class="hover:text-white">{{ __('app.admissions') }}</a></li>
                        <li><a href="#" class="hover:text-white">{{ __('app.calendar') }}</a></li>
                        <li><a href="#" class="hover:text-white">{{ __('app.results') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-white">{{ __('app.contact_us') }}</h5>
                    <ul class="mt-4 space-y-3 text-sm text-slate-300">
                        <li><i class="fas fa-map-marker-alt text-orange-400"></i> 123 Education St, City</li>
                        <li><i class="fas fa-phone text-orange-400"></i> +251-XXX-XXXXXX</li>
                        <li><i class="fas fa-envelope text-orange-400"></i> info@schoolofredemption.com</li>
                        <li><i class="fas fa-clock text-orange-400"></i> Mon-Fri: 8AM-5PM</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-6 text-center text-sm text-slate-400">&copy; {{ date('Y') }}
                {{ __('app.school_name') }}. {{ __('app.all_rights_reserved') }}</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
