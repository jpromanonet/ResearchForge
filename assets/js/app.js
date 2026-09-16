(function () {
  'use strict';

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function $$(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function initSidebar() {
    var shell = $('.app-shell');
    var toggle = $('#sidebar-toggle');
    var backdrop = $('.sidebar-backdrop');
    if (!shell || !toggle) return;

    function open() {
      shell.classList.add('sidebar-open');
      toggle.setAttribute('aria-expanded', 'true');
    }
    function close() {
      shell.classList.remove('sidebar-open');
      toggle.setAttribute('aria-expanded', 'false');
    }
    toggle.addEventListener('click', function () {
      shell.classList.contains('sidebar-open') ? close() : open();
    });
    if (backdrop) backdrop.addEventListener('click', close);
  }

  function resolveTheme(pref) {
    if (pref === 'dark' || pref === 'light') return pref;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }
    return 'light';
  }

  function applyTheme(pref) {
    var root = document.documentElement;
    var theme = resolveTheme(pref);
    root.setAttribute('data-theme', theme);
    root.setAttribute('data-theme-pref', pref);
    try {
      localStorage.setItem('rf-theme', pref);
    } catch (e) {}
    return theme;
  }

  function initThemeToggle() {
    var root = document.documentElement;
    var pref = root.getAttribute('data-theme-pref') || 'system';
    try {
      var stored = localStorage.getItem('rf-theme');
      if (stored) pref = stored;
    } catch (e) {}
    applyTheme(pref);

    if (window.matchMedia) {
      var mq = window.matchMedia('(prefers-color-scheme: dark)');
      var onChange = function () {
        var currentPref = root.getAttribute('data-theme-pref') || 'system';
        if (currentPref === 'system') applyTheme('system');
      };
      if (mq.addEventListener) mq.addEventListener('change', onChange);
      else if (mq.addListener) mq.addListener(onChange);
    }

    $$('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var current = root.getAttribute('data-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);

        var url = btn.getAttribute('data-theme-url');
        var csrf = btn.getAttribute('data-csrf') || '';
        if (!url) return;

        var body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('theme', next);
        fetch(url, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: body.toString(),
          credentials: 'same-origin'
        }).catch(function () {});
      });
    });
  }

  /* ---------- Charts (canvas vanilla) ---------- */

  function cssVar(name, fallback) {
    var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return v || fallback;
  }

  function chartColors() {
    return [
      cssVar('--copper', '#B86B3A'),
      cssVar('--moss', '#2F6B5A'),
      cssVar('--source', '#3A5F8A'),
      cssVar('--crimson', '#9B3A3A'),
      cssVar('--amber', '#A67C2D'),
      '#6B7C8A',
      '#8B5E73'
    ];
  }

  function ink() { return cssVar('--ink', '#1A2332'); }
  function muted() { return cssVar('--muted', '#5A6570'); }
  function surface2() { return cssVar('--surface-2', '#F0EBE1'); }

  function fitCanvas(canvas) {
    var parent = canvas.parentElement;
    var cssW = Math.max(280, parent ? parent.clientWidth - 8 : 400);
    var cssH = parseInt(canvas.getAttribute('height'), 10) || 220;
    var dpr = window.devicePixelRatio || 1;
    canvas.style.width = cssW + 'px';
    canvas.style.height = cssH + 'px';
    canvas.width = Math.floor(cssW * dpr);
    canvas.height = Math.floor(cssH * dpr);
    var ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    return { ctx: ctx, w: cssW, h: cssH };
  }

  function emptyChart(ctx, w, h, msg) {
    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = muted();
    ctx.font = '13px Sora, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(msg || 'Sin datos todavía', w / 2, h / 2);
  }

  function sumValues(items) {
    return items.reduce(function (a, b) { return a + (Number(b.value) || 0); }, 0);
  }

  function drawLegend(ctx, items, colors, x, y) {
    ctx.font = '11px IBM Plex Mono, monospace';
    ctx.textAlign = 'left';
    var lx = x;
    var ly = y;
    items.forEach(function (item, i) {
      var label = (item.label || '') + ' (' + (item.value || 0) + ')';
      var tw = ctx.measureText(label).width + 22;
      if (lx + tw > ctx.canvas.width / (window.devicePixelRatio || 1) - 8) {
        lx = x;
        ly += 16;
      }
      ctx.fillStyle = colors[i % colors.length];
      ctx.fillRect(lx, ly - 8, 10, 10);
      ctx.fillStyle = muted();
      ctx.fillText(label, lx + 14, ly);
      lx += tw;
    });
    return ly + 8;
  }

  function drawBar(canvas, items, opts) {
    var fit = fitCanvas(canvas);
    var ctx = fit.ctx, w = fit.w, h = fit.h;
    opts = opts || {};
    if (!items || !items.length || sumValues(items) === 0) {
      emptyChart(ctx, w, h);
      return;
    }
    var colors = chartColors();
    var pad = { t: 18, r: 16, b: 56, l: 36 };
    var max = opts.max != null ? Number(opts.max) : Math.max.apply(null, items.map(function (i) { return i.value; }));
    if (max <= 0) max = 1;
    var plotW = w - pad.l - pad.r;
    var plotH = h - pad.t - pad.b;
    var gap = 8;
    var barW = Math.max(8, (plotW - gap * (items.length - 1)) / items.length);

    ctx.clearRect(0, 0, w, h);
    // grid
    ctx.strokeStyle = surface2();
    ctx.lineWidth = 1;
    for (var g = 0; g <= 4; g++) {
      var gy = pad.t + (plotH * g) / 4;
      ctx.beginPath();
      ctx.moveTo(pad.l, gy);
      ctx.lineTo(w - pad.r, gy);
      ctx.stroke();
    }

    items.forEach(function (item, i) {
      var bh = (item.value / max) * plotH;
      var x = pad.l + i * (barW + gap);
      var y = pad.t + plotH - bh;
      ctx.fillStyle = colors[i % colors.length];
      ctx.fillRect(x, y, barW, bh);
      ctx.fillStyle = muted();
      ctx.font = '10px IBM Plex Mono, monospace';
      ctx.textAlign = 'center';
      ctx.fillText(String(item.value), x + barW / 2, y - 4);
      var label = String(item.label || '');
      if (label.length > 10) label = label.slice(0, 9) + '…';
      ctx.save();
      ctx.translate(x + barW / 2, h - 12);
      ctx.rotate(-0.45);
      ctx.textAlign = 'right';
      ctx.fillText(label, 0, 0);
      ctx.restore();
    });
  }

  function drawHBar(canvas, items) {
    var fit = fitCanvas(canvas);
    var ctx = fit.ctx, w = fit.w, h = fit.h;
    if (!items || !items.length || sumValues(items) === 0) {
      emptyChart(ctx, w, h);
      return;
    }
    var colors = chartColors();
    var pad = { t: 12, r: 24, b: 12, l: 110 };
    var max = Math.max.apply(null, items.map(function (i) { return i.value; })) || 1;
    var rowH = Math.min(36, (h - pad.t - pad.b) / items.length);
    ctx.clearRect(0, 0, w, h);
    items.forEach(function (item, i) {
      var y = pad.t + i * rowH + 4;
      var bw = ((w - pad.l - pad.r) * item.value) / max;
      ctx.fillStyle = muted();
      ctx.font = '11px Sora, sans-serif';
      ctx.textAlign = 'right';
      var label = String(item.label || '');
      if (label.length > 14) label = label.slice(0, 13) + '…';
      ctx.fillText(label, pad.l - 8, y + 14);
      ctx.fillStyle = colors[i % colors.length];
      ctx.fillRect(pad.l, y, Math.max(2, bw), 16);
      ctx.fillStyle = ink();
      ctx.textAlign = 'left';
      ctx.font = '11px IBM Plex Mono, monospace';
      ctx.fillText(String(item.value), pad.l + bw + 6, y + 13);
    });
  }

  function drawPie(canvas, items, donut) {
    var fit = fitCanvas(canvas);
    var ctx = fit.ctx, w = fit.w, h = fit.h;
    if (!items || !items.length || sumValues(items) === 0) {
      emptyChart(ctx, w, h);
      return;
    }
    var colors = chartColors();
    var total = sumValues(items);
    var cx = w / 2;
    var cy = (h - 36) / 2 + 4;
    var r = Math.min(w, h - 40) * 0.34;
    var start = -Math.PI / 2;
    ctx.clearRect(0, 0, w, h);
    items.forEach(function (item, i) {
      var slice = (item.value / total) * Math.PI * 2;
      ctx.beginPath();
      ctx.moveTo(cx, cy);
      ctx.arc(cx, cy, r, start, start + slice);
      ctx.closePath();
      ctx.fillStyle = colors[i % colors.length];
      ctx.fill();
      start += slice;
    });
    if (donut) {
      ctx.beginPath();
      ctx.fillStyle = cssVar('--surface', '#FAF7F1');
      ctx.arc(cx, cy, r * 0.55, 0, Math.PI * 2);
      ctx.fill();
      ctx.fillStyle = ink();
      ctx.font = '700 16px Newsreader, serif';
      ctx.textAlign = 'center';
      ctx.fillText(String(total), cx, cy + 5);
    }
    drawLegend(ctx, items, colors, 12, h - 22);
  }

  function drawLineMulti(canvas, timeline) {
    var fit = fitCanvas(canvas);
    var ctx = fit.ctx, w = fit.w, h = fit.h;
    if (!timeline || !timeline.length) {
      emptyChart(ctx, w, h);
      return;
    }
    var series = [
      { key: 'total', label: 'Total', color: cssVar('--copper', '#B86B3A') },
      { key: 'claims', label: 'Claims', color: cssVar('--moss', '#2F6B5A') },
      { key: 'sources', label: 'Fuentes', color: cssVar('--source', '#3A5F8A') },
      { key: 'evidence', label: 'Evidencia', color: cssVar('--crimson', '#9B3A3A') }
    ];
    var pad = { t: 24, r: 16, b: 40, l: 36 };
    var max = 0;
    timeline.forEach(function (d) {
      series.forEach(function (s) {
        max = Math.max(max, Number(d[s.key]) || 0);
      });
    });
    if (max <= 0) {
      emptyChart(ctx, w, h, 'Sin actividad en 14 días');
      return;
    }
    var plotW = w - pad.l - pad.r;
    var plotH = h - pad.t - pad.b;
    ctx.clearRect(0, 0, w, h);

    ctx.strokeStyle = surface2();
    for (var g = 0; g <= 4; g++) {
      var gy = pad.t + (plotH * g) / 4;
      ctx.beginPath();
      ctx.moveTo(pad.l, gy);
      ctx.lineTo(w - pad.r, gy);
      ctx.stroke();
    }

    function xAt(i) {
      return pad.l + (plotW * i) / Math.max(1, timeline.length - 1);
    }
    function yAt(v) {
      return pad.t + plotH - (v / max) * plotH;
    }

    series.forEach(function (s) {
      ctx.beginPath();
      ctx.strokeStyle = s.color;
      ctx.lineWidth = s.key === 'total' ? 2.5 : 1.6;
      timeline.forEach(function (d, i) {
        var x = xAt(i);
        var y = yAt(Number(d[s.key]) || 0);
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      });
      ctx.stroke();
      timeline.forEach(function (d, i) {
        var v = Number(d[s.key]) || 0;
        if (!v && s.key !== 'total') return;
        ctx.beginPath();
        ctx.fillStyle = s.color;
        ctx.arc(xAt(i), yAt(v), s.key === 'total' ? 3 : 2, 0, Math.PI * 2);
        ctx.fill();
      });
    });

    ctx.fillStyle = muted();
    ctx.font = '10px IBM Plex Mono, monospace';
    ctx.textAlign = 'center';
    timeline.forEach(function (d, i) {
      if (i % 2 === 0 || i === timeline.length - 1) {
        ctx.fillText(d.label || '', xAt(i), h - 18);
      }
    });

    var lx = pad.l;
    series.forEach(function (s) {
      ctx.fillStyle = s.color;
      ctx.fillRect(lx, 8, 10, 10);
      ctx.fillStyle = muted();
      ctx.textAlign = 'left';
      ctx.fillText(s.label, lx + 14, 17);
      lx += ctx.measureText(s.label).width + 30;
    });
  }

  function drawRadar(canvas, items) {
    var fit = fitCanvas(canvas);
    var ctx = fit.ctx, w = fit.w, h = fit.h;
    if (!items || items.length < 3) {
      emptyChart(ctx, w, h);
      return;
    }
    var cx = w / 2;
    var cy = h / 2 + 4;
    var r = Math.min(w, h) * 0.32;
    var n = items.length;
    ctx.clearRect(0, 0, w, h);

    function pt(i, value) {
      var ang = -Math.PI / 2 + (i * 2 * Math.PI) / n;
      var rr = r * (Math.max(0, Math.min(100, value)) / 100);
      return { x: cx + Math.cos(ang) * rr, y: cy + Math.sin(ang) * rr };
    }

    // rings
    [0.25, 0.5, 0.75, 1].forEach(function (scale) {
      ctx.beginPath();
      for (var i = 0; i < n; i++) {
        var p = pt(i, 100 * scale);
        if (i === 0) ctx.moveTo(p.x, p.y);
        else ctx.lineTo(p.x, p.y);
      }
      ctx.closePath();
      ctx.strokeStyle = surface2();
      ctx.stroke();
    });

    // axes + labels
    ctx.font = '10px IBM Plex Mono, monospace';
    ctx.fillStyle = muted();
    items.forEach(function (item, i) {
      var edge = pt(i, 100);
      ctx.beginPath();
      ctx.moveTo(cx, cy);
      ctx.lineTo(edge.x, edge.y);
      ctx.strokeStyle = surface2();
      ctx.stroke();
      var lab = pt(i, 118);
      ctx.textAlign = 'center';
      ctx.fillText(item.label || '', lab.x, lab.y);
    });

    ctx.beginPath();
    items.forEach(function (item, i) {
      var p = pt(i, item.value || 0);
      if (i === 0) ctx.moveTo(p.x, p.y);
      else ctx.lineTo(p.x, p.y);
    });
    ctx.closePath();
    ctx.fillStyle = 'rgba(184, 107, 58, 0.28)';
    ctx.strokeStyle = cssVar('--copper', '#B86B3A');
    ctx.lineWidth = 2;
    ctx.fill();
    ctx.stroke();
  }

  function initCharts() {
    var board = $('#metrics-charts');
    if (!board) return;
    var data = {};
    try {
      data = JSON.parse(board.getAttribute('data-charts') || '{}');
    } catch (e) {
      data = {};
    }

    function renderAll() {
      $$('[data-chart]', board).forEach(function (canvas) {
        var type = canvas.getAttribute('data-chart');
        var series = canvas.getAttribute('data-series');
        var payload = data[series] || [];
        var maxAttr = canvas.getAttribute('data-max');
        if (type === 'bar') drawBar(canvas, payload, maxAttr ? { max: Number(maxAttr) } : {});
        else if (type === 'hbar') drawHBar(canvas, payload);
        else if (type === 'pie') drawPie(canvas, payload, false);
        else if (type === 'donut') drawPie(canvas, payload, true);
        else if (type === 'line-multi') drawLineMulti(canvas, payload);
        else if (type === 'radar') drawRadar(canvas, payload);
      });
    }

    renderAll();
    var resizeTimer = null;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(renderAll, 120);
    });

    // re-render on theme change
    var obs = new MutationObserver(function () { renderAll(); });
    obs.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initThemeToggle();
    initCharts();
  });
})();
