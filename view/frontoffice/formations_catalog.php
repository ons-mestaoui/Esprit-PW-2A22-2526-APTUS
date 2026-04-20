<?php 
$pageTitle = "Catalogue des Formations - Aptus AI";

if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/FormationController.php';
    $formationC = new FormationController();
    
    $liste = $formationC->listerFormations()->fetchAll();
    
    // Filter Mock logic
    $q = $_GET['q'] ?? '';
    $domaine = $_GET['domaine'] ?? '';
    $niveau = $_GET['niveau'] ?? '';
    
    $formations = [];
    $domainesMap = [];
    foreach($liste as $f) {
        if (!empty($f['domaine'])) {
            $domainesMap[$f['domaine']] = true;
        }
        
        $match = true;
        if ($q && stripos($f['titre'], $q) === false && stripos($f['description'], $q) === false) $match = false;
        if ($domaine && $f['domaine'] != $domaine) $match = false;
        if ($niveau && $f['niveau'] != $niveau) $match = false;
        
        if ($match) {
            $formations[] = $f;
        }
    }
    $sort = $_GET['sort'] ?? 'date_desc';
    usort($formations, function($a, $b) use ($sort) {
        if ($sort === 'date_asc') {
            return $a['id_formation'] <=> $b['id_formation'];
        } elseif ($sort === 'titre_asc') {
            return strcasecmp($a['titre'], $b['titre']);
        } else {
            // date_desc: Plus récent (nouvellement ajouté)
            return $b['id_formation'] <=> $a['id_formation'];
        }
    });

    $domaines = array_keys($domainesMap);
    
    // Pagination for infinite scroll
    $totalFormations = count($formations);
    $perPage = 6;
    $page = (int)($_GET['page'] ?? 1);
    $totalPages = ceil($totalFormations / $perPage);
    $offset = ($page - 1) * $perPage;
    
    $formationsPage = array_slice($formations, $offset, $perPage);

    // If AJAX request, return only the HTML for the new cards
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        foreach($formationsPage as $f) {
            // Include card HTML
            include 'catalog_card_partial.php'; // We'll create this file to keep it clean, OR output inline
        }
        exit();
    }

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div style="margin-top: 1rem; margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(0, 163, 218, 0.1), rgba(154, 50, 147, 0.1)); border: 1px solid var(--primary-cyan); border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 1.3rem; margin-bottom: 0.3rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
            <i data-lucide="git-branch" style="color: var(--primary-cyan);"></i> Découvrez les Parcours de Compétences
        </h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">Ne choisissez plus vos cours au hasard. Suivez un cheminement logique pour atteindre vos objectifs d'apprentissage.</p>
    </div>
    <a href="skill_tree.php" class="btn btn-primary" style="white-space: nowrap;"
       data-intro="Ne choisissez plus vos cours au hasard ! Suivez votre cheminement logique d'apprentissage ici." 
       data-step="1">
        Voir l'Arbre (Skill Tree) <i data-lucide="arrow-right" style="width: 16px; height: 16px; margin-left: 5px;"></i>
    </a>
</div>

