<?php $pageTitle = "Offres Disponibles"; $pageCSS = "feeds.css"; ?>

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
      <h1>Offres Disponibles</h1>
      <p>Gestion complète des offres d'emploi publiées par les entreprises</p>
    </div>
    <button class="btn btn-primary" data-modal="add-offer-modal" id="add-offer-btn">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Ajouter une offre
    </button>
  </div>
</div>

<!-- ═══ Stats Cards ═══ -->
<div class="offers-admin-stats stagger">
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Offres Actives</div>
      <div class="stat-card__value">0</div>
      <div class="stat-card__trend"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> 0 cette semaine</div>
    </div>
    <div class="stat-card__icon purple"><i data-lucide="briefcase" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Candidatures Totales</div>
      <div class="stat-card__value">0</div>
      <div class="stat-card__trend"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0%</div>
    </div>
    <div class="stat-card__icon teal"><i data-lucide="file-text" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Catégorie #1</div>
      <div class="stat-card__value" style="font-size:var(--fs-md);">—</div>
      <div class="stat-card__trend"><span class="text-tertiary">0% des offres</span></div>
    </div>
    <div class="stat-card__icon blue"><i data-lucide="crown" style="width:22px;height:22px;"></i></div>
  </div>
  <div class="stat-card animate-on-scroll">
    <div>
      <div class="stat-card__label">Offres / Mois</div>
      <div class="stat-card__value">0</div>
      <div class="stat-card__trend"><i data-lucide="trending-up" style="width:14px;height:14px;"></i> +0 vs dernier mois</div>
    </div>
    <div class="stat-card__icon orange"><i data-lucide="calendar" style="width:22px;height:22px;"></i></div>
  </div>
</div>

<!-- ═══ Search & Sort Toolbar ═══ -->
<div class="filter-bar mb-6">
  <div class="search-bar" style="flex:1;max-width:350px;">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    <input type="text" class="input" placeholder="Rechercher une offre..." id="admin-offers-search">
  </div>
  <select class="select" style="max-width:160px;" id="admin-offers-category">
    <option value="">Toutes catégories</option>
    <option>IT & Dev</option>
    <option>Data & IA</option>
    <option>Design</option>
    <option>Marketing</option>
    <option>Finance</option>
    <option>RH</option>
  </select>
  <select class="select" style="max-width:140px;" id="admin-offers-status">
    <option value="">Tous statuts</option>
    <option>Actif</option>
    <option>En pause</option>
    <option>Clôturé</option>
  </select>
  <select class="select" style="max-width:140px;" id="admin-offers-sort">
    <option>Plus récent</option>
    <option>Plus ancien</option>
    <option>Plus de candidats</option>
  </select>
</div>

<!-- ═══ Offers Data Table ═══ -->
<div class="card-flat" style="overflow:hidden;">
  <table class="data-table">
    <thead>
      <tr>
        <th>Offre</th>
        <th>Entreprise</th>
        <th>Catégorie</th>
        <th>Type</th>
        <th>Candidats</th>
        <th>Statut</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="8">
          <div class="empty-state-mini" style="padding:var(--space-12);text-align:center;background:var(--bg-secondary);border-radius:var(--radius-lg);opacity:0.6;">
            <i data-lucide="briefcase" style="width:40px;height:40px;margin:0 auto var(--space-3);display:block;color:var(--text-tertiary);"></i>
            <p style="color:var(--text-secondary);">Aucune offre d'emploi disponible</p>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<!-- ═══ Add Offer Modal ═══ -->
<div class="modal-overlay" id="add-offer-modal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3>Ajouter une offre</h3>
      <button class="modal-close btn-icon"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
    </div>
    <div class="modal-body">
      <form class="auth-form" data-validate id="add-offer-form">
        <div class="form-group">
          <label class="form-label">Titre du poste</label>
          <input type="text" class="input" name="title" placeholder="Ex: Senior Full Stack Developer" required>
          <span class="form-error"></span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="form-group">
            <label class="form-label">Entreprise</label>
            <select class="select" name="company" required>
              <option value="">Sélectionnez...</option>
              <option>TechSphere Inc.</option>
              <option>DataFlow Analytics</option>
              <option>InnoLab Design</option>
              <option>CloudPeak Systems</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Catégorie</label>
            <select class="select" name="category" required>
              <option value="">Sélectionnez...</option>
              <option>IT & Dev</option>
              <option>Data & IA</option>
              <option>Design</option>
              <option>Marketing</option>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="form-group">
            <label class="form-label">Type de contrat</label>
            <select class="select" name="type">
              <option>CDI</option><option>CDD</option><option>Stage</option><option>Freelance</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Salaire (TND)</label>
            <input type="text" class="input" name="salary" placeholder="3,000 - 5,000">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="textarea" name="description" rows="4" placeholder="Décrivez le poste..." required></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary modal-close">Annuler</button>
      <button class="btn btn-primary" type="submit" form="add-offer-form">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Publier l'offre
      </button>
    </div>
  </div>
</div>
