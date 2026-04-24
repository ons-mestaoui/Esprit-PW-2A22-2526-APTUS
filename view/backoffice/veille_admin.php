<?php $pageTitle = "Veille Marché — Publisher"; $pageCSS = "veille.css"; ?>

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
      <h1>Veille du Marché — Publisher</h1>
      <p>Rédigez et publiez les rapports et données du marché</p>
    </div>
    <span class="badge badge-success">
      <i data-lucide="check-circle" style="width:12px;height:12px;"></i> 12 rapports publiés
    </span>
  </div>
</div>

<!-- ═══ Stats Summary ═══ -->
<div class="grid grid-3 gap-6 mb-8 stagger">
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Rapports Publiés</div>
      <div class="stat-card__value">12</div>
      <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +3 ce mois</div>
    </div>
    <div class="stat-card__icon purple"><i data-lucide="file-text" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Vues totales</div>
      <div class="stat-card__value">8,920</div>
      <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +24%</div>
    </div>
    <div class="stat-card__icon teal"><i data-lucide="eye" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Engagement moyen</div>
      <div class="stat-card__value">4.8m</div>
      <div class="stat-card__trend up"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +12%</div>
    </div>
    <div class="stat-card__icon blue"><i data-lucide="timer" style="width:22px;height:22px;"></i></div>
  </div>
</div>

<div class="publisher-layout">
  <!-- ═══ PUBLISHER FORM ═══ -->
  <div class="publisher-form">
    <h3 class="mb-6" style="display:flex;align-items:center;gap:var(--space-2);">
      <i data-lucide="pen-tool" style="width:20px;height:20px;color:var(--accent-primary);"></i>
      Nouveau Rapport
    </h3>
    <form action="#" method="POST" data-validate enctype="multipart/form-data" id="publish-report-form">

      <div class="form-group">
        <label class="form-label" for="report-title">Titre du rapport</label>
        <input type="text" class="input" id="report-title" name="title" placeholder="Ex: Tendances du marché IT Q1 2026" required>
        <span class="form-error"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="report-category">Catégorie</label>
        <select class="select" id="report-category" name="category" required>
          <option value="">Sélectionnez...</option>
          <option value="tech">Technologie</option>
          <option value="salaires">Salaires</option>
          <option value="competences">Compétences</option>
          <option value="emploi">Emploi</option>
          <option value="ia">IA & HR Tech</option>
          <option value="general">Général</option>
        </select>
        <span class="form-error"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="report-content">Contenu du rapport</label>
        <textarea class="textarea" id="report-content" name="content" rows="12" placeholder="Rédigez votre rapport complet ici. Utilisez des paragraphes structurés avec des données chiffrées..." required style="min-height:250px;"></textarea>
        <span class="form-error"></span>
        <span class="form-hint">Rédigez un rapport détaillé avec des données, analyses et recommandations</span>
      </div>

      <div class="auth-form__row" style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label" for="report-data-label">Donnée clé (Label)</label>
          <input type="text" class="input" id="report-data-label" name="data_label" placeholder="Ex: Offres IT ce mois">
        </div>
        <div class="form-group">
          <label class="form-label" for="report-data-value">Valeur</label>
          <input type="text" class="input" id="report-data-value" name="data_value" placeholder="Ex: 2,845">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Document / Statistiques (optionnel)</label>
        <div class="drop-zone">
          <input type="file" class="drop-zone__input" name="attachment" accept=".pdf,.xlsx,.csv,.png,.jpg">
          <div class="drop-zone__prompt">
            <i data-lucide="upload-cloud" style="width:28px;height:28px;"></i>
            <span>Déposez un fichier ici ou <span class="text-accent">parcourir</span></span>
            <span class="text-xs text-tertiary">PDF, Excel, CSV, Image — Max. 10MB</span>
          </div>
          <div class="drop-zone__preview"></div>
        </div>
      </div>

      <div class="flex gap-3" style="margin-top:var(--space-4);">
        <button type="submit" class="btn btn-primary btn-lg">
          <i data-lucide="send" style="width:18px;height:18px;"></i>
          Publier le rapport
        </button>
        <button type="button" class="btn btn-secondary btn-lg">
          <i data-lucide="save" style="width:18px;height:18px;"></i>
          Brouillon
        </button>
      </div>
    </form>
  </div>

  <!-- ═══ PUBLISHED REPORTS LIST ═══ -->
  <div>
    <h4 class="mb-4" style="display:flex;align-items:center;gap:var(--space-2);">
      <i data-lucide="history" style="width:18px;height:18px;color:var(--text-secondary);"></i>
      Rapports publiés
    </h4>
    <div class="published-list">
      <?php
      $published = [
        ['id' => 1, 'title' => 'Tendances IT Q1 2026', 'date' => '08 Avr.', 'views' => '1.2k', 'status' => 'Publié'],
        ['id' => 2, 'title' => 'Compétences clés 2026', 'date' => '02 Avr.', 'views' => '890', 'status' => 'Publié'],
        ['id' => 3, 'title' => 'Salaires digital comparatif', 'date' => '28 Mar.', 'views' => '1.5k', 'status' => 'Publié'],
        ['id' => 4, 'title' => 'Impact IA recrutement', 'date' => '20 Mar.', 'views' => '2.1k', 'status' => 'Publié'],
        ['id' => 5, 'title' => 'Freelancing vs CDI', 'date' => '15 Mar.', 'views' => '756', 'status' => 'Publié'],
        ['id' => 6, 'title' => 'Rapport Q2 (brouillon)', 'date' => '10 Avr.', 'views' => '—', 'status' => 'Brouillon'],
      ];
      foreach ($published as $p):
      ?>
      <div class="published-item" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div class="published-item__title"><?php echo $p['title']; ?></div>
            <div class="published-item__meta">
              <span><i data-lucide="calendar" style="width:11px;height:11px;display:inline;vertical-align:-1px;"></i> <?php echo $p['date']; ?></span>
              <span><i data-lucide="eye" style="width:11px;height:11px;display:inline;vertical-align:-1px;"></i> <?php echo $p['views']; ?></span>
              <span class="badge <?php echo $p['status'] === 'Publié' ? 'badge-success' : 'badge-warning'; ?>" style="font-size:10px;"><?php echo $p['status']; ?></span>
            </div>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-sm btn-ghost" title="Modifier"><i data-lucide="pencil" style="width:14px;height:14px;"></i></button>
            <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" onclick="openDeleteModal(<?php echo $p['id']; ?>, '<?php echo addslashes($p['title']); ?>')" title="Supprimer"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- 3. Modal Confirmation Suppression -->