<div class="catalogue-layout" style="display: flex; gap: 2rem;">
    <!-- SIDEBAR -->
    <form id="filterForm" action="formations_catalog.php" method="get" class="sidebar" style="width: 250px; flex-shrink: 0;">
        <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'date_desc'); ?>">
        <input type="hidden" name="domaine" id="hiddenDomaine" value="<?php echo htmlspecialchars($_GET['domaine'] ?? ''); ?>">
        <input type="hidden" name="niveau" id="hiddenNiveau" value="<?php echo htmlspecialchars($_GET['niveau'] ?? ''); ?>">

        <div class="filter-section" style="margin-bottom: 2rem; background: var(--bg-card); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 1rem;">Domaines</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <span class="filter-link <?php echo empty($_GET['domaine']) ? 'active' : ''; ?>" onclick="setFilter('domaine', '')" style="cursor: pointer; padding: 0.5rem; border-radius: 6px; font-size: 0.9rem;">Tous</span>
                <?php foreach($domaines as $d): ?>
                    <span class="filter-link <?php echo (($_GET['domaine'] ?? '') == $d) ? 'active' : ''; ?>" onclick="setFilter('domaine', '<?php echo addslashes($d); ?>')" style="cursor: pointer; padding: 0.5rem; border-radius: 6px; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($d); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-section" style="background: var(--bg-card); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 1rem;">Niveau</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <span class="filter-link <?php echo empty($_GET['niveau']) ? 'active' : ''; ?>" onclick="setFilter('niveau', '')" style="cursor: pointer; padding: 0.5rem; border-radius: 6px; font-size: 0.9rem;">Tous</span>
                <?php foreach(['Débutant', 'Intermédiaire', 'Avancé', 'Expert'] as $n): ?>
                    <span class="filter-link <?php echo (($_GET['niveau'] ?? '') == $n) ? 'active' : ''; ?>" onclick="setFilter('niveau', '<?php echo $n; ?>')" style="cursor: pointer; padding: 0.5rem; border-radius: 6px; font-size: 0.9rem;">
                        <?php echo $n; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </form>

    <!-- CONTENT -->
    <div class="main-content" style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-weight: 600; color: var(--text-primary); display: flex; align-items: center; gap: 0.75rem;">
                <span style="background: var(--bg-card); padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                    <span style="color: var(--primary-cyan); font-size: 1.2rem;"><?php echo $totalFormations; ?></span> formation(s) trouvée(s)
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <?php if (!empty($_GET['q']) || !empty($_GET['domaine']) || !empty($_GET['niveau'])): ?>
                    <a href="formations_catalog.php" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                        <i data-lucide="x" style="width: 14px; height: 14px;"></i> Effacer filtres
                    </a>
                <?php endif; ?>
                <!-- View Toggle -->
                <div style="display: flex; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden;">
                    <button class="view-toggle-btn active" id="gridViewBtn" onclick="setViewMode('grid')" title="Vue grille" style="padding: 0.5rem 0.65rem; border: none; background: none; cursor: pointer; color: var(--text-secondary); display: flex; align-items: center; transition: all 0.2s;">
                        <i data-lucide="grid-3x3" style="width: 18px; height: 18px;"></i>
                    </button>
                    <button class="view-toggle-btn" id="listViewBtn" onclick="setViewMode('list')" title="Vue liste" style="padding: 0.5rem 0.65rem; border: none; border-left: 1px solid var(--border-color); background: none; cursor: pointer; color: var(--text-secondary); display: flex; align-items: center; transition: all 0.2s;">
                        <i data-lucide="list" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>
        </div>

        <form id="topBarForm" action="formations_catalog.php" method="get" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; background: var(--bg-card); padding: 1rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <input type="hidden" name="domaine" value="<?php echo htmlspecialchars($_GET['domaine'] ?? ''); ?>">
            <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($_GET['niveau'] ?? ''); ?>">
            
            <div style="flex: 1; position: relative;" 
                 data-intro="Vous cherchez une compétence précise ? Tapez-la ici pour filtrer le catalogue instantanément." 
                 data-step="2">
                <input type="text" name="q" placeholder="Rechercher une formation..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" oninput="debounceSubmit()" style="width: 100%; border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-primary); padding: 0.5rem 0.5rem 0.5rem 2.5rem; border-radius:6px; outline:none;">
                <span style="position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); opacity: 0.4;">
                    <i data-lucide="search" style="width:16px;height:16px;color:var(--text-primary);"></i>
                </span>
            </div>

            <select name="sort" onchange="this.form.submit()" class="select" style="max-width: 200px; background: var(--bg-input); color: var(--text-primary); border: 1px solid var(--border-color);">
                <option value="date_desc" <?php echo (($_GET['sort'] ?? '') == 'date_desc') ? 'selected' : ''; ?>>Plus récent</option>
                <option value="date_asc" <?php echo (($_GET['sort'] ?? '') == 'date_asc') ? 'selected' : ''; ?>>Plus ancien</option>
                <option value="titre_asc" <?php echo (($_GET['sort'] ?? '') == 'titre_asc') ? 'selected' : ''; ?>>Titre A-Z</option>
            </select>
        </form>

        <div class="grid" id="catalog-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); display: grid; gap: 1.5rem;">
            <?php if (!empty($formationsPage)): foreach($formationsPage as $f): ?>
                <?php include 'catalog_card_partial.php'; // Inclusion de la carte ?>
            <?php endforeach; else: ?>
                <div style="grid-column: 1/-1;" class="empty-state">
                    <div class="empty-state__icon">
                        <i data-lucide="search-x" style="width: 40px; height: 40px;"></i>
                    </div>
                    <h3 class="empty-state__title">Aucune formation trouvée</h3>
                    <p class="empty-state__text">Nous n'avons trouvé aucune formation correspondant à vos critères de recherche. Essayez de modifier ou d'effacer vos filtres.</p>
                    <a href="formations_catalog.php" class="btn btn-primary" style="margin-top: 1rem;">Effacer tous les filtres</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div id="infinite-scroll-trigger" data-page="1" data-total="<?php echo $totalPages; ?>" style="height: 40px; margin-top: 2rem; display: flex; justify-content: center; align-items: center;">
                <div class="loading-spinner" style="display: none; align-items: center; gap: 0.5rem; color: var(--text-secondary);">
                    <i data-lucide="loader-2" style="animation: spin 1s linear infinite;"></i> Chargement...
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* ── Responsive grid ── */
    @media (max-width: 1024px) {
        .catalogue-layout { flex-direction: column; }
        form.sidebar { width: 100% !important; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
    }

    /* ── Catalog card hover ── */
    .catalog-card .catalog-card__inner:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(99,102,241,0.15);
        border-color: var(--accent-primary);
    }

    /* ── View toggle active ── */
    .view-toggle-btn.active {
        background: var(--gradient-primary) !important;
        color: #fff !important;
    }

    /* ───────────────────────────────────────────
       LIST VIEW — Premium Horizontal Row Layout
    ─────────────────────────────────────────── */
    #catalog-grid.list-view {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }
    /* Force the inner card to horizontal */
    #catalog-grid.list-view .catalog-card__inner {
        flex-direction: row !important;
        height: auto !important;
        align-items: stretch;
    }
    /* Thumbnail: fixed left column */
    #catalog-grid.list-view .catalog-card__thumb {
        width: 180px !important;
        min-width: 180px !important;
        height: auto !important;
        min-height: 120px;
        border-radius: 14px 0 0 14px !important;
        flex-shrink: 0;
    }
    /* Body fills remaining space */
    #catalog-grid.list-view .catalog-card__inner > div:last-child {
        flex-direction: row !important;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem 1.25rem;
    }
    /* Description stays on one line in list mode */
    #catalog-grid.list-view p {
        -webkit-line-clamp: 1 !important;
        flex: 1;
        min-width: 180px;
    }
    /* Footer row stays at end */
    #catalog-grid.list-view .catalog-card__inner > div:last-child > div:last-child {
        border-top: none !important;
        padding-top: 0 !important;
        margin-top: 0 !important;
        margin-left: auto;
    }

    @media (max-width: 768px) {
        #catalog-grid.list-view .catalog-card__inner { flex-direction: column !important; }
        #catalog-grid.list-view .catalog-card__thumb { width: 100% !important; min-width: unset !important; height: 140px !important; border-radius: 14px 14px 0 0 !important; }
    }
