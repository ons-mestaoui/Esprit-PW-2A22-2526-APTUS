<?php
$pageTitle = "Gérer un Template CV";
$pageCSS = "cv.css";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Template.php';
require_once __DIR__ . '/../../controller/TemplateC.php';

$tc = new TemplateC();

$action = $_GET['action'] ?? 'add';
$template = null;
$id = $_GET['id'] ?? null;

if ($action === 'edit' && $id) {
    $template = $tc->getTemplateById($id);
    if (!$template) {
        die("Template introuvable");
    }
}

// POST processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    
    if (isset($_POST['description']) && is_array($_POST['description'])) {
        $description = implode(', ', $_POST['description']);
    } else {
        $description = '';
    }
    
    $urlMiniature = $_POST['urlMiniature'] ?? '';
    $structureHtml = $_POST['structureHtml'] ?? '';
    $estPremium = isset($_POST['estPremium']) ? 1 : 0;
    
    $tplObj = new Template(null, $nom, $description, $urlMiniature, $structureHtml, $estPremium);
    
    if ($action === 'edit' && $id) {
        $tc->updateTemplate($id, $tplObj);
    } else {
        $tc->addTemplate($tplObj);
    }
    
    header("Location: cv_templates_admin.php");
    exit;
}

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_back.php';
    exit();
}

$nomVal = $template ? htmlspecialchars($template['nom'], ENT_QUOTES) : '';
$descVal = $template ? $template['description'] : '';
$descTags = array_map('trim', explode(',', $descVal));
$htmlVal = $template ? htmlspecialchars($template['structureHtml'], ENT_QUOTES) : '';
$urlVal = $template ? $template['urlMiniature'] : '';
$isPremium = $template ? $template['estPremium'] : 0;
?>

<div class="back-page-header">
  <div class="back-page-header__row">
    <div>
      <a href="cv_templates_admin.php" class="btn btn-ghost btn-sm mb-2" style="padding-left:0; color:var(--text-tertiary);">
         <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Retour aux templates
      </a>
      <h1><?php echo ($action === 'edit') ? 'Éditer le Template' : 'Créer un Template'; ?></h1>
      <p>Configurez le code et l'apparence de votre modèle CV.</p>
    </div>
  </div>
</div>

