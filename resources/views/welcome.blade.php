<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SAKU V2') }} - Sistem Administrasi Keuangan</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 dark:bg-zinc-950 dark:text-gray-100 selection:bg-emerald-500 selection:text-white">

    <!-- Navigation -->
    <nav class="fixed top-0 z-50 w-full transition-all duration-300 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-gray-200/50 dark:border-zinc-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo_full_h.png') }}" alt="SAKU Logo" class="h-10 w-auto hidden dark:block object-contain filter brightness-200">
                    <img src="{{ asset('logo_full_h.png') }}" alt="SAKU Logo" class="h-10 w-auto block dark:hidden object-contain">
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#hero" class="text-sm font-medium text-gray-700 hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400 transition-colors">Beranda</a>
                    <a href="#fitur" class="text-sm font-medium text-gray-700 hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400 transition-colors">Fitur</a>
                    <a href="#kontak" class="text-sm font-medium text-gray-700 hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400 transition-colors">Kontak & Pengaduan</a>
                </div>
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-300 bg-emerald-600 rounded-full hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-950">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400 transition-colors">Masuk</a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white transition-all duration-300 bg-emerald-600 rounded-full hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-500/30 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-950">
                                Mulai Sekarang
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden flex items-center min-h-[90vh]">
        <!-- Background Effects -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 -left-4 w-72 h-72 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-teal-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-emerald-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center">
            <div class="inline-flex items-center px-3 py-1.5 mb-8 rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-sm font-semibold tracking-wide">
                <span class="flex w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                SAKU V2 Kini Hadir
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-6 leading-tight">
                Kelola Keuangan Lembaga <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-400">Lebih Cerdas & Akurat</span>
            </h1>
            
            <p class="max-w-2xl mx-auto text-lg lg:text-xl text-gray-600 dark:text-gray-300 mb-10 leading-relaxed">
                Sistem Administrasi Keuangan (SAKU) V2 hadir memberikan kemudahan pencatatan tagihan dan pembayaran secara terpusat, transparan, dan real-time.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-white transition-all duration-300 bg-emerald-600 rounded-full hover:bg-emerald-700 hover:shadow-xl hover:shadow-emerald-500/40 hover:-translate-y-1">
                    Masuk ke Sistem
                    <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </a>
                <a href="#fitur" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-gray-700 dark:text-gray-200 transition-all duration-300 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-full hover:bg-gray-50 dark:hover:bg-zinc-700 hover:shadow-lg hover:-translate-y-1">
                    Pelajari Fitur
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-24 bg-white dark:bg-zinc-900 border-t border-gray-100 dark:border-zinc-800 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl mb-4">
                    Keunggulan SAKU V2
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Didesain khusus untuk memenuhi kebutuhan administrasi yang cepat, akurat, dan dapat diandalkan oleh berbagai lembaga.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="relative group bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-8 transition-all duration-300 hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-2 border border-gray-200 dark:border-zinc-700/50 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all duration-300"></div>
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Otomatis</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Semua pembayaran dan transaksi langsung tercatat secara otomatis ke dalam sistem buku besar tanpa input ganda.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="relative group bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-8 transition-all duration-300 hover:shadow-2xl hover:shadow-teal-500/10 hover:-translate-y-2 border border-gray-200 dark:border-zinc-700/50 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-500/10 rounded-full blur-xl group-hover:bg-teal-500/20 transition-all duration-300"></div>
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-teal-100 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Transparan</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Tagihan, pembayaran, dan tabungan dapat dipantau secara transparan dan real-time oleh pengurus.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="relative group bg-gray-50 dark:bg-zinc-800/50 rounded-2xl p-8 transition-all duration-300 hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-2 border border-gray-200 dark:border-zinc-700/50 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all duration-300"></div>
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Pemberitahuan</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Kirimkan notifikasi, kwitansi, dan pemberitahuan tagihan langsung ke WhatsApp wali murid secara instan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Pengaduan -->
    <section id="kontak" class="py-24 bg-gray-50 dark:bg-zinc-950 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl overflow-hidden border border-gray-200 dark:border-zinc-800">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    
                    <!-- Contact Info -->
                    <div class="p-10 lg:p-16 bg-emerald-600 dark:bg-emerald-900 text-white flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-500 dark:bg-emerald-800 rounded-full opacity-50 blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-teal-500 dark:bg-teal-800 rounded-full opacity-50 blur-3xl"></div>
                        
                        <div class="relative z-10">
                            <h2 class="text-3xl font-bold mb-6">Hubungi Kami</h2>
                            <p class="text-emerald-50 dark:text-emerald-100 mb-10 text-lg leading-relaxed">
                                Memiliki pertanyaan mengenai SAKU V2 atau butuh bantuan? Tim kami siap membantu Anda dengan cepat.
                            </p>
                            
                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <svg class="w-6 h-6 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium">Alamat</h4>
                                        <p class="text-emerald-100 mt-1">Jl. Pendidikan No. 1, Blitar<br>Jawa Timur, Indonesia</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <svg class="w-6 h-6 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium">Email</h4>
                                        <p class="text-emerald-100 mt-1">info@saku-v2.local</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <svg class="w-6 h-6 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-lg font-medium">Telepon / WhatsApp</h4>
                                        <p class="text-emerald-100 mt-1">+62 812-3456-7890</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Pengaduan -->
                    <div class="p-10 lg:p-16">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Form Pengaduan</h3>
                        <form onsubmit="event.preventDefault(); alert('Terima kasih. Pesan pengaduan Anda telah kami terima dan akan segera diproses.'); this.reset();" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                                <input type="text" id="name" required class="mt-2 block w-full rounded-xl border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-4 py-3 transition-colors">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email atau WhatsApp</label>
                                <input type="text" id="email" required class="mt-2 block w-full rounded-xl border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-4 py-3 transition-colors">
                            </div>
                            
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pesan Pengaduan</label>
                                <textarea id="message" rows="4" required class="mt-2 block w-full rounded-xl border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm px-4 py-3 transition-colors"></textarea>
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all hover:shadow-lg hover:shadow-emerald-500/30">
                                    Kirim Pengaduan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 py-12 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-8 w-auto grayscale opacity-70">
                <span class="text-xl font-bold text-gray-400 dark:text-gray-500">SAKU V2</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center md:text-left">
                &copy; {{ date('Y') }} Sistem Administrasi Keuangan Terpadu. All rights reserved.
            </p>
            <div class="flex space-x-6">
                <a href="#" class="text-gray-400 hover:text-emerald-500 transition-colors">
                    <span class="sr-only">Facebook</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                </a>
                <a href="#" class="text-gray-400 hover:text-emerald-500 transition-colors">
                    <span class="sr-only">Instagram</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                </a>
            </div>
        </div>
    </footer>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</body>
</html>