</style>

<script>
    function setFilter(name, value) {
        document.getElementById('hidden' + name.charAt(0).toUpperCase() + name.slice(1)).value = value;
        document.getElementById('filterForm').submit();
    }

    let searchTimeout;
    function debounceSubmit() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('topBarForm').submit();
        }, 600);
    }

    // View Mode Toggle
    function setViewMode(mode) {
        const grid = document.getElementById('catalog-grid');
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        
        if (mode === 'list') {
            grid.classList.add('list-view');
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
        } else {
            grid.classList.remove('list-view');
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');
        }
        // Persist preference
        localStorage.setItem('catalog_view', mode);
    }
    
    // Restore saved view preference on load
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('catalog_view');
        if (savedView === 'list') setViewMode('list');
    });
    
    // Infinite Scroll Logic
    document.addEventListener("DOMContentLoaded", function() {
        const trigger = document.getElementById("infinite-scroll-trigger");
        if (!trigger) return;
        
        let isLoading = false;
        const spinner = trigger.querySelector('.loading-spinner');
        
        const observer = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting && !isLoading) {
                let currentPage = parseInt(trigger.getAttribute('data-page'));
                let totalPages = parseInt(trigger.getAttribute('data-total'));
                
                if (currentPage < totalPages) {
                    isLoading = true;
                    spinner.style.display = 'flex';
                    
                    let nextPage = currentPage + 1;
                    
                    // Build URL with current parameters
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', nextPage);
                    url.searchParams.set('ajax', '1');
                    
                    fetch(url.toString())
                        .then(response => response.text())
                        .then(html => {
                            if (html.trim() !== '') {
                                const grid = document.getElementById('catalog-grid');
                                grid.insertAdjacentHTML('beforeend', html);
                                trigger.setAttribute('data-page', nextPage);
                                
                                // Re-initialize Lucide icons if present
                                if (typeof lucide !== 'undefined') {
                                    lucide.createIcons();
                                }
                            }
                        })
                        .catch(err => console.error("Erreur Infinite Scroll:", err))
                        .finally(() => {
                            isLoading = false;
                            spinner.style.display = 'none';
                            
                            // If we reached the last page, stop observing and hide observer element
                            if (nextPage >= totalPages) {
                                observer.disconnect();
                                trigger.style.display = 'none';
                            }
                        });
                }
            }
        }, {
            rootMargin: '100px'
        });
        
        observer.observe(trigger);
    });

    // Launch Intro.js Tour guided
    document.addEventListener("DOMContentLoaded", function() {
        // Optionnel: vérifier en session si c'est le 1er login
        if (!localStorage.getItem('aptus_tour_completed')) {
            setTimeout(() => {
                introJs().setOptions({
                    nextLabel: 'Suivant',
                    prevLabel: 'Précédent',
                    skipLabel: 'Passer',
                    doneLabel: 'Compri !',
                    showProgress: true,
                    exitOnOverlayClick: false,
                    scrollToElement: true
                }).oncomplete(function() {
                    localStorage.setItem('aptus_tour_completed', 'true');
                }).onskip(function() {
                    localStorage.setItem('aptus_tour_completed', 'true');
                }).start();
            }, 1000); // Petit délai pour laisser le layout charger
        }
    });
</script>
