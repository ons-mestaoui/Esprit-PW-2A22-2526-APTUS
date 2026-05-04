<?php
// We put this in standalone mode since it's meant to be printed
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();

$id_user = SessionManager::getUserId();
$id_formation = isset($_GET['f_id']) ? (int)$_GET['f_id'] : 0;

if ($id_formation <= 0) {
    die("Formation invalide.");
}

require_once __DIR__ . '/../../controller/InscriptionController.php';

$inscriptionController = new InscriptionController();
$access = $inscriptionController->getCertificateAccessData($id_user, $id_formation);

if (!$access) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:#ef4444;'>🚫 Accès Refusé</h1>
            <p>Vous n'êtes pas inscrit à cette formation.</p>
         </div>");
}

if ($access['role'] !== 'Candidat' && $access['role'] !== 'candidat') {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:#ef4444;'>❌ Rôle Invalide</h1>
            <p>Seuls les candidats peuvent obtenir un certificat de réussite.</p>
         </div>");
}

if ($access['progression'] < 100) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:#f59e0b;'>⏳ Formation non terminée</h1>
            <p>Vous devez atteindre 100% de progression pour débloquer votre certificat.</p>
            <progress value='{$access['progression']}' max='100'></progress> {$access['progression']}%
         </div>");
}

$user = [
    'id' => $id_user, 
    'nom' => $access['user_nom'] ?? 'Candidat Aptus'
];
$cours_fini = [
    'id_formation' => $id_formation,
    'titre' => $access['titre'],
    'tuteur_nom' => $access['tuteur_nom']
];

try {
    // Suppression de l'ancien bloc try/SQL ici car tout est déjà dans $access
} catch (Exception $e) {
    // Bloc vide conservé pour éviter de casser la structure de fin de fichier si nécessaire
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de Réussite - Aptus AI</title>
    <!-- Inclure HTML2PDF et Lucide Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap');
        
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body { margin: 0; padding: 0; background: #f8fafc; font-family: 'Outfit', sans-serif; }
        
        .certificate-wrapper {
            width: 297mm;
            height: 210mm;
            padding: 15mm;
            box-sizing: border-box;
            background: white;
            margin: 20px auto;
            box-shadow: 0 0 50px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            border: 10px solid #f1f5f9;
        }

        .certificate-inner {
            border: 2px solid #cbd5e1;
            height: 100%;
            padding: 30px 60px;
            box-sizing: border-box;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: radial-gradient(circle at top right, rgba(6,182,212,0.02), transparent),
                        radial-gradient(circle at bottom left, rgba(139,92,246,0.02), transparent);
        }

        .header { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .logo span { color: #8b5cf6; }
        .cert-id { font-size: 0.75rem; color: #94a3b8; }

        .main-title { font-family: 'Playfair Display', serif; font-size: 3.2rem; color: #1e293b; margin: 0; }
        .decoration { width: 100px; height: 3px; background: linear-gradient(90deg, transparent, #8b5cf6, transparent); margin: 15px auto; }
        .subtitle { font-size: 1rem; text-transform: uppercase; letter-spacing: 0.4em; color: #64748b; margin-bottom: 2rem; }

        .content { font-size: 1.3rem; color: #334155; line-height: 1.6; }
        .student-name { font-size: 2.8rem; font-weight: 700; color: #0f172a; margin: 1rem 0; font-family: 'Playfair Display', serif; }
        .course-title { font-weight: 700; color: #8b5cf6; border-bottom: 2px solid rgba(139,92,246,0.1); padding-bottom: 4px; }

        .footer { display: flex; justify-content: space-between; align-items: flex-end; padding: 0 40px 20px 40px; }
        .sig-block { text-align: center; width: 220px; }
        .sig-line { border-top: 1px solid #cbd5e1; margin-bottom: 8px; }
        .sig-name { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
        .sig-title { font-size: 0.75rem; color: #64748b; }

        .seal { 
            width: 110px; height: 110px; background: rgba(6,182,212,0.05); 
            border-radius: 50%; border: 2px dashed #06b6d4; 
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #0891b2; font-weight: 700; font-size: 0.7rem; transform: rotate(-10deg);
        }

        @media print {
            body { background: white; }
            .certificate-wrapper { margin: 0; box-shadow: none; border: none; width: 100%; height: 100%; }
            .no-print { display: none; }
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: transparent;
            padding: 0;
            border: none;
            cursor: pointer;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="no-print" style="display: flex; gap: 1rem; background: transparent; padding: 0; align-items: center;">
        
        <!-- Bouton Télécharger -->
        <button onclick="telechargerCertificatPDF()" style="
            background: linear-gradient(90deg, #1882c4 0%, #8a2594 100%);
            color: #ffffff;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-family: 'Inter', system-ui, sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(138, 37, 148, 0.2);
            transition: all 0.2s ease;"
            onmouseover="this.style.opacity='0.9'; this.style.transform='translateY(-1px)';" 
            onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)';"
            onmousedown="this.style.transform='scale(0.97)';"
            onmouseup="this.style.transform='translateY(-1px)';">
            
            <i data-lucide="download" style="width: 16px; height: 16px;"></i> 
            Télécharger en PDF
        </button>

        <!-- Bouton Imprimer -->
        <button onclick="window.print()" style="
            background: linear-gradient(90deg, #1882c4 0%, #8a2594 100%);
            color: #ffffff;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-family: 'Inter', system-ui, sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(138, 37, 148, 0.2);
            transition: all 0.2s ease;"
            onmouseover="this.style.opacity='0.9'; this.style.transform='translateY(-1px)';" 
            onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)';"
            onmousedown="this.style.transform='scale(0.97)';"
            onmouseup="this.style.transform='translateY(-1px)';">
            
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i> 
            Imprimer
        </button>
    </div>

    <!-- id=certificate-content added for html2pdf target -->
    <div class="certificate-wrapper" id="certificate-content">
        <div class="certificate-inner">
            <div class="header">
                <div class="logo">APTUS<span>AI</span></div>
                <div class="cert-id">Réf: CERT-<?php echo strtoupper(substr(md5($user['id'].$cours_fini['id_formation']), 0, 8)); ?></div>
            </div>

            <div>
                <div class="subtitle">Certificat de Réussite</div>
                <div class="main-title">Félicitations</div>
                <div class="decoration"></div>
            </div>
            
            <div class="content">
                Ce diplôme est fièrement décerné à<br>
                <div class="student-name"><?php echo htmlspecialchars($user['nom']); ?></div>
                pour avoir complété avec succès le programme de formation<br>
                <div class="course-title">« <?php echo htmlspecialchars($cours_fini['titre']); ?> »</div><br>
                auprès de l'académie numérique Aptus AI, le <?php echo date('d F Y'); ?>.
            </div>

            <div class="footer">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-name"><?php echo htmlspecialchars($cours_fini['tuteur_nom'] ?? 'Responsable Pédagogique'); ?></div>
                    <div class="sig-title">Tuteur Expert Aptus AI</div>
                </div>
                
                <div class="seal">
                    <span style="font-size: 1.5rem;">⭐</span>
                    CERTIFIÉ<br>STUDIO APTUS
                </div>

                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-name">Direction Aptus AI</div>
                    <div class="sig-title">Authentifié numériquement</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialisation des icônes Lucide
        lucide.createIcons();

        function telechargerCertificatPDF() {
            var element = document.getElementById('certificate-content');
            var opt = {
                margin:       0,
                filename:     'Certificat_Aptus_AI.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
