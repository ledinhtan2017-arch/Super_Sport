/**
 * admin/product-form.js — Product form enhancements
 *
 * - Auto-slug generation
 * - Image preview
 * - Variant price formatting
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── Slug auto-generation ──────────────────────────────────────────────────
  const nameField = document.getElementById('f-name');
  const slugField = document.getElementById('f-slug');

  if (nameField && slugField) {
    let slugManual = slugField.value.length > 0;

    nameField.addEventListener('input', function () {
      if (slugManual) return;
      slugField.value = Utils?.toSlug?.(this.value) || toSlugFallback(this.value);
    });

    slugField.addEventListener('input', function () {
      slugManual = this.value.length > 0;
    });

    slugField.addEventListener('blur', function () {
      this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '');
    });
  }

  function toSlugFallback(text) {
    const map = {'à':'a','á':'a','ả':'a','ã':'a','ạ':'a','ă':'a','ắ':'a','ằ':'a','ặ':'a','â':'a','ấ':'a','ầ':'a','ậ':'a','è':'e','é':'e','ẻ':'e','ẹ':'e','ê':'e','ế':'e','ề':'e','ệ':'e','ì':'i','í':'i','ị':'i','ò':'o','ó':'o','ọ':'o','ô':'o','ố':'o','ồ':'o','ộ':'o','ơ':'o','ớ':'o','ờ':'o','ợ':'o','ù':'u','ú':'u','ụ':'u','ư':'u','ứ':'u','ừ':'u','ự':'u','ý':'y','ỵ':'y','đ':'d'};
    return text.toLowerCase().replace(/./g, c => map[c] || c).replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-');
  }

  // ── Copy coupon codes ────────────────────────────────────────────────────
  document.querySelectorAll('.coupon-code').forEach(el => {
    el.style.cursor = 'pointer';
    el.title = 'Click to copy';
    el.addEventListener('click', async () => {
      const ok = await (Utils?.copyToClipboard?.(el.textContent) || navigator.clipboard.writeText(el.textContent).then(() => true).catch(() => false));
      if (ok) Toast?.success?.('Đã sao chép: ' + el.textContent);
    });
  });

  // ── Confirm delete forms ───────────────────────────────────────────────
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

  // ── Auto dismiss flash messages ────────────────────────────────────────
  document.querySelectorAll('.flash').forEach(el => {
    setTimeout(() => el.style.opacity = '0', 4000);
    setTimeout(() => el.remove(), 4400);
  });

});
