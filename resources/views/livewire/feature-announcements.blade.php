<li
    class="nav-item"
    x-data="{ open: @js($hasUnseen) }"
    @keydown.window.escape="if (open) { open = false; $wire.dismiss(); }"
>
    <a href="#" class="nav-link" @click.prevent="open = !open" title="{{ __('announcements.title') }}">
        <i class="fa fa-bullhorn"></i>
        @if ($unseenCount > 0)
            <span class="badge badge-pill">{{ $unseenCount }}</span>
        @endif
    </a>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @click="open = false; $wire.dismiss();"
        class="fa-whats-new-backdrop"
    ></div>

    <aside
        x-show="open"
        x-cloak
        x-transition:enter="fa-slide-enter"
        x-transition:enter-start="fa-slide-enter-start"
        x-transition:enter-end="fa-slide-enter-end"
        x-transition:leave="fa-slide-leave"
        x-transition:leave-start="fa-slide-leave-start"
        x-transition:leave-end="fa-slide-leave-end"
        role="dialog"
        aria-modal="true"
        class="fa-whats-new-panel"
    >
        <header class="fa-whats-new-header">
            <div class="fa-whats-new-header-left">
                <div class="fa-whats-new-icon-circle">
                    <i class="fa fa-bullhorn"></i>
                </div>
                <div>
                    <h4 class="fa-whats-new-title">{{ __('announcements.title') }}</h4>
                    <span class="fa-whats-new-subtitle">
                        @if ($unseenCount > 0)
                            {{ trans_choice('announcements.count', $unseenCount, ['count' => $unseenCount]) }}
                        @else
                            {{ __('announcements.all_caught_up') }}
                        @endif
                    </span>
                </div>
            </div>
            <button
                type="button"
                @click="open = false; $wire.dismiss();"
                aria-label="{{ __('announcements.close') }}"
                class="fa-whats-new-close"
            >&times;</button>
        </header>

        <div class="fa-whats-new-body">
            @forelse ($history as $announcement)
                @php $isUnseen = $announcement->seenByUsers->isEmpty(); @endphp
                <article wire:key="fa-{{ $announcement->id }}" class="fa-whats-new-card {{ $isUnseen ? 'is-unseen' : '' }}">
                    <div class="fa-whats-new-card-head">
                        @if ($isUnseen)
                            <span class="fa-whats-new-badge">{{ __('announcements.new_badge') }}</span>
                        @else
                            <span></span>
                        @endif
                        <small class="fa-whats-new-date">
                            {{ $announcement->published_at?->format('d.m.Y') }}
                        </small>
                    </div>

                    <h5 class="fa-whats-new-card-title">{{ $announcement->title() }}</h5>

                    @if ($announcement->image_path)
                        <img src="{{ asset('storage/' . $announcement->image_path) }}" alt="" class="fa-whats-new-image">
                    @endif

                    <div class="fa-whats-new-card-body">
                        {!! \Illuminate\Support\Str::markdown($announcement->body() ?? '') !!}
                    </div>

                    @if ($announcement->link_url)
                        <a href="{{ $announcement->link_url }}" target="_blank" rel="noopener" class="fa-whats-new-link">
                            {{ __('announcements.learn_more') }} <i class="fa fa-arrow-right" style="margin-left: 4px; font-size: 11px;"></i>
                        </a>
                    @endif
                </article>
            @empty
                <div class="fa-whats-new-empty">
                    <i class="fa fa-bullhorn" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;"></i>
                    <p style="margin: 0; color: #64748b; font-size: 14px;">{{ __('announcements.none_yet') }}</p>
                </div>
            @endforelse
        </div>

        @if ($hasUnseen || $history->isNotEmpty())
            <footer class="fa-whats-new-footer">
                <button type="button" @click="open = false; $wire.dismiss();" class="fa-whats-new-cta">
                    {{ __('announcements.got_it') }}
                </button>
            </footer>
        @endif
    </aside>

    <style>
        [x-cloak] { display: none !important; }
        .fa-whats-new-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }
        .fa-whats-new-panel {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            box-shadow: -12px 0 32px rgba(15, 23, 42, 0.18);
            z-index: 2001;
            display: flex;
            flex-direction: column;
            font-family: inherit;
        }
        .fa-whats-new-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 24px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
        }
        .fa-whats-new-header-left { display: flex; align-items: center; gap: 14px; }
        .fa-whats-new-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
        }
        .fa-whats-new-title {
            margin: 0 0 2px;
            font-size: 17px;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.2;
        }
        .fa-whats-new-subtitle { font-size: 12px; color: rgba(255, 255, 255, 0.82); }
        .fa-whats-new-close {
            border: none;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .fa-whats-new-close:hover { background: rgba(255, 255, 255, 0.28); }
        .fa-whats-new-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 22px 8px;
            background: #f8fafc;
        }
        .fa-whats-new-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
        }
        .fa-whats-new-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .fa-whats-new-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.12);
        }
        .fa-whats-new-card.is-unseen {
            border-color: #bfdbfe;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);
        }
        .fa-whats-new-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            min-height: 20px;
        }
        .fa-whats-new-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 3px 9px;
            border-radius: 999px;
        }
        .fa-whats-new-date { color: #94a3b8; font-size: 11px; font-weight: 500; }
        .fa-whats-new-card-title {
            margin: 0 0 8px;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.4;
        }
        .fa-whats-new-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #e2e8f0;
        }
        .fa-whats-new-card-body { font-size: 13px; color: #475569; line-height: 1.6; }
        .fa-whats-new-card-body p { margin: 0 0 8px; }
        .fa-whats-new-card-body p:last-child { margin-bottom: 0; }
        .fa-whats-new-card-body ul,
        .fa-whats-new-card-body ol { margin: 6px 0 6px 20px; padding: 0; }
        .fa-whats-new-card-body strong { color: #0f172a; }
        .fa-whats-new-card-body a { color: #2563eb; text-decoration: none; }
        .fa-whats-new-card-body a:hover { text-decoration: underline; }
        .fa-whats-new-link {
            display: inline-flex;
            align-items: center;
            margin-top: 12px;
            padding: 8px 16px;
            background: #3b82f6;
            color: #ffffff !important;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none !important;
            transition: background 0.15s ease;
        }
        .fa-whats-new-link:hover { background: #2563eb; color: #ffffff !important; }
        .fa-whats-new-footer {
            padding: 16px 22px;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            text-align: right;
        }
        .fa-whats-new-cta {
            background: #3b82f6;
            color: #ffffff;
            border: none;
            padding: 10px 28px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, box-shadow 0.15s ease;
        }
        .fa-whats-new-cta:hover {
            background: #2563eb;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }
        .fa-slide-enter { transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
        .fa-slide-enter-start { transform: translateX(100%); }
        .fa-slide-enter-end { transform: translateX(0); }
        .fa-slide-leave { transition: transform 0.25s cubic-bezier(0.4, 0, 1, 1); }
        .fa-slide-leave-start { transform: translateX(0); }
        .fa-slide-leave-end { transform: translateX(100%); }
    </style>
</li>
