<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Login CERR Task Manager</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="shortcut icon" type="image/x-icon" href="https://cerr.uz/themes/cer/icon/favicon.ico">
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}">
    </head>
    <body class="auth-page">
        <main class="auth-layout">
            <section class="auth-left-pane" aria-label="Platform info">
                <div class="auth-left-content">
                    <h1 class="auth-left-title">Центр экономических исследований и реформ</h1>
                    <p class="auth-left-subtitle">Официальная платформа управления задачами</p>
                </div>
                <p class="auth-left-copy">&copy; 2026 CERR.</p>
            </section>

            <section class="auth-right-pane">
                <section class="auth-card" aria-label="Login form">
                    <div class="auth-brand">
                        <img src="{{ asset('img/213232-0cd1efa818.jpg') }}" alt="CERR logo" class="auth-brand-image">
                    </div>

                    <h2 class="auth-card-title">Введите свои учетные данные для доступа к своему аккаунту.</h2>

                    @if ($errors->any())
                        <div class="auth-alert" role="alert">
                            <ul class="auth-alert-list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="auth-form">
                        @csrf

                        <label for="email" class="auth-label">Почта</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 12.2a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 1.79-8 4v.8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-.8c0-2.21-3.58-4-8-4Z"></path>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                class="auth-input"
                                value="{{ old('email') }}"
                                placeholder="Логин"
                                required
                                autocomplete="username"
                            >
                        </div>

                        <label for="password" class="auth-label">Пароль</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M17 9h-1V7a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-6 6.73V17a1 1 0 0 0 2 0v-1.27a2 2 0 1 0-2 0ZM10 7a2 2 0 0 1 4 0v2h-4Z"></path>
                                </svg>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="auth-input"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <div class="auth-row">
                            <label class="auth-remember">
                                <input type="checkbox" name="remember" @checked(old('remember'))>
                                <span>Запомнить меня</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="auth-forgot">Забыл пароль?</a>
                        </div>

                        <button type="submit" class="auth-submit">Log In</button>
                    </form>
                </section>
            </section>
        </main>

        <button type="button" class="dark-mode-toggle" title="Темная тема" aria-label="Темная тема">
            <i class="fa fa-moon-o"></i>
            <i class="fa fa-sun-o"></i>
        </button>

        <script>
            (function () {
                function applyThemeFromStorage() {
                    if (localStorage.getItem('dark-mode') === 'true') {
                        document.body.classList.add('dark-mode');
                    } else {
                        document.body.classList.remove('dark-mode');
                    }
                }

                function syncThemeButtonState(button) {
                    if (!button) {
                        return;
                    }

                    button.setAttribute('aria-pressed', document.body.classList.contains('dark-mode') ? 'true' : 'false');
                }

                document.addEventListener('DOMContentLoaded', function () {
                    var themeButton = document.querySelector('.dark-mode-toggle');

                    applyThemeFromStorage();
                    syncThemeButtonState(themeButton);

                    if (!themeButton) {
                        return;
                    }

                    themeButton.addEventListener('click', function () {
                        var isDarkMode = document.body.classList.toggle('dark-mode');

                        if (isDarkMode) {
                            localStorage.setItem('dark-mode', 'true');
                        } else {
                            localStorage.removeItem('dark-mode');
                        }

                        syncThemeButtonState(themeButton);
                    });
                });
            })();
        </script>
    </body>
</html>
