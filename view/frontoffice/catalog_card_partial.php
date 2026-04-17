<div class="card-flat card-formation-hover" style="padding: 1.25rem; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); display:flex; flex-direction:column; color: var(--text-primary); transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;">
    <?php if (!empty($f['image_base64'])): ?>
        <div style="width:100%; height:150px; background: url('<?php echo $f['image_base64']; ?>') center/cover; border-radius:8px; margin-bottom:1rem; transition: transform 0.5s ease;"></div>
    <?php else: ?>
        <div style="width:100%; height:150px; background: linear-gradient(135deg, var(--primary-cyan), var(--accent-primary)); opacity: 0.1; border-radius:8px; margin-bottom:1rem;"></div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
        <span class="badge badge-info" style="font-size: 0.7rem;"><?php echo htmlspecialchars($f['domaine'] ?? 'Général'); ?></span>
        <span class="badge badge-primary" style="font-size: 0.7rem;"><?php echo htmlspecialchars($f['niveau']); ?></span>
    </div>

    <h2 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($f['titre']); ?></h2>
    <p style="font-size: 0.85rem; color: var(--text-secondary); height: 3.2rem; overflow: hidden; margin-bottom: 1.5rem; flex:1;">
        <?php echo htmlspecialchars(substr(strip_tags($f['description']), 0, 120)); ?>...
    </p>

    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: auto;">
        <span style="font-size: 0.8rem; color: var(--text-secondary);">
            <i data-lucide="<?php echo $f['is_online'] ? 'video' : 'map-pin'; ?>" style="width:14px;height:14px;vertical-align:middle;"></i>
            <?php echo $f['is_online'] ? 'Ligne' : 'Présentiel'; ?>
        </span>
        <a href="formation_detail.php?id=<?php echo $f['id_formation']; ?>" class="btn btn-primary btn-sm" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Détails</a>
    </div>
</div>
