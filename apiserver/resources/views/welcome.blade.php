<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Commerinity') }} - Backend System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=work-sans:300,400,500,600,700|orbitron:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes rotateReverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes flowCircuit {
            0% { stroke-dashoffset: 1000; }
            100% { stroke-dashoffset: 0; }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .gradient-text {
            background: linear-gradient(90deg, #00ffcc, #0088ff, #00ffcc);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 3s linear infinite;
        }

        body {
            font-family: 'Work Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-black text-white overflow-hidden selection:bg-[#00ffcc] selection:text-black">

<!-- Animated SVG Background -->
<div class="fixed inset-0 z-0 overflow-hidden opacity-20">
    <!-- Large Gear - Top Left -->
    <svg class="absolute -top-24 -left-24 w-64 h-64 text-[#00ffcc]/30" style="animation: rotate 20s linear infinite;" viewBox="0 0 100 100">
        <path d="M50,10 L54,20 L46,20 Z M50,90 L54,80 L46,80 Z M10,50 L20,54 L20,46 Z M90,50 L80,54 L80,46 Z M20,20 L28,28 L22,32 Z M80,20 L72,28 L78,32 Z M20,80 L28,72 L22,68 Z M80,80 L72,72 L78,68 Z" fill="currentColor"/>
        <circle cx="50" cy="50" r="25" fill="none" stroke="currentColor" stroke-width="2"/>
        <circle cx="50" cy="50" r="15" fill="currentColor"/>
    </svg>

    <!-- Medium Gear - Top Right -->
    <svg class="absolute -top-12 right-20 w-40 h-40 text-[#0088ff]/30" style="animation: rotateReverse 15s linear infinite;" viewBox="0 0 100 100">
        <path d="M50,15 L53,22 L47,22 Z M50,85 L53,78 L47,78 Z M15,50 L22,53 L22,47 Z M85,50 L78,53 L78,47 Z M25,25 L31,31 L26,34 Z M75,25 L69,31 L74,34 Z M25,75 L31,69 L26,66 Z M75,75 L69,69 L74,66 Z" fill="currentColor"/>
        <circle cx="50" cy="50" r="20" fill="none" stroke="currentColor" stroke-width="2"/>
        <circle cx="50" cy="50" r="12" fill="currentColor"/>
    </svg>

    <!-- Small Gear - Bottom Left -->
    <svg class="absolute bottom-20 left-32 w-32 h-32 text-[#ff0080]/30" style="animation: rotate 12s linear infinite;" viewBox="0 0 100 100">
        <path d="M50,20 L52,26 L48,26 Z M50,80 L52,74 L48,74 Z M20,50 L26,52 L26,48 Z M80,50 L74,52 L74,48 Z M30,30 L35,35 L31,37 Z M70,30 L65,35 L69,37 Z M30,70 L35,65 L31,63 Z M70,70 L65,65 L69,63 Z" fill="currentColor"/>
        <circle cx="50" cy="50" r="18" fill="none" stroke="currentColor" stroke-width="2"/>
        <circle cx="50" cy="50" r="10" fill="currentColor"/>
    </svg>

    <!-- Large Gear - Bottom Right -->
    <svg class="absolute -bottom-20 -right-20 w-72 h-72 text-[#00ffcc]/20" style="animation: rotateReverse 25s linear infinite;" viewBox="0 0 100 100">
        <path d="M50,5 L55,18 L45,18 Z M50,95 L55,82 L45,82 Z M5,50 L18,55 L18,45 Z M95,50 L82,55 L82,45 Z M18,18 L28,28 L20,34 Z M82,18 L72,28 L80,34 Z M18,82 L28,72 L20,66 Z M82,82 L72,72 L80,66 Z" fill="currentColor"/>
        <circle cx="50" cy="50" r="28" fill="none" stroke="currentColor" stroke-width="2"/>
        <circle cx="50" cy="50" r="18" fill="currentColor"/>
    </svg>

    <!-- Circuit Lines -->
    <svg class="absolute inset-0 w-full h-full opacity-40" xmlns="http://www.w3.org/2000/svg">
        <!-- Horizontal Circuit Lines -->
        <line x1="0" y1="30%" x2="100%" y2="30%" stroke="#00ffcc" stroke-width="1" stroke-dasharray="4 4" style="animation: pulse 2s ease-in-out infinite;"/>
        <line x1="0" y1="70%" x2="100%" y2="70%" stroke="#0088ff" stroke-width="1" stroke-dasharray="4 4" style="animation: pulse 2.5s ease-in-out infinite;"/>

        <!-- Vertical Circuit Lines -->
        <line x1="25%" y1="0" x2="25%" y2="100%" stroke="#ff0080" stroke-width="1" stroke-dasharray="4 4" style="animation: pulse 3s ease-in-out infinite;"/>
        <line x1="75%" y1="0" x2="75%" y2="100%" stroke="#00ffcc" stroke-width="1" stroke-dasharray="4 4" style="animation: pulse 2.2s ease-in-out infinite;"/>

        <!-- Circuit Nodes -->
        <circle cx="25%" cy="30%" r="3" fill="#00ffcc" style="animation: pulse 1.5s ease-in-out infinite;"/>
        <circle cx="75%" cy="30%" r="3" fill="#0088ff" style="animation: pulse 1.8s ease-in-out infinite;"/>
        <circle cx="25%" cy="70%" r="3" fill="#ff0080" style="animation: pulse 2.1s ease-in-out infinite;"/>
        <circle cx="75%" cy="70%" r="3" fill="#00ffcc" style="animation: pulse 1.6s ease-in-out infinite;"/>
    </svg>
</div>

<!-- Main Content -->
<div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 py-12">

    <!-- Corner Tech Brackets -->
    <div class="fixed top-0 left-0 w-20 sm:w-32 h-20 sm:h-32 opacity-0" style="animation: fadeIn 1s ease-out 0.3s forwards;">
        <svg class="w-full h-full text-[#00ffcc]/40" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M 30 0 L 0 0 L 0 30"/>
            <path d="M 15 0 L 15 15 L 0 15"/>
        </svg>
    </div>
    <div class="fixed top-0 right-0 w-20 sm:w-32 h-20 sm:h-32 opacity-0" style="animation: fadeIn 1s ease-out 0.3s forwards;">
        <svg class="w-full h-full text-[#00ffcc]/40" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M 70 0 L 100 0 L 100 30"/>
            <path d="M 85 0 L 85 15 L 100 15"/>
        </svg>
    </div>
    <div class="fixed bottom-0 left-0 w-20 sm:w-32 h-20 sm:h-32 opacity-0" style="animation: fadeIn 1s ease-out 0.3s forwards;">
        <svg class="w-full h-full text-[#00ffcc]/40" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M 30 100 L 0 100 L 0 70"/>
            <path d="M 15 100 L 15 85 L 0 85"/>
        </svg>
    </div>
    <div class="fixed bottom-0 right-0 w-20 sm:w-32 h-20 sm:h-32 opacity-0" style="animation: fadeIn 1s ease-out 0.3s forwards;">
        <svg class="w-full h-full text-[#00ffcc]/40" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M 70 100 L 100 100 L 100 70"/>
            <path d="M 85 100 L 85 85 L 100 85"/>
        </svg>
    </div>

    <!-- Central Logo with Rotating Gear -->
    <div class="mb-8 sm:mb-12 opacity-0" style="animation: scaleIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s forwards;">
        <div class="relative group">
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-[#00ffcc]/20 blur-3xl rounded-full scale-150 group-hover:scale-[1.8] transition-transform duration-700"></div>

            <!-- Rotating Outer Gear -->
            <div class="relative w-28 h-28 sm:w-36 sm:h-36 flex items-center justify-center">
                <svg class="absolute inset-0 w-full h-full text-[#00ffcc]/50" style="animation: rotate 10s linear infinite;" viewBox="0 0 100 100">
                    <path d="M50,8 L54,18 L46,18 Z M50,92 L54,82 L46,82 Z M8,50 L18,54 L18,46 Z M92,50 L82,54 L82,46 Z M18,18 L26,26 L20,30 Z M82,18 L74,26 L80,30 Z M18,82 L26,74 L20,70 Z M82,82 L74,74 L80,70 Z" fill="currentColor"/>
                    <circle cx="50" cy="50" r="28" fill="none" stroke="currentColor" stroke-width="1.5"/>
                </svg>

                <!-- Inner Rotating Gear -->
                <svg class="absolute inset-4 w-20 h-20 sm:w-28 sm:h-28 text-[#0088ff]/60" style="animation: rotateReverse 8s linear infinite;" viewBox="0 0 100 100">
                    <path d="M50,15 L53,23 L47,23 Z M50,85 L53,77 L47,77 Z M15,50 L23,53 L23,47 Z M85,50 L77,53 L77,47 Z M25,25 L32,32 L27,35 Z M75,25 L68,32 L73,35 Z M25,75 L32,68 L27,65 Z M75,75 L68,68 L73,65 Z" fill="currentColor"/>
                    <circle cx="50" cy="50" r="22" fill="none" stroke="currentColor" stroke-width="1.5"/>
                </svg>

                <!-- Central Icon -->
                <svg class="w-12 h-12 sm:w-16 sm:h-16 relative z-10 text-[#00ffcc] drop-shadow-[0_0_15px_rgba(0,255,204,0.8)]" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="text-center mb-6 sm:mb-8 max-w-4xl opacity-0" style="animation: slideDown 0.8s ease-out 0.2s forwards;">
        <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black mb-3 leading-tight tracking-wider"
            style="font-family: 'Orbitron', sans-serif; text-shadow: 0 0 40px rgba(0, 255, 204, 0.5), 0 0 80px rgba(0, 255, 204, 0.3);">
                    <span class="gradient-text">
                        {{ config('app.name', 'Commerinity') }}
                    </span>
        </h1>

        <!-- Subtitle -->
        <div class="flex items-center justify-center gap-3 sm:gap-4 flex-wrap px-4">
            <div class="h-px w-10 sm:w-16 bg-gradient-to-r from-transparent via-[#00ffcc]/50 to-transparent"></div>
            <p class="text-xs sm:text-sm md:text-base tracking-[0.35em] uppercase text-[#00ffcc]/80 font-mono">
                Backend API Server
            </p>
            <div class="h-px w-10 sm:w-16 bg-gradient-to-l from-transparent via-[#00ffcc]/50 to-transparent"></div>
        </div>
    </div>

    <!-- System Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mb-8 sm:mb-10 w-full max-w-3xl opacity-0 px-4"
         style="animation: slideUp 0.8s ease-out 0.4s forwards;">

        <!-- Status -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-[#00ffcc]/20 to-[#0088ff]/20 rounded-lg blur opacity-50 group-hover:opacity-75 transition"></div>
            <div class="relative bg-black border border-[#00ffcc]/30 rounded-lg p-4 sm:p-5 hover:border-[#00ffcc]/50 transition-all duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] sm:text-xs text-white/60 uppercase tracking-widest font-mono">Status</span>
                    <div class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00ffcc] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00ffcc] shadow-[0_0_10px_#00ffcc]"></span>
                    </div>
                </div>
                <p class="text-base sm:text-lg font-bold text-[#00ffcc]" style="font-family: 'Orbitron', sans-serif;">OPERATIONAL</p>
                <div class="mt-2 h-1 bg-[#00ffcc]/10 rounded-full overflow-hidden">
                    <div class="h-full bg-[#00ffcc] rounded-full" style="width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Version -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-[#0088ff]/20 to-[#00ffcc]/20 rounded-lg blur opacity-50 group-hover:opacity-75 transition"></div>
            <div class="relative bg-black border border-[#0088ff]/30 rounded-lg p-4 sm:p-5 hover:border-[#0088ff]/50 transition-all duration-300">
                <div class="mb-2">
                    <span class="text-[10px] sm:text-xs text-white/60 uppercase tracking-widest font-mono">API Version</span>
                </div>
                <p class="text-base sm:text-lg font-bold text-[#0088ff]" style="font-family: 'Orbitron', sans-serif;">v2.0.1</p>
                <div class="mt-2 flex gap-1">
                    <div class="h-1 flex-1 bg-[#0088ff] rounded-full"></div>
                    <div class="h-1 flex-1 bg-[#0088ff]/60 rounded-full"></div>
                    <div class="h-1 flex-1 bg-[#0088ff]/30 rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- Uptime -->
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-[#ff0080]/20 to-[#0088ff]/20 rounded-lg blur opacity-50 group-hover:opacity-75 transition"></div>
            <div class="relative bg-black border border-[#ff0080]/30 rounded-lg p-4 sm:p-5 hover:border-[#ff0080]/50 transition-all duration-300">
                <div class="mb-2">
                    <span class="text-[10px] sm:text-xs text-white/60 uppercase tracking-widest font-mono">Uptime</span>
                </div>
                <p class="text-base sm:text-lg font-bold text-[#ff0080]" style="font-family: 'Orbitron', sans-serif;">99.9%</p>
                <div class="mt-2 h-1 bg-[#ff0080]/10 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#ff0080] to-[#0088ff] rounded-full" style="width: 99.9%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Access Info Card -->
    <div class="max-w-2xl w-full opacity-0 px-4" style="animation: fadeIn 0.8s ease-out 0.6s forwards;">
        <div class="relative group">
            <div class="absolute -inset-px bg-gradient-to-r from-[#00ffcc]/30 via-[#0088ff]/30 to-[#ff0080]/30 rounded-lg blur opacity-40 group-hover:opacity-60 transition"></div>
            <div class="relative bg-black/90 backdrop-blur-sm border border-[#00ffcc]/30 rounded-lg p-5 sm:p-7">
                <div class="flex items-start gap-3 sm:gap-4">
                    <!-- Shield Icon -->
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-[#00ffcc]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <!-- Text -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-white mb-2" style="font-family: 'Orbitron', sans-serif;">RESTRICTED ACCESS</h3>
                        <p class="text-sm sm:text-base text-white/70 leading-relaxed">
                            This is a private backend API server. Access to the admin panel and API endpoints requires proper authentication and authorization.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Time -->
    <div class="mt-8 sm:mt-10 text-center opacity-0" style="animation: fadeIn 0.8s ease-out 0.8s forwards;">
        <div class="inline-flex items-center gap-2 px-5 sm:px-7 py-2.5 sm:py-3 bg-black/80 backdrop-blur-sm border border-[#00ffcc]/25 rounded-full hover:border-[#00ffcc]/40 transition-colors">
            <svg class="w-3 h-3 text-[#00ffcc] animate-pulse" fill="currentColor" viewBox="0 0 8 8">
                <circle cx="4" cy="4" r="3" />
            </svg>
            <span class="text-xs sm:text-sm text-white/80 font-mono tracking-wider" id="system-time">
                        SYS_TIME: LOADING...
                    </span>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="fixed bottom-4 sm:bottom-6 left-0 right-0 text-center pointer-events-none opacity-0" style="animation: fadeIn 1s ease-out 1s forwards;">
    <p class="text-[10px] sm:text-xs text-white/25 tracking-[0.2em] uppercase font-mono">
        &copy; {{ date('Y') }} {{ config('app.name') }}. ALL SYSTEMS OPERATIONAL.
    </p>
</div>

<!-- Real-time Clock Script -->
<script>
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleString('en-US', {
            hour12: false,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).replace(/\//g, '-').replace(',', '');
        document.getElementById('system-time').textContent = `SYS_TIME: ${timeString}`;
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>
</body>
</html>
