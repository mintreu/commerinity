<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Commerinity') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                @import "tailwindcss";

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                .animate-fade-in-up {
                    animation: fadeInUp 0.8s ease-out forwards;
                }

                .delay-300 {
                    animation-delay: 300ms;
                }

                .delay-500 {
                    animation-delay: 500ms;
                }
            </style>
        @endif
    </head>
    <body class="bg-[#0a0a0a] text-white flex items-center justify-center min-h-screen font-sans antialiased overflow-hidden selection:bg-indigo-500 selection:text-white">

        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 z-0">
            <img
                src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
                alt="Background"
                class="w-full h-full object-cover opacity-60"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/80 to-[#0a0a0a]/40"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150 mix-blend-overlay"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center justify-center space-y-8 p-12 ">

            <!-- Logo/Icon Animation -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative w-24 h-24 bg-black/50 backdrop-blur-xl rounded-2xl border border-white/10 flex items-center justify-center shadow-2xl">
                    <svg class="w-12 h-12 text-white/90" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <!-- Text Content -->
            <div class="text-center space-y-6 max-w-2xl">
                <h1 class="text-6xl md:text-8xl font-black tracking-tighter bg-clip-text text-transparent bg-gradient-to-b from-white via-white/90 to-white/50 drop-shadow-2xl animate-fade-in-up">
                    {{ config('app.name', 'Commerinity') }}
                </h1>

                <div class="flex items-center justify-center space-x-4 opacity-0 animate-fade-in-up delay-300" style="animation-fill-mode: forwards;">
                    <div class="h-px w-12 bg-gradient-to-r from-transparent to-white/30"></div>
                    <p class="text-xl md:text-2xl text-white/70 font-medium tracking-[0.2em] uppercase">Backend API Server</p>
                    <div class="h-px w-12 bg-gradient-to-l from-transparent to-white/30"></div>
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="mt-12 relative group cursor-default opacity-0 animate-fade-in-up delay-500" style="animation-fill-mode: forwards;">
                <div class="flex items-center space-x-3 px-5 py-2.5 bg-white/5 backdrop-blur-md rounded-full border border-white/10 hover:bg-white/10 transition-colors duration-300">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-sm font-medium text-white/90">System Operational</span>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="absolute bottom-6 text-white/20 text-[10px] tracking-widest uppercase">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </body>
</html>
