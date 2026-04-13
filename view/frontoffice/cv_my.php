<?php 
$pageTitle = "Mes CVs"; $pageCSS = "cv_premium.css"; 

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$id_candidat = $_SESSION['user_id'] ?? null;

$cvc = new CVC();
$tc  = new TemplateC();

// Si pas de session utilisateur, on affiche tous les CVs (mode dév)
$cvList   = $cvc->listCVByCandidat($id_candidat);
$totalCVs = count($cvList);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="file-text" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mes CVs
      </h1>
      <p class="page-header__subtitle">Gérez, modifiez et téléchargez vos CVs générés.</p>
    </div>
    <a href="cv_templates.php" class="btn btn-primary">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Créer un nouveau CV
    </a>
  </div>
</div>

<!-- ═══ Stats Summary ═══ -->
<div class="grid grid-3 gap-6 mb-8">
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Total CVs</div>
      <div class="stat-card__value"><?php echo $totalCVs; ?></div>
    </div>
    <div class="stat-card__icon purple">
      <i data-lucide="file-text" style="width:22px;height:22px;"></i>
    </div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Statut</div>
      <div class="stat-card__value" style="font-size:var(--fs-sm);"><?php echo $totalCVs > 0 ? 'Actif' : 'Inactif'; ?></div>
    </div>
    <div class="stat-card__icon teal">
      <i data-lucide="check-circle" style="width:22px;height:22px;"></i>
    </div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-card__label">Dernière mise à jour</div>
      <div class="stat-card__value" style="font-size:var(--fs-sm);">
        <?php echo $totalCVs > 0 ? date('d/m/Y', strtotime($cvList[0]['dateMiseAJour'])) : 'N/A'; ?>
      </div>
    </div>
    <div class="stat-card__icon blue">
      <i data-lucide="clock" style="width:22px;height:22px;"></i>
    </div>
  </div>
</div>

