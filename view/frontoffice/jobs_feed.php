<?php 
$pageTitle = "Browse Jobs"; 
$pageCSS = "feeds.css"; 

require_once '../../controller/offreC.php';
require_once '../../controller/candidatureC.php';
require_once '../../model/candidature.php';

$offreC = new offreC();
$candidatureC = new candidatureC();

// Marquer les notifications comme lues (AJAX)
if (isset($_GET['mark_read'])) {
    $candidatureC->markNotificationsRead(1); // ID candidat par défaut
    echo 'ok';
    exit();
}

// Supprimer une notification (AJAX)
if (isset($_GET['delete_notif'])) {
    $candidatureC->deleteNotification(intval($_GET['delete_notif']));
    echo 'ok';
    exit();
}
// --- TRAITEMENT DU FORMULAIRE DE CANDIDATURE ---
$cand_errors = [];
$cand_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $id_offre = $_POST['id_offre'] ?? null;
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reponses = trim($_POST['reponses_ques'] ?? '');
    $offer_title = $_POST['offer_title'] ?? '';
    $offer_question = $_POST['offer_question'] ?? '';
    $date_candidature = date('Y-m-d');
    
    // PHP VALIDATION
    if (empty($nom)) {
        $cand_errors['nom'] = "Le nom est obligatoire.";
    }
    if (empty($prenom)) {
        $cand_errors['prenom'] = "Le prénom est obligatoire.";
    }
    if (empty($email)) {
        $cand_errors['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $cand_errors['email'] = "Le format de l'email est invalide.";
    }
    
    $reponses_text = strip_tags(str_replace('&nbsp;', ' ', $reponses));
    if (empty(trim($reponses_text))) {
        $cand_errors['reponses'] = "La réponse est obligatoire.";
    } elseif (mb_strlen(trim($reponses_text)) < 10) {
        $cand_errors['reponses'] = "La réponse doit contenir au moins 10 caractères.";
    }
    
    // Gérer l'upload du CV
    $cv_cand_base64 = null;
    if (isset($_FILES['cv_cand']) && $_FILES['cv_cand']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['cv_cand']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_data = file_get_contents($file_tmp);
        $cv_cand_base64 = 'data:' . $file_type . ';base64,' . base64_encode($file_data);
    } else {
        $cand_errors['cv_cand'] = "Le CV est obligatoire.";
    }
    
    if (empty($cand_errors)) {
        // Pour l'id_candidat, on met 1 par défaut pour le moment (ou null s'il n'est pas connecté)
        $id_candidat = 1; 
        
        // VÉRIFIER SI DÉJÀ POSTULÉ
        if ($candidatureC->hasAlreadyApplied($id_candidat, $id_offre)) {
            header("Location: jobs_feed.php?error=already_applied");
            exit();
        }
        
        $nouvelleCandidature = new candidature($id_candidat, $id_offre, $nom, $prenom, $email, $date_candidature, $reponses, $cv_cand_base64, null, 'En attente');
        $candidatureC->addCandidature($nouvelleCandidature);
        
        // Redirection pour éviter la soumission en double
        header("Location: jobs_feed.php?success=applied");
        exit();
    } else {
        $cand_data = [
            'id_offre' => $id_offre,
            'offer_title' => $offer_title,
            'offer_question' => $offer_question,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'reponses_ques' => $reponses
        ];
    }
}

$criteres = [];
// Gestion de l'ouverture automatique de la modale via GET
if (isset($_GET['apply_to'])) {
    $id_pre_apply = intval($_GET['apply_to']);
    $pre_offre = $offreC->getOffreById($id_pre_apply);
    if ($pre_offre) {
        $cand_data['id_offre'] = $pre_offre['id_offre'];
        $cand_data['offer_title'] = $pre_offre['titre'];
        $cand_data['offer_question'] = $pre_offre['question'] ?? 'Décrivez succinctement votre parcours et vos motivations...';
    }
}

if (!empty($_GET['sort_salaire'])) {
    $criteres['sort_salaire'] = $_GET['sort_salaire'];
}
if (!empty($_GET['sort_date'])) {
    $criteres['sort_date'] = $_GET['sort_date'];
}

// Toujours filtrer sur les offres actives pour les candidats
if (!empty($criteres)) {
    $listeOffres = $offreC->filtrerOffres(array_merge($criteres, ['statut' => 'Actif']));
} else {
    $listeOffres = $offreC->afficherOffres(true);
}
$count = $listeOffres->rowCount();
?><?php
if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<style>
/* Custom style to integrate Quill nicely inside our modal */
.ql-toolbar.ql-snow {
    border: 1px solid #f0f0f5;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
    background: #fcfcfc;
}
.ql-container.ql-snow {
    border: 1px solid #f0f0f5;
    border-top: none;
    border-bottom-left-radius: 6px;
    border-bottom-right-radius: 6px;
}
/* Empêche le texte de déborder horizontalement et ajoute une scrollbar verticale si trop long */
.ql-editor {
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: pre-wrap;
    max-height: 300px;
    overflow-y: auto;
}
</style>

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="search" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Nos offres &amp; Tasks
  </h1>
  <p class="page-header__subtitle">Trouvez l'offre qui correspond à votre profil</p>
</div>

