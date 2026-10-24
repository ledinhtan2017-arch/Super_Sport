/**
 * core/toast.js — Toast notification system
 *
 * Usage:
 *   Toast.success('Thêm vào giỏ hàng thành công!');
 *   Toast.error('Đã xảy ra lỗi!');
 *   Toast.info('Đang xử lý...');
 *   Toast.warning('Sản phẩm sắp hết hàng!');
 */

const Toast = (() => {
  let container = null;

  function getContainer() {
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.setAttribute('aria-live', 'polite');
      container.setAttribute('aria-atomic', 'false');
      container.style.cssText = [
        'position:fixed', 'top:1.25rem', 'right:1.25rem',
        'z-index:9999', 'display:flex', 'flex-direction:column',
        'gap:0.5rem', 'pointer-events:none', 'max-width:22rem', 'width:100%'
      ].join(';');
      document.body.appendChild(container);
    }
    return container;
  }

  const icons = {
    success: '✓',
    error:   '✕',
    warning: '⚠',
    info:    'ℹ',
  };

  const colors = {
    success: { bg: '#0f2a1a', border: '#22c55e', icon: '#22c55e' },
    error:   { bg: '#2a0f0f', border: '#ef4444', icon: '#ef4444' },
    warning: { bg: '#2a200f', border: '#f59e0b', icon: '#f59e0b' },
    info:    { bg: '#0f1a2a', border: '#3b82f6', icon: '#3b82f6' },
  };

  function show(message, type = 'info', duration = 4000) {
    const c = getContainer();
    const col = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.setAttribute('role', 'alert');
    toast.style.cssText = [
      'display:flex', 'align-items:flex-start', 'gap:0.75rem',
      'padding:0.875rem 1rem', 'border-radius:0.625rem',
      `background:${col.bg}`, `border:1px solid ${col.border}40`,
      'backdrop-filter:blur(12px)', 'pointer-events:auto',
      'box-shadow:0 8px 32px rgba(0,0,0,0.4)',
      'animation:toastIn 0.25s ease forwards',
      'transform:translateX(110%)', 'opacity:0',
    ].join(';');

    const iconEl = document.createElement('span');
    iconEl.textContent = icons[type] || icons.info;
    iconEl.style.cssText = [
      'flex-shrink:0', 'font-weight:700', 'font-size:0.875rem',
      'margin-top:0.1rem',
      `color:${col.icon}`,
      'width:1.25rem', 'height:1.25rem', 'display:flex',
      'align-items:center', 'justify-content:center',
      'background:' + col.icon + '20', 'border-radius:50%',
    ].join(';');

    const msgEl = document.createElement('span');
    msgEl.textContent = message;
    msgEl.style.cssText = 'color:#e5e7eb;font-size:0.875rem;line-height:1.4;flex:1';

    const closeBtn = document.createElement('button');
    closeBtn.textContent = '✕';
    closeBtn.style.cssText = [
      'background:none', 'border:none', 'color:#6b7280', 'cursor:pointer',
      'font-size:0.75rem', 'padding:0', 'flex-shrink:0', 'line-height:1',
      'margin-top:0.1rem', 'transition:color 0.15s',
    ].join(';');
    closeBtn.addEventListener('mouseover', () => closeBtn.style.color = '#e5e7eb');
    closeBtn.addEventListener('mouseout',  () => closeBtn.style.color = '#6b7280');
    closeBtn.addEventListener('click', () => dismiss(toast));

    toast.appendChild(iconEl);
    toast.appendChild(msgEl);
    toast.appendChild(closeBtn);
    c.appendChild(toast);

    // Inject animation keyframes once
    if (!document.getElementById('toast-keyframes')) {
      const style = document.createElement('style');
      style.id = 'toast-keyframes';
      style.textContent = `
        @keyframes toastIn  { from { transform:translateX(110%); opacity:0 } to { transform:translateX(0); opacity:1 } }
        @keyframes toastOut { from { transform:translateX(0); opacity:1 } to { transform:translateX(110%); opacity:0 } }
      `;
      document.head.appendChild(style);
    }

    requestAnimationFrame(() => {
      toast.style.animation = 'toastIn 0.25s ease forwards';
    });

    const timer = setTimeout(() => dismiss(toast), duration);
    toast._timer = timer;

    return toast;
  }

  function dismiss(toast) {
    if (!toast || !toast.parentNode) return;
    clearTimeout(toast._timer);
    toast.style.animation = 'toastOut 0.25s ease forwards';
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
  }

  return {
    success: (msg, dur) => show(msg, 'success', dur),
    error:   (msg, dur) => show(msg, 'error',   dur),
    warning: (msg, dur) => show(msg, 'warning', dur),
    info:    (msg, dur) => show(msg, 'info',    dur),
    show,
  };
})();

if (typeof module !== 'undefined') module.exports = Toast;
