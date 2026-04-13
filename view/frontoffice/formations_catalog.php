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
    $domaines = array_keys($domainesMap);
    
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div class="catalogue-layout" style="display: flex; gap: 2rem; margin-top: 1rem;">
    <!-- SIDEBAR -->
    <form id="filterForm" action="formations_catalog.php" method="get" class="sidebar" style="width: 250px; flex-shrink: 0;">
        <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'date_desc'); ?>">
        <input type="hidden" name="domaine" id="hiddenDomaine" value="<?php echo htmlspecialchars($_GET['domaine'] ?? ''); ?>">
        <input type="hidden" name="niveau" id="hiddenNiveau" value="<?php echo htmlspecialchars($_GET['niveau'] ?? ''); ?>">

        <div class="filter-section" style="margin-bottom: 2rem; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-light); margin-bottom: 1rem;">Domaines</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <span class="filter-link <?php echo empty($_GET['domaine']) ? 'active' : ''; ?>" onclick="setFilter('domaine', '')" style="cursor: pointer; padding: 0.5rem; border-radius: 6px; font-size: 0.9rem;">Tous</span>
                <?php foreach($domaines as $d): ?>
                    <span class="filter-link <?php echo (($_GET['domaine'] ?? '') == $d) ? 'active' : ''; ?>" onclick="setFilter('domaine', '<?php echo addslashes($d); ?>')" style="cursor: pointer; padding: 0.5rem; border-radius: 6px; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($d); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-section" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-light); margin-bottom: 1rem;">Niveau</h3>
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
        <form id="topBarForm" action="formations_catalog.php" method="get" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
            <input type="hidden" name="domaine" value="<?php echo htmlspecialchars($_GET['domaine'] ?? ''); ?>">
            <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($_GET['niveau'] ?? ''); ?>">
            
            <div style="flex: 1; position: relative;">
                <input type="text" name="q" placeholder="Rechercher une formation..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" oninput="debounceSubmit()" style="width: 100%; border: thin solid #eee; background: transparent; padding: 0.5rem 0.5rem 0.5rem 2rem; border-radius:6px;">
                <span style="position: absolute; left: 0.5rem; top: 50%; transform: translateY(-50%); opacity: 0.4;">🔍</span>
            </div>

            <select name="sort" onchange="this.form.submit()" class="select" style="max-width: 200px; background: #f8fafc; border:none;">
                <option value="date_desc" <?php echo (($_GET['sort'] ?? '') == 'date_desc') ? 'selected' : ''; ?>>Plus récent</option>
                <option value="date_asc" <?php echo (($_GET['sort'] ?? '') == 'date_asc') ? 'selected' : ''; ?>>Plus ancien</option>
                <option value="titre_asc" <?php echo (($_GET['sort'] ?? '') == 'titre_asc') ? 'selected' : ''; ?>>Titre A-Z</option>
            </select>
        </form>

        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); display: grid; gap: 1.5rem;">
            <?php if (!empty($formations)): foreach($formations as $f): ?>
                <div class="card-flat" style="padding: 1.25rem; background: white; border-radius: 12px; border: 1px solid var(--border-color); display:flex; flex-direction:column;">
                    <?php if (!empty($f['image_base64'])): ?>
                        <div style="width:100%; height:150px; background: url('<?php echo $f['image_base64']; ?>') center/cover; border-radius:8px; margin-bottom:1rem;"></div>
                    <?php else: ?>
                        <div style="width:100%; height:150px; background: linear-gradient(135deg, var(--primary-cyan), var(--accent-primary)); opacity: 0.1; border-radius:8px; margin-bottom:1rem;"></div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span class="badge badge-info" style="font-size: 0.7rem;"><?php echo htmlspecialchars($f['domaine'] ?? 'Général'); ?></span>
                        <span class="badge badge-primary" style="font-size: 0.7rem;"><?php echo htmlspecialchars($f['niveau']); ?></span>
                    </div>

                    <h2 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($f['titre']); ?></h2>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); height: 3.2rem; overflow: hidden; margin-bottom: 1.5rem; flex:1;">
                        <?php echo htmlspecialchars($f['description']); ?>
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: auto;">
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">
                            <i data-lucide="<?php echo $f['is_online'] ? 'video' : 'map-pin'; ?>" style="width:14px;height:14px;vertical-align:middle;"></i>
                            <?php echo $f['is_online'] ? 'Ligne' : 'Présentiel'; ?>
                        </span>
                        <a href="formation_detail.php?id=<?php echo $f['id_formation']; ?>" class="btn btn-primary btn-sm" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Détails</a>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 4rem; opacity: 0.4;">
                    <span style="font-size: 3rem;">🔍</span>
                    <p>Aucune formation trouvée.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
</script>
