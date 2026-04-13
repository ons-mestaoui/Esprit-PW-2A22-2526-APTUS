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

    // Handle radio group toggles
    document.querySelectorAll('.radio-toggle').forEach(function(group) {
      var radios = group.querySelectorAll('input[type="radio"]');
      radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
          var targetId = this.getAttribute('data-toggle-target');
          var showValue = this.getAttribute('data-toggle-value');
          
          // Hide all conditional sections first
          group.querySelectorAll('[data-conditional]').forEach(function(section) {
            section.style.display = 'none';
          });

          if (targetId && this.value === showValue) {
            var target = document.getElementById(targetId);
            if (target) {
              target.style.display = 'block';
              target.classList.add('animate-fade-in-up');
            }
          }
        });
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

      // Initial render
      if (tags.length > 0) {
        renderTags();
      }

      // Handle suggested skills buttons if they exist nearby
      var parentGroup = container.closest('.form-group');
      if (parentGroup) {
        parentGroup.querySelectorAll('.suggested-skill-btn').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var value = this.textContent.trim();
            if (value && tags.indexOf(value) === -1) {
              tags.push(value);
              renderTags();
            }
          });
        });
      }
    });

    /* ══════════════════════════════════════════════
       FORM VALIDATION
       ══════════════════════════════════════════════ */
    /* ══════════════════════════════════════════════
       MOTEUR DE VALIDATION PERSONNALISÉ
       Cette section remplace les contrôles natifs HTML5 par une logique
       JavaScript sur mesure (Conformité aux contraintes du projet).
       ══════════════════════════════════════════════ */
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        var isValid = true;
        
        // Réinitialisation des messages d'erreur et des styles visuels
        form.querySelectorAll('.form-error').forEach(function(err) {
          err.textContent = '';
        });
        form.querySelectorAll('.input-error').forEach(function(inp) {
          inp.classList.remove('input-error');
        });

        // 1. Validation des champs obligatoires via l'attribut personnalisé 'data-required'
        // On évite 'required' pour ne pas déclencher la bulle native du navigateur.
        form.querySelectorAll('[data-required]').forEach(function(field) {
          if (!field.value.trim()) {
            isValid = false;
            field.classList.add('input-error');
            var errorEl = field.closest('.form-group')
              ? field.closest('.form-group').querySelector('.form-error')
              : null;
            if (errorEl) errorEl.textContent = 'Ce champ est obligatoire';
          }
        });

        // 2. Validation des formats spécifiques via 'data-type'
        // Permet de vérifier les emails et URLs sans utiliser input type="email/url"
        form.querySelectorAll('[data-type]').forEach(function(field) {
          var type = field.getAttribute('data-type');
          var val = field.value.trim();
          if (!val) return; // On ne valide le format que si le champ n'est pas vide

          // Validation Email par Expression Régulière (Regex)
          if (type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            isValid = false;
            field.classList.add('input-error');
            var errorEl = field.closest('.form-group') ? field.closest('.form-group').querySelector('.form-error') : null;
            if (errorEl) errorEl.textContent = 'Format d\'email invalide';
          }
          // Validation URL par Regex
          if (type === 'url' && !/^https?:\/\/[^\s$.?#].[^\s]*$/.test(val)) {
            isValid = false;
            field.classList.add('input-error');
            var errorEl = field.closest('.form-group') ? field.closest('.form-group').querySelector('.form-error') : null;
            if (errorEl) errorEl.textContent = 'URL invalide (doit commencer par http/https)';
          }
        });

        // 3. Validation de correspondance (Confirmation de mot de passe)
        var pwMatches = form.querySelectorAll('[data-match]');
        pwMatches.forEach(function(pw) {
           var matchTarget = form.querySelector('#' + pw.dataset.match);
           if (matchTarget && pw.value !== matchTarget.value) {
             isValid = false;
             pw.classList.add('input-error');
             var errorEl = pw.closest('.form-group') ? pw.closest('.form-group').querySelector('.form-error') : null;
             if (errorEl) errorEl.textContent = 'Les mots de passe ne correspondent pas';
           }
        });

        // Si une erreur est détectée, on bloque la soumission et on scrolle vers l'erreur
        if (!isValid) {
          e.preventDefault();
          var firstError = form.querySelector('.input-error');
          if (firstError) {
            var rect = firstError.getBoundingClientRect();
            window.scrollTo({ top: window.pageYOffset + rect.top - 100, behavior: 'smooth' });
            firstError.focus();
          }
        }
      });

      // Live validation on input
      form.querySelectorAll('.input, .select, .textarea').forEach(function(field) {
        field.addEventListener('blur', function() {
          if (this.classList.contains('input-error') && this.value.trim()) {
            this.classList.remove('input-error');
            var errorEl = this.closest('.form-group')
              ? this.closest('.form-group').querySelector('.form-error')
              : null;
            if (errorEl) errorEl.textContent = '';
          }
        });
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
        if (e.target === this) {
          this.closest('.modal-overlay').classList.remove('active');
        }
      });
    });

    // Close modal on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
          m.classList.remove('active');
        });
      }
    });

    /* ══════════════════════════════════════════════
       TAB SWITCHING
       ══════════════════════════════════════════════ */
    document.querySelectorAll('.tabs').forEach(function(tabContainer) {
      tabContainer.querySelectorAll('.tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
          // Remove active from siblings
          tabContainer.querySelectorAll('.tab').forEach(function(t) {
            t.classList.remove('active');
          });
          this.classList.add('active');

          // Show corresponding panel
          var panelId = this.getAttribute('data-tab');
          if (panelId) {
            var parent = tabContainer.closest('.tab-container') || document;
            parent.querySelectorAll('.tab-panel').forEach(function(panel) {
              panel.classList.remove('active');
            });
            var target = parent.querySelector('#' + panelId);
            if (target) target.classList.add('active');
          }
        });
      });
    });

  });
})();
