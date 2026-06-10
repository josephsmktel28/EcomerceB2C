/* Lightweight, safe timeago for bid timestamps (.js-bid-time) */
(function () {
  'use strict';
  try {
    const els = () => Array.from(document.querySelectorAll('.js-bid-time'));

    function relTime(date) {
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);
      if (diff < 0) return 'sekarang';
      if (diff < 5) return 'baru saja';
      if (diff < 60) return diff + ' detik yang lalu';
      if (diff < 3600) return Math.floor(diff / 60) + ' menit yang lalu';
      if (diff < 86400) return Math.floor(diff / 3600) + ' jam yang lalu';
      return Math.floor(diff / 86400) + ' hari yang lalu';
    }

    function update() {
      const nodes = els();
      if (!nodes.length) return;
      nodes.forEach((el) => {
        const dt = el.dataset.datetime;
        if (!dt) return;
        const d = new Date(dt);
        if (Number.isNaN(d.getTime())) return;
        el.textContent = relTime(d);
      });
    }

    // initial update and periodic refresh
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        update();
        setInterval(update, 10 * 1000);
      });
    } else {
      update();
      setInterval(update, 10 * 1000);
    }
  } catch (e) {
    // fail silently
    console.warn('timeago-bids error', e);
  }
})();
