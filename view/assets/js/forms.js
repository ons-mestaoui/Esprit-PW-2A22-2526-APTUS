/* ============================================================
   APTUS AI — Form Utilities
   Drag & Drop upload, dynamic fields, tag input, validation
   ============================================================ */

(function() {
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {

    /* ══════════════════════════════════════════════
       DRAG & DROP FILE UPLOAD
       ══════════════════════════════════════════════ */
    document.querySelectorAll('.drop-zone').forEach(function(zone) {
      var fileInput = zone.querySelector('.drop-zone__input');
      var preview = zone.querySelector('.drop-zone__preview');
      var prompt = zone.querySelector('.drop-zone__prompt');

      zone.addEventListener('click', function() {
        if (fileInput) fileInput.click();
      });

      zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('drag-over');
      });

      zone.addEventListener('dragleave', function() {
        zone.classList.remove('drag-over');
      });

      zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
          if (fileInput) fileInput.files = e.dataTransfer.files;
          handleFilePreview(e.dataTransfer.files[0], preview, prompt);
        }
      });

      if (fileInput) {
        fileInput.addEventListener('change', function() {
          if (this.files.length) {
            handleFilePreview(this.files[0], preview, prompt);
          }
        });
      }
    });

    function handleFilePreview(file, preview, prompt) {
      if (!preview) return;
      if (file.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function(e) {
          preview.style.backgroundImage = 'url(' + e.target.result + ')';
          preview.style.display = 'block';
          if (prompt) prompt.style.display = 'none';
        };
        reader.readAsDataURL(file);
      } else {
        preview.textContent = file.name;
        preview.style.display = 'flex';
        if (prompt) prompt.style.display = 'none';
      }
    }

    /* ══════════════════════════════════════════════
       DYNAMIC FIELD REVEAL (Présentiel / En ligne)
       ══════════════════════════════════════════════ */
    document.querySelectorAll('[data-toggle-target]').forEach(function(toggle) {
      toggle.addEventListener('change', function() {
        var targetId = this.getAttribute('data-toggle-target');
        var targetEl = document.getElementById(targetId);
        if (!targetEl) return;

        var showValue = this.getAttribute('data-toggle-value');
        if (this.value === showValue || this.checked) {
          targetEl.style.display = 'block';
          targetEl.classList.add('animate-fade-in-up');
          var input = targetEl.querySelector('input, textarea, select');
          if (input) input.setAttribute('data-required', 'true');
        } else {
          targetEl.style.display = 'none';
          var input = targetEl.querySelector('input, textarea, select');
          if (input) input.removeAttribute('data-required');
        }
      });
    });

    /* ══════════════════════════════════════════════
       TAG INPUT (for Skills, etc.)
       ══════════════════════════════════════════════ */
    document.querySelectorAll('.tag-input').forEach(function(container) {
      var input = container.querySelector('.tag-input__field');
      var tagsContainer = container.querySelector('.tag-input__tags');
      var hiddenInput = container.querySelector('.tag-input__hidden');
      var tags = [];

      if (hiddenInput && hiddenInput.value) {
          tags = hiddenInput.value.split(',').map(function(t) { return t.trim(); }).filter(Boolean);
      }

      if (!input) return;

      input.addEventListener('keydown', function(e) {
        if ((e.key === 'Enter' || e.key === ',') && this.value.trim()) {
          e.preventDefault();
          var value = this.value.trim().replace(',', '');
          if (value && tags.indexOf(value) === -1) {
            tags.push(value);
            renderTags();
            this.value = '';
          }
        }
        if (e.key === 'Backspace' && !this.value && tags.length) {
          tags.pop();
          renderTags();
        }
      });

      function renderTags() {
        if (!tagsContainer) return;
        tagsContainer.innerHTML = '';
        tags.forEach(function(tag, i) {
          var el = document.createElement('span');
          el.className = 'tag-input__tag';
          el.innerHTML = tag + '<button type="button" class="tag-input__remove" data-index="' + i + '">&times;</button>';
          tagsContainer.appendChild(el);
        });
        if (hiddenInput) hiddenInput.value = tags.join(',');

        tagsContainer.querySelectorAll('.tag-input__remove').forEach(function(btn) {
          btn.addEventListener('click', function() {
            tags.splice(parseInt(this.dataset.index), 1);
            renderTags();
          });
        });
      }

      if (tags.length > 0) renderTags();
    });

    /* ══════════════════════════════════════════════
       MOTEUR DE VALIDATION PERSONNALISÉ
       ══════════════════════════════════════════════ */
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
      
      function getOrCreateErrorEl(field) {
        var group = field.closest('.form-group');
        if (!group) return null;
        var errorEl = group.querySelector('.form-error');
        if (!errorEl) {
          errorEl = document.createElement('div');
          errorEl.className = 'form-error animate-fade-in-up';
          group.appendChild(errorEl);
        }
        return errorEl;
      }

      form.addEventListener('submit', function(e) {
        var isValid = true;
        form.querySelectorAll('.form-error').forEach(function(err) { err.textContent = ''; });
        form.querySelectorAll('.input-error').forEach(function(inp) { inp.classList.remove('input-error'); });

        form.querySelectorAll('[data-required]').forEach(function(field) {
          if (!field.value.trim()) {
            isValid = false;
            field.classList.add('input-error');
            var errorEl = getOrCreateErrorEl(field);
            if (errorEl) errorEl.textContent = 'Ce champ est obligatoire';
          }
        });

        form.querySelectorAll('[data-type]').forEach(function(field) {
          var type = field.getAttribute('data-type');
          var val = field.value.trim();
          if (!val) return; 
          if (type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            isValid = false;
            field.classList.add('input-error');
            var errorEl = getOrCreateErrorEl(field);
            if (errorEl) errorEl.textContent = 'Format d\'email invalide';
          }
        });

        var pwMatches = form.querySelectorAll('[data-match]');
        pwMatches.forEach(function(pw) {
           var matchTarget = form.querySelector('#' + pw.dataset.match);
           if (matchTarget && pw.value !== matchTarget.value) {
             isValid = false;
             pw.classList.add('input-error');
             var errorEl = getOrCreateErrorEl(pw);
             if (errorEl) errorEl.textContent = 'Les mots de passe ne correspondent pas';
           }
         });

        if (!isValid) {
          e.preventDefault();
          var firstError = form.querySelector('.input-error');
          if (firstError) {
            var rect = firstError.getBoundingClientRect();
            window.scrollTo({ top: window.pageYOffset + rect.top - 150, behavior: 'smooth' });
            firstError.focus();
          }
        }
      });
    });

    /* ══════════════════════════════════════════════
       MODAL HANDLERS
       ══════════════════════════════════════════════ */
    document.querySelectorAll('[data-modal]').forEach(function(trigger) {
      trigger.addEventListener('click', function() {
        var modalId = this.getAttribute('data-modal');
        var modal = document.getElementById(modalId);
        if (modal) modal.classList.add('active');
      });
    });

    document.querySelectorAll('.modal-close, .modal-overlay').forEach(function(el) {
      el.addEventListener('click', function(e) {
        if (e.target === this || this.classList.contains('modal-close')) {
          var modal = this.closest('.modal-overlay');
          if (modal) modal.classList.remove('active');
        }
      });
    });

  });
})();
