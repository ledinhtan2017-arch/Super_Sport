/**
 * core/utils.js — Shared utility functions
 */

/** Format currency VND */
function formatPrice(amount) {
  return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}

/** Debounce: delay function call until user stops typing */
function debounce(fn, delay = 300) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

/** Convert text to slug */
function toSlug(text) {
  const map = {
    'à':'a','á':'a','ả':'a','ã':'a','ạ':'a','ă':'a','ắ':'a','ằ':'a','ẳ':'a','ẵ':'a','ặ':'a',
    'â':'a','ấ':'a','ầ':'a','ẩ':'a','ẫ':'a','ậ':'a','è':'e','é':'e','ẻ':'e','ẽ':'e','ẹ':'e',
    'ê':'e','ế':'e','ề':'e','ể':'e','ễ':'e','ệ':'e','ì':'i','í':'i','ỉ':'i','ĩ':'i','ị':'i',
    'ò':'o','ó':'o','ỏ':'o','õ':'o','ọ':'o','ô':'o','ố':'o','ồ':'o','ổ':'o','ỗ':'o','ộ':'o',
    'ơ':'o','ớ':'o','ờ':'o','ở':'o','ỡ':'o','ợ':'o','ù':'u','ú':'u','ủ':'u','ũ':'u','ụ':'u',
    'ư':'u','ứ':'u','ừ':'u','ử':'u','ữ':'u','ự':'u','ỳ':'y','ý':'y','ỷ':'y','ỹ':'y','ỵ':'y',
    'đ':'d'
  };
  return text.toLowerCase()
    .replace(/./g, ch => map[ch] || ch)
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/[\s]+/g, '-');
}

/** Copy text to clipboard */
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    return false;
  }
}

/** Format date to Vietnamese format */
function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric' });
}

/** Get query param from current URL */
function getParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

/** Build URL with updated query params */
function buildUrl(base, params) {
  const url = new URL(base, window.location.origin);
  Object.entries(params).forEach(([k, v]) => {
    if (v === null || v === undefined || v === '') {
      url.searchParams.delete(k);
    } else {
      url.searchParams.set(k, v);
    }
  });
  return url.toString();
}

// Export for module environments
if (typeof module !== 'undefined') {
  module.exports = { formatPrice, debounce, toSlug, copyToClipboard, formatDate, getParam, buildUrl };
} else {
  window.Utils = { formatPrice, debounce, toSlug, copyToClipboard, formatDate, getParam, buildUrl };
}
