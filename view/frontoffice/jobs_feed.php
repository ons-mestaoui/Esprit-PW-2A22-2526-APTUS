<?php 
$pageTitle = "Browse Jobs"; 
$pageCSS = "feeds.css"; 

require_once '../../controller/offreC.php';
require_once '../../controller/candidatureC.php';
require_once '../../model/candidature.php';

$offreC = new offreC();
$candidatureC = new candidatureC();

// --- TRAITEMENT DU FORMULAIRE DE CANDIDATURE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $id_offre = $_POST['id_offre'] ?? null;
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $reponses = $_POST['reponses_ques'] ?? '';
    $date_candidature = date('Y-m-d');
    
    // Pour l'id_candidat, on met 1 par défaut pour le moment (ou null s'il n'est pas connecté)
    $id_candidat = 1; 
    
    // Gérer l'upload du CV
    $cv_cand_base64 = null;
    if (isset($_FILES['cv_cand']) && $_FILES['cv_cand']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['cv_cand']['tmp_name'];
        $file_type = mime_content_type($file_tmp);
        $file_data = file_get_contents($file_tmp);
        $cv_cand_base64 = 'data:' . $file_type . ';base64,' . base64_encode($file_data);
    }
    
    // On ignore le statut et note d'après le constructeur (ou on met des valeurs mock si le constructeur les exige)
    // constructeur actuel : __construct($id_candidat, $id_offre, $nom, $prenom, $email, $date_candidature, $reponses_ques, $cv_cand, $note, $statut)
    $nouvelleCandidature = new candidature($id_candidat, $id_offre, $nom, $prenom, $email, $date_candidature, $reponses, $cv_cand_base64, null, 'en_attente');
    
    $candidatureC->addCandidature($nouvelleCandidature);
    
    // Redirection pour éviter la soumission en double
    header("Location: jobs_feed.php?success=applied");
    exit();
}

$criteres = [];
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

<!-- ═══ FILTER BAR ═══ -->
<div class="job-filter-bar mb-6" id="job-filters" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
  <!-- Group 1: Search -->
  <div class="input-icon-wrapper search-input" style="flex:1; min-width: 300px; position: relative; display: flex; align-items: center;">
    <i data-lucide="search" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-search" name="q" autocomplete="off" placeholder="Mot-clé, poste..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="flex: 1;">
    <div id="job-search-spinner" style="position: absolute; right: 1rem; display: none;">
      <div class="spinner-border" style="width: 18px; height: 18px; border: 2px solid rgba(168, 100, 228, 0.2); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
    </div>
  </div>
  <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

  <!-- Group 2: Location & Mode -->
  <div class="input-icon-wrapper" style="width: 180px;">
    <i data-lucide="map-pin" style="width:16px;height:16px;"></i>
    <input type="text" class="input" id="job-location" placeholder="Localisation...">
  </div>

  <div class="mode-toggle" id="mode-toggle" style="flex-shrink: 0;">
    <button class="mode-toggle__option active" data-mode="all">Tout</button>
    <button class="mode-toggle__option" data-mode="remote">À distance</button>
    <button class="mode-toggle__option" data-mode="onsite">Sur site</button>
    <button class="mode-toggle__option" data-mode="hybrid">Hybride</button>
  </div>

  <!-- Group 3: Sorting Options -->
  <form method="GET" action="jobs_feed.php" style="display:flex; gap: 0.5rem; flex-shrink: 0;">
    <select class="select" id="job-sort-date" name="sort_date" style="width: 140px;" onchange="this.form.submit()">
      <option value="">Date de pub.</option>
      <option value="DESC" <?php echo (isset($_GET['sort_date']) && $_GET['sort_date'] === 'DESC') ? 'selected' : ''; ?>>Plus récent ↓</option>
      <option value="ASC" <?php echo (isset($_GET['sort_date']) && $_GET['sort_date'] === 'ASC') ? 'selected' : ''; ?>>Plus ancien ↑</option>
    </select>
    
    <select class="select" id="job-sort-salary" name="sort_salaire" style="width: 140px;" onchange="this.form.submit()">
      <option value="">Salaire</option>
      <option value="ASC" <?php echo (isset($_GET['sort_salaire']) && $_GET['sort_salaire'] === 'ASC') ? 'selected' : ''; ?>>Croissant ↑</option>
      <option value="DESC" <?php echo (isset($_GET['sort_salaire']) && $_GET['sort_salaire'] === 'DESC') ? 'selected' : ''; ?>>Décroissant ↓</option>
    </select>
  </form>
</div>

<!-- Results Info -->
<div class="results-info mb-4">
  <strong><?php echo $count; ?></strong> results found
</div>

