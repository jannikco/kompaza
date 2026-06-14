/**
 * Kompaza countdown timers.
 * Hydrates any <div data-countdown-timer-id="N"></div> on the page by fetching its
 * config from /api/countdown-timer and rendering a live-ticking countdown.
 * Supports fixed (server end date) and evergreen (per-visitor duration) timers.
 */
(function () {
  'use strict';

  function pad(n) { return n < 10 ? '0' + n : '' + n; }

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : s;
    return d.innerHTML;
  }

  function handleExpiry(el, cfg) {
    if (el._kzInterval) clearInterval(el._kzInterval);
    if (cfg.expired_action === 'redirect' && cfg.redirect_url) {
      window.location.href = cfg.redirect_url;
    } else if (cfg.expired_action === 'show_message') {
      el.innerHTML = '<div style="padding:12px;text-align:center;color:' + (cfg.text_color || '#fff') + ';">' +
        (cfg.expired_message ? escapeHtml(cfg.expired_message) : 'This offer has expired.') + '</div>';
    } else {
      el.style.display = 'none';
    }
  }

  function build(el, cfg) {
    el.style.background = cfg.bg_color || '#111827';
    el.style.color = cfg.text_color || '#FFFFFF';
    el.style.borderRadius = '10px';
    el.style.padding = '16px';
    el.style.display = 'block';
    var html = '';
    if (cfg.headline) {
      html += '<div style="text-align:center;font-weight:700;margin-bottom:8px;">' + escapeHtml(cfg.headline) + '</div>';
    }
    html += '<div class="kz-cd-clock" style="display:flex;gap:12px;justify-content:center;align-items:center;"></div>';
    if (cfg.subheadline) {
      html += '<div style="text-align:center;font-size:0.85rem;opacity:0.8;margin-top:8px;">' + escapeHtml(cfg.subheadline) + '</div>';
    }
    el.innerHTML = html;
  }

  function render(el, cfg, endMs) {
    var clock = el.querySelector('.kz-cd-clock');
    function unit(val, label) {
      return '<div style="text-align:center;min-width:54px;">' +
        '<div style="font-size:1.75rem;font-weight:700;line-height:1;color:' + (cfg.accent_color || cfg.text_color || '#fff') + ';">' + pad(val) + '</div>' +
        '<div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em;opacity:0.7;">' + label + '</div></div>';
    }
    function tick() {
      var remaining = endMs - Date.now();
      if (remaining <= 0) { handleExpiry(el, cfg); return; }
      var t = Math.floor(remaining / 1000);
      var days = Math.floor(t / 86400);
      var hours = Math.floor((t % 86400) / 3600);
      var mins = Math.floor((t % 3600) / 60);
      var secs = t % 60;
      var parts = '';
      if (days > 0) parts += unit(days, 'Days');
      parts += unit(hours, 'Hours') + unit(mins, 'Min') + unit(secs, 'Sec');
      if (clock) clock.innerHTML = parts;
    }
    tick();
    el._kzInterval = setInterval(tick, 1000);
  }

  function init(el) {
    var id = el.getAttribute('data-countdown-timer-id');
    if (!id || el._kzInit) return;
    el._kzInit = true;
    fetch('/api/countdown-timer?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (cfg) {
        if (!cfg || cfg.error) { el.style.display = 'none'; return; }
        var endMs;
        if (cfg.type === 'fixed') {
          endMs = cfg.end_timestamp;
        } else {
          var key = 'kz_cd_start_' + id;
          var start = parseInt(localStorage.getItem(key), 10);
          if (!start) { start = Date.now(); try { localStorage.setItem(key, String(start)); } catch (e) {} }
          endMs = start + (cfg.duration_ms || 0);
        }
        if (!endMs || endMs <= Date.now()) { handleExpiry(el, cfg); return; }
        build(el, cfg);
        render(el, cfg, endMs);
      })
      .catch(function () { el.style.display = 'none'; });
  }

  function boot() {
    var els = document.querySelectorAll('[data-countdown-timer-id]');
    for (var i = 0; i < els.length; i++) init(els[i]);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
