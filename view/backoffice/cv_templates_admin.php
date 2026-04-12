<?php 
$pageTitle = "Templates CV"; 
$pageCSS = "cv.css"; 

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Template.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

$tc = new TemplateC();

// --- CRUD Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $tc->deleteTemplate($_GET['id']);
    header("Location: cv_templates_admin.php");
    exit;
}

$dbTemplates = $tc->listeTemplates();
$totalTemplates = count($dbTemplates);

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
      <h1>Templates CV</h1>
      <p>Gérez les templates disponibles pour les utilisateurs</p>
    </div>
    <a href="template_form.php?action=add" class="btn btn-primary" id="add-template-btn">
      <i data-lucide="plus" style="width:18px;height:18px;"></i>
      Ajouter un Template
    </a>
  </div>
</div>

<!-- ═══ Layout Restructuration: Stats & Chart Top, Table Full Below ═══ -->
<div class="grid gap-6 mb-8" style="grid-template-columns: repeat(12, 1fr);">
  <!-- Stats Section (Left 8 columns) -->
  <div style="grid-column: span 8;">
      <div class="grid grid-2 gap-6 stagger h-full">
          <div class="stat-card animate-on-scroll">
            <div>
              <div class="stat-card__label">Total Templates</div>
              <div class="stat-card__value"><?php echo $totalTemplates; ?></div>
              <div class="stat-card__trend up">
                <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +3 ce mois
              </div>
            </div>
            <div class="stat-card__icon purple"><i data-lucide="layout-template" style="width:22px;height:22px;"></i></div>
          </div>
          <div class="stat-card animate-on-scroll">
            <div>
              <div class="stat-card__label">Plus utilisé</div>
              <div class="stat-card__value" style="font-size:var(--fs-md);">Tech Stack</div>
              <div class="stat-card__trend up">
                <i data-lucide="trending-up" style="width:14px;height:14px;"></i> 1,240 utilisations
              </div>
            </div>
            <div class="stat-card__icon teal"><i data-lucide="star" style="width:22px;height:22px;"></i></div>
          </div>
          <div class="stat-card animate-on-scroll">
            <div>
              <div class="stat-card__label">CVs générés</div>
              <div class="stat-card__value">8,432</div>
              <div class="stat-card__trend up">
                <i data-lucide="trending-up" style="width:14px;height:14px;"></i> +18% ce mois
              </div>
            </div>
            <div class="stat-card__icon blue"><i data-lucide="file-check" style="width:22px;height:22px;"></i></div>
          </div>
          <div class="stat-card animate-on-scroll">
            <div>
              <div class="stat-card__label">Ajouts récents</div>
              <div class="stat-card__value">3</div>
              <div class="stat-card__trend">
                <span class="text-tertiary">Cette semaine</span>
              </div>
            </div>
            <div class="stat-card__icon orange"><i data-lucide="clock" style="width:22px;height:22px;"></i></div>
          </div>
      </div>
  </div>

  <!-- Chart Section (Right 4 columns) -->
  <div class="card" style="grid-column: span 4; height: 100%;">
    <h4 class="text-sm fw-semibold mb-6">Top Templates utilisés</h4>
    <div id="template-usage-chart" style="min-height: 200px;"></div>
  </div>
</div>