<!-- ═══ JOB CARDS GRID ═══ -->
<div class="job-cards-grid stagger" id="jobs-container">
  <?php foreach ($listeOffres as $offreItem): ?>
    <div class="job-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
      <?php if (!empty($offreItem['img_post'])): ?>
        <div style="height: 160px; background-image: url('<?php echo htmlspecialchars($offreItem['img_post']); ?>'); background-size: cover; background-position: center; border-bottom: 1px solid var(--border-color);"></div>
      <?php else: ?>
        <div style="height: 6px; background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));"></div>
      <?php endif; ?>
      <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
      <div class="job-card__header">
        <div class="job-card__company-logo">
        <i data-lucide="building" style="width:20px;height:20px;color:var(--accent-primary);"></i>
      </div>
      <div class="job-card__title-group">
        <h3 class="job-card__title"><?php echo htmlspecialchars($offreItem['titre'] ?? ''); ?></h3>
        <span class="job-card__company"><?php echo htmlspecialchars($offreItem['nom_entreprise'] ?? 'Entreprise Inconnue'); ?> • <?php echo htmlspecialchars($offreItem['domaine'] ?? ''); ?></span>
      </div>
      <span class="badge badge-info job-card__type-badge">Job</span>
    </div>
    <p class="job-card__description" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;"><?php echo htmlspecialchars($offreItem['description'] ?? ''); ?></p>
    <div class="job-card__tags">
      <span class="job-card__tag" title="Compétences"><i data-lucide="award" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['competences_requises'] ?? ''); ?></span>
      <span class="job-card__tag" title="Expérience"><i data-lucide="clock" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['experience_requise'] ?? ''); ?></span>
      <span class="job-card__tag" title="Salaire"><i data-lucide="banknote" style="width:14px;height:14px;"></i> <?php echo htmlspecialchars($offreItem['salaire'] ?? ''); ?> TND</span>
    </div>
    <div class="job-card__footer">
      <span class="job-card__date">
        <i data-lucide="calendar" style="width:12px;height:12px;"></i> Publié: <?php echo htmlspecialchars($offreItem['date_publication'] ?? ''); ?>
      </span>
      <button type="button" class="btn btn-sm" style="background: linear-gradient(90deg, #4fb5ff 0%, #a864e4 50%, #d85ab2 100%); border: none; color: white; padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 4px 15px rgba(168, 100, 228, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';" onclick="openOfferModal(<?php echo htmlspecialchars(json_encode([
          'id' => $offreItem['id_offre'],
          'titre' => $offreItem['titre'],
          'nom_entreprise' => $offreItem['nom_entreprise'] ?? 'Entreprise Inconnue',
          'domaine' => $offreItem['domaine'],
          'description' => $offreItem['description'],
          'competences' => $offreItem['competences_requises'],
          'experience' => $offreItem['experience_requise'],
          'salaire' => $offreItem['salaire'],
          'question' => $offreItem['question'] ?? 'Décrivez succinctement votre parcours et vos motivations...',
          'date_pub' => $offreItem['date_publication'],
          'img_post' => $offreItem['img_post'] ?? ''
      ])); ?>)">
        <i data-lucide="eye" style="width:14px;height:14px;"></i> Voir détails
      </button>
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

