<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';

$formationC = new FormationController();

$search  = $_GET['s'] ?? '';
$domaine = $_GET['d'] ?? '';
$niveau  = $_GET['n'] ?? '';

$formations = $formationC->rechercherFormations($search, $domaine, $niveau);

if (empty($formations)) {
    echo '<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-secondary);">Aucune formation trouvée avec ces critères.</td></tr>';
    exit();
}

foreach ($formations as $f) {
    // Calcul de la classe de badge pour le niveau
    $levelClass = 'badge-secondary';
    switch ($f['niveau']) {
        case 'Débutant':     $levelClass = 'badge-success'; break;
        case 'Intermédiaire': $levelClass = 'badge-warning'; break;
        case 'Avancé':       $levelClass = 'badge-danger'; break;
        case 'Expert':        $levelClass = 'badge-primary'; break;
    }
    ?>
    <tr>
        <td class="fw-medium">
            <div style="display:flex; align-items:center; gap:12px;">
                <img src="<?php echo $f['image_base64']; ?>" alt=""
                     style="width:45px; height:25px; object-fit:cover; border-radius:4px; background:#eee;">
                <?php echo htmlspecialchars($f['titre']); ?>
            </div>
        </td>
        <td><span class="badge badge-info"><?php echo htmlspecialchars($f['domaine']); ?></span></td>
        <td><span class="badge <?php echo $levelClass; ?>"><?php echo $f['niveau']; ?></span></td>
        <td class="text-sm">
            <i data-lucide="<?php echo $f['is_online'] ? 'video' : 'map-pin'; ?>"
               style="width:14px; height:14px; vertical-align:middle; margin-right:5px;"></i>
            <?php echo $f['is_online'] ? 'En ligne' : 'Présentiel'; ?>
        </td>
        <td class="text-sm text-secondary">
            <?php echo date('d M. Y', strtotime($f['date_formation'])); ?>
        </td>
        <td>
            <div class="flex gap-1">
                <a href="edit_formation.php?id=<?php echo $f['id_formation']; ?>" class="btn btn-sm btn-ghost" title="Éditer">
                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                </a>
                <button type="button" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"
                        onclick='aptusConfirmDelete("../../controller/traitement_delete.php?delete_id=<?php echo $f["id_formation"]; ?>", "Supprimer définitivement la formation « <?php echo addslashes($f["titre"]); ?> » ?");'>
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
            </div>
        </td>
    </tr>
    <?php
}
?>
