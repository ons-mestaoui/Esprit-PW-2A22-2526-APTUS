/* ============================================================
   APTUS AI — Charts (Vanilla JS)
   Lightweight bar charts, donut charts, and stat counters
   using canvas & DOM — no external libraries
   ============================================================ */

var AptusCharts = (function() {
  'use strict';

  /* ══════════════════════════════════════════════
     BAR CHART
     ══════════════════════════════════════════════ */
  function barChart(containerId, data, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var defaults = {
      barColor: 'var(--chart-1)',
      barColor2: 'var(--chart-2)',
      barBg: 'var(--chart-bar-bg)',
      height: 250,
      gap: 12,
      borderRadius: 6,
      showLabels: true,
      showValues: true,
      animate: true,
      dualBars: false
    };
    var opts = Object.assign({}, defaults, options || {});

    container.innerHTML = '';
    container.style.height = opts.height + 'px';
    container.classList.add('aptus-bar-chart');

    var maxVal = 0;
    data.forEach(function(item) {
      var v = opts.dualBars ? Math.max(item.value1 || 0, item.value2 || 0) : item.value;
      if (v > maxVal) maxVal = v;
    });
    if (maxVal === 0) maxVal = 1;

    var chartArea = document.createElement('div');
    chartArea.className = 'bar-chart__area';
    chartArea.style.cssText = 'display:flex;align-items:flex-end;gap:' + opts.gap + 'px;height:' + (opts.height - 40) + 'px;padding:0 8px;';

    data.forEach(function(item, i) {
      var group = document.createElement('div');
      group.className = 'bar-chart__group';
      group.style.cssText = 'flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;justify-content:flex-end;';

      if (opts.dualBars) {
        var barsRow = document.createElement('div');
        barsRow.style.cssText = 'display:flex;gap:4px;align-items:flex-end;width:100%;height:calc(100% - 24px);';

        var h1 = ((item.value1 || 0) / maxVal) * 100;
        var bar1 = document.createElement('div');
        bar1.className = 'bar-chart__bar';
        bar1.style.cssText = 'flex:1;border-radius:' + opts.borderRadius + 'px ' + opts.borderRadius + 'px 0 0;background:' + opts.barColor + ';height:0;transition:height 0.8s cubic-bezier(0.4,0,0.2,1);min-width:16px;';
        bar1.setAttribute('data-value', item.value1 || 0);
        bar1.title = (item.label1 || 'Set 1') + ': ' + (item.value1 || 0);

        var h2 = ((item.value2 || 0) / maxVal) * 100;
        var bar2 = document.createElement('div');
        bar2.className = 'bar-chart__bar';
        bar2.style.cssText = 'flex:1;border-radius:' + opts.borderRadius + 'px ' + opts.borderRadius + 'px 0 0;background:' + opts.barColor2 + ';height:0;transition:height 0.8s cubic-bezier(0.4,0,0.2,1);min-width:16px;opacity:0.7;';
        bar2.setAttribute('data-value', item.value2 || 0);
        bar2.title = (item.label2 || 'Set 2') + ': ' + (item.value2 || 0);

        barsRow.appendChild(bar1);
        barsRow.appendChild(bar2);
        group.appendChild(barsRow);

        if (opts.animate) {
          setTimeout(function() { bar1.style.height = h1 + '%'; }, 100 + i * 80);
          setTimeout(function() { bar2.style.height = h2 + '%'; }, 150 + i * 80);
        } else {
          bar1.style.height = h1 + '%';
          bar2.style.height = h2 + '%';
        }
      } else {
        var pct = (item.value / maxVal) * 100;
        var bar = document.createElement('div');
        bar.className = 'bar-chart__bar';
        bar.style.cssText = 'width:100%;border-radius:' + opts.borderRadius + 'px ' + opts.borderRadius + 'px 0 0;background:' + opts.barColor + ';height:0;transition:height 0.8s cubic-bezier(0.4,0,0.2,1);min-height:4px;';
        bar.setAttribute('data-value', item.value);
        bar.title = item.label + ': ' + item.value;

        // Hover effect
        bar.addEventListener('mouseenter', function() { this.style.opacity = '0.8'; });
        bar.addEventListener('mouseleave', function() { this.style.opacity = '1'; });

        group.appendChild(bar);

        if (opts.animate) {
          setTimeout(function() { bar.style.height = pct + '%'; }, 100 + i * 80);
        } else {
          bar.style.height = pct + '%';
        }
      }

      if (opts.showLabels) {
        var label = document.createElement('span');
        label.className = 'bar-chart__label';
        label.style.cssText = 'font-size:12px;color:var(--text-secondary);white-space:nowrap;';
        label.textContent = item.label;
        group.appendChild(label);
      }

      chartArea.appendChild(group);
    });

    container.appendChild(chartArea);
  }

  /* ══════════════════════════════════════════════
     DONUT CHART (SVG)
     ══════════════════════════════════════════════ */
  function donutChart(containerId, data, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var defaults = {
      size: 160,
      strokeWidth: 28,
      centerLabel: '',
      centerValue: '',
      animate: true,
      colors: ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)']
    };
    var opts = Object.assign({}, defaults, options || {});

    container.innerHTML = '';
    container.classList.add('aptus-donut-chart');

    var total = 0;
    data.forEach(function(d) { total += d.value; });
    if (total === 0) total = 1;

    var radius = (opts.size - opts.strokeWidth) / 2;
    var circumference = 2 * Math.PI * radius;
    var center = opts.size / 2;

    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('width', opts.size);
    svg.setAttribute('height', opts.size);
    svg.setAttribute('viewBox', '0 0 ' + opts.size + ' ' + opts.size);
    svg.style.transform = 'rotate(-90deg)';

    var offset = 0;
    data.forEach(function(item, i) {
      var pct = item.value / total;
      var dash = circumference * pct;
      var gap = circumference - dash;

      var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      circle.setAttribute('cx', center);
      circle.setAttribute('cy', center);
      circle.setAttribute('r', radius);
      circle.setAttribute('fill', 'none');
      circle.setAttribute('stroke', opts.colors[i % opts.colors.length]);
      circle.setAttribute('stroke-width', opts.strokeWidth);
      circle.setAttribute('stroke-linecap', 'round');

      if (opts.animate) {
        circle.setAttribute('stroke-dasharray', '0 ' + circumference);
        circle.setAttribute('stroke-dashoffset', -offset);
        setTimeout(function() {
          circle.setAttribute('stroke-dasharray', dash + ' ' + gap);
        }, 100 + i * 150);
        circle.style.transition = 'stroke-dasharray 0.8s cubic-bezier(0.4,0,0.2,1)';
      } else {
        circle.setAttribute('stroke-dasharray', dash + ' ' + gap);
        circle.setAttribute('stroke-dashoffset', -offset);
      }

      svg.appendChild(circle);
      offset += dash;
    });

    var wrapper = document.createElement('div');
    wrapper.style.cssText = 'position:relative;display:inline-flex;align-items:center;justify-content:center;';
    wrapper.appendChild(svg);

    if (opts.centerLabel || opts.centerValue) {
      var centerEl = document.createElement('div');
      centerEl.style.cssText = 'position:absolute;text-align:center;';
      if (opts.centerValue) {
        var valEl = document.createElement('div');
        valEl.style.cssText = 'font-size:24px;font-weight:700;color:var(--text-primary);line-height:1;';
        valEl.textContent = opts.centerValue;
        centerEl.appendChild(valEl);
      }
      if (opts.centerLabel) {
        var labEl = document.createElement('div');
        labEl.style.cssText = 'font-size:11px;color:var(--text-secondary);margin-top:2px;';
        labEl.textContent = opts.centerLabel;
        centerEl.appendChild(labEl);
      }
      wrapper.appendChild(centerEl);
    }

    container.appendChild(wrapper);

    // Legend
    if (data.length > 1) {
      var legend = document.createElement('div');
      legend.className = 'donut-legend';
      legend.style.cssText = 'display:flex;flex-wrap:wrap;gap:12px;margin-top:12px;';
      data.forEach(function(item, i) {
        var li = document.createElement('div');
        li.style.cssText = 'display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-secondary);';
        var dot = document.createElement('span');
        dot.style.cssText = 'width:10px;height:10px;border-radius:50%;background:' + opts.colors[i % opts.colors.length] + ';flex-shrink:0;';
        li.appendChild(dot);
        li.appendChild(document.createTextNode(item.label + ' (' + item.value + ')'));
        legend.appendChild(li);
      });
      container.appendChild(legend);
    }
  }

  /* ══════════════════════════════════════════════
     ANIMATED COUNTER
     ══════════════════════════════════════════════ */
  function animateCounter(elementId, endValue, duration) {
    var el = document.getElementById(elementId);
    if (!el) return;

    duration = duration || 1500;
    var startTime = null;
    var startValue = 0;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      var current = Math.floor(startValue + (endValue - startValue) * eased);
      el.textContent = current.toLocaleString();
      if (progress < 1) {
        requestAnimationFrame(step);
      }
    }
    requestAnimationFrame(step);
  }

  /* ══════════════════════════════════════════════
     MINI SPARKLINE (CSS bars)
     ══════════════════════════════════════════════ */
  function sparkline(containerId, values, color) {
    var container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';
    container.style.cssText = 'display:flex;align-items:flex-end;gap:2px;height:32px;';
    color = color || 'var(--chart-1)';

    var max = Math.max.apply(null, values);
    if (max === 0) max = 1;

    values.forEach(function(v, i) {
      var bar = document.createElement('div');
      var pct = (v / max) * 100;
      bar.style.cssText = 'flex:1;background:' + color + ';border-radius:2px;height:0;transition:height 0.5s ease;opacity:0.7;min-width:3px;';
      container.appendChild(bar);
      setTimeout(function() { bar.style.height = Math.max(pct, 8) + '%'; }, 50 + i * 30);
    });
  }

  // Public API
  return {
    bar: barChart,
    donut: donutChart,
    counter: animateCounter,
    sparkline: sparkline
  };
})();
