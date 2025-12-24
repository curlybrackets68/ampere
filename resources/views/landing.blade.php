<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chirag Automative</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .animated-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
    </style>
</head>

<body class="antialiased text-slate-800 selection:bg-brand-500 selection:text-white">

    <!-- Background -->
    <div class="fixed inset-0 animated-bg -z-10 bg-slate-900"></div>
    <div class="fixed inset-0 bg-black/10 -z-10"></div>

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col">

        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-50 flex justify-center px-4 mt-4">
            <div class="w-full max-w-7xl glass-panel rounded-2xl px-6 py-4 flex justify-between items-center shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg text-brand-600">

                        <img src="{{ asset('assets/assets/img/logo.png') }}">
                    </div>
                    <span class="text-xl font-bold tracking-tight text-brand-900">Chirag Automative</span>
                </div>

                <nav class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-brand-900 font-medium hover:text-brand-600 transition-colors">Home</a>
                    <a href="#about" class="text-brand-900 font-medium hover:text-brand-600 transition-colors">About
                        Us</a>
                    <a href="#contact"
                        class="text-brand-900 font-medium hover:text-brand-600 transition-colors">Contact</a>
                </nav>

                <div>
                    <a href="{{ route('auth.show-login') }}"
                        class="group relative inline-flex items-center justify-center px-6 py-2 text-sm font-semibold text-white transition-all duration-200 bg-brand-600 rounded-full hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600 shadow-lg">
                        <span>Login</span>
                        <svg class="w-4 h-4 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Content Padding for Fixed Header -->
        <div class="h-24"></div>

        <!-- Hero Section -->
        <section id="home" class="min-h-[80vh] flex items-center justify-center px-6 relative">
            <div class="max-w-4xl w-full text-center text-white">
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-8 drop-shadow-lg leading-tight">
                    Power Your <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-amber-400">Future
                        Workflow</span>
                </h1>

                <p
                    class="text-xl md:text-2xl text-white/90 mb-12 max-w-2xl mx-auto font-light leading-relaxed drop-shadow">
                    Experience the next generation of management. Streamlined, efficient, and designed for growth.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#about"
                        class="px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-full font-bold text-lg hover:bg-white/20 transition-all duration-300">
                        Learn More
                    </a>
                    <a href="{{ route('auth.show-login') }}"
                        class="px-8 py-4 bg-white text-brand-900 rounded-full font-bold text-lg shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)] transform hover:-translate-y-1 transition-all duration-300">
                        Get Started
                    </a>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="about" class="py-20 px-6">
            <div class="max-w-6xl mx-auto">
                <div class="glass-card rounded-3xl p-8 md:p-12 text-white">
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold mb-6">About Us</h2>
                            <p class="text-lg text-white/80 leading-relaxed mb-6">
                                Chirag Automotive is redefining urban mobility with a premium range of electric
                                two-wheelers designed for performance, style, and sustainability.
                                Driven by innovation and backed by reliability, our EVs are built to deliver a smooth,
                                powerful, and eco-friendly riding experience for modern commuters.
                            </p>
                            <p class="text-lg text-white/80 leading-relaxed">
                                We believe in making electric mobility accessible, affordable, and exciting. From
                                advanced battery technology to sleek contemporary designs, every Chirag Automotive
                                vehicle reflects our commitment to quality, efficiency, and a greener tomorrow.
                            </p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/10">
                            <ul class="space-y-4">
                                <li class="flex items-center gap-4">
                                    <span
                                        class="w-8 h-8 rounded-full bg-green-400/20 flex items-center justify-center text-green-400">✓</span>
                                    <span class="text-lg">Advanced EV Technology</span>
                                </li>
                                <li class="flex items-center gap-4">
                                    <span
                                        class="w-8 h-8 rounded-full bg-blue-400/20 flex items-center justify-center text-blue-400">✓</span>
                                    <span class="text-lg">Cost-Effective & Eco-Friendly</span>
                                </li>
                                <li class="flex items-center gap-4">
                                    <span
                                        class="w-8 h-8 rounded-full bg-purple-400/20 flex items-center justify-center text-purple-400">✓</span>
                                    <span class="text-lg">Premium Design & Comfort</span>
                                </li>
                                <li class="flex items-center gap-4">
                                    <span
                                        class="w-8 h-8 rounded-full bg-yellow-400/20 flex items-center justify-center text-yellow-400">✓</span>
                                    <span class="text-lg">Trusted Support & Service</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location & Contact Section -->
        <section id="contact" class="py-20 px-6">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-white mb-12">Get in Touch</h2>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Contact Details -->
                    <div class="glass-card rounded-3xl p-8 hover:bg-white/10 transition-colors">
                        <div class="flex items-start gap-4 mb-8">
                            <div
                                class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">Email Us</h3>
                                <p class="text-white/70">automotivechirag@gmail.com</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">Call Us</h3>
                                <p class="text-white/70">Sama: 90233 42463</p>
                                <p class="text-white/70">Kalali: 97241 36574</p>
                                <p class="text-white/70">Mon - Sat, 9am - 6pm</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="glass-card rounded-3xl p-8 hover:bg-white/10 transition-colors">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center text-red-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-4">Our Location</h3>
                                <p class="text-white/70 mb-2">Ground Floor, Eartheon Complex, Shop No, 23-24, Road,
                                    opposite Urmi High School, New Sama, Vadodara, Gujarat 390024</p>

                                <div class="w-full h-40 bg-white/10 rounded-xl overflow-hidden relative">
                                    <!-- Placeholder map visual -->
                                    <div class="absolute inset-0 bg-slate-700 opacity-50"></div>
                                    <div
                                        class="absolute inset-0 flex items-center justify-center text-white/50 text-xs">
                                        <iframe
                                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d27023.9217557087!2d73.16565167431642!3d22.3414608!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395fcf8168f6e429%3A0xb8d1cc3a6c25c4d9!2sAmpere%20EV%20by%20Greaves%20-%20Chirag%20Automotive%20Sama!5e1!3m2!1sen!2sin!4v1766569602058!5m2!1sen!2sin"
                                            width="600" height="450" style="border:0;" allowfullscreen=""
                                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="w-full py-8 text-center text-white/60 text-sm z-10 border-t border-white/10 bg-black/20 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; {{ date('Y') }} Chirag Automotive. All rights reserved.</p>
                {{-- <div class="flex gap-6">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Contact</a>
                </div> --}}
            </div>
        </footer>
    </div>

</body>

</html>