<?php if (isset($_GET['error']) && $_GET['error'] === 'already_applied'): ?>
    <div id="already-applied-alert" style="margin: 0 2rem 2rem 2rem; padding: 1rem 1.5rem; background: #fff5f5; border: 1px solid #feb2b2; border-radius: 12px; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 12px rgba(245, 101, 101, 0.08);">
        <div style="width: 28px; height: 28px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #f56565; border: 2px solid #f56565; flex-shrink: 0;">
            <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
        </div>
        <p style="color: #c53030; font-weight: 500; margin: 0; font-size: 0.95rem;">Vous avez déjà postulé à cette offre.</p>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'applied'): ?>
    <div id="applied-success-alert" style="margin: 0 2rem 2rem 2rem; padding: 1rem 1.5rem; background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 12px; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 12px rgba(72, 187, 120, 0.08);">
        <div style="width: 28px; height: 28px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #48bb78; border: 2px solid #48bb78; flex-shrink: 0;">
            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
        </div>
        <p style="color: #276749; font-weight: 500; margin: 0; font-size: 0.95rem;">Votre candidature a été envoyée avec succès !</p>
    </div>
<?php endif; ?>

<!-- Banner Maps (Coming soon) -->
<div class="promo-banner">
  <div style="display: flex; gap: 1rem; align-items: center;">
    <div style="color: #0ea5e9;">
      <i data-lucide="map-pin" style="width: 24px; height: 24px;"></i>
    </div>
    <div>
      <h3 class="promo-banner__title">Découvrez les Offres Proches de Chez Vous</h3>
      <p class="promo-banner__desc">Ne cherchez plus au hasard. Affichez les opportunités à proximité de votre domicile pour faciliter votre quotidien.</p>
    </div>
  </div>
  <button type="button" style="background: linear-gradient(90deg, #0ea5e9 0%, #9333ea 100%); color: white; border: none; border-radius: 6px; padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; white-space: nowrap; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 12px rgba(147, 51, 234, 0.25);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(147, 51, 234, 0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(147, 51, 234, 0.25)';">
    Afficher Maps <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
  </button>
</div>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

