<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Abonelik Sona Erdi - Öğrenci Takip Sistemi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Üst kırmızı bant */
        .card-header {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255,255,255,0.4);
        }

        .icon-circle svg {
            width: 42px;
            height: 42px;
            color: #fff;
        }

        .card-header h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }

        .card-header p {
            color: rgba(255,255,255,0.85);
            font-size: 0.95rem;
        }

        /* İçerik */
        .card-body {
            padding: 2rem;
        }

        /* Kullanıcı bilgisi */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            background: #f8f9ff;
            border: 1px solid #e5e7eb;
            border-radius: 0.85rem;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }

        .avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .user-info-text .name {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.95rem;
        }

        .user-info-text .email {
            color: #6b7280;
            font-size: 0.82rem;
        }

        /* Abonelik bilgisi */
        .subscription-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 0.85rem;
            padding: 1rem 1.2rem;
            margin-bottom: 1.8rem;
            font-size: 0.88rem;
        }

        .subscription-box .label {
            color: #92400e;
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .subscription-box .value {
            color: #b45309;
        }

        /* Bilgi notu */
        .info-note {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 0 0.6rem 0.6rem 0;
            padding: 0.85rem 1rem;
            margin-bottom: 1.8rem;
            font-size: 0.88rem;
            color: #1e40af;
            line-height: 1.5;
        }

        /* Butonlar */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.45);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #4b5563;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-danger {
            background: #fff;
            color: #ef4444;
            border: 1.5px solid #fca5a5;
        }

        .btn-danger:hover {
            background: #fef2f2;
        }

        .divider {
            text-align: center;
            color: #9ca3af;
            font-size: 0.82rem;
            margin: 0.25rem 0;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 38%;
            height: 1px;
            background: #e5e7eb;
        }

        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        /* Alt bilgi */
        .card-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <!-- Üst Kırmızı Alan -->
        <div class="card-header">
            <div class="icon-circle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h1>Aboneliğiniz Sona Erdi</h1>
            <p>Koç panelinize erişmek için aboneliğinizin yenilenmesi gerekiyor.</p>
        </div>

        <!-- İçerik -->
        <div class="card-body">

            <!-- Kullanıcı Bilgisi -->
            <div class="user-info">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div class="user-info-text">
                    <div class="name">{{ $user->name }}</div>
                    <div class="email">{{ $user->email }}</div>
                </div>
            </div>

            <!-- Abonelik Bilgisi -->
            <div class="subscription-box">
                @if($subscription)
                    <div class="label">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Abonelik Bitiş Tarihi
                    </div>
                    <div class="value">
                        {{ $subscription->end_date ? $subscription->end_date->format('d.m.Y') : 'Belirtilmemiş' }}
                        @if($subscription->end_date && $subscription->end_date->isPast())
                            — {{ $subscription->end_date->diffForHumans() }} sona erdi
                        @endif
                    </div>
                @else
                    <div class="label">⚠ Abonelik Bulunamadı</div>
                    <div class="value">Hesabınıza henüz bir abonelik paketi tanımlanmamış.</div>
                @endif
            </div>

            <!-- Bilgi Notu -->
            <div class="info-note">
                📞 Aboneliğinizi yenilemek için lütfen sistem yöneticinizle iletişime geçin.
                Yönetici aboneliğinizi aktif ettiğinde, aynı kullanıcı adı ve şifrenizle tekrar giriş yapabilirsiniz.
            </div>

            <!-- Butonlar -->
            <div class="btn-group">
                <a href="mailto:admin@anitmezarlik.com" class="btn btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Yöneticiyle İletişime Geç
                </a>

                <div class="divider">veya</div>

                <a href="{{ route('login') }}" class="btn btn-secondary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Farklı Hesapla Giriş Yap
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="width:100%;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Oturumu Kapat
                    </button>
                </form>
            </div>

        </div>

        <!-- Alt Bilgi -->
        <div class="card-footer">
            Öğrenci Takip Sistemi &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
