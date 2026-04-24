/**
 * Aptus — Auto-dismiss Alerts
 * Automatically fades out and removes all .alert elements after 5 seconds.
 */
(function () {
  'use strict';

  function initAlertDismiss() {
    var alerts = document.querySelectorAll('.alert');

    alerts.forEach(function (alert) {
      // Set up the fade-out transition
      alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease';
      alert.style.overflow = 'hidden';

      // After 5 seconds, fade out
      setTimeout(function () {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';

        // After the fade animation, collapse and remove
        setTimeout(function () {
          alert.style.maxHeight = '0';
          alert.style.margin = '0';
          alert.style.padding = '0';
          alert.style.border = 'none';

          // Remove from DOM after collapse
          setTimeout(function () {
            if (alert.parentNode) {
              alert.parentNode.removeChild(alert);
            }
          }, 500);
        }, 500);
      }, 5000);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAlertDismiss);
  } else {
    initAlertDismiss();
  }
})();
