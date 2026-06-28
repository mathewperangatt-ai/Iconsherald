<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'IconsHerald') — Herald Yourself.</title>
    <meta name="description" content="@yield('meta_description', 'IconsHerald writes and publishes permanent professional profiles for doctors, lawyers, scientists, architects, and others whose careers deserve a proper record.')">

    {{-- Favicon placeholder --}}
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0a0a0a] text-[#f5f0e8] font-serif antialiased min-h-screen">

    {{-- Navigation --}}
    <header class="border-b border-[#2a2a2a]">
        <nav class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-[#c9a84c] text-xl font-semibold tracking-wide">
                IconsHerald
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm">
                <a href="{{ route('home') }}" class="text-[#a09070] hover:text-[#f5f0e8] transition-colors">Home</a>
                <a href="{{ route('about') }}" class="text-[#a09070] hover:text-[#f5f0e8] transition-colors">About</a>
                <a href="{{ route('pricing') }}" class="text-[#a09070] hover:text-[#f5f0e8] transition-colors">Pricing</a>

                {{-- In Memoriam — visually separated, lighter weight --}}
                <a href="{{ route('in-memoriam.index') }}" class="text-[#7a6a55] hover:text-[#a09070] transition-colors text-xs tracking-wider uppercase">
                    In Memoriam
                </a>
            </div>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('member.dashboard') }}" class="text-sm text-[#a09070] hover:text-[#f5f0e8] transition-colors">
                        My Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-[#a09070] hover:text-[#f5f0e8] transition-colors">
                            Sign Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('auth.google') }}"
                       class="bg-[#c9a84c] hover:bg-[#b8973b] text-black text-sm font-medium px-5 py-2 rounded transition-colors">
                        Apply for a Profile
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="bg-[#1a2a1a] border border-[#2a4a2a] text-[#8bc48b] px-4 py-3 rounded text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="bg-[#2a1a1a] border border-[#4a2a2a] text-[#c48b8b] px-4 py-3 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-[#2a2a2a] mt-24">
        <div class="max-w-7xl mx-auto px-6 py-12">
            <div class="flex flex-col md:flex-row justify-between gap-8">
                <div>
                    <p class="text-[#c9a84c] font-semibold text-lg">IconsHerald</p>
                    <p class="text-[#7a6a55] text-sm mt-1 italic">Herald Yourself.</p>
                    <p class="text-[#7a6a55] text-sm mt-2">
                        <a href="mailto:hello@iconsherald.com" class="hover:text-[#a09070] transition-colors">hello@iconsherald.com</a>
                    </p>
                </div>

                <div class="flex gap-12 text-sm text-[#7a6a55]">
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('register.index') }}" class="hover:text-[#a09070] transition-colors">The Register</a>
                        <a href="{{ route('pricing') }}" class="hover:text-[#a09070] transition-colors">Pricing</a>
                        <a href="{{ route('apply') }}" class="hover:text-[#a09070] transition-colors">Apply</a>
                        <a href="{{ route('about') }}" class="hover:text-[#a09070] transition-colors">About</a>
                        <a href="{{ route('contact') }}" class="hover:text-[#a09070] transition-colors">Contact</a>
                    </div>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('in-memoriam.index') }}" class="hover:text-[#a09070] transition-colors">In Memoriam</a>
                        <a href="{{ route('terms') }}" class="hover:text-[#a09070] transition-colors">Terms</a>
                        <a href="{{ route('privacy') }}" class="hover:text-[#a09070] transition-colors">Privacy</a>
                        <a href="{{ route('refund-policy') }}" class="hover:text-[#a09070] transition-colors">Refund Policy</a>
                    </div>
                </div>
            </div>

            <p class="text-[#4a4a4a] text-xs mt-8 text-center">
                &copy; {{ date('Y') }} IconsHerald / Oreon Dynamics Pvt Ltd (proposed). All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>