<div class="hr-layout" style="grid-template-columns: 260px 1fr;">
  <!-- ═══ SIDEBAR (Gauche) ═══ -->
  <aside class="hr-sidebar">

    <!-- Filter: Type de poste -->
    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
      <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">TYPE DE POSTE</h4>
      <div style="display: flex; flex-direction: column; gap: 1.25rem;">
          <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--accent-primary); font-weight: 600; transition: all 0.2s;" onclick="handleSidebarFilter(this, 'type')">
              <input type="radio" name="filter_type" value="all" checked style="position:absolute; opacity:0; width:0; height:0;">
              Tout
          </label>
          <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--text-secondary); font-weight: 500; transition: all 0.2s;" onclick="handleSidebarFilter(this, 'type')">
              <input type="radio" name="filter_type" value="À distance" style="position:absolute; opacity:0; width:0; height:0;">
              À distance
          </label>
          <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--text-secondary); font-weight: 500; transition: all 0.2s;" onclick="handleSidebarFilter(this, 'type')">
              <input type="radio" name="filter_type" value="Sur site" style="position:absolute; opacity:0; width:0; height:0;">
              Sur site
          </label>
          <label class="filter-type-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--text-secondary); font-weight: 500; transition: all 0.2s;" onclick="handleSidebarFilter(this, 'type')">
              <input type="radio" name="filter_type" value="Hybride" style="position:absolute; opacity:0; width:0; height:0;">
              Hybride
          </label>
      </div>
    </div>

    <!-- Favoris -->
    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color); transition: transform 0.3s ease;">
      <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
          <i data-lucide="star" style="width:14px;height:14px;color:#f59e0b;"></i> Ma Sélection
      </h4>
      <button onclick="filterByFavoris(this)" class="btn-favoris-filter" style="width: 100%; padding: 0.85rem; border-radius: 14px; border: 2px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary); display: flex; align-items: center; justify-content: center; gap: 0.75rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-weight: 700; font-size: 0.9rem; position: relative; overflow: hidden;" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.color='var(--accent-primary)'; this.style.transform='translateY(-2px)';" onmouseout="if(!showFavorisOnly){ this.style.borderColor='var(--border-color)'; this.style.color='var(--text-secondary)'; this.style.transform='translateY(0)'; }">
          <div class="fav-icon-container" style="display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: var(--bg-card); border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <i data-lucide="bookmark" style="width: 16px; height: 16px; transition: all 0.3s;"></i>
          </div>
          Voir mes favoris
      </button>
    </div>

    <!-- Date de publication -->
    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
      <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">DATE DE PUBLICATION</h4>
      <div style="display: flex; flex-direction: column; gap: 1.25rem;">
          <label class="filter-date-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--accent-primary); font-weight: 600; transition: all 0.2s;" onclick="handleDateFilter(this)">
              <input type="radio" name="filter_date" value="" checked style="position:absolute; opacity:0; width:0; height:0;">
              Tout
          </label>
          <label class="filter-date-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--text-secondary); font-weight: 500; transition: all 0.2s;" onclick="handleDateFilter(this)">
              <input type="radio" name="filter_date" value="DESC" style="position:absolute; opacity:0; width:0; height:0;">
              Plus récent
          </label>
          <label class="filter-date-label" style="cursor: pointer; display: block; font-size: 1.1rem; color: var(--text-secondary); font-weight: 500; transition: all 0.2s;" onclick="handleDateFilter(this)">
              <input type="radio" name="filter_date" value="ASC" style="position:absolute; opacity:0; width:0; height:0;">
              Plus ancien
          </label>
      </div>
    </div>

    <!-- Salaire Range Slider -->
    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
      <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">SALAIRE</h4>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <span id="salary-min-label" style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">0 TND</span>
        <span style="color: var(--text-tertiary); font-size: 0.85rem;">—</span>
        <span id="salary-max-label" style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">10 000 TND</span>
      </div>
      <div class="range-slider-container" style="position: relative; height: 36px; margin-bottom: 0.5rem;">
        <div class="range-slider-track" style="position: absolute; top: 50%; left: 0; right: 0; height: 4px; background: var(--bg-tertiary); border-radius: 4px; transform: translateY(-50%);"></div>
        <div id="range-slider-fill" style="position: absolute; top: 50%; height: 4px; background: var(--accent-primary); border-radius: 4px; transform: translateY(-50%); left: 0%; right: 0%;"></div>
        <input type="range" id="salary-min-range" min="0" max="10000" value="0" step="100"
               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; -webkit-appearance: none; appearance: none; background: transparent; pointer-events: none; margin: 0; z-index: 2;"
               oninput="updateSalaryRange()">
        <input type="range" id="salary-max-range" min="0" max="10000" value="10000" step="100"
               style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; -webkit-appearance: none; appearance: none; background: transparent; pointer-events: none; margin: 0; z-index: 3;"
               oninput="updateSalaryRange()">
      </div>
      <style>
        /* Range slider thumb styling */
        #salary-min-range::-webkit-slider-thumb,
        #salary-max-range::-webkit-slider-thumb {
          -webkit-appearance: none;
          appearance: none;
          width: 20px;
          height: 20px;
          border-radius: 50%;
          background: var(--accent-primary);
          border: 3px solid var(--bg-card);
          box-shadow: 0 2px 8px rgba(107, 52, 163, 0.35);
          cursor: pointer;
          pointer-events: all;
          position: relative;
          z-index: 5;
          transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        #salary-min-range::-webkit-slider-thumb:hover,
        #salary-max-range::-webkit-slider-thumb:hover {
          transform: scale(1.2);
          box-shadow: 0 3px 12px rgba(107, 52, 163, 0.5);
        }
        #salary-min-range::-moz-range-thumb,
        #salary-max-range::-moz-range-thumb {
          width: 20px;
          height: 20px;
          border-radius: 50%;
          background: var(--accent-primary);
          border: 3px solid var(--bg-card);
          box-shadow: 0 2px 8px rgba(107, 52, 163, 0.35);
          cursor: pointer;
          pointer-events: all;
        }
        #salary-min-range::-webkit-slider-runnable-track,
        #salary-max-range::-webkit-slider-runnable-track {
          height: 4px;
          background: transparent;
        }
        #salary-min-range::-moz-range-track,
        #salary-max-range::-moz-range-track {
          height: 4px;
          background: transparent;
        }
      </style>
      <script>
        var salaryDebounce;
        function updateSalaryRange() {
            let minVal = parseInt(document.getElementById('salary-min-range').value);
            let maxVal = parseInt(document.getElementById('salary-max-range').value);
            
            // Prevent crossover
            if (minVal > maxVal) {
                const target = event.target;
                if (target.id === 'salary-min-range') {
                    minVal = maxVal;
                    target.value = minVal;
                } else {
                    maxVal = minVal;
                    target.value = maxVal;
                }
            }
            
            // Update labels
            document.getElementById('salary-min-label').textContent = minVal.toLocaleString('fr-FR') + ' TND';
            document.getElementById('salary-max-label').textContent = maxVal.toLocaleString('fr-FR') + ' TND';
            
            // Update fill bar
            const minPercent = (minVal / 10000) * 100;
            const maxPercent = (maxVal / 10000) * 100;
            document.getElementById('range-slider-fill').style.left = minPercent + '%';
            document.getElementById('range-slider-fill').style.right = (100 - maxPercent) + '%';
            
            // Debounced search
            clearTimeout(salaryDebounce);
            salaryDebounce = setTimeout(() => {
                fetchJobsSearch(document.getElementById('job-search').value);
            }, 400);
        }
      </script>
    </div>

    <script>
    var currentDateSort = '';

    function handleSidebarFilter(labelElement, category) {
        const radio = labelElement.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;

        const selector = '.filter-type-label';
        document.querySelectorAll(selector).forEach(lbl => {
            lbl.style.color = 'var(--text-secondary)';
            lbl.style.fontWeight = '500';
        });
        labelElement.style.color = 'var(--accent-primary)';
        labelElement.style.fontWeight = '600';

        currentModeFilter = radio.value;
        fetchJobsSearch(document.getElementById('job-search').value);
    }

    function handleDateFilter(labelElement) {
        const radio = labelElement.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;

        document.querySelectorAll('.filter-date-label').forEach(lbl => {
            lbl.style.color = 'var(--text-secondary)';
            lbl.style.fontWeight = '500';
        });
        labelElement.style.color = 'var(--accent-primary)';
        labelElement.style.fontWeight = '600';

        currentDateSort = radio.value;
        fetchJobsSearch(document.getElementById('job-search').value);
    }
    let showFavorisOnly = false;

    function filterByFavoris(btn) {
        if (!btn) return;
        showFavorisOnly = !showFavorisOnly;
        const icon = btn.querySelector('i');
        const iconContainer = btn.querySelector('.fav-icon-container');
        
        if (showFavorisOnly) {
            btn.style.borderColor = 'var(--accent-primary)';
            btn.style.color = 'var(--accent-primary)';
            btn.style.background = 'rgba(168, 100, 228, 0.08)';
            btn.style.boxShadow = '0 8px 20px rgba(168, 100, 228, 0.15)';
            if (icon) {
                icon.style.fill = 'currentColor';
                icon.style.transform = 'scale(1.2)';
            }
            if (iconContainer) iconContainer.style.background = 'white';
            fetchFavoris();
        } else {
            btn.style.borderColor = 'var(--border-color)';
            btn.style.color = 'var(--text-secondary)';
            btn.style.background = 'var(--bg-secondary)';
            btn.style.boxShadow = 'none';
            if (icon) {
                icon.style.fill = 'none';
                icon.style.transform = 'scale(1)';
            }
            if (iconContainer) iconContainer.style.background = 'var(--bg-card)';
            fetchJobsSearch(document.getElementById('job-search').value);
        }
    }

    function fetchFavoris() {
        console.log("DEBUG: Début fetchFavoris");
        const spinner = document.getElementById('job-search-spinner');
        if (spinner) spinner.style.display = 'block';
        
        fetch('ajax_favoris.php?action=get_favoris')
        .then(r => r.json())
        .then(data => {
            console.log("DEBUG: Favoris reçus:", data);
            if (data.results && data.results.length > 0) {
                updateJobsGrid(data.results);
            } else {
                const container = document.getElementById('jobs-container');
                if (container) {
                    container.innerHTML = '<div style="text-align:center; padding:3rem; color:var(--text-tertiary);">Vous n\'avez pas encore d\'offres sauvegardées.</div>';
                }
                const resultsInfo = document.querySelector('.results-info');
                if (resultsInfo) {
                    resultsInfo.innerHTML = '<span style="color: #0ea5e9; font-weight: 700; font-size: 1.1rem;">0</span><span>favoris trouvés</span>';
                }
            }
            if (spinner) spinner.style.display = 'none';
        })
        .catch(err => {
            console.error("DEBUG: Erreur fetchFavoris:", err);
            alert("Erreur lors de la récupération des favoris.");
        });
    }
    </script>
  </aside>

  <!-- ═══ MAIN CONTENT (Droite) ═══ -->
  <div>
    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem;">
      <div class="results-info" style="background: var(--bg-card); padding: 0.75rem 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 0.6rem; color: var(--text-primary); font-weight: 500; font-size: 0.95rem; white-space: nowrap;">
        <span style="color: #0ea5e9; font-weight: 700; font-size: 1.1rem;"><?php echo $count; ?></span>
        <span>offres trouvées</span>
      </div>

      <!-- ═══ BARRE DE RECHERCHE DYNAMIQUE ═══ -->
      <div style="flex: 1; background: var(--bg-card); border-radius: 20px; padding: 0.5rem 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid var(--border-color);">
          <div style="display: flex; align-items: center; gap: 1rem; margin: 0;">
              <div style="flex: 1; position: relative; display: flex; align-items: center;">
                  <i data-lucide="search" style="position: absolute; left: 1.25rem; width: 20px; height: 20px; color: var(--text-tertiary);"></i>
                  <input type="text" id="job-search" name="q" autocomplete="off" placeholder="Rechercher une offre..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                         style="width: 100%; padding: 0.75rem 1rem 0.75rem 3.5rem; border: 1px solid var(--border-color); border-radius: 14px; font-size: 1rem; outline: none; transition: all 0.2s; background: var(--bg-secondary); color: var(--text-primary);"
                         onfocus="this.style.borderColor='var(--accent-primary)'; this.style.background='var(--bg-card)';" 
                         onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-secondary)';"
                         class="search-input-field">
              </div>
              <div id="job-search-spinner" style="display: none;">
                  <div class="spinner-border text-primary" role="status" style="width: 24px; height: 24px; border: 3px solid rgba(168, 100, 228, 0.2); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
              </div>
          </div>
      </div>
    </div>

    <!-- ═══ JOB CARDS GRID ═══ -->
    <div class="job-cards-grid stagger" id="jobs-container">
      <?php foreach ($listeOffres as $offreItem): ?>
        <div class="job-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
          <?php if (!empty($offreItem['img_post'])): ?>
            <div style="height: 140px; background-image: url('<?php echo htmlspecialchars($offreItem['img_post']); ?>'); background-size: cover; background-position: center; position: relative;">
          <?php else: ?>
            <div style="height: 80px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%); position: relative; display: flex; align-items: center; justify-content: center;">
               <i data-lucide="image" style="width: 32px; height: 32px; color: var(--text-secondary); opacity: 0.5;"></i>
          <?php endif; ?>
               <div style="position: absolute; top: 12px; right: 12px;">
                   <span class="badge badge-info" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);"><?php echo htmlspecialchars($offreItem['type'] ?? 'Sur site'); ?></span>
               </div>
            </div>
          <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
            <div class="job-card__header" style="margin-bottom: 0.75rem;">
              <div class="job-card__company-logo">
                <i data-lucide="building" style="width:20px;height:20px;color:var(--accent-primary);"></i>
              </div>
              <div class="job-card__title-group">
                <h3 class="job-card__title"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
                <span class="job-card__company"><?php echo htmlspecialchars($offreItem['nom_entreprise'] ?? 'Entreprise Inconnue'); ?> • <?php echo htmlspecialchars($offreItem['domaine'] ?? ''); ?></span>
              </div>
            </div>
            <p class="job-card__description" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; font-size: 0.85rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($offreItem['description'] ?? ''); ?></p>
            <div class="job-card__tags" style="margin-bottom: 0.75rem;">
              <span class="job-card__tag" title="Compétences"><i data-lucide="award" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['competences_requises'] ?? ''); ?></span>
              <span class="job-card__tag" title="Expérience"><i data-lucide="clock" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['experience_requise'] ?? ''); ?></span>
              <span class="job-card__tag" title="Salaire"><i data-lucide="banknote" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['salaire'] ?? ''); ?> TND</span>
            </div>
            <div class="job-card__footer">
              <span class="job-card__date">
                <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo htmlspecialchars($offreItem['date_publication'] ?? ''); ?>
              </span>
                <a href="job_details.php?id=<?php echo $offreItem['id_offre']; ?>" class="btn btn-sm btn-primary">
                  <i data-lucide="eye" style="width:14px;height:14px;"></i> Voir détails
                </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      
      <?php if ($count == 0): ?>
        <div class="empty-state text-center" style="padding: 3rem; background: var(--surface-1); border-radius: 12px; grid-column: 1 / -1;">
            <p>Aucune offre trouvée pour le moment.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mode toggle interaction is now handled by handleModeToggle
});

