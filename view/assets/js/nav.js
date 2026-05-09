/* ============================================================
   APTUS AI — Navigation Controller
   Handles mobile menu, dropdowns, active links, sidebar toggle
   ============================================================ */

(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {

    /* ── Mobile Menu Toggle ──────────────────── */
    var hamburger = document.getElementById('hamburger-btn');
    var mobileMenu = document.getElementById('mobile-menu');
    
    if (hamburger && mobileMenu) {
      hamburger.addEventListener('click', function(e) {
        e.stopPropagation();
        mobileMenu.classList.toggle('open');
        hamburger.classList.toggle('active');
      });
    }

    /* ── Sidebar Toggle (Back-office) ─────────── */
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('main-content');
    
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');
      });
    }

    /* ── Dropdown Toggles ─────────────────────── */
    document.querySelectorAll('.dropdown-trigger').forEach(function(trigger) {
      trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        var dropdown = this.closest('.dropdown');
        
        // Close other dropdowns
        document.querySelectorAll('.dropdown.open').forEach(function(d) {
          if (d !== dropdown) d.classList.remove('open');
        });
        
        dropdown.classList.toggle('open');
      });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.open').forEach(function(d) {
          d.classList.remove('open');
        });
      }
      if (mobileMenu && !e.target.closest('#mobile-menu') && !e.target.closest('#hamburger-btn')) {
        mobileMenu.classList.remove('open');
        if (hamburger) hamburger.classList.remove('active');
      }
    });

    /* ── Active Nav Link Highlighting ──────────── */
    var currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link, .sidebar-link, .nav-anchor').forEach(function(link) {
      var href = link.getAttribute('href');
      var pageName = currentPath.split('/').pop() || 'index.php';
      if (href && href !== '#' && !href.startsWith('#') && (pageName === href || currentPath.endsWith(href))) {
        link.classList.add('active');
      }
    });

    /* ── Smooth Scroll for Anchor Links ────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        var target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    /* ── Scroll Animation Observer ────────────── */
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
      observer.observe(el);
    });
  });
})();