<form method="POST" action="template_form.php?action=<?php echo $action; ?><?php echo $id ? '&id='.$id : ''; ?>" id="full-template-form" class="mt-4">
    <div class="grid gap-6 stagger" style="grid-template-columns: 1fr 400px; align-items: start;">
        
        <!-- Left Side: Form Details -->
        <div class="card-flat" style="padding: 30px; display:flex; flex-direction:column; gap:24px;">
            <div class="form-group">
                <label class="form-label" for="tpl-name" style="font-size: 15px;">Nom du template</label>
                <input type="text" class="input" id="tpl-name" name="nom" required placeholder="Ex: Élégance Corporative" value="<?php echo $nomVal; ?>" style="padding: 12px; font-size:16px;">
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size: 15px;">Catégories (Tags)</label>
                <div class="flex flex-wrap gap-2 mt-2" id="tags-wrapper">
                    <?php 
                    $defaultTags = ['Classique', 'Moderne', 'Minimaliste', 'Créatif', 'Professionnel', 'Technologie', 'Marketing', 'Epuré', 'ATS-Friendly', 'Coloré'];
                    $allTags = array_unique(array_merge($defaultTags, $descTags));
                    $allTags = array_filter(array_map('trim', $allTags));

                    foreach($allTags as $idx => $tag): 
                        if(empty($tag)) continue;
                        $isChecked = in_array($tag, $descTags) ? 'checked' : '';
                    ?>
                    <label class="page-tag-wrapper">
                        <input type="checkbox" name="description[]" value="<?php echo htmlspecialchars($tag); ?>" <?php echo $isChecked; ?>>
                        <span><?php echo htmlspecialchars($tag); ?></span>
                    </label>
                    <?php endforeach; ?>
                    
                    <!-- Datalist for suggestions -->
                    <datalist id="tags-suggestions">
                        <option value="IT">
                        <option value="Finance">
                        <option value="Santé">
                        <option value="Étudiant">
                        <option value="Freelance">
                        <option value="B2B">
                    </datalist>

                    <!-- Add custom tag input -->
                    <div style="display:flex; align-items:center; background: rgba(0,0,0,0.15); border: 1px dashed rgba(255,255,255,0.1); border-radius: 99px; padding: 2px 14px; transition:all 0.3s;" id="add-tag-box">
                        <i data-lucide="plus" style="width:14px;height:14px; color:var(--text-tertiary); margin-right:4px;"></i>
                        <input type="text" id="custom-tag-input" list="tags-suggestions" placeholder="Nouveau tag (Entrée)" style="background:transparent; border:none; outline:none; color:var(--text-secondary); font-size:13px; width:130px; box-shadow:none;">
                    </div>
                </div>
            </div>
            
            <div class="form-group premium-wrapper" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(217, 70, 239, 0.05)); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; padding: 15px 20px; display:flex; align-items:center; justify-content:space-between; position:relative; overflow:hidden;">
                <div class="premium-glow" style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle, rgba(245,158,11,0.05) 0%, transparent 60%); pointer-events:none;"></div>
                
                <div style="display:flex; align-items:center; gap:16px; z-index:1;">
                    <div style="background: linear-gradient(135deg, #f59e0b, #d946ef); width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 15px rgba(245,158,11,0.3);">
                        <i data-lucide="crown" style="color:white; width:22px; height:22px;"></i>
                    </div>
                    <div>
                        <h4 class="fw-semibold text-sm m-0" style="color:#f59e0b; letter-spacing:0.5px;">Statut Premium VIP</h4>
                        <p class="text-xs m-0 mt-1" style="color:rgba(255,255,255,0.5);">Réservé aux abonnements avancés</p>
                    </div>
                </div>
                
                <div class="premium-switch" style="z-index:1; position:relative; width:50px; height:26px;">
                    <input type="checkbox" id="tpl-premium" name="estPremium" value="1" <?php echo ($isPremium) ? 'checked' : ''; ?> style="opacity:0; width:0; height:0;">
                    <label for="tpl-premium" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:34px; transition:.4s; box-shadow:inset 0 1px 3px rgba(0,0,0,0.3);"></label>
                </div>
            </div>

            <div class="form-group" style="flex-grow: 1; display:flex; flex-direction:column;">
                <div class="flex justify-between items-end mb-2">
                    <label class="form-label" for="tpl-html" style="font-size: 15px; margin:0;">Code Structure (HTML/CSS)</label>
                    <span class="text-xs" style="color:var(--text-tertiary);">Rendu en temps réel possible via le bouton à droite</span>
                </div>
                <textarea class="textarea" id="tpl-html" name="structureHtml" required placeholder="Écrivez le code source HTML/CSS du CV ici..." style="font-family: 'Fira Code', monospace; line-height: 1.5; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); color: #cbd5e1; flex-grow: 1; min-height: 400px; padding:15px; font-size:13px; resize:vertical;"><?php echo $htmlVal; ?></textarea>
            </div>
        </div>

        <!-- Right Side: Sticky Preview & Actions -->
        <div style="position: sticky; top: 24px; display:flex; flex-direction:column; gap:24px;">
            <div class="card" style="padding: 24px; text-align:center;">
                <h3 class="text-md fw-semibold mb-2">Générateur Automatique</h3>
                <p class="text-xs text-secondary mb-4">Générez visuellement le rendu HTML. La miniature s'ouvrira directement dans un Popup.</p>
                
                <button type="button" class="btn btn-secondary w-full" id="btn-page-generate" style="justify-content:center; padding: 12px; border: 1px solid rgba(56, 189, 248, 0.3); background:rgba(56, 189, 248, 0.05); color:#38bdf8; font-weight:600;">
                   <i data-lucide="scan-line" style="width:18px;height:18px;"></i> Scanner & Afficher
                </button>
                
                <!-- Display existing image popup button if it has one -->
                <?php if($urlVal): ?>
                <button type="button" class="btn btn-ghost w-full mt-3" onclick="openLightbox('<?php echo htmlspecialchars($urlVal, ENT_QUOTES); ?>')" style="justify-content:center; color:var(--text-secondary); border: 1px dashed rgba(255,255,255,0.1);">
                   <i data-lucide="image" style="width:16px;height:16px;"></i> Voir l'image actuelle
                </button>
                <?php endif; ?>

                <input type="hidden" name="urlMiniature" id="page-url-hidden" value="<?php echo htmlspecialchars($urlVal); ?>">
            </div>
            
            <button class="btn btn-primary w-full" type="submit" form="full-template-form" style="padding: 16px; font-size:16px; background: linear-gradient(135deg, #38bdf8, #818cf8); border:none; box-shadow: 0 4px 15px rgba(56,189,248,0.3); border-radius:12px; justify-content:center;">
              <i data-lucide="save" style="width:20px;height:20px;"></i>
              Sauvegarder le Template
            </button>
        </div>
    </div>
</form>

<!-- Lightbox Modal -->
<div id="lightbox-modal" onclick="closeLightbox(event)">
   <div id="lightbox-close" onclick="closeLightboxEvent()">&times;</div>
   <img id="lightbox-img" src="" alt="Aperçu Grand Format">
</div>