var currentModeFilter = 'all';

function handleModeToggle(btn, typeValue) {
    document.querySelectorAll('.mode-toggle__option').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    currentModeFilter = typeValue;
    fetchJobsSearch(document.getElementById('job-search').value);
}

var currentOfferId = null;
var currentOfferTitle = '';
var currentOfferQuestion = '';

function openOfferModal(data) {
    currentOfferId = data.id;
    currentOfferTitle = data.titre || 'Poste sans titre';
    currentOfferQuestion = data.question || 'Décrivez succinctement votre parcours et vos motivations...';
    document.getElementById('modal-title').innerText = data.titre || 'Titre inconnu';
    document.getElementById('modal-company').innerText = (data.nom_entreprise || 'Entreprise Inconnue') + ' • ' + (data.domaine || '');
    document.getElementById('modal-desc').innerText = data.description || 'Aucune description fournie.';
    document.getElementById('modal-skills').innerText = data.competences || 'N/A';
    document.getElementById('modal-exp').innerText = data.experience || 'N/A';
    document.getElementById('modal-salary').innerText = data.salaire || 'N/A';
    
    var coverEl = document.getElementById('modal-cover-img');
    if (data.img_post) {
        coverEl.style.backgroundImage = "url('" + data.img_post + "')";
    } else {
        coverEl.style.backgroundImage = "linear-gradient(90deg, var(--accent-primary), var(--accent-secondary))";
    }

    var overlay = document.getElementById('offer-details-modal');
    if (overlay) {
        overlay.classList.add('active');
        var modal = overlay.querySelector('.modal');
        if (modal) modal.classList.add('active');
    }
}

