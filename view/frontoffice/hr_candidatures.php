<?php $pageTitle = "Candidatures"; $pageCSS = "feeds.css"; $userRole = "Entreprise"; ?>

<?php
require_once '../../controller/candidatureC.php';
$candidatureC = new candidatureC();
$listeCandidatures = $candidatureC->afficherCandidatures();
$count = $listeCandidatures->rowCount();

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php (Enterprise view) -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="users" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Candidatures Reçues
  </h1>
  <p class="page-header__subtitle">Examinez et triez les candidatures pour vos postes</p>
</div>

<div class="hr-layout">
  <!-- ═══ CANDIDATE CARDS ═══ -->
  <div>
    <div class="results-info mb-4">
      <strong><?php echo $count; ?></strong> candidatures totales
    </div>

    <div class="candidate-cards-grid stagger">
      <?php foreach ($listeCandidatures as $cand): 
          $initials = strtoupper(substr($cand['prenom'], 0, 1) . substr($cand['nom'], 0, 1));
          $nomComplet = htmlspecialchars($cand['prenom'] . ' ' . $cand['nom']);
          $titreOffre = htmlspecialchars($cand['titre_offre'] ?? 'Candidature Spontanée');
          $dateCand = date('d/m/Y', strtotime($cand['date_candidature']));
          $idCand = $cand['id_candidature'];
      ?>
      <div class="candidate-card animate-on-scroll" id="candidate-<?php echo $idCand; ?>">
        <div class="candidate-card__header">
          <div class="avatar avatar-lg avatar-initials"><?php echo $initials; ?></div>
          <div class="candidate-card__info">
            <div class="candidate-card__name"><?php echo $nomComplet; ?></div>
            <div class="candidate-card__role">Poste: <?php echo $titreOffre; ?></div>
            <span class="text-xs text-tertiary">Déposé le: <?php echo $dateCand; ?></span>
          </div>
          <div class="candidate-card__match">
            <span class="badge badge-<?php echo ($cand['statut'] === 'accepte') ? 'success' : (($cand['statut'] === 'refuse') ? 'danger' : 'warning'); ?>">
              <?php echo htmlspecialchars($cand['statut']); ?>
            </span>
          </div>
        </div>

        <div class="candidate-card__skills" style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.85rem; padding: 0.5rem; background: var(--surface-1); border-radius: 6px;">
          <strong>Motivation :</strong> <?php echo htmlspecialchars(substr($cand['reponses_ques'], 0, 80)); ?><?php echo strlen($cand['reponses_ques']) > 80 ? '...' : ''; ?>
        </div>

        <div class="candidate-card__actions">
          <a href="#" class="btn btn-sm btn-primary" onclick="alert('Feature to download CV Base64/Blob coming soon.')"><i data-lucide="file-text" style="width:14px;height:14px;"></i> Voir CV</a>
          <button class="btn btn-sm btn-success"><i data-lucide="check" style="width:14px;height:14px;"></i> Shortlister</button>
          <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"><i data-lucide="x" style="width:14px;height:14px;"></i> Refuser</button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if ($count == 0): ?>
         <div class="empty-state text-center" style="padding: 3rem; background: var(--surface-1); border-radius: 12px; grid-column: 1 / -1;">
             <p>Aucune candidature reçue pour le moment.</p>
         </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="hr-sidebar">
    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Filtrer pour le poste</h4>
      <select class="select w-full mb-3" id="filter-post">
        <option>Senior Full Stack Developer</option>
        <option>Data Engineer</option>
        <option>UI/UX Designer</option>
        <option>Product Manager</option>
      </select>
    </div>

    <div class="hr-sidebar__section">
      <div class="search-bar" style="max-width:100%;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher un candidat..." id="candidate-search">
      </div>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Trier par</h4>
      <label class="cv-sidebar__option"><input type="radio" name="sort-cand" checked> Match % (desc)</label>
      <label class="cv-sidebar__option"><input type="radio" name="sort-cand"> Date (récent)</label>
      <label class="cv-sidebar__option"><input type="radio" name="sort-cand"> Nom (A-Z)</label>
    </div>

    <div class="hr-sidebar__section">
      <h4 class="text-sm fw-semibold mb-3">Statut</h4>
      <label class="cv-sidebar__option"><input type="checkbox" checked> Tous</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Shortlisté</label>
      <label class="cv-sidebar__option"><input type="checkbox"> En attente</label>
      <label class="cv-sidebar__option"><input type="checkbox"> Refusé</label>
    </div>
  </aside>
</div>
