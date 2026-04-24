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

<div class="hr-layout" style="grid-template-columns: 300px 1fr;">
  <!-- ═══ SIDEBAR (Gauche) ═══ -->
  <aside class="hr-sidebar">
    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
        <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">Filtrer par poste</h4>
        <select class="input" id="filter-post" style="width: 100%; cursor: pointer;">
            <option>Tous les postes</option>
            <option>Senior Full Stack Developer</option>
            <option>Data Engineer</option>
            <option>UI/UX Designer</option>
            <option>Product Manager</option>
        </select>
    </div>



    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
        <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">Trier par</h4>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <label class="custom-radio">
                <input type="radio" name="sort-cand" checked>
                <span>Score de Match</span>
            </label>
            <label class="custom-radio">
                <input type="radio" name="sort-cand">
                <span>Date de dépôt</span>
            </label>
            <label class="custom-radio">
                <input type="radio" name="sort-cand">
                <span>Ordre alphabétique</span>
            </label>
        </div>
    </div>

    <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid var(--border-color);">
        <form method="GET" action="hr_candidatures.php" style="margin: 0;">
            <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">Statut</h4>
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <?php $current_status = $_GET['status'] ?? ''; ?>
                <label style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo ($current_status === '') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo ($current_status === '') ? '600' : '500'; ?>; transition: all 0.2s;">
                    <input type="radio" name="status" value="" onchange="this.form.submit()" <?php echo ($current_status === '') ? 'checked' : ''; ?> style="display:none;">
                    Tous
                </label>
                <label style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo ($current_status === 'shortliste') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo ($current_status === 'shortliste') ? '600' : '500'; ?>; transition: all 0.2s;">
                    <input type="radio" name="status" value="shortliste" onchange="this.form.submit()" <?php echo ($current_status === 'shortliste') ? 'checked' : ''; ?> style="display:none;">
                    Shortlisté
                </label>
                <label style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo ($current_status === 'en_attente') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo ($current_status === 'en_attente') ? '600' : '500'; ?>; transition: all 0.2s;">
                    <input type="radio" name="status" value="en_attente" onchange="this.form.submit()" <?php echo ($current_status === 'en_attente') ? 'checked' : ''; ?> style="display:none;">
                    En attente
                </label>
                <label style="cursor: pointer; display: block; font-size: 1.1rem; color: <?php echo ($current_status === 'refuse') ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo ($current_status === 'refuse') ? '600' : '500'; ?>; transition: all 0.2s;">
                    <input type="radio" name="status" value="refuse" onchange="this.form.submit()" <?php echo ($current_status === 'refuse') ? 'checked' : ''; ?> style="display:none;">
                    Refusé
                </label>
            </div>
        </form>
    </div>
  </aside>

  <!-- ═══ MAIN CONTENT (Droite) ═══ -->
  <div>
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <div class="results-info" style="background: var(--bg-card); padding: 0.6rem 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 0.6rem; color: var(--text-primary); font-weight: 500; font-size: 0.95rem;">
            <span style="color: #0ea5e9; font-weight: 700; font-size: 1.1rem;"><?php echo $count; ?></span>
            <span>candidatures reçues au total</span>
        </div>

        <!-- ═══ VIEW TOGGLE ═══ -->
        <div style="background: var(--bg-card); padding: 4px; border-radius: 12px; display: flex; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <button onclick="setViewMode('grid')" id="view-grid-btn" title="Vue Grille" style="border: none; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); color: white;">
                <i data-lucide="layout-grid" style="width: 18px; height: 18px;"></i>
            </button>
            <button onclick="setViewMode('list')" id="view-list-btn" title="Vue Liste" style="border: none; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; background: transparent; color: var(--text-tertiary);">
                <i data-lucide="list" style="width: 18px; height: 18px;"></i>
            </button>
        </div>
    </div>

    <!-- ═══ BARRE DE RECHERCHE (AU-DESSUS DES CANDIDATS) ═══ -->
    <div style="background: var(--bg-card); border-radius: 20px; padding: 0.75rem 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <form method="GET" action="hr_candidatures.php" style="display: flex; align-items: center; gap: 1rem; margin: 0;">
            <div style="flex: 1; position: relative; display: flex; align-items: center;">
                <i data-lucide="search" style="position: absolute; left: 1.25rem; width: 20px; height: 20px; color: var(--text-tertiary);"></i>
                <input type="text" name="q" placeholder="Rechercher un candidat par nom..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                       style="width: 100%; padding: 1rem 1rem 1rem 3.5rem; border: 1px solid var(--border-color); border-radius: 14px; font-size: 1rem; outline: none; transition: all 0.2s; background: var(--bg-secondary); color: var(--text-primary);"
                       onfocus="this.style.borderColor='var(--accent-primary)'; this.style.background='var(--bg-card)';" 
                       onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-secondary)';"
                       id="candidate-search-main">
            </div>
            <button type="submit" style="width: 52px; height: 52px; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); border: none; border-radius: 14px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(168, 100, 228, 0.3); transition: all 0.2s;" 
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(168, 100, 228, 0.4)';" 
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(168, 100, 228, 0.3)';">
                <i data-lucide="search" style="width: 22px; height: 22px;"></i>
            </button>
            <?php if(!empty($_GET['status'])): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status']); ?>">
            <?php endif; ?>
        </form>
    </div>

    <div class="candidate-cards-grid stagger" id="candidates-container">
      <?php foreach ($listeCandidatures as $cand): 
          $initials = strtoupper(substr($cand['prenom'], 0, 1) . substr($cand['nom'], 0, 1));
          $nomComplet = htmlspecialchars($cand['prenom'] . ' ' . $cand['nom']);
          $titreOffre = htmlspecialchars($cand['titre_offre'] ?? 'Candidature Spontanée');
          $dateCand = date('d/m/Y', strtotime($cand['date_candidature']));
          $idCand = $cand['id_candidature'];
      ?>
      <div class="candidate-card animate-on-scroll" id="candidate-<?php echo $idCand; ?>" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; transition: all 0.3s ease; display: flex; flex-direction: column; gap: 1rem;">
        <div class="candidate-card__header" style="display: flex; gap: 1rem; align-items: flex-start;">
          <div class="avatar avatar-lg" style="width: 56px; height: 56px; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.25rem; box-shadow: 0 4px 12px rgba(168, 100, 228, 0.2);">
            <?php echo $initials; ?>
          </div>
          <div class="candidate-card__info" style="flex: 1;">
            <div class="candidate-card__name" style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.2rem;"><?php echo $nomComplet; ?></div>
            <div class="candidate-card__role" style="font-size: 0.9rem; color: var(--accent-primary); font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
                <i data-lucide="briefcase" style="width: 14px; height: 14px;"></i> <?php echo $titreOffre; ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 0.4rem;">Déposé le <?php echo $dateCand; ?></div>
          </div>
          <div>
            <span class="badge badge-<?php echo ($cand['statut'] === 'accepte') ? 'success' : (($cand['statut'] === 'refuse') ? 'danger' : 'warning'); ?>" style="padding: 0.4rem 0.8rem; border-radius: 8px;">
              <?php echo ucfirst(htmlspecialchars($cand['statut'])); ?>
            </span>
          </div>
        </div>



        <div class="candidate-card__actions" style="display: flex; gap: 0.75rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
          <button class="btn btn-primary" style="flex: 1; padding: 0.6rem; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onclick="alert('Téléchargement du CV...')">
            <i data-lucide="file-text" style="width:16px;height:16px;"></i> Voir CV
          </button>
          <button class="btn btn-success" style="padding: 0.6rem; width: 42px;" title="Shortlister"><i data-lucide="check" style="width:18px;height:18px;"></i></button>
          <button class="btn btn-ghost" style="padding: 0.6rem; width: 42px; border: 1px solid var(--border-color);" title="Refuser"><i data-lucide="x" style="width:18px;height:18px;color:#ef4444;"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
      
      <?php if ($count == 0): ?>
          <div style="text-align: center; padding: 4rem 2rem; background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-color); grid-column: 1 / -1;">
              <div style="width: 80px; height: 80px; background: var(--bg-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                  <i data-lucide="user-x" style="width: 40px; height: 40px; color: var(--text-tertiary);"></i>
              </div>
              <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Aucune candidature</h3>
              <p style="color: var(--text-tertiary);">Les candidatures apparaîtront ici dès que des postulants répondront à vos offres.</p>
          </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