function openApplyModal() {
    // Fermer l'ancien modal
    var detailsOverlay = document.getElementById('offer-details-modal');
    if (detailsOverlay) {
        detailsOverlay.classList.remove('active');
        var detailsModal = detailsOverlay.querySelector('.modal');
        if (detailsModal) detailsModal.classList.remove('active');
    }

    // Mettre l'ID de l'offre dans le form
    document.getElementById('apply-id-offre').value = currentOfferId;
    
    var titleEl = document.getElementById('apply-modal-title');
    if (titleEl) {
        titleEl.innerText = "Nouvelle Candidature - " + currentOfferTitle;
    }
    var questionEl = document.getElementById('apply-modal-question-label');
    if (questionEl) {
        questionEl.innerText = currentOfferQuestion;
    }

    document.getElementById('apply-offer-title').value = currentOfferTitle;
    document.getElementById('apply-offer-question').value = currentOfferQuestion;

    // Ouvrir le nouveau modal
    var applyOverlay = document.getElementById('apply-modal');
    if (applyOverlay) {
        applyOverlay.classList.add('active');
        var applyModal = applyOverlay.querySelector('.modal');
        if (applyModal) applyModal.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var closeBtns = document.querySelectorAll('.modal-close-btn');
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var overlay = document.getElementById('offer-details-modal');
            if (overlay) {
                overlay.classList.remove('active');
                var modal = overlay.querySelector('.modal');
                if (modal) modal.classList.remove('active');
            }
        });
    });
    // Auto-hide alerts after 3 seconds
    setTimeout(() => {
        ['already-applied-alert', 'applied-success-alert'].forEach(id => {
            const alert = document.getElementById(id);
            if (alert) {
                alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 600);
            }
        });
    }, 3000);
});
</script>

