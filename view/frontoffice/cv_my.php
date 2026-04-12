<?php 
$pageTitle = "Mes CVs"; $pageCSS = "cv.css"; 

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/CVC.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

session_start();
$id_candidat = $_SESSION['user_id'] ?? 1;

$cvc = new CVC();
$tc = new TemplateC();
$cvList = $cvc->listCVByCandidat($id_candidat);
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
      <p class="page-header__subtitle">Gérez et téléchargez vos CVs générés</p>
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
      <div class="stat-card__value" style="font-size:var(--fs-sm); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
        <?php echo $totalCVs > 0 ? date('d/m/Y', strtotime($cvList[0]['dateMiseAJour'])) : 'N/A'; ?>
      </div>
    </div>
    <div class="stat-card__icon blue">
      <i data-lucide="clock" style="width:22px;height:22px;"></i>
    </div>
  </div>
</div>

<!-- ═══ CV Cards Grid ═══ -->
<?php if($totalCVs > 0): ?>
<div class="my-cvs-grid stagger">
  <?php foreach ($cvList as $cv): 
      $tpl = $tc->getTemplateById($cv['id_template']);
  ?>
  <div class="my-cv-card animate-on-scroll">
    <div class="my-cv-card__preview">
      <?php if($tpl && !empty($tpl['urlMiniature'])): ?>
        <img src="<?php echo $tpl['urlMiniature']; ?>" style="width:100%; height:100%; object-fit:cover; opacity: 0.8;">
      <?php else: ?>
        <div class="template-card__preview-inner" style="width:60%;height:75%;">
            <div class="template-card__preview-line accent" style="background:<?php echo $cv['couleurTheme']; ?>;"></div>
            <div class="template-card__preview-line medium"></div>
            <div class="template-card__preview-block"></div>
        </div>
      <?php endif; ?>
    </div>
    <div class="my-cv-card__body">
      <div class="my-cv-card__title"><?php echo htmlspecialchars($cv['nomComplet']); ?></div>
      <div class="my-cv-card__date">
        <i data-lucide="clock" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i>
        Modifié le <?php echo date('d M Y', strtotime($cv['dateMiseAJour'])); ?>
      </div>
      <div class="my-cv-card__actions">
        <a href="cv_form.php?cv_id=<?php echo $cv['id_cv']; ?>" class="btn btn-sm btn-secondary" style="text-decoration:none;">
          <i data-lucide="pencil" style="width:14px;height:14px;"></i> Éditer
        </a>
        <button class="btn btn-sm btn-primary" onclick="generatePDF(<?php echo $cv['id_cv']; ?>)">
          <i data-lucide="download" style="width:14px;height:14px;"></i> PDF
        </button>
        <button class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" onclick="deleteCV(<?php echo $cv['id_cv']; ?>)">
          <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state" style="text-align:center; padding: 60px 20px;">
  <div class="empty-state__icon" style="margin-bottom:20px; opacity:0.3;">
    <i data-lucide="file-plus" style="width:64px;height:64px;"></i>
  </div>
  <h3 class="empty-state__title">Aucun CV créé</h3>
  <p class="empty-state__text">Commencez par choisir un template et créez votre premier CV professionnel.</p>
  <a href="cv_templates.php" class="btn btn-primary" style="margin-top:20px;">
    <i data-lucide="plus" style="width:18px;height:18px;"></i>
    Créer mon premier CV
  </a>
</div>
<?php endif; ?>

<!-- Hidden Print Frame -->
<iframe id="print-frame" style="display:none;"></iframe>

<script>
function generatePDF(cvId) {
    // We open a specialized print view in a hidden iframe
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
