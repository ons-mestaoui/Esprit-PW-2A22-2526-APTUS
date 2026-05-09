<?php $pageTitle = "Candidatures"; $pageCSS = "feeds.css"; $userRole = "Entreprise"; ?>

<?php
require_once '../../controller/candidatureC.php';
require_once '../../controller/offreC.php';
$candidatureC = new candidatureC();
$offreC = new offreC();

// Récupération des offres de l'entreprise (ou toutes les offres pour l'instant)
$listeOffresDisponibles = $offreC->afficherOffres();

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_statut'])) {
    $id_cand = intval($_POST['id_candidature']);
    $statut = $_POST['update_statut'];
    if (in_array($statut, ['Accepté', 'Refusé'])) {
        $candidatureC->updateStatut($id_cand, $statut);
    }
    header('Location: hr_candidatures.php');
    exit();
}

$criteres = [];
if (!empty($_GET['status'])) {
    $criteres['status'] = $_GET['status'];
}
if (!empty($_GET['q'])) {
    $criteres['q'] = $_GET['q'];
}
if (!empty($_GET['offre_id'])) {
    $criteres['offre_id'] = intval($_GET['offre_id']);
}
$listeCandidatures = $candidatureC->filtrerCandidatures($criteres);
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
    <form method="GET" action="hr_candidatures.php" style="margin: 0;">
        <?php if (!empty($_GET['q'])): ?>
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q']); ?>">
        <?php endif; ?>
        
        <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
            <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-tertiary); font-weight: 700; letter-spacing: 0.1em; margin-bottom: 1.25rem;">Filtrer par poste</h4>
            <select class="input" name="offre_id" style="width: 100%; cursor: pointer;" onchange="this.form.submit()">
                <option value="">Tous les postes</option>
                <?php while($o = $listeOffresDisponibles->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $o['id_offre']; ?>" <?php echo (isset($_GET['offre_id']) && $_GET['offre_id'] == $o['id_offre']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($o['titre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="hr-sidebar__section" style="background: var(--bg-card); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid var(--border-color);">
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
        </div>
    </form>
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

    <!-- ═══ BARRE DE RECHERCHE DYNAMIQUE (CANDIDATS) ═══ -->
    <div style="background: var(--bg-card); border-radius: 20px; padding: 0.75rem 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 2rem; border: 1px solid var(--border-color);">
        <div style="display: flex; align-items: center; gap: 1rem; margin: 0;">
            <div style="flex: 1; position: relative; display: flex; align-items: center;">
                <i data-lucide="search" style="position: absolute; left: 1.25rem; width: 20px; height: 20px; color: var(--text-tertiary);"></i>
                <input type="text" id="ajax-search-candidates" placeholder="Rechercher un candidat par nom..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                       style="width: 100%; padding: 1rem 1rem 1rem 3.5rem; border: 1px solid var(--border-color); border-radius: 14px; font-size: 1rem; outline: none; transition: all 0.2s; background: var(--bg-secondary); color: var(--text-primary);"
                       onfocus="this.style.borderColor='var(--accent-primary)'; this.style.background='var(--bg-card)';" 
                       onblur="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-secondary)';"
                       class="search-input-field">
            </div>
            <div id="search-spinner-cand" style="display: none;">
                <div class="spinner-border text-primary" role="status" style="width: 24px; height: 24px; border: 3px solid rgba(168, 100, 228, 0.2); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
            </div>
        </div>
    </div>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

    <div class="candidate-cards-grid stagger" id="candidates-container">
      <?php foreach ($listeCandidatures as $cand): 
          $initials = strtoupper(substr($cand['prenom'], 0, 1) . substr($cand['nom'], 0, 1));
          $nomComplet = htmlspecialchars($cand['prenom'] . ' ' . $cand['nom']);
          $titreOffre = htmlspecialchars($cand['titre_offre'] ?? 'Candidature Spontanée');
          $dateCand = date('d/m/Y', strtotime($cand['date_candidature']));
          $idCand = $cand['id_candidature'];
      ?>
      <div class="candidate-card animate-on-scroll" id="candidate-<?php echo $idCand; ?>" 
           style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.5rem; transition: all 0.3s ease; display: flex; flex-direction: column; gap: 1rem; cursor: pointer;"
           onclick="openDetailsModal(event, <?php echo $idCand; ?>)">
           
        <div id="details-data-<?php echo $idCand; ?>" style="display:none;" 
             data-nom="<?php echo htmlspecialchars($nomComplet); ?>"
             data-email="<?php echo htmlspecialchars($cand['email']); ?>"
             data-offre="<?php echo htmlspecialchars($titreOffre); ?>"
             data-date="<?php echo htmlspecialchars($dateCand); ?>"
             data-statut="<?php echo htmlspecialchars($cand['statut']); ?>"
             data-question="<?php echo htmlspecialchars($cand['question_offre'] ?? 'Aucune question spécifiée.'); ?>">
             <?php echo $cand['reponses_ques']; ?>
        </div>
        <div class="candidate-card__header" style="display: flex; gap: 1rem; align-items: flex-start;">
          <div class="avatar avatar-lg" style="width: 56px; height: 56px; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.25rem; box-shadow: 0 4px 12px rgba(168, 100, 228, 0.2);">
            <?php echo $initials; ?>
          </div>
          <div class="candidate-card__info" style="flex: 1;">
            <div class="candidate-card__name" style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.2rem;"><?php echo $nomComplet; ?></div>
            <div class="candidate-card__role" style="font-size: 0.9rem; color: var(--accent-primary); font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
                <i data-lucide="briefcase" style="width: 14px; height: 14px;"></i> <?php echo $titreOffre; ?>
            </div>
            <?php 
                $note = isset($cand['note']) ? intval($cand['note']) : 0;
            ?>
            <div class="ai-score-badge" style="margin-top: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.6rem; background: <?php echo $note >= 80 ? 'rgba(16, 185, 129, 0.1)' : ($note >= 50 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(107, 114, 128, 0.1)'); ?>; color: <?php echo $note >= 80 ? '#10b981' : ($note >= 50 ? '#f59e0b' : '#6b7280'); ?>; border-radius: 6px; font-size: 0.8rem; font-weight: 700; border: 1px solid currentColor;">
                <i data-lucide="sparkles" style="width: 12px; height: 12px;"></i> Score IA: <?php echo $note > 0 ? $note . '%' : 'En attente'; ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 0.4rem;">Déposé le <?php echo $dateCand; ?></div>
          </div>
          <div>
            <span class="badge badge-<?php 
                $st = strtolower($cand['statut']);
                if (strpos($st, 'accept') !== false) echo 'success';
                elseif (strpos($st, 'refus') !== false) echo 'danger';
                else echo 'warning';
              ?>" style="padding: 0.4rem 0.8rem; border-radius: 8px;">
                <?php echo htmlspecialchars($cand['statut']); ?>
              </span>
          </div>
        </div>



        <div class="candidate-card__actions" style="display: flex; gap: 0.75rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
          <textarea id="cv-data-<?php echo $cand['id_candidature']; ?>" style="display:none;"><?php echo htmlspecialchars($cand['cv__cand'] ?? $cand['cv_cand'] ?? ''); ?></textarea>
          
          <button class="btn btn-primary" style="flex: 1; padding: 0.6rem; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onclick="event.stopPropagation(); openDetailsModal(null, <?php echo $cand['id_candidature']; ?>)">
            <i data-lucide="file-text" style="width:16px;height:16px;"></i> Voir Détails & CV
          </button>
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





<!-- Modal Unifiée de la Candidature -->
<div class="modal-overlay" id="details-modal-overlay">
  <div class="modal" style="max-width: 1200px; width: 95%; padding: 2rem; border-radius: 16px; background: var(--bg-card); box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; height: 90vh;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; flex-shrink: 0;">
      <div>
          <h2 id="det-modal-nom" style="font-size: 1.6rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.5rem 0;">Nom</h2>
          <div style="display: flex; gap: 1.25rem; color: var(--text-tertiary); font-size: 0.95rem;">
              <span style="display:flex; align-items:center; gap:0.4rem;"><i data-lucide="mail" style="width:16px;height:16px;"></i> <span id="det-modal-email"></span></span>
              <span style="display:flex; align-items:center; gap:0.4rem;"><i data-lucide="calendar" style="width:16px;height:16px;"></i> <span id="det-modal-date"></span></span>
          </div>
      </div>
      <div style="display: flex; gap: 0.75rem; align-items: center;">
          <form method="POST" action="hr_candidatures.php" id="form-shortlister" style="margin:0;">
              <input type="hidden" name="id_candidature" id="action-cand-id-1" value="">
              <input type="hidden" name="update_statut" value="Accepté">
              <button type="submit" class="btn btn-success" style="padding: 0.5rem 1rem; display:flex; align-items:center; gap:0.4rem; font-size:0.85rem; color:white; background:#10b981; border:none; border-radius:8px; cursor:pointer;" title="Shortlister">
                  <i data-lucide="check" style="width:16px;height:16px;"></i> Shortlister
              </button>
          </form>
          <form method="POST" action="hr_candidatures.php" id="form-refuser" style="margin:0;">
              <input type="hidden" name="id_candidature" id="action-cand-id-2" value="">
              <input type="hidden" name="update_statut" value="Refusé">
              <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; display:flex; align-items:center; gap:0.4rem; font-size:0.85rem; color:white; background:#ef4444; border:none; border-radius:8px; cursor:pointer;" title="Refuser">
                  <i data-lucide="x" style="width:16px;height:16px;"></i> Refuser
              </button>
          </form>

          <!-- BOUTON RAPPORT IA -->
          <button onclick="handleAiReport(currentOpenedCandidatureId)" class="btn-ai-generate">
              <i data-lucide="sparkles"></i> Rapport IA
          </button>



          <button onclick="closeDetailsModal()" style="background: none; border: none; cursor: pointer; color: var(--text-tertiary); margin-left: 0.5rem;">
            <i data-lucide="x" style="width:28px;height:28px;"></i>
          </button>
      </div>
    </div>
    
    <!-- Corps du modal (2 colonnes) -->
    <div style="display: flex; flex: 1; gap: 2rem; overflow: hidden;">
        
        <!-- Colonne Gauche : Détails et Motivations -->
        <div style="flex: 0 0 40%; display: flex; flex-direction: column; overflow-y: auto; padding-right: 0.5rem;">
            <div style="margin-bottom: 1.5rem; padding: 1.25rem; background: var(--bg-secondary); border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 1rem; flex-shrink: 0;">
                <div style="width: 40px; height: 40px; background: rgba(79, 70, 229, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="briefcase" style="width:20px;height:20px;color:var(--accent-primary);"></i>
                </div>
                <div>
                    <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-secondary); margin: 0 0 0.25rem 0; font-weight: 700; letter-spacing: 0.05em;">Offre concernée</h4>
                    <div id="det-modal-offre" style="font-size: 1.15rem; color: var(--text-primary); font-weight: 700;"></div>
                </div>
            </div>
            
            <div style="flex: 1; display: flex; flex-direction: column;">
                <h4 style="font-size: 1.05rem; color: var(--text-primary); margin: 0 0 1rem 0; font-weight: 700; display:flex; align-items:center; gap:0.5rem; flex-shrink: 0;">
                    <i data-lucide="message-square" style="width:20px;height:20px;color:var(--accent-primary);"></i> Motivations & Réponses
                </h4>
                <div id="det-modal-question" style="padding: 1rem 1.5rem; background: rgba(79, 70, 229, 0.05); border-left: 4px solid var(--accent-primary); border-radius: 0 8px 8px 0; color: var(--text-primary); font-weight: 600; font-style: italic; margin-bottom: 1rem; font-size: 0.95rem; flex-shrink: 0;">
                    <!-- Question -->
                </div>
                <div id="det-modal-reponses" style="padding: 1.5rem; background: var(--bg-body); border-radius: 12px; color: var(--text-secondary); line-height: 1.7; border: 1px solid var(--border-color); flex: 1; overflow-y: auto;">
                    <!-- Content -->
                </div>
            </div>
        </div>

        <!-- Colonne Droite : CV -->
        <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-shrink: 0;">
                <h4 style="font-size: 1.05rem; color: var(--text-primary); margin: 0; font-weight: 700; display:flex; align-items:center; gap:0.5rem;">
                    <i data-lucide="file-text" style="width:20px;height:20px;color:var(--accent-primary);"></i> Curriculum Vitae
                </h4>
            </div>
            <div style="flex: 1; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; background: #fff; position: relative; display: flex; flex-direction: column;" id="cv-container">
                <iframe id="cv-viewer-iframe" style="width: 100%; height: 100%; flex: 1; border: none; display: block;"></iframe>
            </div>
        </div>
        
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
/* --- AI REPORT ADAPTIVE DESIGN --- */
:root {
    --ai-report-bg: rgba(255, 255, 255, 0.4);
    --ai-report-text: #334155;
    --ai-report-header-text: #0f172a;
    --ai-report-card-bg: #ffffff;
    --ai-report-border: rgba(0, 0, 0, 0.08);
    --ai-report-section-bg: rgba(248, 250, 252, 0.8);
    --ai-report-content-bg: #ffffff;
    --ai-report-badge-blue-bg: rgba(14, 165, 233, 0.15);
    --ai-report-badge-blue-text: #0284c7;
}