<div class="aptus-modal-overlay" id="modal-delete">
    <div class="aptus-modal-content" style="max-width:450px; text-align:center; padding: 40px 32px; position:relative;">
        <button class="modal-close" onclick="closeModals()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:var(--text-tertiary); cursor:pointer;"><i data-lucide="x" style="width:24px;height:24px;"></i></button>

        <div style="width:64px; height:64px; background:rgba(239,68,68,0.1); color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <i data-lucide="alert-triangle" style="width:32px;height:32px;"></i>
        </div>

        <h3 style="margin-bottom:12px; color:var(--text-primary); font-size:1.5rem; font-weight:800;">Confirmation de suppression</h3>
        <p id="delete-modal-msg" style="color:var(--text-secondary); margin-bottom:24px; line-height:1.6;">Êtes-vous sûr de vouloir continuer ? Cette action est irréversible.</p>

        <form action="veille_admin.php" method="POST" id="form-delete">
            <input type="hidden" name="action" id="delete-action" value="delete">
            <input type="hidden" name="id" id="delete-id-field" value="">

            <div style="display:flex; gap:12px; justify-content:center;">
                <button type="button" class="btn btn-secondary" style="flex:1; border-radius:12px; font-weight:700;" onclick="closeModals()">Annuler</button>
                <button type="submit" class="btn btn-primary" style="flex:1; background:#ef4444; border-color:#ef4444; color:white; border-radius:12px; font-weight:700;">Oui, Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, title) {
    document.getElementById('delete-id-field').value = id;
    document.getElementById('delete-modal-msg').innerHTML = `Êtes-vous sûr de vouloir supprimer le rapport <strong>"${title}"</strong> ? Cette action est irréversible.`;
    document.getElementById('modal-delete').classList.add('active');
}

function closeModals() {
    document.getElementById('modal-delete').classList.remove('active');
}
</script>