<!-- Pagination -->
<div class="pagination">
  <button class="pagination__btn">&laquo;</button>
  <button class="pagination__btn active">1</button>
  <button class="pagination__btn">2</button>
  <button class="pagination__btn">3</button>
  <button class="pagination__btn">&raquo;</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mode toggle interaction
  document.querySelectorAll('.mode-toggle__option').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.mode-toggle__option').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
    });
  });
});

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
    
    // Mettre à jour le titre et la question du modal
    var titleEl = document.getElementById('apply-modal-title');
    if (titleEl) {
        titleEl.innerText = "Nouvelle Candidature - " + currentOfferTitle;
    }
    var questionEl = document.getElementById('apply-modal-question-label');
    if (questionEl) {
        questionEl.innerText = currentOfferQuestion;
    }

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
<div class="modal" style="max-width:1150px; padding: 2.5rem; border-radius: 12px; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2.5rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 1rem;">
        <h2 id="apply-modal-title" style="font-size:1.6rem; font-weight:700; color:#1a1a2e; margin:0;">Nouvelle Candidature</h2>
        <button type="button" onclick="closeApplyModal()" style="background:none; border:none; cursor:pointer; color:#999;"><i data-lucide="x" style="width:24px;height:24px;"></i></button>
    </div>

    <!-- Formulaire (aspect sérieux, bords fins) -->
    <form id="apply-form" method="POST" action="jobs_feed.php" enctype="multipart/form-data">
      <input type="hidden" name="id_offre" id="apply-id-offre" value="">
      
      <div style="display: flex; gap: 2rem;">
        <!-- Colonne Gauche -->
        <div style="flex:0 0 32%;">
            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#4a4a68; margin-bottom:0.5rem;">Nom de famille <span style="color:#e94560;">*</span></label>
                <input type="text" name="nom" required style="width:100%; padding: 0.65rem 1rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size:0.95rem; outline:none; transition:border 0.2s; background:#fafafa;" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='#e0e0e0'">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#4a4a68; margin-bottom:0.5rem;">Prénom <span style="color:#e94560;">*</span></label>
                <input type="text" name="prenom" required style="width:100%; padding: 0.65rem 1rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size:0.95rem; outline:none; transition:border 0.2s; background:#fafafa;" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='#e0e0e0'">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:600; color:#4a4a68; margin-bottom:0.5rem;">Adresse Email <span style="color:#e94560;">*</span></label>
                <input type="email" name="email" required style="width:100%; padding: 0.65rem 1rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size:0.95rem; outline:none; transition:border 0.2s; background:#fafafa;" onfocus="this.style.borderColor='var(--accent-primary)'" onblur="this.style.borderColor='#e0e0e0'">
            </div>
            
            <div style="margin-bottom: 1.5rem; padding: 1.25rem 1rem; border: 1px dashed #d0d0e0; border-radius: 8px; background: #fafafa; text-align: center;">
                <i data-lucide="file-up" style="width:24px;height:24px;color:#a0a0b0; margin-bottom: 0.5rem;"></i>
                <p style="font-size: 0.75rem; color: #666; margin-bottom: 0.75rem;">Téléchargez votre CV (PDF, DOCX)</p>
                <input type="file" name="cv_cand" accept=".pdf,.doc,.docx" required style="max-width: 100%; font-size:0.8rem; outline:none;">
            </div>
        </div>

        <!-- Colonne Droite (Façon panneau map dans le screenshot) -->
        <div style="flex:1;">
            <div style="height: 100%; border: 1px solid #f0f0f5; border-radius: 10px; padding: 1.5rem; background: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display:flex; flex-direction:column;">
                <label id="apply-modal-question-label" style="display:block; font-size:0.9rem; font-weight:700; color:#4a4a68; margin-bottom:1rem; text-align:center; letter-spacing:0.5px; border-bottom:1px solid #f0f0f5; padding-bottom:0.75rem;">Vos Motivations</label>
                
                <div style="margin-bottom: 0; flex:1; display:flex; flex-direction:column; min-height:180px;">
                    <input type="hidden" name="reponses_ques" id="hidden_reponses_ques">
                    <div id="quill-editor" style="flex:1; background: #fdfdfd; font-size:1rem;"></div>
                </div>
            </div>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; margin-top:2rem;">
        <button type="submit" name="submit_application" style="padding: 0.6rem 2rem; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); color: white; border:none; border-radius: 6px; font-weight: 600; cursor:pointer; box-shadow:0 4px 10px rgba(168,100,228,0.3);">Suivant</button>
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
}

// Ensure close clicking outside applies to the new modal too if needed
// Or it's already handled by CSS if there's no JS specifically binding it

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
        resultsInfo.innerHTML = `<strong>${offres.length}</strong> results found`;
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
        const question = o.question || 'Décrivez succinctement votre parcours et vos motivations...';
        
        // Données JSON pour le modal
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
            imgSection = `<div style="height: 160px; background-image: url('${imgPost}'); background-size: cover; background-position: center; border-bottom: 1px solid var(--border-color);"></div>`;
        } else {
            imgSection = `<div style="height: 6px; background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));"></div>`;
        }
        
        html += `<div class="job-card animate-on-scroll" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            ${imgSection}
            <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                <div class="job-card__header">
                    <div class="job-card__company-logo">
                        <i data-lucide="building" style="width:20px;height:20px;color:var(--accent-primary);"></i>
                    </div>
                    <div class="job-card__title-group">
                        <h3 class="job-card__title">${titre}</h3>
                        <span class="job-card__company">${entreprise} • ${domaine}</span>
                    </div>
                    <span class="badge badge-info job-card__type-badge">Job</span>
                </div>
                <p class="job-card__description" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">${description}</p>
                <div class="job-card__tags">
                    <span class="job-card__tag" title="Compétences"><i data-lucide="award" style="width:14px;height:14px;"></i> ${competences}</span>
                    <span class="job-card__tag" title="Expérience"><i data-lucide="clock" style="width:14px;height:14px;"></i> ${experience}</span>
                    <span class="job-card__tag" title="Salaire"><i data-lucide="banknote" style="width:14px;height:14px;"></i> ${salaire} TND</span>
                </div>
                <div class="job-card__footer">
                    <span class="job-card__date">
                        <i data-lucide="calendar" style="width:12px;height:12px;"></i> Publié: ${datePub}
                    </span>
                    <button type="button" class="btn btn-sm" style="background: linear-gradient(90deg, #4fb5ff 0%, #a864e4 50%, #d85ab2 100%); border: none; color: white; padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 4px 15px rgba(168, 100, 228, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';" onclick='openOfferModal(${modalData})'>
                        <i data-lucide="eye" style="width:14px;height:14px;"></i> Voir détails
                    </button>
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
