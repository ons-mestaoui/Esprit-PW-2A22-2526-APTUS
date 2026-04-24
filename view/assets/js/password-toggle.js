/**
 * Aptus — Password Visibility Toggle
 * Adds a show/hide eye icon to all password inputs.
 * Must be loaded BEFORE lucide.createIcons() is called.
 * The init is deferred until DOMContentLoaded + a small delay to ensure
 * Lucide has finished rendering icons.
 */
(function () {
  'use strict';

  // SVG icons as raw strings (no Lucide dependency)
  var SVG_EYE_OFF = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>';

  var SVG_EYE = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>';

  function initPasswordToggles() {
    var passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach(function (input) {
      if (input.dataset.toggleInit) return;
      input.dataset.toggleInit = 'true';

      // Ensure the wrapper is position:relative
      var wrapper = input.closest('.input-icon-wrapper') || input.parentElement;
      if (wrapper) {
        wrapper.style.position = 'relative';
      }

      // Add right padding so text doesn't go under the button
      input.style.paddingRight = '2.75rem';

      // Create toggle button with INLINE styles to avoid any CSS conflicts
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.setAttribute('aria-label', 'Afficher le mot de passe');
      btn.setAttribute('tabindex', '-1');

      // Apply all styles inline to guarantee positioning
      btn.style.cssText = 'position:absolute;right:0.75rem;left:auto;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;padding:4px;display:flex;align-items:center;justify-content:center;z-index:5;pointer-events:all;border-radius:4px;transition:color 0.15s ease,background 0.15s ease;';

      btn.innerHTML = SVG_EYE_OFF;

      // Make sure the SVG inside the button also has correct inline styles
      var svg = btn.querySelector('svg');
      if (svg) {
        svg.style.cssText = 'position:static;transform:none;pointer-events:none;width:18px;height:18px;left:auto;top:auto;';
      }

      // Hover effects
      btn.addEventListener('mouseenter', function () {
        btn.style.color = '#6C5CE7';
        btn.style.background = 'rgba(108,92,231,0.08)';
      });
      btn.addEventListener('mouseleave', function () {
        btn.style.color = '#9CA3AF';
        btn.style.background = 'none';
      });

      // Insert after the input
      input.insertAdjacentElement('afterend', btn);

      // Toggle logic
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.innerHTML = isPassword ? SVG_EYE : SVG_EYE_OFF;

        // Re-apply inline styles to the new SVG
        var newSvg = btn.querySelector('svg');
        if (newSvg) {
          newSvg.style.cssText = 'position:static;transform:none;pointer-events:none;width:18px;height:18px;left:auto;top:auto;';
        }

        btn.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        input.focus();
      });
    });
  }

  // Run after everything is loaded and Lucide has rendered
  function safeInit() {
    // Small delay to ensure Lucide has finished
    setTimeout(initPasswordToggles, 100);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', safeInit);
  } else {
    safeInit();
  }
})();