/* Support Mode Sombre */
body.dark-mode, [data-theme="dark"], .dark-theme {
    --ai-report-bg: rgba(10, 15, 28, 0.6);
    --ai-report-text: #f8fafc;
    --ai-report-header-text: #ffffff;
    --ai-report-card-bg: rgba(255, 255, 255, 0.05);
    --ai-report-border: rgba(255, 255, 255, 0.1);
    --ai-report-section-bg: rgba(255, 255, 255, 0.03);
    --ai-report-content-bg: rgba(0,0,0,0.3);
    --ai-report-badge-blue-bg: rgba(79, 181, 255, 0.2);
    --ai-report-badge-blue-text: #4fb5ff;
}

.ai-report-modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: var(--ai-report-bg);
    backdrop-filter: blur(12px);
    display: none;
    align-items: center; justify-content: center;
    z-index: 9999; padding: 20px;
}

.ai-report-card {
    background: var(--ai-report-card-bg);
    border: 1px solid var(--ai-report-border);
    color: var(--ai-report-text);
    border-radius: 24px;
    width: 100%; max-width: 800px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideIn {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.ai-report-header {
    padding: 25px 30px;
    background: linear-gradient(135deg, rgba(79, 181, 255, 0.12) 0%, rgba(168, 100, 228, 0.12) 100%);
    border-bottom: 1px solid var(--ai-report-border);
    display: flex; justify-content: space-between; align-items: center;
    color: var(--ai-report-header-text);
    position: relative;
    overflow: hidden;
}

.ai-report-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: linear-gradient(90deg, #4fb5ff, #a864e4, #4fb5ff);
    background-size: 200% auto;
    animation: shine 3s linear infinite;
}

.ai-report-body { padding: 30px; line-height: 1.9; }

.report-content {
    background: var(--ai-report-content-bg);
    padding: 30px; border-radius: 24px;
    border: 1px solid var(--ai-report-border);
    line-height: 1.9; color: var(--ai-report-text);
    font-size: 1.05rem;
    font-weight: 400;
}

.report-card-section {
    background: var(--ai-report-section-bg);
    border-left: 6px solid #4fb5ff;
    padding: 20px 25px;
    margin-bottom: 25px;
    border-radius: 0 16px 16px 0;
}

.report-card-section.warning { border-left-color: #fbbf24; background: rgba(251, 191, 36, 0.05); }
.report-card-section.success { border-left-color: #34d399; background: rgba(52, 211, 153, 0.05); }

.ai-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 16px; border-radius: 12px;
    font-size: 0.85rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.1em;
    background: var(--ai-report-badge-blue-bg); 
    color: var(--ai-report-badge-blue-text);
    margin-bottom: 15px;
}

/* LOADER AI SCANNER */
.ai-loader {
    display: none;
    flex-direction: column; align-items: center; gap: 25px;
    padding: 40px 0;
}

.ai-scanner {
    position: relative;
    width: 80px; height: 80px;
    border-radius: 16px;
    background: rgba(79, 181, 255, 0.05);
    border: 2px solid rgba(79, 181, 255, 0.2);
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    box-shadow: inset 0 0 20px rgba(79, 181, 255, 0.1);
}

.ai-scanner i {
    color: rgba(79, 181, 255, 0.4);
    width: 40px; height: 40px;
}

.ai-scanner::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: #4fb5ff;
    box-shadow: 0 0 15px #4fb5ff, 0 0 30px #a864e4;
    animation: scan 2s cubic-bezier(0.4, 0, 0.2, 1) infinite alternate;
}

@keyframes scan {
    0% { top: 0; opacity: 0.5; }
    50% { opacity: 1; }
    100% { top: 100%; opacity: 0.5; }
}

.ai-loading-text {
    background: linear-gradient(90deg, #4fb5ff, #a864e4, #4fb5ff);
    background-size: 200% auto;
    color: transparent;
    -webkit-background-clip: text;
    background-clip: text;
    font-weight: 700;
    font-size: 1.1rem;
    animation: shine 2s linear infinite;
    letter-spacing: 0.05em;
    text-align: center;
}

@keyframes shine {
    to { background-position: 200% center; }
}

.btn-ai-generate {
    background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%);
    color: white; border: none;
    padding: 12px 24px; border-radius: 12px;
    display: flex; align-items: center; gap: 10px;
    font-weight: 600; cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 20px -5px rgba(168, 100, 228, 0.4);
}

.btn-ai-generate:hover { transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(168, 100, 228, 0.6); }
.btn-ai-generate:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
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

// ═══ DETAILS MODAL LOGIC (UNIFIED) ═══
let currentOpenedCandidatureId = null;

// Fonction pour générer ou voir le rapport IA
function handleAiReport(id) {
    currentOpenedCandidatureId = id;
    const modal = document.getElementById('ai-report-modal-overlay');
    const content = document.getElementById('ai-report-content-area');
    const loader = document.getElementById('ai-report-loader');
    
    modal.style.display = 'flex';
    content.style.display = 'none';
    loader.style.display = 'flex';

    // Appel AJAX
    fetch('ajax_generate_report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_candidature=${id}`
    })
    .then(response => response.json())
    .then(data => {
        loader.style.display = 'none';
        if (data.status === 'success') {
            loader.style.display = 'none';
            content.style.display = 'block';
            
            // Formatage intelligent du texte
            let formattedReport = data.report
                .replace(/💎/g, '<div class="report-card-section success"><span class="ai-badge" style="background:rgba(16,185,129,0.2);color:#10b981">Points Forts</span><br>')
                .replace(/⚠️/g, '</div><div class="report-card-section warning"><span class="ai-badge" style="background:rgba(245,158,11,0.2);color:#f59e0b">Vigilance</span><br>')
                .replace(/🚀/g, '</div><div class="report-card-section"><span class="ai-badge">Impression</span><br>')
                .replace(/\n/g, '<br>');
            
            formattedReport += '</div>'; // Fermeture de la dernière section
            
            document.getElementById('report-text-container').innerHTML = formattedReport;
        } else {
            alert("Erreur: " + data.message);
            modal.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Une erreur est survenue lors de la génération.");
        modal.style.display = 'none';
    });
}

function closeAiReportModal() {
    document.getElementById('ai-report-modal-overlay').style.display = 'none';
}

function openDetailsModal(event, id) {
    currentOpenedCandidatureId = id;
    if (event && event.target.closest('button') && !event.target.closest('.btn-primary')) {
        // Ignore clicks on reject/accept buttons, but allow clicks on the row or "Voir CV" button
        return;
    }
    
    const dataDiv = document.getElementById('details-data-' + id);
    if (!dataDiv) return;
    
    const nom = dataDiv.getAttribute('data-nom');
    const email = dataDiv.getAttribute('data-email');
    const offre = dataDiv.getAttribute('data-offre');
    const date = dataDiv.getAttribute('data-date');
    const statut = dataDiv.getAttribute('data-statut');
    const question = dataDiv.getAttribute('data-question');
    const reponses = dataDiv.innerHTML;
    
    document.getElementById('det-modal-nom').innerText = nom;
    document.getElementById('det-modal-email').innerText = email;
    document.getElementById('det-modal-offre').innerText = offre;
    document.getElementById('det-modal-date').innerText = "Déposée le " + date;
    
    const questionContainer = document.getElementById('det-modal-question');
    questionContainer.innerText = question;
    
    const reponsesContainer = document.getElementById('det-modal-reponses');
    if (reponses.trim() === '') {
        reponsesContainer.innerHTML = "<em style='color:var(--text-tertiary);'>Aucune motivation fournie.</em>";
    } else {
        reponsesContainer.innerHTML = reponses;
    }

    // Load CV
    const cvInput = document.getElementById('cv-data-' + id);
    const iframe = document.getElementById('cv-viewer-iframe');
    
    if (cvInput && cvInput.value) {
        iframe.src = cvInput.value;
    } else {
        iframe.src = "";
        iframe.contentDocument?.write("<body style='font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100%;color:#888;'>Aucun CV disponible</body>");
    }
    
    const overlay = document.getElementById('details-modal-overlay');
    overlay.classList.add('active');
    const modal = overlay.querySelector('.modal');
    if (modal) modal.classList.add('active');
    
    // Mettre à jour les formulaires d'action avec l'ID de la candidature
    const btnShort = document.getElementById('form-shortlister').querySelector('button');
    const btnRefuse = document.getElementById('form-refuser').querySelector('button');
    document.getElementById('action-cand-id-1').value = id;
    document.getElementById('action-cand-id-2').value = id;

    // Bloquer les boutons si déjà traité
    const statutNormalised = (statut || "").trim().toLowerCase();
    const isAlreadyProcessed = statutNormalised.includes('accept') || statutNormalised.includes('refus');

    if (isAlreadyProcessed) {
        btnShort.disabled = true;
        btnRefuse.disabled = true;
        btnShort.style.opacity = '0.5';
        btnRefuse.style.opacity = '0.5';
        btnShort.style.cursor = 'not-allowed';
        btnRefuse.style.cursor = 'not-allowed';
        btnShort.title = "Déjà traité (" + statut + ")";
        btnRefuse.title = "Déjà traité (" + statut + ")";
    } else {
        btnShort.disabled = false;
        btnRefuse.disabled = false;
        btnShort.style.opacity = '1';
        btnRefuse.style.opacity = '1';
        btnShort.style.cursor = 'pointer';
        btnRefuse.style.cursor = 'pointer';
        btnShort.title = "Shortlister";
        btnRefuse.title = "Refuser";
    }
    
    if (window.lucide) lucide.createIcons();
}

function closeDetailsModal() {
    const overlay = document.getElementById('details-modal-overlay');
    overlay.classList.remove('active');
    const modal = overlay.querySelector('.modal');
    if (modal) modal.classList.remove('active');
    document.getElementById('cv-viewer-iframe').src = "";
}

// ═══ DYNAMIC AJAX SEARCH (CANDIDATES) ═══
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('ajax-search-candidates');
    const container = document.getElementById('candidates-container');
    const spinner = document.getElementById('search-spinner-cand');
    let timeout = null;

    if (searchInput && container) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            if (spinner) spinner.style.display = 'block';
            
            timeout = setTimeout(() => {
                const query = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('q', query);
                
                fetch(url.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.getElementById('candidates-container');
                        const newCount = doc.querySelector('.results-info');
                        
                        if (newContent) {
                            container.innerHTML = newContent.innerHTML;
                            if (localStorage.getItem('hr_candidates_view_mode') === 'list') {
                                container.classList.add('view-list');
                            }
                            if (window.lucide) lucide.createIcons();
                        }
                        
                        if (newCount) {
                            document.querySelector('.results-info').innerHTML = newCount.innerHTML;
                        }
                        
                        window.history.replaceState({}, '', url.href);
                        if (spinner) spinner.style.display = 'none';
                    })
                    .catch(err => {
                        console.error('Search error:', err);
                        if (spinner) spinner.style.display = 'none';
                    });
            }, 300);
        });
    }

    const savedMode = localStorage.getItem('hr_candidates_view_mode');
    if (savedMode === 'list') {
        setViewMode('list');
    }
    
    // Fermeture du modal Détails au clic à l'extérieur
    const detailsOverlay = document.getElementById('details-modal-overlay');
    if (detailsOverlay) {
        detailsOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetailsModal();
            }
        });
    }
});
</script>


<!-- MODAL RAPPORT IA PREMIUM -->
<div id="ai-report-modal-overlay" class="ai-report-modal">
    <div class="ai-report-card">
        <div class="ai-report-header">
            <h3 style="display:flex; align-items:center; gap:10px; margin:0; color: inherit;">
                <i data-lucide="sparkles" style="color:#4fb5ff"></i>
                Rapport d'Analyse IA Aptus
            </h3>
            <button onclick="closeAiReportModal()" class="btn-ghost" style="border-radius:50%; width:40px; height:40px; color: inherit;">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="ai-report-body">
            <!-- LOADER -->
            <div id="ai-report-loader" class="ai-loader">
                <div class="ai-scanner">
                    <i data-lucide="cpu"></i>
                </div>
                <p class="ai-loading-text">Analyse experte en cours par Llama 3.3...</p>
            </div>

            <!-- CONTENT -->
            <div id="ai-report-content-area" style="display:none;">
                <div class="report-section">
                    <h4><i data-lucide="file-text"></i> Rapport de Synthèse</h4>
                    <div id="report-text-container" class="report-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>