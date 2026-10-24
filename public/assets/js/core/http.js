/**
 * core/http.js — Lightweight fetch wrapper (CSRF-aware)
 *
 * Usage:
 *   const res = await Http.get('/api/products');
 *   const res = await Http.post('/api/cart/items', { product_id: 1, qty: 2 });
 *   const res = await Http.delete('/api/cart/items/1');
 */

const Http = (() => {
  function getCsrf() {
    return (
      window.SPORTZONE?.csrfToken ||
      document.querySelector('meta[name="csrf-token"]')?.content ||
      ''
    );
  }

  async function request(method, url, data = null) {
    const headers = {
      'Accept': 'application/json',
      'X-Csrf-Token': getCsrf(),
      'X-Requested-With': 'XMLHttpRequest',
    };

    const opts = { method, headers, credentials: 'same-origin' };

    if (data !== null) {
      headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(data);
    }

    const baseUrl = window.SPORTZONE?.baseUrl?.replace(/\/$/, '') || '';
    const fullUrl = url.startsWith('http') ? url : baseUrl + url;

    try {
      const resp = await fetch(fullUrl, opts);
      const json = await resp.json().catch(() => ({}));
      return { ok: resp.ok, status: resp.status, data: json };
    } catch (err) {
      console.error('[Http] Network error:', err);
      return { ok: false, status: 0, data: { message: 'Network error' } };
    }
  }

  return {
    get:    (url)          => request('GET', url),
    post:   (url, data)    => request('POST', url, data),
    put:    (url, data)    => request('PUT', url, data),
    patch:  (url, data)    => request('PATCH', url, data),
    delete: (url)          => request('DELETE', url),
  };
})();

if (typeof module !== 'undefined') module.exports = Http;
// Lowercase alias for backward compatibility
const http = Http;
