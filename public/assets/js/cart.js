/**
 * cart.js — Client-side cart state manager
 *
 * Đồng bộ giỏ hàng với server API (/api/v1/cart)
 * Cập nhật badge số lượng trên header
 */
(function (global) {
  'use strict';

  const CART_API = (global.SPORTZONE?.baseUrl || '') + '/api/v1/cart';

  const Cart = {
    _count: 0,

    /** Khởi động: load cart count từ server */
    async init() {
      try {
        const data = await http.get(CART_API);
        this._updateBadge(data?.item_count ?? 0);
      } catch (e) {
        // Không show error ở init — cart count không quan trọng khi load lần đầu
        console.warn('[Cart] init failed:', e.message);
      }
    },

    /** Thêm sản phẩm vào giỏ */
    async add(variantId, quantity = 1, btn = null) {
      if (btn) Utils.setLoading(btn, true);

      try {
        const data = await http.post(CART_API + '/items', { variant_id: variantId, quantity });
        this._updateBadge(data?.item_count ?? (this._count + quantity));
        Toast.success('Đã thêm vào giỏ hàng! 🛒');
        this._triggerEvent('cart:added', { variantId, quantity });
        return data;
      } catch (err) {
        const msg = err.status === 422
          ? (err.data?.message || 'Không đủ hàng trong kho.')
          : (err.data?.message || 'Không thể thêm vào giỏ. Vui lòng thử lại.');
        Toast.error(msg);
        throw err;
      } finally {
        if (btn) Utils.setLoading(btn, false);
      }
    },

    /** Cập nhật số lượng item trong giỏ */
    async update(itemId, quantity, btn = null) {
      if (btn) Utils.setLoading(btn, true);
      try {
        const data = await http.put(`${CART_API}/items/${itemId}`, { quantity });
        this._updateBadge(data?.item_count ?? this._count);
        this._triggerEvent('cart:updated', { itemId, quantity });
        return data;
      } catch (err) {
        Toast.error(err.data?.message || 'Không thể cập nhật giỏ hàng.');
        throw err;
      } finally {
        if (btn) Utils.setLoading(btn, false);
      }
    },

    /** Xóa item khỏi giỏ */
    async remove(itemId, btn = null) {
      if (btn) Utils.setLoading(btn, true);
      try {
        const data = await http.delete(`${CART_API}/items/${itemId}`);
        this._updateBadge(data?.item_count ?? Math.max(0, this._count - 1));
        Toast.info('Đã xóa sản phẩm khỏi giỏ hàng.');
        this._triggerEvent('cart:removed', { itemId });
        return data;
      } catch (err) {
        Toast.error('Không thể xóa sản phẩm.');
        throw err;
      } finally {
        if (btn) Utils.setLoading(btn, false);
      }
    },

    /** Áp mã giảm giá */
    async applyCoupon(code) {
      try {
        const data = await http.post(CART_API + '/coupon', { code });
        Toast.success('Áp dụng mã thành công! 🎉');
        this._triggerEvent('cart:coupon', data);
        return data;
      } catch (err) {
        Toast.error(err.data?.message || 'Mã giảm giá không hợp lệ.');
        throw err;
      }
    },

    // ── Private ────────────────────────────────────────────────

    _updateBadge(count) {
      this._count = count;
      const badges = document.querySelectorAll('[id="cart-count"]');
      badges.forEach(el => {
        el.textContent = count;
        el.dataset.count = count;
        el.hidden = count === 0;
      });
    },

    _triggerEvent(name, detail = {}) {
      window.dispatchEvent(new CustomEvent(name, { detail, bubbles: true }));
    },
  };

  // ── Auto-bind "Add to Cart" buttons ──────────────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-add-to-cart]');
    if (!btn) return;
    e.preventDefault();

    const variantId = parseInt(btn.dataset.addToCart, 10);
    const quantity  = parseInt(btn.dataset.quantity || '1', 10);
    if (!variantId) return;

    Cart.add(variantId, quantity, btn);
  });

  // Init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Cart.init());
  } else {
    Cart.init();
  }

  global.Cart = Cart;

})(typeof window !== 'undefined' ? window : globalThis);
