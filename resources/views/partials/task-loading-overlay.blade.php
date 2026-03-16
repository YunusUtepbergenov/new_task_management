{{-- Task loading overlay --}}
<div
    wire:loading.flex
    wire:target="{{ $target ?? 'view' }}"
    style="position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;background:rgba(15,23,42,0.45);backdrop-filter:blur(2px);"
>
    <div style="background:var(--card-bg);border-radius:16px;padding:36px 48px;display:flex;flex-direction:column;align-items:center;gap:16px;box-shadow:0 20px 60px rgba(0,0,0,0.18);border:1px solid var(--border-color);">
        <div style="width:44px;height:44px;border-radius:50%;border:3px solid var(--border-color);border-top-color:var(--sidebar-active-bg);animation:vm-spin 0.7s linear infinite;"></div>
        <span style="font-size:14px;font-weight:500;color:var(--text-secondary);letter-spacing:0.01em;">Загрузка задачи...</span>
    </div>
</div>