.custom-radio, .custom-checkbox {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-size: 0.95rem;
    color: var(--text-secondary);
    transition: all 0.2s;
}

.custom-radio:hover, .custom-checkbox:hover {
    color: var(--accent-primary);
}

.custom-radio input, .custom-checkbox input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--accent-primary);
}

.candidate-card:hover {
    transform: translateY(-5px);
    border-color: var(--accent-primary) !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

/* ── LIST VIEW STYLES ── */
.candidate-cards-grid.view-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.75rem !important;
}

.candidate-cards-grid.view-list .candidate-card {
    flex-direction: row !important;
    align-items: center !important;
    padding: 0.75rem 1.5rem !important;
    gap: 1.5rem !important;
    border-radius: 12px !important;
    transform: none !important;
}

.candidate-cards-grid.view-list .candidate-card:hover {
    transform: translateX(4px) !important;
}

.candidate-cards-grid.view-list .avatar {
    width: 40px !important;
    height: 40px !important;
    font-size: 1rem !important;
    border-radius: 10px !important;
}

.candidate-cards-grid.view-list .candidate-card__header {
    display: grid !important;
    grid-template-columns: 40px 1.5fr 1fr 1fr !important;
    align-items: center !important;
    flex: 1 !important;
    gap: 1.5rem !important;
}

