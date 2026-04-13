<?php
// We put this in standalone mode since it's meant to be printed
session_start();
require_once __DIR__ . '/../../config.php';

$id_user = 10; // For demo matching candidate test ID
$id_formation = $_GET['f_id'] ?? 1;

$db = config::getConnexion();

$user = ['id' => $id_user, 'nom' => 'Candidat Aptus'];
$cours_fini = [
    'id_formation' => $id_formation,
    'titre' => 'Formation',
    'tuteur_nom' => 'Aptus AI'
];

try {
    // We try to fetch real data
    $stmt = $db->prepare("
        SELECT f.titre, COALESCE(u.nom, 'Aptus AI') as tuteur_nom, 
               (SELECT nom FROM utilisateur WHERE id = ?) as etudiant_nom
        FROM Formation f
        LEFT JOIN utilisateur u ON f.id_tuteur = u.id
        WHERE f.id_formation = ?
    ");
    $stmt->execute([$id_user, $id_formation]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        if (!empty($data['etudiant_nom'])) $user['nom'] = $data['etudiant_nom'];
        $cours_fini['titre'] = $data['titre'];
        $cours_fini['tuteur_nom'] = $data['tuteur_nom'];
    }
} catch (Exception $e) {
    try {
        $stmt = $db->prepare("
            SELECT f.titre, COALESCE(u.nom, 'Aptus AI') as tuteur_nom, 
                   (SELECT nom FROM User WHERE id = ?) as etudiant_nom
            FROM Formation f
            LEFT JOIN User u ON f.id_tuteur = u.id
            WHERE f.id_formation = ?
        ");
        $stmt->execute([$id_user, $id_formation]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            if (!empty($data['etudiant_nom'])) $user['nom'] = $data['etudiant_nom'];
            $cours_fini['titre'] = $data['titre'];
            $cours_fini['tuteur_nom'] = $data['tuteur_nom'];
        }
    } catch(Exception $e2) {}
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de Réussite - Aptus AI</title>
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
            background: #0f172a;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">📥 Enregistrer le Certificat (PDF)</button>

    <div class="certificate-wrapper">
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
</body>
</html>
