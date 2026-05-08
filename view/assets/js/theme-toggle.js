/* ============================================================
   APTUS AI — Theme Toggle
   Manages Light/Dark mode with localStorage persistence & DB sync
   ============================================================ */

(function() {
  'use strict';

  const STORAGE_KEY = 'aptus-theme';
  const html = document.documentElement;

  function getPreferredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) return stored;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function setTheme(theme) {
    html.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
    updateToggleIcons(theme);
    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
  }

  function saveThemeToDatabase(theme) {
    fetch('/aptus_first_official_version/view/frontoffice/update_theme_ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ theme: theme })
    }).catch(function(e) {
      // Ignore errors (user might not be logged in)
    });
  }

  function updateToggleIcons(theme) {
    document.querySelectorAll('.theme-toggle').forEach(function(btn) {
      if (theme === 'dark') {
        btn.classList.add('is-dark');
      } else {
        btn.classList.remove('is-dark');
      }
      
      // Legacy support for display:block/none icons
      var sunIcon = btn.querySelector('.icon-sun');
      var moonIcon = btn.querySelector('.icon-moon');
      if (sunIcon && moonIcon) {
        if (theme === 'dark') {
          sunIcon.style.display = 'block';
          moonIcon.style.display = 'none';
        } else {
          sunIcon.style.display = 'none';
          moonIcon.style.display = 'block';
        }
      }
    });
  }

  function toggleTheme() {
    var current = html.getAttribute('data-theme') || 'light';
    var newTheme = current === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
    saveThemeToDatabase(newTheme);
  }

  // Initialize on load
  setTheme(getPreferredTheme());

  // Listen for system preference changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
    if (!localStorage.getItem(STORAGE_KEY)) {
      setTheme(e.matches ? 'dark' : 'light');
    }
  });

  // Expose toggle function globally
  window.AptusTheme = {
    toggle: toggleTheme,
    set: setTheme,
    get: function() { return html.getAttribute('data-theme') || 'light'; }
  };

  // Auto-bind toggle buttons
  document.addEventListener('DOMContentLoaded', function() {
    updateToggleIcons(getPreferredTheme());
    document.querySelectorAll('.theme-toggle').forEach(function(btn) {
      btn.addEventListener('click', toggleTheme);
    });
  });
})();