.candidate-cards-grid.view-list .candidate-card__name {
    font-size: 0.95rem !important;
    margin: 0 !important;
}

.candidate-cards-grid.view-list .candidate-card__role {
    font-size: 0.85rem !important;
}

.candidate-cards-grid.view-list .candidate-card__actions {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    width: auto !important;
    gap: 0.5rem !important;
}

.candidate-cards-grid.view-list .candidate-card__actions .btn-primary {
    padding: 0.5rem 1rem !important;
    flex: none !important;
}

.candidate-cards-grid.view-list .candidate-card__actions .btn-success,
.candidate-cards-grid.view-list .candidate-card__actions .btn-ghost {
    width: 36px !important;
    height: 36px !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
</style>

<script>
function setViewMode(mode) {
    const container = document.getElementById('candidates-container');
    const gridBtn = document.getElementById('view-grid-btn');
    const listBtn = document.getElementById('view-list-btn');
    
    if (!container || !gridBtn || !listBtn) return;

    if (mode === 'list') {
        container.classList.add('view-list');
        listBtn.style.background = 'linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%)';
        listBtn.style.color = 'white';
        listBtn.style.boxShadow = '0 4px 12px rgba(168, 100, 228, 0.2)';
        
        gridBtn.style.background = 'transparent';
        gridBtn.style.color = 'var(--text-tertiary)';
        gridBtn.style.boxShadow = 'none';
    } else {
        container.classList.remove('view-list');
        gridBtn.style.background = 'linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%)';
        gridBtn.style.color = 'white';
        gridBtn.style.boxShadow = '0 4px 12px rgba(168, 100, 228, 0.2)';
        
        listBtn.style.background = 'transparent';
        listBtn.style.color = 'var(--text-tertiary)';
        listBtn.style.boxShadow = 'none';
    }
    localStorage.setItem('hr_candidates_view_mode', mode);
}

document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('hr_candidates_view_mode');
    if (savedMode === 'list') {
        setViewMode('list');
    }
});
</script>
