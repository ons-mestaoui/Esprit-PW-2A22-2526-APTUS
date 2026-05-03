<?php if (empty($listeFormations)): ?>
    <tr>
        <td colspan="6" style="text-align:center; padding:40px; color:var(--text-secondary);">
            Aucune formation trouvée.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($listeFormations as $f): ?>
        <tr>
            <td class="fw-medium">
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="<?php echo $f['image_base64']; ?>" alt=""
                        style="width:45px; height:25px; object-fit:cover; border-radius:4px; background:var(--bg-secondary);">
                    <?php echo $f['titre_safe']; ?>
                </div>
            </td>
            <td><span class="badge badge-info">
                    <?php echo $f['domaine_safe']; ?>
                </span></td>
            <td><span class="badge <?php echo $f['niveau_class']; ?>">
                    <?php echo $f['niveau']; ?>
                </span></td>
            <td class="text-sm">
                <i data-lucide="<?php echo $f['lieu_icon']; ?>"
                    style="width:14px; height:14px; vertical-align:middle; margin-right:5px;"></i>
                <?php echo $f['lieu_label']; ?>
            </td>
            <td class="text-sm text-secondary">
                <?php echo $f['date_format']; ?>
            </td>
            <td>
                <div class="flex gap-1">
                    <a href="edit_formation.php?id=<?php echo $f['id_formation']; ?>"
                        class="btn btn-sm btn-ghost" title="Éditer">
                        <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-ghost" style="color:var(--accent-tertiary);"
                        onclick='deleteFormation(<?php echo $f["id_formation"]; ?>, "<?php echo addslashes($f["titre"]); ?>");'>
                        <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
