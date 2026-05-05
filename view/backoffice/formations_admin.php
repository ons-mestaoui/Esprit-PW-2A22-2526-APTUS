<?php $pageTitle = "Formations"; $pageCSS = "formations.css"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>
<!-- Included inside layout_back.php -->

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <h1>Gestion des Formations</h1>
      <p>Ajoutez, modifiez et gérez le catalogue de formations</p>
    </div>
    <button class="btn btn-primary" data-modal="add-formation-modal" id="add-formation-btn">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Ajouter une formation
    </button>
  </div>
</div>

<!-- ═══ Stats ═══ -->
<div class="grid grid-4 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Total Formations</div>
      <div class="stat-card__value">0</div>
    </div>
    <div class="stat-card__icon purple"><i data-lucide="graduation-cap" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Étudiants inscrits</div>
      <div class="stat-card__value">0</div>
    </div>
    <div class="stat-card__icon teal"><i data-lucide="users" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Certificats délivrés</div>
      <div class="stat-card__value">0</div>
    </div>
    <div class="stat-card__icon blue"><i data-lucide="award" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Taux de complétion</div>
      <div class="stat-card__value">0%</div>
    </div>
    <div class="stat-card__icon orange"><i data-lucide="target" style="width:22px;height:22px;"></i></div>
  </div>
</div>

<!-- ═══ Formations Table ═══ -->
<div class="card-flat" style="overflow:hidden;">
  <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
    <h3 class="text-md fw-semibold">Toutes les formations</h3>
    <div class="search-bar" style="max-width:280px;">
      <i data-lucide="search" style="width:16px;height:16px;"></i>
      <input type="text" class="input" placeholder="Rechercher..." id="admin-formation-search">
    </div>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>Formation</th>
        <th>Domaine</th>
        <th>Niveau</th>
        <th>Lieu</th>
        <th>Tuteur</th>
        <th>Inscrits</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="8">
          <div class="empty-state-mini" style="padding:var(--space-12);text-align:center;background:var(--bg-secondary);border-radius:var(--radius-lg);opacity:0.6;">
            <i data-lucide="graduation-cap" style="width:40px;height:40px;margin:0 auto var(--space-3);display:block;color:var(--text-tertiary);"></i>
            <p style="color:var(--text-secondary);">Aucune formation n'a été créée pour le moment</p>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<!-- ═══ Add Formation Modal (Smart Form) ═══ -->
<div class="modal-overlay" id="add-formation-modal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3>Nouvelle Formation</h3>
      <button class="modal-close btn-icon"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
    </div>
    <div class="modal-body">
      <form class="auth-form" data-validate enctype="multipart/form-data" id="add-formation-form">

        <div class="form-group">
          <label class="form-label" for="form-title">Titre de la formation</label>
          <input type="text" class="input" id="form-title" name="title" placeholder="Ex: React.js Avancé" required>
          <span class="form-error"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="form-description">Description</label>
          <textarea class="textarea" id="form-description" name="description" rows="4" placeholder="Décrivez le contenu de la formation..." required></textarea>
          <span class="form-error"></span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="form-group">
            <label class="form-label" for="form-date">Date de début</label>
            <input type="date" class="input" id="form-date" name="date" required>
            <span class="form-error"></span>
          </div>
          <div class="form-group">
            <label class="form-label" for="form-domain">Domaine</label>
            <select class="select" id="form-domain" name="domain" required>
              <option value="">Sélectionnez...</option>
              <option>Développement Web</option>
              <option>Data Science</option>
              <option>Design UI/UX</option>
              <option>Cybersécurité</option>
              <option>Marketing Digital</option>
              <option>Cloud & DevOps</option>
            </select>
            <span class="form-error"></span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="form-level">Niveau</label>
          <select class="select" id="form-level" name="level" required>
            <option value="">Sélectionnez...</option>
            <option>Débutant</option>
            <option>Intermédiaire</option>
            <option>Avancé</option>
            <option>Expert</option>
          </select>
          <span class="form-error"></span>
        </div>

        <!-- Cover Image (16:9 Drop Zone) -->
        <div class="form-group">
          <label class="form-label">Image de couverture (16:9)</label>
          <div class="drop-zone drop-zone--landscape">
            <input type="file" class="drop-zone__input" name="cover_image" accept="image/*">
            <div class="drop-zone__prompt">
              <i data-lucide="image" style="width:32px;height:32px;"></i>
              <span>Déposez l'image ici ou <span class="text-accent">parcourir</span></span>
              <span class="text-xs text-tertiary">Format 16:9 recommandé — PNG, JPG — Max. 5MB</span>
            </div>
            <div class="drop-zone__preview"></div>
          </div>
        </div>

        <!-- Lieu Toggle (Présentiel / En ligne) -->
        <div class="form-group radio-toggle">
          <label class="form-label">Lieu</label>
          <div class="lieu-toggle">
            <div class="lieu-toggle__option">
              <input type="radio" name="lieu" id="lieu-presentiel" value="presentiel" checked>
              <label for="lieu-presentiel" class="lieu-toggle__label">
                <i data-lucide="map-pin" style="width:16px;height:16px;"></i>
                Présentiel
              </label>
            </div>
            <div class="lieu-toggle__option">
              <input type="radio" name="lieu" id="lieu-online" value="online"
                data-toggle-target="online-url-field" data-toggle-value="online">
              <label for="lieu-online" class="lieu-toggle__label">
                <i data-lucide="video" style="width:16px;height:16px;"></i>
                En ligne
              </label>
            </div>
          </div>
        </div>

        <!-- Conditional URL field -->
        <div class="form-group conditional-field" id="online-url-field" data-conditional>
          <label class="form-label" for="form-url">URL de la session en ligne</label>
          <div class="input-icon-wrapper">
            <i data-lucide="link" style="width:18px;height:18px;"></i>
            <input type="url" class="input" id="form-url" name="online_url" placeholder="https://meet.aptus.ai/...">
          </div>
          <span class="form-error"></span>
          <span class="form-hint">Cette URL sera partagée avec les étudiants inscrits</span>
        </div>

      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary modal-close">Annuler</button>
      <button class="btn btn-primary" type="submit" form="add-formation-form">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Créer la formation
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle lieu radio toggle for online URL field
  var onlineRadio = document.getElementById('lieu-online');
  var presentielRadio = document.getElementById('lieu-presentiel');
  var urlField = document.getElementById('online-url-field');
  
  function toggleUrlField() {
    if (onlineRadio && onlineRadio.checked) {
      urlField.style.display = 'block';
      urlField.classList.add('animate-fade-in-up');
      var input = urlField.querySelector('input');
      if (input) input.setAttribute('required', '');
    } else {
      urlField.style.display = 'none';
      var input = urlField.querySelector('input');
      if (input) input.removeAttribute('required');
    }
  }

  if (onlineRadio) onlineRadio.addEventListener('change', toggleUrlField);
  if (presentielRadio) presentielRadio.addEventListener('change', toggleUrlField);
});
</script>