<!-- ═══ Modal Détails de l'Offre ═══ -->
<div class="modal-overlay" id="offer-details-modal">
  <div class="modal" style="max-width:650px; padding:0; overflow:hidden; border-radius:16px; display:flex; flex-direction:column; max-height:90vh;">
    <div id="modal-cover-img" style="height:150px; background:linear-gradient(90deg, var(--accent-primary), var(--accent-secondary)); background-size:cover; background-position:center;"></div>
    <div class="modal-body" style="padding: 2.5rem; overflow-y:auto; flex:1;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 1.5rem;">
        <div>
          <h2 id="modal-title" style="font-size:1.75rem; font-weight:700; margin-bottom:0.35rem; color:var(--text-primary);">Titre</h2>
          <p id="modal-company" style="color:var(--text-secondary); font-size:1rem;">Entreprise • Domaine</p>
        </div>
        <span class="badge badge-success">Actif</span>
      </div>
      
      <div style="display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; padding: 1rem; background: var(--bg-body); border-radius: 8px;">
        <span class="job-card__tag" style="background:transparent;"><i data-lucide="award" style="width:16px;height:16px;color:var(--accent-primary);"></i> <strong id="modal-skills" style="margin-left:4px;"></strong></span>
        <span class="job-card__tag" style="background:transparent;"><i data-lucide="clock" style="width:16px;height:16px;color:var(--accent-primary);"></i> <strong id="modal-exp" style="margin-left:4px;"></strong></span>
        <span class="job-card__tag" style="background:transparent;"><i data-lucide="banknote" style="width:16px;height:16px;color:var(--accent-primary);"></i> <strong id="modal-salary" style="margin-left:4px;"></strong> TND</span>
      </div>
      
      <div style="margin-bottom: 2.5rem; max-height: 45vh; overflow-y: auto; padding-right: 0.5rem;">
        <h4 style="font-size:1.1rem; font-weight:600; margin-bottom:0.75rem; color:var(--text-primary);">Description du poste</h4>
        <p id="modal-desc" style="color:var(--text-secondary); line-height:1.7; white-space:pre-wrap; word-break: break-word; overflow-wrap: anywhere;"></p>
      </div>

      <div class="flex gap-3 justify-end" style="border-top:1px solid var(--border-color); padding-top:1.5rem;">
        <button type="button" class="btn btn-secondary modal-close-btn" style="padding: 0.5rem 1.5rem;">Fermer</button>
        <button type="button" class="btn btn-primary" onclick="openApplyModal()" style="padding: 0.5rem 1.5rem;">
          <i data-lucide="send" style="width:16px;height:16px;"></i> Postuler
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ Modal Formulaire de Candidature ═══ -->
<div class="modal-overlay" id="apply-modal">
<div class="modal" style="max-width:1150px; padding: 2.5rem; border-radius: 12px; background: var(--bg-card); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h2 id="apply-modal-title" style="font-size:1.6rem; font-weight:700; color:var(--text-primary); margin:0;">Nouvelle Candidature</h2>
        <button type="button" onclick="closeApplyModal()" style="background:none; border:none; cursor:pointer; color:var(--text-tertiary);"><i data-lucide="x" style="width:24px;height:24px;"></i></button>
    </div>

    <!-- Formulaire -->
    <form id="apply-form" method="POST" action="jobs_feed.php" enctype="multipart/form-data">
      <input type="hidden" name="id_offre" id="apply-id-offre" value="<?php echo htmlspecialchars($cand_data['id_offre'] ?? ''); ?>">
      <input type="hidden" name="offer_title" id="apply-offer-title" value="<?php echo htmlspecialchars($cand_data['offer_title'] ?? ''); ?>">
      <input type="hidden" name="offer_question" id="apply-offer-question" value="<?php echo htmlspecialchars($cand_data['offer_question'] ?? ''); ?>">
      
      <div style="display: flex; gap: 2rem;">
        <!-- Colonne Gauche -->
        <div style="flex:0 0 32%;">
            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--text-secondary); margin-bottom:0.5rem;">Nom de famille <span style="color:#e94560;">*</span></label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($cand_data['nom'] ?? ''); ?>" style="width:100%; padding: 0.65rem 1rem; border: 1px solid <?php echo isset($cand_errors['nom']) ? '#e94560' : 'var(--border-color)'; ?>; border-radius: 6px; font-size:0.95rem; outline:none; transition:border 0.2s; background:var(--bg-input); color:var(--text-primary);" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="if(!this.value) this.style.borderColor='<?php echo isset($cand_errors['nom']) ? '#e94560' : 'var(--border-color)'; ?>'">
                <?php if(isset($cand_errors['nom'])): ?><span style="color:#e94560; font-size:0.8rem; margin-top:0.25rem; display:block;"><?php echo $cand_errors['nom']; ?></span><?php endif; ?>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--text-secondary); margin-bottom:0.5rem;">Prénom <span style="color:#e94560;">*</span></label>
                <input type="text" name="prenom" value="<?php echo htmlspecialchars($cand_data['prenom'] ?? ''); ?>" style="width:100%; padding: 0.65rem 1rem; border: 1px solid <?php echo isset($cand_errors['prenom']) ? '#e94560' : 'var(--border-color)'; ?>; border-radius: 6px; font-size:0.95rem; outline:none; transition:border 0.2s; background:var(--bg-input); color:var(--text-primary);" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="if(!this.value) this.style.borderColor='<?php echo isset($cand_errors['prenom']) ? '#e94560' : 'var(--border-color)'; ?>'">
                <?php if(isset($cand_errors['prenom'])): ?><span style="color:#e94560; font-size:0.8rem; margin-top:0.25rem; display:block;"><?php echo $cand_errors['prenom']; ?></span><?php endif; ?>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:var(--text-secondary); margin-bottom:0.5rem;">Adresse Email <span style="color:#e94560;">*</span></label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($cand_data['email'] ?? ''); ?>" style="width:100%; padding: 0.65rem 1rem; border: 1px solid <?php echo isset($cand_errors['email']) ? '#e94560' : 'var(--border-color)'; ?>; border-radius: 6px; font-size:0.95rem; outline:none; transition:border 0.2s; background:var(--bg-input); color:var(--text-primary);" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="if(!this.value) this.style.borderColor='<?php echo isset($cand_errors['email']) ? '#e94560' : 'var(--border-color)'; ?>'">
                <?php if(isset($cand_errors['email'])): ?><span style="color:#e94560; font-size:0.8rem; margin-top:0.25rem; display:block;"><?php echo $cand_errors['email']; ?></span><?php endif; ?>
            </div>
            
            <div style="margin-bottom: 1.5rem; padding: 1.25rem 1rem; border: 1px dashed <?php echo isset($cand_errors['cv_cand']) ? '#e94560' : 'var(--border-color)'; ?>; border-radius: 8px; background: var(--bg-secondary); text-align: center;">
                <i data-lucide="file-up" style="width:24px;height:24px;color:var(--text-tertiary); margin-bottom: 0.5rem;"></i>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.75rem;">Téléchargez votre CV (PDF, DOCX)</p>
                <input type="file" name="cv_cand" accept=".pdf,.doc,.docx" style="max-width: 100%; font-size:0.8rem; outline:none; color:var(--text-primary);">
                <?php if(isset($cand_errors['cv_cand'])): ?><span style="color:#e94560; font-size:0.8rem; margin-top:0.5rem; display:block;"><?php echo $cand_errors['cv_cand']; ?></span><?php endif; ?>
            </div>
        </div>

        <!-- Colonne Droite -->
        <div style="flex:1;">
            <div style="height: 100%; border: 1px solid <?php echo isset($cand_errors['reponses']) ? '#e94560' : 'var(--border-color)'; ?>; border-radius: 10px; padding: 1.5rem; background: var(--bg-primary); box-shadow: 0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column;">
                <label id="apply-modal-question-label" style="display:block; font-size:0.9rem; font-weight:700; color:var(--text-secondary); margin-bottom:1rem; text-align:center; letter-spacing:0.5px; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">Vos Motivations</label>
                
                <div style="margin-bottom: 0; flex:1; display:flex; flex-direction:column; min-height:180px;">
                    <input type="hidden" name="reponses_ques" id="hidden_reponses_ques" value="<?php echo htmlspecialchars($cand_data['reponses_ques'] ?? ''); ?>">
                    <div id="quill-editor" style="flex:1; background: var(--bg-input); font-size:1rem; color:var(--text-primary);"><?php echo $cand_data['reponses_ques'] ?? ''; ?></div>
                    <?php if(isset($cand_errors['reponses'])): ?><span style="color:#e94560; font-size:0.8rem; margin-top:0.5rem; display:block;"><?php echo $cand_errors['reponses']; ?></span><?php endif; ?>
                </div>
            </div>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; margin-top:2rem;">
        <button type="submit" name="submit_application" style="padding: 0.6rem 2rem; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); color: white; border:none; border-radius: 6px; font-weight: 600; cursor:pointer; box-shadow:0 4px 10px rgba(168,100,228,0.3);">Envoyer</button>
      </div>
    </form>
  </div>
