<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificat — Aptus</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/variables.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/global.css">
  <link rel="stylesheet" href="/aptus_first_official_version/view/assets/css/formations.css">
  <script src="/aptus_first_official_version/view/assets/js/theme-toggle.js"></script>
</head>
<body>

  <div class="certificate-page">
    <!-- Actions (hidden in print) -->
    <div class="certificate-actions">
      <button class="btn btn-primary" onclick="window.print();" id="print-certificate-btn">
        <i data-lucide="printer" style="width:18px;height:18px;"></i>
        Imprimer le Certificat
      </button>
      <button class="btn btn-secondary" onclick="window.history.back();">
        <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        Retour
      </button>
      <button class="theme-toggle" aria-label="Toggle theme">
        <i data-lucide="sun" class="icon-sun" style="display:none;"></i>
        <i data-lucide="moon" class="icon-moon"></i>
      </button>
    </div>

    <!-- ═══════════════════════════════════════════
         CERTIFICATE (A4 Landscape)
         ═══════════════════════════════════════════ -->
    <div class="certificate" id="certificate">
      <!-- Header -->
      <div class="certificate__header">
        <div class="certificate__logo">
          <img src="/aptus_first_official_version/view/assets/img/logo.png" alt="Aptus" class="certificate__logo-icon" style="background:none;padding:4px;">
          <span class="certificate__logo-text">Aptus</span>
        </div>
        <div class="certificate__label">Certificat de Réussite</div>
        <div class="certificate__title">Certificate of Completion</div>
      </div>

      <!-- Body -->
      <div class="certificate__body">
        <div class="certificate__awarded-to">Ce certificat est décerné à</div>
        <div class="certificate__name">
          <?php echo isset($candidateName) ? $candidateName : 'Amine Belloumi'; ?>
        </div>
        <div class="certificate__description">
          Pour avoir complété avec succès l'ensemble des modules et satisfait aux exigences de la formation
        </div>
        <div class="certificate__course-title">
          <?php echo isset($courseTitle) ? $courseTitle : 'React.js Avancé : Hooks, Context & Performance'; ?>
        </div>
        <div class="certificate__date">
          Délivré le <?php echo isset($certDate) ? $certDate : date('d/m/Y'); ?> — Aptus Platform
        </div>
      </div>

      <!-- Footer -->
      <div class="certificate__footer">
        <!-- Tutor Signature -->
        <div class="certificate__signature">
          <div class="certificate__signature-line"></div>
          <div class="certificate__signature-name">
            <?php echo isset($tutorName) ? $tutorName : 'Ahmed Ben Ali'; ?>
          </div>
          <div class="certificate__signature-role">Instructeur / Tuteur</div>
        </div>

        <!-- Seal -->
        <div class="certificate__seal">
          <i data-lucide="award" style="width:32px;height:32px;"></i>
        </div>

        <!-- Platform Signature -->
        <div class="certificate__signature">
          <div class="certificate__signature-line"></div>
          <div class="certificate__signature-name">Aptus</div>
          <div class="certificate__signature-role">Plateforme de Formation</div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