<!-- ═══ CV Cards Grid (cv-builder-v2 dashboard style) ═══ -->
<?php if($totalCVs > 0): ?>
<div class="dashboard-grid">
  <?php foreach ($cvList as $cv): 
      $tpl = $tc->getTemplateById($cv['id_template']);
      $tplNom = strtolower($tpl['nom'] ?? 'standard');
      $tplClass = strtolower(preg_replace('/[^a-z0-9]/i', '-', $tpl['nom'] ?? 'standard'));
      $theme = $cv['couleurTheme'] ?: '#6B34A3';
      $contact = implode(' | ', array_filter([$cv['infoContact'] ?? '']));
      $isManager = str_contains($tplNom, 'manager') || str_contains($tplNom, 'sidebar');
      
      // Parse contact info
      $parts = array_map('trim', explode('|', $cv['infoContact'] ?? ''));
      $email = $parts[0] ?? '';
      $phone = $parts[1] ?? '';
      $location = $parts[2] ?? '';
      $contactStr = implode(' | ', array_filter([$email, $phone, $location]));
  ?>
  
  <div class="dashboard-card" id="resume-<?php echo $cv['id_cv']; ?>">
    
    <!-- Scaled CV Preview (exact same technique as cv-builder-v2) -->
    <div class="dashboard-preview-wrapper" style="--cv-accent: <?php echo htmlspecialchars($theme); ?>;">
      
      <!-- Eye overlay on hover -->
      <div class="overlay-eye" onclick="window.location.href='cv_builder.php?cv_id=<?php echo $cv['id_cv']; ?>'">
        <i class="fa-solid fa-eye"></i>
      </div>

      <!-- Scaled-down full CV render -->
      <div class="dashboard-scaled-cv cv-render template-<?php echo htmlspecialchars($tplClass); ?>" style="--cv-accent: <?php echo htmlspecialchars($theme); ?>;">
        
        <?php if($isManager): ?>
          <!-- Manager/Sidebar Layout -->
          <div class="template-manager">
            <div class="cv-sidebar">
              <?php if(!empty($cv['urlPhoto'])): ?><img src="<?php echo $cv['urlPhoto']; ?>" class="cv-photo active" alt="Photo"><?php endif; ?>
              <div class="cv-header" style="text-align:center; border:none; display:block;">
                <div class="cv-name"><?php echo htmlspecialchars($cv['nomComplet']); ?></div>
                <div class="cv-title"><?php echo htmlspecialchars($cv['titrePoste']); ?></div>
                <div class="cv-contact"><?php echo htmlspecialchars($contactStr); ?></div>
              </div>
              <div class="cv-section"><div class="cv-section-title">Compétences</div><div class="cv-content"><?php echo str_replace(',', ' • ', htmlspecialchars($cv['competences'])); ?></div></div>
              <div class="cv-section"><div class="cv-section-title">Langues</div><div class="cv-content"><?php echo htmlspecialchars($cv['langues']); ?></div></div>
              <div class="cv-section"><div class="cv-section-title">Formation</div><div class="cv-content"><?php echo htmlspecialchars($cv['formation']); ?></div></div>
            </div>
            <div class="cv-main">
              <div class="cv-section"><div class="cv-section-title">Résumé</div><div class="cv-content"><?php echo htmlspecialchars($cv['resume']); ?></div></div>
              <div class="cv-section"><div class="cv-section-title">Expérience</div><div class="cv-content"><?php echo htmlspecialchars($cv['experience']); ?></div></div>
            </div>
          </div>

        <?php else: ?>
          <!-- Standard Layout -->
          <div class="cv-header">
            <?php if(!empty($cv['urlPhoto'])): ?><img src="<?php echo $cv['urlPhoto']; ?>" class="cv-photo active" alt="Photo"><?php endif; ?>
            <div style="flex:1;">
              <div class="cv-name"><?php echo htmlspecialchars($cv['nomComplet']); ?></div>
              <div class="cv-title"><?php echo htmlspecialchars($cv['titrePoste']); ?></div>
              <div class="cv-contact"><?php echo htmlspecialchars($contactStr); ?></div>
            </div>
          </div>
          <div class="cv-section"><div class="cv-section-title">Résumé</div><div class="cv-content"><?php echo htmlspecialchars($cv['resume']); ?></div></div>
          <div class="cv-section"><div class="cv-section-title">Expérience</div><div class="cv-content"><?php echo htmlspecialchars($cv['experience']); ?></div></div>
          <div style="display:flex; gap:20px;">
            <div class="cv-section" style="flex:1;"><div class="cv-section-title">Compétences</div><div class="cv-content"><?php echo str_replace(',', ' • ', htmlspecialchars($cv['competences'])); ?></div></div>
            <div class="cv-section" style="flex:1;"><div class="cv-section-title">Langues</div><div class="cv-content"><?php echo htmlspecialchars($cv['langues']); ?></div></div>
          </div>
          <div class="cv-section"><div class="cv-section-title">Formation</div><div class="cv-content"><?php echo htmlspecialchars($cv['formation']); ?></div></div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Card Info -->
    <div class="dashboard-info">
      <h3><?php echo htmlspecialchars($cv['nomComplet'] ?: 'CV sans titre'); ?></h3>
      <p>
        Poste: <?php echo htmlspecialchars($cv['titrePoste']); ?><br>
        Modifié le <?php echo date('d M Y', strtotime($cv['dateMiseAJour'])); ?>
      </p>
      <div class="dashboard-actions">
        <div>
          <a href="cv_builder.php?cv_id=<?php echo $cv['id_cv']; ?>" class="btn-primary-cv" style="text-decoration:none; font-size:0.8rem; padding:0.5rem 1rem;">
            <i data-lucide="edit-3" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> Éditer
          </a>
        </div>
        <div style="display:flex; gap:0.5rem;">
          <button class="btn-secondary-cv" style="font-size:0.8rem; padding:0.5rem 1rem;" onclick="generatePDF(<?php echo $cv['id_cv']; ?>)">
            <i data-lucide="download" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> PDF
          </button>
          <button class="btn-secondary-cv" style="font-size:0.8rem; padding:0.5rem 0.75rem; color:var(--accent-tertiary);" onclick="deleteCV(<?php echo $cv['id_cv']; ?>)">
            <i data-lucide="trash-2" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align:center; padding: 60px 20px;">
  <div style="margin-bottom:20px; opacity:0.3;">
    <i data-lucide="file-plus" style="width:64px;height:64px;"></i>
  </div>
  <h3>Aucun CV créé</h3>
  <p style="color:var(--text-secondary);">Commencez par choisir un template et créez votre premier CV professionnel.</p>
  <a href="cv_templates.php" class="btn btn-primary" style="margin-top:20px;">
    <i data-lucide="plus" style="width:18px;height:18px;"></i>
    Créer mon premier CV
  </a>
</div>
<?php endif; ?>

<!-- Hidden Print Frame -->
<iframe id="print-frame" style="display:none;"></iframe>

<script>
// Set active nav
document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('nav-cv-my');
    if(link) {
        document.querySelectorAll('.nav-anchor').forEach(a => a.classList.remove('active'));
        link.classList.add('active');
    }
});

function generatePDF(cvId) {
    const frame = document.getElementById('print-frame');
    frame.src = 'cv_print.php?id=' + cvId;
    frame.onload = function() {
        frame.contentWindow.print();
    };
}

function deleteCV(cvId) {
    if(confirm('Voulez-vous vraiment supprimer ce CV ?')) {
        window.location.href = 'cv_delete.php?id=' + cvId;
    }
}
</script>
