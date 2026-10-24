/**
 * filter-state.js — Centralized filter + sort state
 *
 * Chịu trách nhiệm:
 *  1. Lưu trữ toàn bộ trạng thái bộ lọc (category, brand, price, size, color, sort, page)
 *  2. Đồng bộ hai chiều với URL query string (history.pushState)
 *  3. Cung cấp API để đọc / cập nhật state
 *  4. Thông báo listener khi state thay đổi
 *
 * Không phụ thuộc vào DOM — có thể test độc lập.
 */
(function (global) {
  'use strict';

  const DEFAULTS = {
    q:        '',       // search query
    category: '',       // slug
    brands:   [],       // array of slugs
    price_min: 0,
    price_max: 10000000,
    sizes:    [],       // array of size strings
    colors:   [],       // array of color slugs
    sort:     'newest', // newest | price_asc | price_desc | name_asc | name_desc
    page:     1,
    per_page: 16,
  };

  let _state = structuredClone
    ? structuredClone(DEFAULTS)
    : JSON.parse(JSON.stringify(DEFAULTS));

  const _listeners = new Set();

  // ── Public API ────────────────────────────────────────────────

  const FilterState = {

    /** Lấy bản sao của state hiện tại */
    get() {
      return JSON.parse(JSON.stringify(_state));
    },

    /** Cập nhật một hoặc nhiều field, tự reset page về 1 */
    set(updates) {
      const isPageUpdate = Object.keys(updates).length === 1 && 'page' in updates;
      if (!isPageUpdate) {
        updates.page = 1; // reset về trang 1 khi filter thay đổi
      }
      Object.assign(_state, updates);
      this._syncToUrl();
      this._notify();
    },

    /** Toggle một brand (multi-select) */
    toggleBrand(slug) {
      const brands = [..._state.brands];
      const idx = brands.indexOf(slug);
      if (idx === -1) brands.push(slug);
      else brands.splice(idx, 1);
      this.set({ brands });
    },

    /** Toggle một size */
    toggleSize(size) {
      const sizes = [..._state.sizes];
      const idx = sizes.indexOf(size);
      if (idx === -1) sizes.push(size);
      else sizes.splice(idx, 1);
      this.set({ sizes });
    },

    /** Toggle một màu */
    toggleColor(slug) {
      const colors = [..._state.colors];
      const idx = colors.indexOf(slug);
      if (idx === -1) colors.push(slug);
      else colors.splice(idx, 1);
      this.set({ colors });
    },

    /** Xóa toàn bộ bộ lọc, giữ lại sort + per_page */
    reset() {
      const { sort, per_page } = _state;
      _state = JSON.parse(JSON.stringify(DEFAULTS));
      _state.sort = sort;
      _state.per_page = per_page;
      this._syncToUrl();
      this._notify();
    },

    /** Đếm số bộ lọc đang active (không tính sort/page/search) */
    activeCount() {
      let count = 0;
      if (_state.category)          count++;
      if (_state.brands.length)     count += _state.brands.length;
      if (_state.price_min > DEFAULTS.price_min) count++;
      if (_state.price_max < DEFAULTS.price_max) count++;
      if (_state.sizes.length)      count += _state.sizes.length;
      if (_state.colors.length)     count += _state.colors.length;
      return count;
    },

    /** Chuyển state thành object params để truyền vào API / mock */
    toParams() {
      const s = this.get();
      const params = {};
      if (s.q)          params.q          = s.q;
      if (s.category)   params.category   = s.category;
      if (s.brands.length) params.brands  = s.brands.join(',');
      if (s.price_min > DEFAULTS.price_min) params.price_min = s.price_min;
      if (s.price_max < DEFAULTS.price_max) params.price_max = s.price_max;
      if (s.sizes.length)  params.sizes   = s.sizes.join(',');
      if (s.colors.length) params.colors  = s.colors.join(',');
      params.sort     = s.sort;
      params.page     = s.page;
      params.per_page = s.per_page;
      return params;
    },

    /** Đọc state từ URL hiện tại */
    fromUrl() {
      const params = new URLSearchParams(window.location.search);
      const updates = {};

      if (params.get('q'))        updates.q        = params.get('q');
      if (params.get('category')) updates.category = params.get('category');
      if (params.get('brands'))   updates.brands   = params.get('brands').split(',').filter(Boolean);
      if (params.get('price_min')) updates.price_min = parseInt(params.get('price_min'), 10) || 0;
      if (params.get('price_max')) updates.price_max = parseInt(params.get('price_max'), 10) || DEFAULTS.price_max;
      if (params.get('sizes'))    updates.sizes    = params.get('sizes').split(',').filter(Boolean);
      if (params.get('colors'))   updates.colors   = params.get('colors').split(',').filter(Boolean);
      if (params.get('sort'))     updates.sort     = params.get('sort');
      if (params.get('page'))     updates.page     = parseInt(params.get('page'), 10) || 1;

      Object.assign(_state, updates);
      // fromUrl không trigger notify — caller sẽ fetch sau khi init
    },

    /** Subscribe vào state changes */
    subscribe(fn) {
      _listeners.add(fn);
      return () => _listeners.delete(fn); // unsubscribe
    },

    // ── Private ──────────────────────────────────────────────────

    _syncToUrl() {
      const params = new URLSearchParams();
      const s = _state;

      if (s.q)            params.set('q', s.q);
      if (s.category)     params.set('category', s.category);
      if (s.brands.length)  params.set('brands', s.brands.join(','));
      if (s.price_min > DEFAULTS.price_min) params.set('price_min', s.price_min);
      if (s.price_max < DEFAULTS.price_max) params.set('price_max', s.price_max);
      if (s.sizes.length)   params.set('sizes', s.sizes.join(','));
      if (s.colors.length)  params.set('colors', s.colors.join(','));
      if (s.sort !== 'newest') params.set('sort', s.sort);
      if (s.page > 1)       params.set('page', s.page);

      const qs = params.toString();
      const newUrl = window.location.pathname + (qs ? '?' + qs : '');
      window.history.replaceState(null, '', newUrl);
    },

    _notify() {
      _listeners.forEach(fn => fn(_state));
    },
  };

  global.FilterState = FilterState;

})(window);
