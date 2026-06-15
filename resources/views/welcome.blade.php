<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Öğrenci Takip ve Koçluk Sistemi</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #F5F3EF;
        }
        .beige-gradient {
            background: linear-gradient(135deg, #F5F3EF 0%, #E8E4DC 100%);
        }
        .accent-blue {
            color: #7C9CBF;
        }
        .accent-green {
            color: #8FA998;
        }
        .bg-accent-blue {
            background-color: #7C9CBF;
        }
        .bg-accent-green {
            background-color: #8FA998;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <span class="text-2xl font-bold text-gray-800">rehber<span class="accent-blue">koçum</span></span>
        </div>
        
        <nav class="flex items-center space-x-4">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isCoach() ? route('coach.dashboard') : route('student.dashboard')) }}" 
                   class="px-5 py-2 bg-accent-blue text-white rounded-lg font-medium shadow-sm hover:opacity-95 transition">
                    Panele Git
                </a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2 text-gray-700 font-medium hover:text-gray-900 transition">
                    Giriş Yap
                </a>
            @endauth
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-6 py-12 flex flex-col lg:flex-row items-center gap-12">
        <!-- Left Column: Hero & Demo Access -->
        <div class="flex-1 space-y-8">
            <div class="space-y-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                    🚀 Yeni Nesil Koçluk Platformu
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight">
                    Öğrencilerinizi <br>
                    <span class="accent-blue">Akıllı İlerleme</span> ile Takip Edin
                </h1>
                <p class="text-lg text-gray-600 max-w-lg">
                    Sınava hazırlanan öğrenciler için ders, konu, soru çözümü ve deneme sonuçlarını tek bir merkezden yönetin. Koç ve öğrenci arasındaki bağı güçlendirin.
                </p>
            </div>

            <!-- Demo Quick Login Buttons -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200/80 space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Hızlı Demo Girişleri</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Admin Login -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="email" value="admin@ogrenci.com">
                        <input type="hidden" name="password" value="password">
                        <button type="submit" class="w-full py-3 px-4 bg-gray-900 text-white rounded-xl font-semibold hover:bg-gray-800 transition flex flex-col items-center justify-center text-xs">
                            🔑 Demo Admin
                            <span class="text-[10px] text-gray-400 font-normal">Sistem Yönetimi</span>
                        </button>
                    </form>

                    <!-- Coach Login -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="email" value="coach1@ogrenci.com">
                        <input type="hidden" name="password" value="password">
                        <button type="submit" class="w-full py-3 px-4 bg-accent-blue text-white rounded-xl font-semibold hover:opacity-95 transition flex flex-col items-center justify-center text-xs">
                            🧠 Demo Koç
                            <span class="text-[10px] text-blue-100 font-normal">Öğrenci & Program</span>
                        </button>
                    </form>

                    <!-- Student Login -->
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="email" value="student1@ogrenci.com">
                        <input type="hidden" name="password" value="password">
                        <button type="submit" class="w-full py-3 px-4 bg-accent-green text-white rounded-xl font-semibold hover:opacity-95 transition flex flex-col items-center justify-center text-xs">
                            🎓 Demo Öğrenci
                            <span class="text-[10px] text-green-100 font-normal">Soru & Deneme Kaydı</span>
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-500 text-center italic mt-2">
                    💡 Butonlara tıklayarak doğrudan demo panellerine erişebilirsiniz. Şifreler otomatik doldurulur.
                </p>
            </div>
        </div>

        <!-- Right Column: Pricing & Info Cards -->
        <div class="w-full lg:w-[480px] space-y-6">
            <!-- Features Overview Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                <h3 class="text-lg font-bold text-gray-900">Neden Biz?</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="p-1 bg-blue-50 text-accent-blue rounded-lg mt-0.5">✓</span>
                        <div>
                            <span class="font-bold text-gray-800 text-sm block">Hiyerarşik Ders Takibi</span>
                            <span class="text-xs text-gray-500">Alan, ders, konu ve alt konuları detaylıca yapılandırın.</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="p-1 bg-green-50 text-accent-green rounded-lg mt-0.5">✓</span>
                        <div>
                            <span class="font-bold text-gray-800 text-sm block">Esnek Program Sihirbazı</span>
                            <span class="text-xs text-gray-500">Saatli ya da saatsiz haftalık çalışma programı hazırlayıp atayın.</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="p-1 bg-purple-50 text-purple-600 rounded-lg mt-0.5">✓</span>
                        <div>
                            <span class="font-bold text-gray-800 text-sm block">Gelişmiş Başarı Grafikleri</span>
                            <span class="text-xs text-gray-500">Soru çözümü ve deneme gelişimlerini akıllı grafiklerle analiz edin.</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Subscription Packages Box -->
            <div class="bg-[#E8E4DC] rounded-2xl p-6 border border-gray-300/60 space-y-4">
                <h3 class="text-sm font-bold text-gray-600 uppercase tracking-wider">Abonelik Paketleri</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white p-3 rounded-xl border border-gray-200">
                        <span class="text-xs font-semibold text-gray-500">Başlangıç</span>
                        <div class="text-lg font-bold text-gray-900">₺199<span class="text-xs font-normal">/ay</span></div>
                        <span class="text-[10px] text-gray-500 block">10 Öğrenci</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-gray-200">
                        <span class="text-xs font-semibold text-gray-500">Standart</span>
                        <div class="text-lg font-bold text-gray-900">₺399<span class="text-xs font-normal">/ay</span></div>
                        <span class="text-[10px] text-gray-500 block">25 Öğrenci</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-gray-200">
                        <span class="text-xs font-semibold text-gray-500">Premium</span>
                        <div class="text-lg font-bold text-gray-900">₺699<span class="text-xs font-normal">/ay</span></div>
                        <span class="text-[10px] text-gray-500 block">50 Öğrenci</span>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-gray-200">
                        <span class="text-xs font-semibold text-gray-500">Sınırsız</span>
                        <div class="text-lg font-bold text-gray-900">₺999<span class="text-xs font-normal">/ay</span></div>
                        <span class="text-[10px] text-gray-500 block">Sınırsız Öğrenci</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-8 border-t border-gray-300/40 text-center text-xs text-gray-500">
        <p>&copy; 2026 rehberkoçum. Tüm hakları saklıdır. Özel kullanım lisansı.</p>
    </footer>

</body>
</html>
