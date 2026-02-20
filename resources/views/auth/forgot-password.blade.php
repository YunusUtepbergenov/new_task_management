<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Сброс пароля - CERR Task Manager</title>
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
                <section class="auth-card" aria-label="Forgot password form">
                    <div class="auth-brand">
                        <img src="{{ asset('img/213232-0cd1efa818.jpg') }}" alt="CERR logo" class="auth-brand-image">
                    </div>

                    <h2 class="auth-card-title">Забыли пароль? Введите свой адрес электронной почты для сброса.</h2>

                    @if (session('status'))
                        <div class="auth-status" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="auth-alert" role="alert">
                            <ul class="auth-alert-list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                        @csrf

                        <label for="email" class="auth-label">Почта</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5Z"></path>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                class="auth-input"
                                value="{{ old('email') }}"
                                placeholder="Введите вашу почту"
                                required
                                autofocus
                            >
                        </div>

                        <button type="submit" class="auth-submit">Отправить ссылку для сброса</button>

                        <div style="text-align: center; margin-top: 4px;">
                            <a href="{{ route('login') }}" class="auth-forgot">Вернуться к входу</a>
                        </div>
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
