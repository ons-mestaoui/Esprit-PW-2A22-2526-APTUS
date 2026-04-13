<?php $pageTitle = "Profil Entreprise"; $pageCSS = "cv.css"; $userRole = "Entreprise"; ?>

<?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php (Enterprise view) -->

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="building-2" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Profil Entreprise
      </h1>
      <p class="page-header__subtitle">Gérez la vitrine de votre entreprise sur Aptus</p>
    </div>
  </div>
</div>

<!-- ═══ Profile Content ═══ -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;">

  <!-- Left: Company Card -->
  <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-8);text-align:center;">
    <div style="width:110px;height:110px;border-radius:var(--radius-lg);background:linear-gradient(135deg,var(--accent-secondary),var(--accent-primary));display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-4);font-size:2rem;font-weight:800;color:#fff;">
      TS
    </div>
    <h2 style="font-size:var(--fs-xl);font-weight:700;margin-bottom:var(--space-1);">TechSphere Inc.</h2>
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);">Technologie • 51-200 employés</p>
    <span class="badge badge-success" style="margin-bottom:var(--space-5);">Compte vérifié</span>

    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-5);margin-top:var(--space-4);text-align:left;display:flex;flex-direction:column;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="mail" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">contact@techsphere.tn</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="globe" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">www.techsphere.tn</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="map-pin" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Tunis, Lac 2</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Inscrit le 20 Mar. 2026</span>
      </div>
    </div>

    <!-- Quick Stats -->
    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-4);margin-top:var(--space-4);display:grid;grid-template-columns:1fr 1fr;gap:var(--space-3);">
      <div style="text-align:center;">
        <div style="font-size:var(--fs-xl);font-weight:700;color:var(--accent-primary);">6</div>
        <div class="text-xs text-secondary">Postes actifs</div>
      </div>
      <div style="text-align:center;">
        <div style="font-size:var(--fs-xl);font-weight:700;color:var(--accent-secondary);">110</div>
        <div class="text-xs text-secondary">Candidatures</div>
      </div>
    </div>

    <button class="btn btn-secondary w-full" style="margin-top:var(--space-5);">
      <i data-lucide="image" style="width:16px;height:16px;"></i>
      Changer le logo
    </button>
  </div>

  <!-- Right: Editable Details -->
  <div style="display:flex;flex-direction:column;gap:var(--space-6);">

    <!-- Company Info -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="building-2" style="width:20px;height:20px;color:var(--accent-primary);"></i>
        Informations de l'Entreprise
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom de l'entreprise</label>
          <input type="text" class="input" value="TechSphere Inc.">
        </div>
        <div class="form-group">
          <label class="form-label">Secteur d'activité</label>
          <select class="select">
            <option value="tech" selected>Technologie</option>
            <option value="finance">Finance & Banque</option>
            <option value="sante">Santé</option>
            <option value="education">Éducation</option>
            <option value="commerce">Commerce & Retail</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Taille de l'entreprise</label>
          <select class="select">
            <option value="1-10">1-10 employés</option>
            <option value="11-50">11-50 employés</option>
            <option value="51-200" selected>51-200 employés</option>
            <option value="201-500">201-500 employés</option>
            <option value="500+">500+ employés</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Année de fondation</label>
          <input type="number" class="input" value="2018">
        </div>
      </div>
    </div>

    <!-- Contact -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="phone" style="width:20px;height:20px;color:var(--accent-secondary);"></i>
        Contact & Liens
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Email professionnel</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" value="contact@techsphere.tn">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <div class="input-icon-wrapper">
            <i data-lucide="phone" style="width:18px;height:18px;"></i>
            <input type="tel" class="input" value="+216 71 123 456">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Site Web</label>
          <div class="input-icon-wrapper">
            <i data-lucide="globe" style="width:18px;height:18px;"></i>
            <input type="url" class="input" value="https://www.techsphere.tn">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">LinkedIn</label>
          <div class="input-icon-wrapper">
            <i data-lucide="linkedin" style="width:18px;height:18px;"></i>
            <input type="url" class="input" value="https://linkedin.com/company/techsphere">
          </div>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Adresse</label>
          <div class="input-icon-wrapper">
            <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
            <input type="text" class="input" value="Rue du Lac Windermere, Les Berges du Lac 2, Tunis">
          </div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="file-text" style="width:20px;height:20px;color:var(--stat-orange);"></i>
        Description
      </h3>
      <div class="form-group">
        <textarea class="textarea" rows="5">TechSphere est une entreprise tunisienne spécialisée dans le développement de solutions logicielles innovantes. Nous accompagnons les entreprises dans leur transformation digitale grâce à nos expertises en développement web, mobile, cloud et intelligence artificielle. Notre équipe de plus de 100 ingénieurs passionnés travaille sur des projets à fort impact pour des clients nationaux et internationaux.</textarea>
      </div>
    </div>

    <!-- Save button -->
    <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
      <button class="btn btn-ghost">Annuler</button>
      <button class="btn btn-primary">
        <i data-lucide="save" style="width:18px;height:18px;"></i>
        Enregistrer les modifications
      </button>
    </div>
  </div>
</div>
