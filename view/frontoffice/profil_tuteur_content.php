<?php
include_once __DIR__ . '/../../controller/TuteurC.php';
$tuteurC = new TuteurC();
$tuteurData = $tuteurC->getTuteurById($userId);
?>
<div class="espace-container">
    <div class="dashboard-header" style="margin-bottom: var(--space-8); display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 class="h2">Mon Profil Professionnel</h1>
            <p class="text-secondary">Gérez votre image publique et vos expertises.</p>
        </div>
        <a href="settings.php?role=tuteur" class="btn btn-outline btn-sm">
            <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
            Modifier mon profil
        </a>
    </div>

    <div class="espace-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--space-8);">
        <!-- Sidebar Profile info -->
        <div class="profile-sidebar" style="display: flex; flex-direction: column; gap: var(--space-6);">
            <div class="glass-card" style="padding: var(--space-6); border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-align: center;">
                <div class="avatar" style="width: 120px; height: 120px; margin: 0 auto var(--space-4); border-radius: 50%; overflow: hidden; border: 4px solid var(--bg-secondary); background: var(--bg-glass); display: flex; align-items: center; justify-content: center;">
                    <?php if ($userPhoto): ?>
                        <img src="<?php echo $userPhoto; ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-size: 40px; font-weight: bold; color: var(--accent-primary);"><?php echo strtoupper(substr($userName, 0, 2)); ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="h4 m-0"><?php echo htmlspecialchars($userName); ?></h3>
                <p style="color: var(--accent-tertiary); font-weight: var(--fw-medium); margin-bottom: var(--space-4);"><?php echo htmlspecialchars($tuteurData['specialite'] ?? 'Tuteur Expert'); ?></p>
                <div style="display: flex; justify-content: center; gap: var(--space-2);">
                    <span class="badge" style="background: rgba(99,102,241,0.1); color: var(--accent-primary); border-radius: 50px; padding: 4px 12px; font-size: var(--fs-xs);">Expert</span>
                    <span class="badge" style="background: rgba(16,185,129,0.1); color: #10B981; border-radius: 50px; padding: 4px 12px; font-size: var(--fs-xs);">Vérifié</span>
                </div>
            </div>

            <div class="glass-card" style="padding: var(--space-6); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <h4 class="h5" style="margin-bottom: var(--space-4);">Informations de contact</h4>
                <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-3); font-size: var(--fs-sm);">
                        <i data-lucide="mail" style="width: 16px; height: 16px; color: var(--text-tertiary);"></i>
                        <span><?php echo $_SESSION['email'] ?? 'Non spécifié'; ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--space-3); font-size: var(--fs-sm);">
                        <i data-lucide="phone" style="width: 16px; height: 16px; color: var(--text-tertiary);"></i>
                        <span><?php echo $_SESSION['telephone'] ?? 'Non spécifié'; ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--space-3); font-size: var(--fs-sm);">
                        <i data-lucide="award" style="width: 16px; height: 16px; color: var(--text-tertiary);"></i>
                        <span>Expérience: <?php echo htmlspecialchars($tuteurData['experience'] ?? 'Non spécifié'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content info -->
        <div class="profile-main" style="display: flex; flex-direction: column; gap: var(--space-6);">
            <div class="glass-card" style="padding: var(--space-6); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <h4 class="h5" style="margin-bottom: var(--space-4);">Ma Biographie</h4>
                <p class="text-secondary" style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($tuteurData['biographie'] ?? 'Aucune biographie disponible pour le moment.')); ?>
                </p>
            </div>
            
            <div class="glass-card" style="padding: var(--space-6); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <h4 class="h5" style="margin-bottom: var(--space-4);">Diplômes & Certifications</h4>
                <div style="color: var(--text-tertiary); font-style: italic; font-size: var(--fs-sm);">
                    Aucune certification ajoutée pour le moment.
                </div>
            </div>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
