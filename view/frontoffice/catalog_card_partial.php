<?php
/**
 * catalog_card_partial.php — VUE UNIQUEMENT
 * Reçoit un tableau $f pré-formaté par FormationController::formatFormationForView()
 */
?>

<div class="catalog-card" style="text-decoration:none; color:inherit; display:block;">
  <!-- ── GRID VIEW card ── -->
  <div class="card-flat card-formation-hover catalog-card__inner" style="
       display:flex; flex-direction:column; height:100%;
       background:var(--bg-card); border-radius:14px;
       border:1px solid var(--border-color);
       transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease;
       overflow:hidden;">

    <!-- Thumbnail -->
    <div class="catalog-card__thumb" style="<?php echo $f['bg_style']; ?> height:160px; flex-shrink:0; position:relative;">
      <!-- Mode pill -->
      <span style="position:absolute;bottom:10px;left:10px;
            background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);
            color:#fff;font-size:0.7rem;font-weight:600;
            padding:3px 10px;border-radius:20px;display:flex;align-items:center;gap:5px;">
        <i data-lucide="<?php echo $f['lieu_icon']; ?>" style="width:11px;height:11px;color:<?php echo $f['lieu_color']; ?>"></i>
        <?php echo $f['lieu_label']; ?>
      </span>
      <!-- Niveau pill -->
      <span style="position:absolute;top:10px;right:10px;
            background:<?php echo $f['niveau_color']; ?>;color:#fff;
            font-size:0.65rem;font-weight:700;padding:3px 9px;border-radius:20px;">
        <?php echo $f['niveau']; ?>
      </span>
    </div>

    <!-- Body -->
    <div style="padding:1.25rem;display:flex;flex-direction:column;flex:1;gap:0.5rem;">
      <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;
                   letter-spacing:.05em;color:<?php echo $f['lieu_color']; ?>;">
        <?php echo $f['domaine_safe']; ?>
      </span>
      <a href="formation_detail.php?id=<?php echo $f['id_formation']; ?>" style="text-decoration:none;">
        <h2 style="font-size:1rem;font-weight:700;margin:0;color:var(--text-primary);line-height:1.3; transition:color 0.2s;" onmouseover="this.style.color='var(--primary-cyan)'" onmouseout="this.style.color='var(--text-primary)'">
          <?php echo $f['titre_safe']; ?>
        </h2>
      </a>
      <p style="font-size:0.82rem;color:var(--text-secondary);margin:0;
               overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;flex:1;">
        <?php echo $f['desc_short']; ?>…
      </p>
      <!-- Footer row -->
      <div style="display:flex;align-items:center;justify-content:space-between;
                  margin-top:auto;padding-top:0.9rem;
                  border-top:1px solid var(--border-color);">
        <span style="font-size:0.78rem;color:var(--text-secondary);display:flex;align-items:center;gap:4px;">
          <i data-lucide="user" style="width:13px;height:13px;"></i>
          <?php echo htmlspecialchars($f['tuteur_nom'] ?? 'Aptus AI'); ?>
        </span>
        <?php if ($f['est_passee']): ?>
            <button class="btn" style="background: var(--bg-tertiary); color: var(--text-tertiary); font-size:0.78rem; padding:.35rem .85rem; border: none; cursor: not-allowed; font-weight: 600;" aria-disabled="true" title="Le délai d'inscription est dépassé">
                🔒 Clôturé
            </button>
        <?php else: ?>
            <a href="formation_detail.php?id=<?= $f['id_formation'] ?>" class="btn btn-primary" style="font-size:0.78rem; padding:.35rem .85rem;">
                S'inscrire ➔
            </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