</div>

<script>
// JS for closing the apply modal
function closeApplyModal() {
    var overlay = document.getElementById('apply-modal');
    if (overlay) {
        overlay.classList.remove('active');
        var modal = overlay.querySelector('.modal');
        if (modal) modal.classList.remove('active');
    }
    
    // Si on quitte le modal et qu'il y avait des erreurs de validation PHP, on recharge la page pour les nettoyer
    <?php if (!empty($cand_errors) || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    window.location.href = window.location.pathname + window.location.search;
    <?php endif; ?>
}

// Ensure close clicking outside applies to the modals
document.addEventListener('DOMContentLoaded', function() {
    var applyOverlay = document.getElementById('apply-modal');
    if (applyOverlay) {
        applyOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeApplyModal();
            }
        });
    }

    var detailsOverlay = document.getElementById('offer-details-modal');
    if (detailsOverlay) {
        detailsOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                var modal = this.querySelector('.modal');
                if (modal) modal.classList.remove('active');
            }
        });
    }

    // Logic for auto-opening application modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('apply_to') || <?php echo !empty($cand_errors) ? 'true' : 'false'; ?>) {
        openApplyModal();
    }
});
document.addEventListener('DOMContentLoaded', function() {
    var lucideScript = document.createElement('script');
    lucideScript.src = "https://unpkg.com/lucide@latest";
    lucideScript.onload = function() {
        lucide.createIcons();
    };
    document.head.appendChild(lucideScript);
    
    // Initialisation de Quill
    if(document.getElementById('quill-editor')) {
        var quill = new Quill('#quill-editor', {
            theme: 'snow',
            placeholder: 'Saisissez votre réponse ici...',
            modules: {
                toolbar: [
                    [{ 'font': [] }, { 'size': ['small', false, 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'align': [] }],
                    ['clean']
                ]
            }
        });

        // Copier le contenu HTML vers l'input caché lors du submit
        var form = document.getElementById('apply-form');
        if(form) {
            form.addEventListener('submit', function(e) {
                var hiddenInput = document.getElementById('hidden_reponses_ques');
                var rawText = quill.getText().trim();
                if(rawText.length === 0) {
                    hiddenInput.value = "";
                } else {
                    hiddenInput.value = quill.root.innerHTML;
                }
            });
        }
    }

    // Re-open apply modal if there are PHP validation errors
    <?php if(!empty($cand_errors)): ?>
        currentOfferId = "<?php echo addslashes($cand_data['id_offre'] ?? ''); ?>";
        currentOfferTitle = "<?php echo addslashes($cand_data['offer_title'] ?? ''); ?>";
        currentOfferQuestion = "<?php echo addslashes($cand_data['offer_question'] ?? ''); ?>";
        openApplyModal();
    <?php endif; ?>
});

// ═══ DYNAMIC AJAX SEARCH (JOBS FEED - MVC) ═══
let searchTimeout;
const jobSearchInput = document.getElementById('job-search');
if (jobSearchInput) {
    jobSearchInput.addEventListener('input', function() {
        const query = this.value;
        const spinner = document.getElementById('job-search-spinner');
        clearTimeout(searchTimeout);
        if (spinner) spinner.style.display = 'block';
        
        searchTimeout = setTimeout(() => {
            fetchJobsSearch(query);
        }, 300);
    });
}

function fetchJobsSearch(query) {
    const formData = new FormData();
    formData.append('action', 'search_offres');
    formData.append('query', query);
    formData.append('only_active', '1');
    
    if (currentModeFilter && currentModeFilter !== 'all') {
        formData.append('filter_type', currentModeFilter);
    }

    const salaryMin = document.getElementById('salary-min-range');
    const salaryMax = document.getElementById('salary-max-range');
    if (salaryMin && salaryMax) {
        formData.append('salary_min', salaryMin.value);
        formData.append('salary_max', salaryMax.value);
    }

    if (currentDateSort) {
        formData.append('sort_date', currentDateSort);
    }
    
    fetch('ajax_offres.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateJobsGrid(data.results);
        }
        const spinner = document.getElementById('job-search-spinner');
        if (spinner) spinner.style.display = 'none';
    })
    .catch(err => {
        console.error('Erreur recherche:', err);
        const spinner = document.getElementById('job-search-spinner');
        if (spinner) spinner.style.display = 'none';
    });
}