<style>
.page-tag-wrapper { display: inline-flex; align-items: center; cursor: pointer; animation: fadeIn 0.3s ease; }
.page-tag-wrapper input[type="checkbox"] { display: none; }
.page-tag-wrapper span { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); color: #94a3b8; padding: 8px 18px; border-radius: 99px; font-size: 14px; font-weight:500; transition: all 0.3s ease; user-select:none; backdrop-filter: blur(4px); }
.page-tag-wrapper input[type="checkbox"]:checked + span { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: #38bdf8; box-shadow: 0 0 15px rgba(56, 189, 248, 0.2); text-shadow: 0 0 10px rgba(56, 189, 248, 0.3); }
.page-tag-wrapper span:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.2); color:#cbd5e1; }

.premium-wrapper { transition: all 0.3s ease; }
.premium-wrapper:hover { border-color: rgba(245, 158, 11, 0.4) !important; box-shadow: 0 5px 20px rgba(245, 158, 11, 0.1); transform: translateY(-2px); }

.premium-switch input:checked + label { background: linear-gradient(135deg, #f59e0b, #d946ef) !important; border-color: transparent !important; box-shadow: 0 0 10px rgba(245,158,11,0.5) !important; }
.premium-switch label:before { position: absolute; content: ""; height: 20px; width: 20px; left: 2px; bottom: 2px; background-color: rgba(255,255,255,0.4); transition: .4s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
.premium-switch input:checked + label:before { transform: translateX(24px); background-color: white; }

@keyframes fadeIn { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }

/* Lightbox effects */
#lightbox-modal { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); z-index: 10000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
#lightbox-modal.active { opacity: 1; pointer-events: auto; }
#lightbox-img { max-width: 90%; max-height: 90vh; border-radius: 8px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); object-fit: contain; }
#lightbox-close { position: absolute; top: 20px; right: 30px; color: white; font-size: 30px; cursor: pointer; background: rgba(255,255,255,0.1); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
#lightbox-close:hover { background: rgba(255,255,255,0.2); }

#btn-page-generate:hover { background: rgba(56, 189, 248, 0.15) !important; text-shadow: 0 0 8px rgba(56, 189, 248, 0.5); }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// --- Global Lightbox logic ---
function openLightbox(srcString) {
    if(srcString && (srcString.startsWith('data:image') || srcString.includes('.png') || srcString.includes('.jpg'))) {
        document.getElementById('lightbox-img').src = srcString;
        document.getElementById('lightbox-modal').classList.add('active');
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
    lucide.createIcons();
    const btnGenerate = document.getElementById('btn-page-generate');
    const urlHidden = document.getElementById('page-url-hidden');
    const htmlArea = document.getElementById('tpl-html');

    if (btnGenerate) {
        btnGenerate.addEventListener('click', function() {
            const htmlCode = htmlArea.value;
            if (!htmlCode.trim()) {
                alert("Le champ HTML est vide.");
                return;
            }

            const originalText = btnGenerate.innerHTML;
            btnGenerate.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i> Génération en cours...';
            btnGenerate.disabled = true;

            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = htmlCode;
            tempContainer.style.position = 'absolute';
            tempContainer.style.top = '-9999px';
            tempContainer.style.left = '-9999px';
            tempContainer.style.width = '794px'; 
            tempContainer.style.minHeight = '1123px';
            tempContainer.style.background = '#ffffff';
            tempContainer.style.padding = '20px';
            document.body.appendChild(tempContainer);

            setTimeout(() => {
                html2canvas(tempContainer, { scale: 1, useCORS: true, logging: false }).then(canvas => {
                    const base64str = canvas.toDataURL('image/jpeg', 0.85);
                    urlHidden.value = base64str;
                    
                    document.body.removeChild(tempContainer);
                    btnGenerate.innerHTML = originalText;
                    btnGenerate.disabled = false;
                    
                    // Open the popup immediately
                    openLightbox(base64str);
                    
                }).catch(err => {
                    console.error("Erreur capture", err);
                    alert("Échec de la génération.");
                    document.body.removeChild(tempContainer);
                    btnGenerate.innerHTML = originalText;
                    btnGenerate.disabled = false;
                });
            }, 600);
        });
    }

    // Dynamic Tags logic
    const tagInput = document.getElementById('custom-tag-input');
    const tagsWrapper = document.getElementById('tags-wrapper');
    const addBox = document.getElementById('add-tag-box');

    if (tagInput && tagsWrapper && addBox) {
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const newTag = tagInput.value.trim();
                
                if (newTag) {
                    // Create new label wrapper
                    const label = document.createElement('label');
                    label.className = 'page-tag-wrapper';
                    
                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.name = 'description[]';
                    input.value = newTag;
                    input.checked = true; // Auto-check it
                    
                    const span = document.createElement('span');
                    span.textContent = newTag;
                    
                    label.appendChild(input);
                    label.appendChild(span);
                    
                    // Insert before the input box
                    tagsWrapper.insertBefore(label, addBox);
                    
                    tagInput.value = '';
                }
            }
        });
    }
});
</script>
