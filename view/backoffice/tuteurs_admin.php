<?php
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();
$pageTitle = "Gestion des Tuteurs";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/TuteurController.php';

    $tuteurC = new TuteurController();
    $tuteurs  = $tuteurC->listerTuteurs();

    // Stats globales
    $nbTuteurs    = count($tuteurs);
    $nbFormations = array_sum(array_column($tuteurs, 'nb_formations'));
    $nbEtudiants  = array_sum(array_column($tuteurs, 'nb_etudiants'));

    $content = __FILE__;
    include 'layout_back.php';
    exit();
}
?>

<!-- =====================================================
     STYLES — Teams-style People Grid
     ===================================================== -->
<style>
/* ── En-tête de page ── */
.tuteurs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}
.tuteurs-header__left h1 {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 0.25rem;
}
.tuteurs-header__left p {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin: 0;
}

/* ── Stats bar ── */
.tuteurs-stats {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}
.tuteurs-stat {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 1.1rem 1.5rem;
    min-width: 160px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform .2s, box-shadow .2s;
}
.tuteurs-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}
.tuteurs-stat__icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.tuteurs-stat__icon.purple { background: rgba(139,92,246,0.12); }
.tuteurs-stat__icon.blue   { background: rgba(59,130,246,0.12); }
.tuteurs-stat__icon.teal   { background: rgba(20,184,166,0.12); }
.tuteurs-stat__val  { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.tuteurs-stat__label{ font-size: 0.77rem; color: var(--text-secondary,#64748b); margin-top: 0.2rem; }

/* ── Barre de recherche + filtre ── */
.tuteurs-toolbar {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}
.tuteurs-search {
    flex: 1;
    min-width: 220px;
    max-width: 400px;
    position: relative;
}
.tuteurs-search input {
    width: 100%;
    padding: 0.65rem 0.75rem 0.65rem 2.4rem;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.9rem;
    background: var(--bg-card);
    color: var(--text-primary);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.tuteurs-search input:focus {
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px var(--accent-primary-light);
}
.tuteurs-search__icon {
    position: absolute;
    left: 0.8rem; top: 50%;
    transform: translateY(-50%);
    opacity: 0.4;
    font-size: 0.95rem;
    pointer-events: none;
}

/* ── Grille des cartes (Teams-style) ── */
.tuteurs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.25rem;
}

/* ── Carte Tuteur ── */
.tuteur-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: transform .22s ease, box-shadow .22s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    animation: cardIn 0.35s ease both;
}
.tuteur-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 36px rgba(0,0,0,0.1);
}
@keyframes cardIn {
    from { opacity: 0; transform: scale(.96) translateY(8px); }
    to   { opacity: 1; transform: scale(1)   translateY(0); }
}

/* Bandeau coloré en haut (Teams "cover") */
.tuteur-card__cover {
    height: 72px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
    position: relative;
    flex-shrink: 0;
}
/* Couleurs variées selon index */
.tuteur-card__cover.c0  { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.tuteur-card__cover.c1  { background: linear-gradient(135deg, #0ea5e9, #06b6d4); }
.tuteur-card__cover.c2  { background: linear-gradient(135deg, #10b981, #059669); }
.tuteur-card__cover.c3  { background: linear-gradient(135deg, #f59e0b, #ef4444); }
.tuteur-card__cover.c4  { background: linear-gradient(135deg, #ec4899, #8b5cf6); }
.tuteur-card__cover.c5  { background: linear-gradient(135deg, #14b8a6, #0ea5e9); }

/* Avatar centré sur le bandeau */
.tuteur-card__avatar {
    position: absolute;
    bottom: -22px;
    left: 50%;
    transform: translateX(-50%);
    width: 52px; height: 52px;
    border-radius: 50%;
    border: 3px solid var(--bg-card);
    background: var(--gradient-primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    font-weight: 800;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    letter-spacing: 0;
    z-index: 2;
}

/* Corps de la carte */
.tuteur-card__body {
    padding: 2.2rem 1.25rem 1.25rem;
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.tuteur-card__name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tuteur-card__specialite {
    font-size: 0.78rem;
    color: var(--accent-primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tuteur-card__email {
    font-size: 0.75rem;
    color: var(--text-secondary, #64748b);
    margin-bottom: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tuteur-card__bio {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

/* Stats mini (formations / étudiants) */
.tuteur-card__stats {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    padding: 0.75rem 0;
    border-top: 1px solid var(--border-color, #f1f5f9);
    border-bottom: 1px solid var(--border-color, #f1f5f9);
    margin-bottom: 1rem;
}
.tuteur-card__stat-item { text-align: center; }
.tuteur-card__stat-val  { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); }
.tuteur-card__stat-key  { font-size: 0.7rem; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: .05em; }

/* Actions */
.tuteur-card__actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}
.tuteur-btn {
    flex: 1;
    padding: 0.45rem 0.5rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 0.78rem;
    font-weight: 600;
    transition: background .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: 0.35rem;
}
.tuteur-btn:hover { transform: scale(1.03); }
.tuteur-btn--message {
    background: rgba(99,102,241,.1);
    color: #6366f1;
}
.tuteur-btn--delete {
    background: rgba(239,68,68,.08);
    color: #ef4444;
}
.tuteur-btn--message:hover { background: rgba(99,102,241,.18); }
.tuteur-btn--delete:hover  { background: rgba(239,68,68,.15); }

/* Pastille "online" fictive (Teams) */
.online-dot {
    position: absolute;
    top: 0.75rem; right: 0.75rem;
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #10b981;
    border: 2px solid white;
    box-shadow: 0 0 0 3px rgba(16,185,129,.2);
}

/* ── Carte "Ajouter un tuteur" ── */
.tuteur-card--add {
    border: 2px dashed var(--border-color);
    background: transparent;
    box-shadow: none;
    cursor: pointer;
    min-height: 260px;
    justify-content: center;
    align-items: center;
    transition: border-color .2s, background .2s;
}
.tuteur-card--add:hover {
    border-color: #6366f1;
    background: rgba(99,102,241,.04);
    transform: none;
    box-shadow: none;
}
.tuteur-card--add__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 2rem;
    color: var(--text-secondary, #64748b);
}
.tuteur-card--add__icon {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: rgba(99,102,241,.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    transition: background .2s, transform .2s;
}
.tuteur-card--add:hover .tuteur-card--add__icon {
    background: rgba(99,102,241,.2);
    transform: scale(1.1);
}
.tuteur-card--add p { font-size: 0.9rem; font-weight: 600; color: #6366f1; margin: 0; }
.tuteur-card--add span { font-size: 0.78rem; color: #94a3b8; }

/* ── Modal d'ajout ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,.45);
    backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    opacity: 0; pointer-events: none;
    transition: opacity .3s;
}
.modal-overlay.open {
    opacity: 1; pointer-events: all;
}
.modal-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 22px;
    padding: 2rem;
    width: 100%;
    max-width: 500px;
    box-shadow: var(--shadow-2xl);
    transform: scale(.95) translateY(20px);
    transition: transform .3s ease, opacity .3s ease;
}
.modal-overlay.open .modal-box {
    transform: scale(1) translateY(0);
}
.modal-box h2 {
    font-size: 1.25rem;
    font-weight: 800;
    margin-bottom: 0.35rem;
    color: var(--text-primary);
}
.modal-box .modal-sub {
    font-size: 0.83rem; color: var(--text-secondary); margin-bottom: 1.75rem;
}
.modal-field { margin-bottom: 1.1rem; }
.modal-field label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.4rem;
}
.modal-field label .req { color: #ef4444; }
.modal-field input,
.modal-field textarea {
    width: 100%;
    padding: 0.65rem 0.9rem;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.88rem;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
    background: var(--bg-secondary);
    color: var(--text-primary);
    outline: none;
    box-sizing: border-box;
}
.modal-field input:focus,
.modal-field textarea:focus {
    border-color: var(--accent-primary);
    background: var(--bg-card);
    box-shadow: 0 0 0 3px var(--accent-primary-light);
}
.modal-field textarea { resize: vertical; min-height: 80px; }
.modal-actions {
    display: flex; gap: 0.75rem; justify-content: flex-end;
    margin-top: 1.5rem;
}
.btn-modal-cancel {
    padding: 0.65rem 1.5rem;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    border: none; cursor: pointer; font-weight: 600; font-size: .88rem;
    transition: background .2s;
}
.btn-modal-cancel:hover { background: #e2e8f0; }
.btn-modal-submit {
    padding: 0.65rem 1.75rem;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border: none; cursor: pointer; font-weight: 700; font-size: .88rem;
    box-shadow: 0 4px 14px rgba(99,102,241,.3);
    transition: opacity .2s, transform .15s;
    display: flex; align-items: center; gap: .5rem;
}
.btn-modal-submit:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
.btn-modal-submit:disabled { opacity: .6; cursor: not-allowed; }

/* ── Vide ── */
.tuteurs-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    opacity: .5;
}

/* ── Validation champs ── */
.iv-field.is-valid   { border-color: var(--accent-secondary) !important; background: var(--accent-secondary-light); }
.iv-field.is-invalid { border-color: var(--accent-tertiary) !important; background: var(--accent-tertiary-light); }
.iv-status.valid  { color: var(--accent-secondary); display:inline-flex !important; }
.iv-status.invalid{ color: var(--accent-tertiary); display:inline-flex !important; }
.iv-msg           { display:none; }
.iv-msg.show      { display:block !important; }
</style>

<!-- =====================================================
     CONTENU
     ===================================================== -->

<!-- En-tête -->
<div class="tuteurs-header">
    <div class="tuteurs-header__left">
        <h1>👨‍🏫 Gestion des Tuteurs</h1>
        <p><?php echo $nbTuteurs; ?> tuteur<?php echo $nbTuteurs > 1 ? 's' : ''; ?> enregistré<?php echo $nbTuteurs > 1 ? 's' : ''; ?> sur la plateforme</p>
    </div>
    <button class="btn btn-primary" id="btn-open-modal" onclick="openModal()" style="display:flex;align-items:center;gap:.5rem;">
        <i data-lucide="user-plus" style="width:17px;height:17px;"></i>
        Ajouter un tuteur
    </button>
</div>

<!-- Stats -->
<div class="tuteurs-stats">
    <div class="tuteurs-stat">
        <div class="tuteurs-stat__icon purple">👨‍🏫</div>
        <div>
            <div class="tuteurs-stat__val"><?php echo $nbTuteurs; ?></div>
            <div class="tuteurs-stat__label">Tuteurs actifs</div>
        </div>
    </div>
    <div class="tuteurs-stat">
        <div class="tuteurs-stat__icon blue">🎓</div>
        <div>
            <div class="tuteurs-stat__val"><?php echo $nbFormations; ?></div>
            <div class="tuteurs-stat__label">Formations animées</div>
        </div>
    </div>
    <div class="tuteurs-stat">
        <div class="tuteurs-stat__icon teal">👩‍💻</div>
        <div>
            <div class="tuteurs-stat__val"><?php echo $nbEtudiants; ?></div>
            <div class="tuteurs-stat__label">Étudiants formés</div>
        </div>
    </div>
</div>

<!-- Toolbar -->
<div class="tuteurs-toolbar">
    <div class="tuteurs-search">
        <span class="tuteurs-search__icon">🔍</span>
        <input type="text" id="search-tuteurs" placeholder="Rechercher un tuteur..." oninput="filterTuteurs(this.value)">
    </div>
    <select id="filter-specialite" class="select" style="max-width:200px;" onchange="filterTuteurs(document.getElementById('search-tuteurs').value)">
        <option value="">Toutes les spécialités</option>
        <?php
        $specs = array_unique(array_filter(array_column($tuteurs, 'specialite')));
        sort($specs);
        foreach ($specs as $s): ?>
            <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
        <?php endforeach; ?>
    </select>
    <span id="count-label" style="font-size:.82rem;color:var(--text-secondary);margin-left:auto;">
        <?php echo $nbTuteurs; ?> tuteur<?php echo $nbTuteurs > 1 ? 's' : ''; ?>
    </span>
</div>

<!-- Grille des tuteurs -->
<div class="tuteurs-grid" id="tuteurs-grid">

    <!-- Carte "Ajouter" (Teams-style) -->
    <div class="tuteur-card tuteur-card--add" onclick="openModal()" id="add-card">
        <div class="tuteur-card--add__inner">
            <div class="tuteur-card--add__icon">➕</div>
            <p>Ajouter un tuteur</p>
            <span>Invitez un expert à rejoindre la plateforme</span>
        </div>
    </div>

    <?php if (empty($tuteurs)): ?>
        <div class="tuteurs-empty">
            <div style="font-size:3.5rem;margin-bottom:1rem;">👨‍🏫</div>
            <p style="font-size:1rem;font-weight:600;">Aucun tuteur enregistré</p>
            <p style="font-size:.85rem;">Ajoutez votre premier tuteur via le bouton ci-dessus.</p>
        </div>
    <?php else: ?>
        <?php $colors = ['c0','c1','c2','c3','c4','c5']; ?>
        <?php foreach ($tuteurs as $i => $t): ?>
        <div class="tuteur-card" data-name="<?php echo strtolower(htmlspecialchars($t['nom'])); ?>"
             data-email="<?php echo strtolower(htmlspecialchars($t['email'])); ?>"
             data-specialite="<?php echo strtolower(htmlspecialchars($t['specialite'])); ?>"
             style="animation-delay: <?php echo ($i % 12) * 0.05; ?>s;">

            <!-- Online dot (décoratif Teams) -->
            <div class="online-dot"></div>

            <!-- Cover + Avatar -->
            <div class="tuteur-card__cover <?php echo $colors[$i % 6]; ?>">
                <div class="tuteur-card__avatar">
                    <?php echo strtoupper(substr($t['nom'], 0, 2)); ?>
                </div>
            </div>

            <!-- Corps -->
            <div class="tuteur-card__body">
                <div class="tuteur-card__name" title="<?php echo htmlspecialchars($t['nom']); ?>">
                    <?php echo htmlspecialchars($t['nom']); ?>
                </div>
                <?php if (!empty($t['specialite'])): ?>
                <div class="tuteur-card__specialite">
                    <?php echo htmlspecialchars($t['specialite']); ?>
                </div>
                <?php endif; ?>
                <div class="tuteur-card__email" title="<?php echo htmlspecialchars($t['email']); ?>">
                    <?php echo htmlspecialchars($t['email']); ?>
                </div>
                <?php if (!empty($t['bio'])): ?>
                <div class="tuteur-card__bio">
                    <?php echo htmlspecialchars($t['bio']); ?>
                </div>
                <?php else: ?>
                <div class="tuteur-card__bio" style="color:#cbd5e1;font-style:italic;">
                    Aucune biographie renseignée.
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="tuteur-card__stats">
                    <div class="tuteur-card__stat-item">
                        <div class="tuteur-card__stat-val"><?php echo $t['nb_formations']; ?></div>
                        <div class="tuteur-card__stat-key">Formations</div>
                    </div>
                    <div class="tuteur-card__stat-item">
                        <div class="tuteur-card__stat-val"><?php echo $t['nb_etudiants']; ?></div>
                        <div class="tuteur-card__stat-key">Étudiants</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tuteur-card__actions">
                    <button class="tuteur-btn tuteur-btn--message"
                            onclick="contactTuteur('<?php echo htmlspecialchars($t['email']); ?>', '<?php echo htmlspecialchars($t['nom']); ?>')">
                        ✉️ Contacter
                    </button>
                    <button class="tuteur-btn tuteur-btn--delete"
                            onclick="supprimerTuteur(<?php echo $t['id']; ?>, '<?php echo addslashes(htmlspecialchars($t['nom'])); ?>')">
                        🗑 Retirer
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- =====================================================
     MODAL : Ajouter un tuteur
     ===================================================== -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModalOnBackdrop(event)">
    <div class="modal" style="max-width:500px;" onclick="event.stopPropagation();">
        <div class="modal-header">
            <h3>➕ Ajouter un tuteur</h3>
            <button class="modal-close btn-icon" type="button" onclick="closeModal()"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>
        <div class="modal-body">
            <p class="text-sm text-secondary" style="margin-bottom:1.5rem;">
                Créez un nouveau compte tuteur ou promouvez un utilisateur existant.<br>
                Un accès temporaire lui sera généré automatiquement.
            </p>

            <form id="add-tuteur-form" class="auth-form" novalidate>
                <div class="form-group">
                    <label class="form-label" for="t-nom">Nom complet <span class="required-star">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <span class="iv-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);pointer-events:none;"><i data-lucide="user" style="width:16px;height:16px;"></i></span>
                        <input type="text" class="input iv-field" id="t-nom" name="nom" placeholder="Ex : Dr. Sami Ouali" data-min="2" data-label="Nom" style="padding-left:36px;">
                        <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                    <span class="iv-msg" id="t-nom-msg" style="display:none;font-size:.78rem;color:var(--accent-tertiary);margin-top:4px;font-weight:600;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="t-email">Adresse email <span class="required-star">*</span></label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <span class="iv-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);pointer-events:none;"><i data-lucide="mail" style="width:16px;height:16px;"></i></span>
                        <input type="email" class="input iv-field" id="t-email" name="email" placeholder="tuteur@email.com" data-type="email" data-label="Email" style="padding-left:36px;">
                        <span class="iv-status" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);display:none;"></span>
                    </div>
                    <span class="iv-msg" id="t-email-msg" style="display:none;font-size:.78rem;color:var(--accent-tertiary);margin-top:4px;font-weight:600;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="t-specialite">Spécialité / Domaine</label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <span class="iv-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);pointer-events:none;"><i data-lucide="briefcase" style="width:16px;height:16px;"></i></span>
                        <input type="text" class="input iv-field" id="t-specialite" name="specialite" placeholder="Ex : Développement Web, Data Science..." data-min="0" style="padding-left:36px;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="t-bio">Biographie courte</label>
                    <div class="input-validated-wrap" style="position:relative;">
                        <textarea class="textarea iv-field" id="t-bio" name="bio" placeholder="Présentez brièvement l'expertise du tuteur..." rows="3" data-min="0"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
            <button class="btn btn-primary" id="btn-submit-tuteur" onclick="submitTuteur()">
                <span id="submit-btn-icon">✅</span>
                <span id="submit-btn-text">Créer le tuteur</span>
            </button>
        </div>
    </div>
</div>

<!-- =====================================================
     JAVASCRIPT
     ===================================================== -->
<script>
// ── Validation inline ───────────────────────────────────────
function ivValidate(input) {
    const wrap     = input.closest('.input-validated-wrap');
    const statusEl = wrap ? wrap.querySelector('.iv-status') : null;
    const msgEl    = document.getElementById(input.id + '-msg');
    const min      = parseInt(input.dataset.min || 0);
    const label    = input.dataset.label || 'Ce champ';
    const type     = input.dataset.type || 'text';
    const val      = input.value.trim();
    let valid = true;
    let errorMsg = '';

    if (min > 0 && val.length === 0) {
        valid = false;
        errorMsg = `${label} est requis.`;
    } else if (val.length > 0 && val.length < min) {
        valid = false;
        errorMsg = `Trop court (min. ${min} caractères).`;
    } else if (type === 'email' && val.length > 0) {
        const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        if (!emailRegex.test(val)) {
            valid = false;
            errorMsg = `Email invalide.`;
        }
    }

    input.classList.toggle('is-valid',   valid && val.length > 0);
    input.classList.toggle('is-invalid', !valid);

    if (statusEl) {
        const hasValue = val !== '';
        const isDirty = input.classList.contains('is-dirty');

        if (hasValue || isDirty) {
            statusEl.className = 'iv-status ' + (valid ? 'valid' : 'invalid');
            statusEl.style.display = 'inline-flex';
            statusEl.innerHTML = valid
                ? '<i data-lucide="check" style="width:14px;height:14px;"></i>'
                : '<i data-lucide="alert-circle" style="width:14px;height:14px;"></i>';
            if (window.lucide) lucide.createIcons();
        } else {
            statusEl.style.display = 'none';
        }
    }
    
    if (msgEl) {
        if (!valid) {
            msgEl.textContent = errorMsg;
            msgEl.style.display = 'block';
        } else {
            msgEl.textContent = '';
            msgEl.style.display = 'none';
        }
    }
    return valid;
}

document.querySelectorAll('.iv-field').forEach(input => {
    ['input', 'blur', 'change'].forEach(ev => {
        input.addEventListener(ev, () => {
            if (ev === 'blur' || ev === 'change') input.classList.add('is-dirty');
            ivValidate(input);
        });
    });
});

// ── Modal ──────────────────────────────────────────────────
function openModal() {
    document.getElementById('modal-overlay').classList.add('active');
    document.getElementById('modal-overlay').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
    setTimeout(() => document.getElementById('t-nom').focus(), 100);
}
function closeModal() {
    document.getElementById('modal-overlay').classList.remove('active');
    setTimeout(() => {
        document.getElementById('modal-overlay').style.display = 'none';
        resetModalForm();
    }, 200);
}
function closeModalOnBackdrop(e) {
    if (e.target === document.getElementById('modal-overlay')) closeModal();
}
function resetModalForm() {
    ['t-nom','t-email','t-specialite','t-bio'].forEach(id => {
        const el = document.getElementById(id);
        el.value = '';
        el.classList.remove('is-valid', 'is-invalid', 'is-dirty');
        const msg = document.getElementById(id + '-msg');
        if(msg) msg.style.display = 'none';
        const wrap = el.closest('.input-validated-wrap');
        const status = wrap ? wrap.querySelector('.iv-status') : null;
        if(status) status.style.display = 'none';
    });
    const btn = document.getElementById('btn-submit-tuteur');
    btn.disabled = false;
    document.getElementById('submit-btn-icon').textContent = '✅';
    document.getElementById('submit-btn-text').textContent = 'Créer le tuteur';
}
// Fermer avec Échap
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// ── Soumettre le formulaire AJAX ────────────────────────────
function submitTuteur() {
    const nomEl = document.getElementById('t-nom');
    const emailEl = document.getElementById('t-email');
    nomEl.classList.add('is-dirty');
    emailEl.classList.add('is-dirty');

    const isNomValid = ivValidate(nomEl);
    const isEmailValid = ivValidate(emailEl);

    if (!isNomValid || !isEmailValid) {
        if (!isNomValid) nomEl.focus();
        else emailEl.focus();
        return;
    }

    const nom        = nomEl.value.trim();
    const email      = emailEl.value.trim();
    const specialite = document.getElementById('t-specialite').value.trim();
    const bio        = document.getElementById('t-bio').value.trim();

    // Feedback de chargement
    const btn = document.getElementById('btn-submit-tuteur');
    btn.disabled = true;
    document.getElementById('submit-btn-icon').textContent = '⏳';
    document.getElementById('submit-btn-text').textContent = 'Création en cours...';

    const formData = new FormData();
    formData.append('action',     'add_tuteur');
    formData.append('nom',        nom);
    formData.append('email',      email);
    formData.append('specialite', specialite);
    formData.append('bio',        bio);

    fetch('../../controller/ajax_tuteur.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            closeModal();
            if (data.success) {
                if (typeof Toast !== 'undefined') Toast.fire({ icon: 'success', title: data.message });
                else aptusAlert(data.message, 'success');
                // Rechargement de la page pour afficher la nouvelle carte
                setTimeout(() => location.reload(), 1200);
            } else {
                if (typeof Toast !== 'undefined') Toast.fire({ icon: 'error', title: data.message });
                else aptusAlert(data.message, 'error');
                btn.disabled = false;
                document.getElementById('submit-btn-icon').textContent = '✅';
                document.getElementById('submit-btn-text').textContent = 'Créer le tuteur';
            }
        })
        .catch(err => {
            closeModal();
            if (typeof Toast !== 'undefined') Toast.fire({ icon: 'error', title: 'Erreur réseau : ' + err.message });
            else aptusAlert('Erreur réseau : ' + err.message, 'error');
        });
}

// ── Supprimer un tuteur ────────────────────────────────────
function supprimerTuteur(id, nom) {
    aptusConfirmDelete(() => {
        const fd = new FormData();
        fd.append('action', 'delete_tuteur');
        fd.append('id', id);

        fetch('../../controller/ajax_tuteur.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    aptusAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    aptusAlert(data.message, 'error');
                }
            })
            .catch(err => aptusAlert(err.message, 'error'));
    }, `Retirer le tuteur « ${nom} » ? Si ce tuteur a des formations actives, son rôle sera simplement rétrogradé.`);
}

// ── Contacter un tuteur ────────────────────────────────────
function contactTuteur(email, nom) {
    Swal.fire({
        title: `✉️ Contacter ${nom}`,
        html: `
            <p style="color:#475569;font-size:.88rem;margin-bottom:1rem;">
                Envoyer un message à <strong>${nom}</strong> via email :
            </p>
            <a href="mailto:${email}" class="btn btn-primary" style="display:inline-block;text-decoration:none;padding:.6rem 1.5rem;border-radius:8px;">
                📧 ${email}
            </a>
        `,
        showConfirmButton: false,
        showCloseButton: true,
    });
}

// ── Recherche live avec filtre ────────────────────────────
function filterTuteurs(query) {
    const specFilter = document.getElementById('filter-specialite').value.toLowerCase();
    const q = query.toLowerCase();
    const cards = document.querySelectorAll('.tuteur-card:not(.tuteur-card--add)');
    let count = 0;

    cards.forEach(card => {
        const name  = card.dataset.name  || '';
        const email = card.dataset.email || '';
        const spec  = card.dataset.specialite || '';

        const matchQ    = !q       || name.includes(q) || email.includes(q) || spec.includes(q);
        const matchSpec = !specFilter || spec.includes(specFilter);

        if (matchQ && matchSpec) {
            card.style.display = '';
            count++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('count-label').textContent =
        count + ' tuteur' + (count > 1 ? 's' : '');
}

// ── Animation de validation (champ invalide) ───────────────
function shakeField(id, msg) {
    const el = document.getElementById(id);
    el.style.borderColor = '#ef4444';
    el.style.boxShadow   = '0 0 0 3px rgba(239,68,68,.15)';
    el.focus();
    el.addEventListener('input', () => {
        el.style.borderColor = '';
        el.style.boxShadow   = '';
    }, { once: true });
    Toast.fire({ icon: 'error', title: msg });
}
</script>

