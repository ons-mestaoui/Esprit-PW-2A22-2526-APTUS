<?php
/**
 * catalog_card_partial.php
 * Dual-mode Formation Card — Grid View (default) & List View.
 * Variables expected: $f (formation row array)
 */
$domaine  = htmlspecialchars($f['domaine'] ?? 'Général');
$niveau   = htmlspecialchars($f['niveau'] ?? '');
$titre    = htmlspecialchars($f['titre'] ?? '');
$desc     = htmlspecialchars(substr(strip_tags($f['description'] ?? ''), 0, 130));
$isOnline = !empty($f['is_online']);
$icon     = $isOnline ? 'video' : 'map-pin';
$modeLbl  = $isOnline ? 'En ligne' : 'Présentiel';
$modeColor= $isOnline ? '#3b82f6' : '#10b981';
$id       = (int)($f['id_formation'] ?? 0);
$tuteur   = htmlspecialchars($f['tuteur_nom'] ?? 'Aptus AI');

// Niveau badge color
$niveauColors = ['Débutant'=>'#10b981','Intermédiaire'=>'#f59e0b','Avancé'=>'#ef4444','Expert'=>'#8b5cf6'];
$niveauColor  = $niveauColors[$f['niveau'] ?? ''] ?? 'var(--accent-primary)';

// Background: image or gradient
$bgStyle = !empty($f['image_base64'])
    ? "background:url('{$f['image_base64']}') center/cover no-repeat;"
    : "background:linear-gradient(135deg,rgba(99,102,241,0.18),rgba(139,92,246,0.12));";
?>

<a href="formation_detail.php?id=<?php echo $id; ?>" class="catalog-card" style="text-decoration:none; color:inherit;">
  <!-- ── GRID VIEW card ── -->
  <div class="card-flat card-formation-hover catalog-card__inner" style="
       display:flex; flex-direction:column; height:100%;
       background:var(--bg-card); border-radius:14px;
       border:1px solid var(--border-color);
       transition:transform .3s ease,box-shadow .3s ease,border-color .3s ease;
       overflow:hidden;">

    <!-- Thumbnail -->
    <div class="catalog-card__thumb" style="<?php echo $bgStyle; ?> height:160px; flex-shrink:0; position:relative;">
      <!-- Mode pill -->
      <span style="position:absolute;bottom:10px;left:10px;
            background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);
            color:#fff;font-size:0.7rem;font-weight:600;
            padding:3px 10px;border-radius:20px;display:flex;align-items:center;gap:5px;">
        <i data-lucide="<?php echo $icon; ?>" style="width:11px;height:11px;color:<?php echo $modeColor; ?>"></i>
        <?php echo $modeLbl; ?>
      </span>
      <!-- Niveau pill -->
      <span style="position:absolute;top:10px;right:10px;
            background:<?php echo $niveauColor; ?>;color:#fff;
            font-size:0.65rem;font-weight:700;padding:3px 9px;border-radius:20px;">
        <?php echo $niveau; ?>
      </span>
    </div>

    <!-- Body -->
    <div style="padding:1.25rem;display:flex;flex-direction:column;flex:1;gap:0.5rem;">
      <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;
                   letter-spacing:.05em;color:<?php echo $modeColor; ?>;">
        <?php echo $domaine; ?>
      </span>
      <h2 style="font-size:1rem;font-weight:700;margin:0;color:var(--text-primary);line-height:1.3;">
        <?php echo $titre; ?>
      </h2>
      <p style="font-size:0.82rem;color:var(--text-secondary);margin:0;
               overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;flex:1;">
        <?php echo $desc; ?>…
      </p>
      <!-- Footer row -->
      <div style="display:flex;align-items:center;justify-content:space-between;
                  margin-top:auto;padding-top:0.9rem;
                  border-top:1px solid var(--border-color);">
        <span style="font-size:0.78rem;color:var(--text-secondary);display:flex;align-items:center;gap:4px;">
          <i data-lucide="user" style="width:13px;height:13px;"></i>
          <?php echo $tuteur; ?>
        </span>
        <span class="btn btn-primary btn-sm" style="font-size:0.78rem;padding:.35rem .85rem;pointer-events:none;">
          Détails →
        </span>
      </div>
    </div>
  </div>
</a>