<!-- ═══ Table Section Full Width ═══ -->
<div class="card-flat" style="overflow:hidden;">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid var(--border-color);">
      <h3 class="text-md fw-semibold">Gérer les Templates</h3>
      <div class="search-bar" style="max-width:300px;">
        <i data-lucide="search" style="width:16px;height:16px;"></i>
        <input type="text" class="input" placeholder="Rechercher un template..." id="admin-template-search">
      </div>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Miniature</th>
          <th>Nom du Template</th>
          <th>Description (Tags)</th>
          <th>Type</th>
          <th>Date d'ajout</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dbTemplates as $t): ?>
        <tr>
          <td>
             <div class="miniature-wrapper" onclick="openLightbox('<?php echo htmlspecialchars($t['urlMiniature'], ENT_QUOTES); ?>')" style="position:relative; cursor:pointer; border-radius: 6px; overflow: hidden; display: inline-block;">
                <img src="<?php echo htmlspecialchars($t['urlMiniature']); ?>" alt="miniature" style="width: 50px; height: 65px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); display: block; transition: transform 0.3s;" onerror="this.src='../../assets/images/placeholder.png';">
                <div class="miniature-overlay" style="position:absolute; inset:0; background:rgba(15,23,42,0.6); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.3s; pointer-events:none;">
                    <i data-lucide="zoom-in" style="width:20px;height:20px; color:white;"></i>
                </div>
             </div>
          </td>
          <td>
            <span class="fw-medium text-high-contrast"><?php echo htmlspecialchars($t['nom']); ?></span>
          </td>
          <td>
            <div class="flex flex-wrap gap-1" style="max-width: 250px;">
                <?php 
                $tags = array_filter(array_map('trim', explode(',', $t['description'])));
                if (!empty($tags)) {
                    foreach($tags as $tag) {
                        echo '<span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:11px;">'.htmlspecialchars($tag).'</span>';
                    }
                } else {
                    echo '<span class="text-muted text-xs">Aucun tag</span>';
                }
                ?>
            </div>
          </td>
          <td>
            <?php if($t['estPremium']): ?>
                <span class="badge" style="background:#f59e0b; color:white;">Premium</span>
            <?php else: ?>
                <span class="badge" style="background:#10b981; color:white;">Gratuit</span>
            <?php endif; ?>
          </td>
          <td class="text-secondary text-sm"><?php echo date('d M Y', strtotime($t['dateCreation'])); ?></td>
          <td>
            <div class="flex gap-1">
              <a href="template_form.php?action=edit&id=<?php echo $t['id_template']; ?>" class="btn btn-sm btn-ghost" title="Éditer">
                 <i data-lucide="pencil" style="width:14px;height:14px;"></i>
              </a>
              
              <a href="cv_templates_admin.php?action=delete&id=<?php echo $t['id_template']; ?>" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);" title="Supprimer" onclick="return confirm('Voulez-vous vraiment supprimer ce template ?');">
                 <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if($totalTemplates === 0): ?>
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">Aucun template trouvé.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
</div>

</div>

<!-- Lightbox Modal -->
<div id="lightbox-modal" onclick="closeLightbox(event)">
   <div id="lightbox-close" onclick="closeLightboxEvent()">&times;</div>
   <img id="lightbox-img" src="" alt="Aperçu Grand Format">
</div>

<style>
/* Lightbox CSS */
#lightbox-modal { position: fixed; top:0; left:0; width:100vw; height:100vh; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); z-index: 999999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease-in-out; }
#lightbox-modal.active { opacity: 1; pointer-events: auto; }
#lightbox-img { max-width: 90vw; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); object-fit: contain; transform: scale(0.95); transition: transform 0.3s ease-in-out; }
#lightbox-modal.active #lightbox-img { transform: scale(1); }
#lightbox-close { position: absolute; top: 25px; right: 35px; color: white; font-size: 36px; cursor: pointer; background: rgba(255,255,255,0.1); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; z-index: 1000000; }
#lightbox-close:hover { background: rgba(239, 68, 68, 0.8); transform: rotate(90deg); }

.miniature-wrapper:hover img { transform: scale(1.1); box-shadow: 0 0 15px rgba(56,189,248,0.5); }
.miniature-wrapper:hover .miniature-overlay { opacity: 1 !important; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// --- Global Lightbox logic ---
function openLightbox(strBase64) {
    if(!strBase64) return;
    document.getElementById('lightbox-img').src = strBase64;
    document.getElementById('lightbox-modal').classList.add('active');
}
function openPreviewLightbox() {
    const src = document.getElementById('tpl-img-preview').src;
    if(src && src.startsWith('data:image')) {
        openLightbox(src);
    }
}
function closeLightbox(e) {
    if(e.target.id === 'lightbox-modal') {
        document.getElementById('lightbox-modal').classList.remove('active');
    }
}
function closeLightboxEvent() {
    document.getElementById('lightbox-modal').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    // Move lightbox to body to avoid CSS transform constraints breaking position:fixed
    const lightboxModal = document.getElementById('lightbox-modal');
    if (lightboxModal) {
        document.body.appendChild(lightboxModal);
    }

  // Chart initialization
  if (typeof AptusCharts !== 'undefined') {
      AptusCharts.bar('template-usage-chart', [
        { label: 'Tech Stack', value: 1240 },
        { label: 'Exec Pro', value: 980 },
        { label: 'Créatif', value: 856 },
        { label: 'Modern', value: 723 },
        { label: 'Data', value: 654 },
      ], { barColor: 'var(--chart-1)', height: 220 });
  }
});
</script>