function updateJobsGrid(offres) {
    const container = document.getElementById('jobs-container');
    const resultsInfo = document.querySelector('.results-info');
    if (!container) return;
    
    if (resultsInfo) {
        resultsInfo.innerHTML = `<span style="color: #0ea5e9; font-weight: 700; font-size: 1.1rem;">${offres.length}</span><span>offres trouvées</span>`;
    }
    
    if (offres.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:3rem; color:var(--text-tertiary);">Aucune offre trouvée</div>';
        return;
    }
    
    let html = '';
    offres.forEach(o => {
        const titre = escapeHtml(o.titre || '');
        const entreprise = escapeHtml(o.nom_entreprise || 'Entreprise Inconnue');
        const domaine = escapeHtml(o.domaine || '');
        const description = escapeHtml(o.description || '');
        const competences = escapeHtml(o.competences_requises || '');
        const experience = escapeHtml(o.experience_requise || '');
        const salaire = escapeHtml(String(o.salaire || ''));
        const datePub = o.date_publication || '';
        const imgPost = o.img_post || '';
        const typePost = o.type || 'Sur site';
        const question = o.question || 'Décrivez succinctement votre parcours et vos motivations...';
        
        const modalData = JSON.stringify({
            id: o.id_offre,
            titre: o.titre,
            nom_entreprise: o.nom_entreprise || 'Entreprise Inconnue',
            domaine: o.domaine,
            description: o.description,
            competences: o.competences_requises,
            experience: o.experience_requise,
            salaire: o.salaire,
            question: question,
            date_pub: datePub,
            img_post: imgPost
        }).replace(/"/g, '&quot;');
        
        let imgSection = '';
        if (imgPost) {
            imgSection = `<div style="height: 140px; background-image: url('${imgPost}'); background-size: cover; background-position: center; position: relative;">`;
        } else {
            imgSection = `<div style="height: 80px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%); position: relative; display: flex; align-items: center; justify-content: center;">
               <i data-lucide="image" style="width: 32px; height: 32px; color: var(--text-secondary); opacity: 0.5;"></i>`;
        }
        
        html += `<div class="job-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            ${imgSection}
               <div style="position: absolute; top: 12px; right: 12px;">
                   <span class="badge badge-info" style="box-shadow: 0 4px 12px rgba(0,0,0,0.1);">${escapeHtml(typePost)}</span>
               </div>
            </div>
            <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
                <div class="job-card__header" style="margin-bottom: 0.75rem;">
                    <div class="job-card__company-logo">
                        <i data-lucide="building" style="width:20px;height:20px;color:var(--accent-primary);"></i>
                    </div>
                    <div class="job-card__title-group">
                        <h3 class="job-card__title">${titre}</h3>
                        <span class="job-card__company">${entreprise} • ${domaine}</span>
                    </div>
                </div>
                <p class="job-card__description" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; font-size: 0.85rem; margin-bottom: 0.75rem;">${description}</p>
                <div class="job-card__tags" style="margin-bottom: 0.75rem;">
                    <span class="job-card__tag" title="Compétences"><i data-lucide="award" style="width:14px;height:14px;"></i> ${competences}</span>
                    <span class="job-card__tag" title="Expérience"><i data-lucide="clock" style="width:14px;height:14px;"></i> ${experience}</span>
                    <span class="job-card__tag" title="Salaire"><i data-lucide="banknote" style="width:14px;height:14px;"></i> ${salaire} TND</span>
                </div>
                <div class="job-card__footer">
                    <span class="job-card__date">
                        <i data-lucide="calendar" style="width:12px;height:12px;"></i> ${datePub}
                    </span>
                    <a href="job_details.php?id=${o.id_offre}" class="btn btn-sm" style="background: linear-gradient(90deg, #4fb5ff 0%, #a864e4 50%, #d85ab2 100%); border: none; color: white; padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; text-decoration: none; box-shadow: 0 4px 15px rgba(168, 100, 228, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
                        <i data-lucide="eye" style="width:14px;height:14px;"></i> Voir détails
                    </a>
                </div>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
    if (window.lucide) lucide.createIcons();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

</script>