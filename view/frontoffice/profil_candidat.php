<?php $pageTitle = "Mon Profil"; $pageCSS = "cv.css"; ?>

<?php
if (!isset($content)) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../controller/InscriptionController.php';
    
    $inscriptionC = new InscriptionController();
    $id_user = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 10;
    $mesBadges = $inscriptionC->getMesBadges($id_user);

    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- Included inside layout_front.php -->

<div class="page-header">
  <div class="section-header">
    <div>
      <h1 class="page-header__title">
        <i data-lucide="user-circle" style="width:28px;height:28px;color:var(--accent-primary);"></i>
        Mon Profil
      </h1>
      <p class="page-header__subtitle">Gérez vos informations personnelles et professionnelles</p>
    </div>
  </div>
</div>

<!-- ═══ Profile Content ═══ -->
<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;">

  <!-- Left: Photo & Quick Info -->
  <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-8);text-align:center;">
    <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-4);font-size:2.5rem;font-weight:700;color:#fff;">
      AK
    </div>
    <h2 style="font-size:var(--fs-xl);font-weight:700;margin-bottom:var(--space-1);">Amine Khelifi</h2>
    <p class="text-secondary text-sm" style="margin-bottom:var(--space-3);">Développeur Full Stack</p>
    <span class="badge badge-success" style="margin-bottom:var(--space-5);">Profil actif</span>

    <div style="border-top:1px solid var(--border-color);padding-top:var(--space-5);margin-top:var(--space-4);text-align:left;display:flex;flex-direction:column;gap:var(--space-3);">
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="mail" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">amine.khelifi@email.com</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="phone" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">+216 55 123 456</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="map-pin" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Tunis, Tunisie</span>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-3);">
        <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-tertiary);flex-shrink:0;"></i>
        <span class="text-sm">Inscrit le 15 Mar. 2026</span>
      </div>
    </div>

    <button class="btn btn-secondary w-full" style="margin-top:var(--space-5);">
      <i data-lucide="camera" style="width:16px;height:16px;"></i>
      Changer la photo
    </button>
  </div>

  <!-- Right: Editable Details -->
  <div style="display:flex;flex-direction:column;gap:var(--space-6);">

    <!-- Badges / Gamification (Moved to top for visibility) -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);margin-bottom:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="award" style="width:22px;height:22px;color:var(--accent-primary);"></i>
        Ma Collection de Badges
      </h3>
      
      <?php if (!empty($mesBadges)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(110px, 1fr));gap:var(--space-4);">
          <?php foreach ($mesBadges as $b): 
              $icon = 'medal';
              $color = '#94a3b8'; // Default
              if (strpos($b['nom'], 'Débutant') !== false) { $color = '#10b981'; $icon = 'award'; }
              elseif (strpos($b['nom'], 'Intermédiaire') !== false) { $color = '#f59e0b'; $icon = 'zap'; }
              elseif (strpos($b['nom'], 'Expert') !== false) { $color = '#8b5cf6'; $icon = 'crown'; }
          ?>
            <div style="text-align:center;padding:var(--space-3);border:1px solid var(--border-color);border-radius:var(--radius-md);background:var(--bg-secondary);transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.borderColor='<?php echo $color; ?>'" onmouseout="this.style.transform='none';this.style.borderColor='var(--border-color)'">
              <div style="width:50px;height:50px;margin:0 auto var(--space-2);background:<?php echo $color; ?>15;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid <?php echo $color; ?>33;">
                <i data-lucide="<?php echo $icon; ?>" style="width:24px;height:24px;color:<?php echo $color; ?>;"></i>
              </div>
              <p style="font-size:0.75rem;font-weight:700;margin-bottom:2px;"><?php echo htmlspecialchars($b['nom']); ?></p>
              <p style="font-size:0.6rem;color:var(--text-tertiary);">Obtenu le <?php echo date('d/m/Y', strtotime($b['date_obtention'])); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div style="padding:var(--space-8);background:var(--bg-secondary);border:1px dashed var(--border-color);border-radius:var(--radius-md);text-align:center;">
          <div style="color:var(--text-tertiary);margin-bottom:var(--space-3);">
            <i data-lucide="lock" style="width:32px;height:32px;margin:0 auto;opacity:0.3;"></i>
          </div>
          <p class="text-sm fw-semibold" style="color:var(--text-secondary);">Aucun badge pour le moment</p>
          <p class="text-xs" style="color:var(--text-tertiary);margin-top:0.5rem;">Complétez votre première formation pour débloquer un trophée !</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Personal Info -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="user" style="width:20px;height:20px;color:var(--accent-primary);"></i>
        Informations Personnelles
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input type="text" class="input" value="Khelifi" placeholder="Votre nom">
        </div>
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input type="text" class="input" value="Amine" placeholder="Votre prénom">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <div class="input-icon-wrapper">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
            <input type="email" class="input" value="amine.khelifi@email.com">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <div class="input-icon-wrapper">
            <i data-lucide="phone" style="width:18px;height:18px;"></i>
            <input type="tel" class="input" value="+216 55 123 456">
          </div>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Adresse</label>
          <div class="input-icon-wrapper">
            <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
            <input type="text" class="input" value="Tunis, Tunisie">
          </div>
        </div>
      </div>
    </div>

    <!-- Professional Info -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:var(--space-6);">
      <h3 style="font-size:var(--fs-lg);font-weight:600;margin-bottom:var(--space-5);display:flex;align-items:center;gap:var(--space-2);">
        <i data-lucide="briefcase" style="width:20px;height:20px;color:var(--accent-secondary);"></i>
        Informations Professionnelles
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
        <div class="form-group">
          <label class="form-label">Poste actuel</label>
          <input type="text" class="input" value="Développeur Full Stack" placeholder="Votre poste">
        </div>
        <div class="form-group">
          <label class="form-label">Années d'expérience</label>
          <input type="number" class="input" value="3">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Bio</label>
          <textarea class="textarea" rows="3" placeholder="Décrivez-vous en quelques mots...">Développeur passionné avec 3 ans d'expérience en React, Node.js et Python. Spécialisé dans le développement d'applications web performantes et scalables.</textarea>
        </div>
      </div>
    </div>

  </div>
</div>
