<?php
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();

$id_tuteur = SessionManager::getUserId();
$pageTitle = "Gérer la Formation - Aptus AI";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurDashboardController.php';

$id_formation = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formationC = new FormationController();
$tuteurC = new TuteurDashboardController();

$formation = $formationC->getFormationById($id_formation);
if (!$formation) {
    die("Formation introuvable.");
}

$students = $tuteurC->getStudentsByFormation($id_formation);
$resources = $tuteurC->getResources($id_formation);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
    .swal-ai-custom {
        border-radius: 20px !important;
        box-shadow: var(--shadow-xl) !important;
        border: 1px solid var(--border-color) !important;
    }
    .badge-info {
        background: var(--accent-info-light);
        color: var(--accent-info);
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
</style>

<div style="background: var(--bg-card); border-radius: 16px; padding: 2.5rem; margin-top: 2rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--text-primary);">Gestion : <?php echo htmlspecialchars($formation['titre']); ?></h1>
            <p style="color: var(--text-secondary); margin:0;">Gérez les étudiants et le contenu de votre cours.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="formation_viewer.php?id=<?php echo $id_formation; ?>" target="_blank" class="btn btn-secondary">👁️ Voir comme étudiant</a>
            <a href="tuteur_dashboard.php" class="btn btn-primary">Retour Dashboard</a>
        </div>
    </div>

    <!-- TABS -->
    <div style="display: flex; gap: 1rem; border-bottom: 2px solid var(--border-color); margin-bottom: 2rem;">
        <button onclick="switchTab('students')" id="tab-students" style="padding: 0.75rem 1.5rem; background: none; border: none; font-size: 1.1rem; font-weight: 600; cursor: pointer; border-bottom: 3px solid var(--accent-primary); color: var(--text-primary);">Étudiants & Progression</button>
        <button onclick="switchTab('resources')" id="tab-resources" style="padding: 0.75rem 1.5rem; background: none; border: none; font-size: 1.1rem; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; color: var(--text-secondary);">Contenu du Cours</button>
    </div>

    <!-- ONGLET ETUDIANTS -->
    <div id="content-students">
        <div style="background: var(--bg-surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <h3 style="margin-bottom: 1.5rem; color: var(--text-primary);">Suivi de la Progression des Étudiants</h3>
            <?php if (empty($students)): ?>
                <p style="color: var(--text-secondary);">Aucun étudiant n'est inscrit à cette formation.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-secondary);">
                            <th style="padding: 1rem;">Étudiant</th>
                            <th style="padding: 1rem;">Statut</th>
                            <th style="padding: 1rem;">Progression (Auto)</th>
                            <th style="padding: 1rem; text-align: right;">Bilan Cognitif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem; font-weight: 600; color: var(--text-primary);">
                                <?php echo htmlspecialchars($s['nom_etudiant']); ?><br>
                                <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-secondary);"><?php echo htmlspecialchars($s['email'] ?? ''); ?></span>
                            </td>
                            <td style="padding: 1rem;">
                                <span class="badge <?php echo $s['statut'] === 'Terminée' ? 'badge-success' : 'badge-info'; ?>"><?php echo htmlspecialchars($s['statut']); ?></span>
                            </td>
                            <td style="padding: 1rem; width: 250px;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="flex: 1; height: 8px; background: var(--bg-tertiary); border-radius: 4px; overflow: hidden;">
                                        <div id="prog-bar-<?php echo $s['id_user']; ?>" style="width: <?php echo $s['progression']; ?>%; height: 100%; background: var(--gradient-primary); transition: width 0.3s;"></div>
                                    </div>
                                    <span id="prog-text-<?php echo $s['id_user']; ?>" style="font-weight: 600; font-size: 0.9rem; min-width: 40px; text-align: right;"><?php echo $s['progression']; ?>%</span>
                                </div>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <button onclick="showStudentEmotions(<?php echo $s['id_user']; ?>, '<?php echo addslashes($s['nom_etudiant']); ?>')" class="btn" style="padding: 0.4rem 0.8rem; background: var(--accent-info-light); color: var(--accent-info); border: 1px solid var(--accent-info); white-space: nowrap; border-radius: 8px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='var(--accent-info)'; this.style.color='white';" onmouseout="this.style.background='var(--accent-info-light)'; this.style.color='var(--accent-info)';">
                                    🧠 Bilan IA
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ONGLET RESSOURCES -->
    <div id="content-resources" style="display: none;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Ajouter Form -->
            <div style="background: var(--bg-surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0; color: var(--text-primary);">Ajouter une ressource</h3>
                    <button onclick="generateSyllabusIA()" id="ai-btn" class="btn" 
                            style="background: var(--gradient-primary); color: white; border: none; font-size: 0.85rem; padding: 0.5rem 1rem;"
                            title="L'IA (Llama 3) analysera le titre du cours pour générer automatiquement un Syllabus détaillé que vous pourrez modifier.">
                        <i data-lucide="sparkles" style="width:14px; height:14px; margin-right: 5px;"></i> Assistant IA
                    </button>
                </div>
                <form id="addResourceForm" onsubmit="addResource(event)">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-secondary);">Type</label>
                        <select name="type" id="resource_type" onchange="toggleResourceInput()" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary);">
                            <option value="video">Vidéo (Lien YouTube, Vimeo...)</option>
                            <option value="pdf">Document PDF (Fichier)</option>
                            <option value="quiz">Quiz Externe (Google Forms, Typeform...)</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-secondary);">Titre</label>
                        <input type="text" name="titre" id="resource_titre" placeholder="Ex: Chapitre 1 - Introduction" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary);">
                    </div>
                    <div id="url_container" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-secondary);">URL de la ressource</label>
                        <input type="url" name="url" id="url_input" placeholder="https://..." style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary);">
                    </div>
                    <div id="file_container" style="margin-bottom: 1.5rem; display: none;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-secondary);">Fichier PDF</label>
                        <input type="file" name="fichier_pdf" id="file_input" accept="application/pdf" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary);">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Ajouter</button>
                </form>
            </div>

            <!-- Liste des ressources -->
            <div style="background: var(--bg-surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
                <h3 style="margin-bottom: 1.5rem; color: var(--text-primary);">Ressources Actuelles</h3>
                <div id="resource-list" style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php if (empty($resources)): ?>
                        <p style="color: var(--text-secondary);">Aucune ressource pour le moment.</p>
                    <?php else: ?>
                        <?php foreach($resources as $res): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-card);">
                            <div>
                                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">[<?php echo strtoupper($res['type']); ?>] <?php echo htmlspecialchars($res['titre']); ?></div>
                                <a href="<?php echo htmlspecialchars($res['url']); ?>" target="_blank" style="font-size: 0.85rem; color: #3498db; text-decoration: none;">Voir le lien</a>
                            </div>
                            <button onclick="deleteResource('<?php echo $res['id']; ?>')" class="btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444; padding: 0.4rem; border-radius: 6px;">🗑️</button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function switchTab(tab) {
        document.getElementById('content-students').style.display = tab === 'students' ? 'block' : 'none';
        document.getElementById('content-resources').style.display = tab === 'resources' ? 'block' : 'none';
        
        document.getElementById('tab-students').style.borderBottomColor = tab === 'students' ? 'var(--accent-primary)' : 'transparent';
        document.getElementById('tab-students').style.color = tab === 'students' ? 'var(--text-primary)' : 'var(--text-secondary)';
        
        document.getElementById('tab-resources').style.borderBottomColor = tab === 'resources' ? 'var(--accent-primary)' : 'transparent';
        document.getElementById('tab-resources').style.color = tab === 'resources' ? 'var(--text-primary)' : 'var(--text-secondary)';
    }

    function toggleResourceInput() {
        const type = document.getElementById('resource_type').value;
        
        if (type === 'pdf') {
            document.getElementById('url_container').style.display = 'none';
            document.getElementById('file_container').style.display = 'block';
        } else {
            document.getElementById('url_container').style.display = 'block';
            document.getElementById('file_container').style.display = 'none';
        }
    }

    function updateProg(idUser, prog) {
        const formData = new FormData();
        formData.append('action', 'update_progression');
        formData.append('id_formation', <?php echo $id_formation; ?>);
        formData.append('id_user', idUser);
        formData.append('progression', prog);

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('prog-bar-' + idUser).style.width = prog + '%';
                document.getElementById('prog-text-' + idUser).innerText = prog + '%';
                // Feature 3 : Confettis si 100% !
                if (parseInt(prog) === 100) {
                    if (typeof confetti !== 'undefined') {
                        confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 },
                            colors: ['#6366f1','#8b5cf6','#10b981','#f59e0b','#ffffff'] });
                    }
                    // SweetAlert de félicitations
                    setTimeout(() => {
                        Swal.fire({ icon: 'success', title: '🎉 Formation Terminée !',
                            text: `L'étudiant a validé 100% du cours. Un certificat est maintenant disponible.`,
                            timer: 3500, showConfirmButton: false,
                            background: 'var(--bg-card)', color: 'var(--text-primary)' });
                    }, 600);
                }
            } else {
                alert('Erreur lors de la mise à jour.');
            }
        });
    }

    function addResource(e) {
        e.preventDefault();

        // --- Contrôle de saisie JS (pas de HTML5 required) ---
        const type = document.getElementById('resource_type').value;
        const titre = document.getElementById('resource_titre').value.trim();
        const urlInput = document.getElementById('url_input');
        const fileInput = document.getElementById('file_input');

        if (!titre) {
            aptusAlert('Le titre de la ressource est obligatoire.', 'error');
            document.getElementById('resource_titre').style.borderColor = '#ef4444';
            return;
        }
        document.getElementById('resource_titre').style.borderColor = '';

        if (type === 'pdf') {
            if (!fileInput.files || fileInput.files.length === 0) {
                aptusAlert('Veuillez sélectionner un fichier PDF.', 'error');
                fileInput.style.borderColor = '#ef4444';
                return;
            }
            fileInput.style.borderColor = '';
        } else {
            if (!urlInput.value.trim()) {
                aptusAlert('L\'URL de la ressource est obligatoire.', 'error');
                urlInput.style.borderColor = '#ef4444';
                return;
            }
            urlInput.style.borderColor = '';
        }
        // --- Fin contrôle de saisie ---

        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'add_resource');
        formData.append('id_formation', <?php echo $id_formation; ?>);

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                aptusAlert('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
            }
        })
        .catch(err => {
            aptusAlert('Erreur réseau / serveur.', 'error');
            console.error(err);
        });
    }

    function showSkeletonLoader() {
        const skeletonItem = (w1, w2) => `
            <div style="background:var(--bg-card); padding:1rem; border-radius:8px; border:1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="height:16px; width:${w1}; background:linear-gradient(90deg,var(--bg-surface) 25%,var(--border-color) 50%,var(--bg-surface) 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:4px;"></div>
                    <div style="height:18px; width:60px; background:linear-gradient(90deg,var(--bg-surface) 25%,var(--border-color) 50%,var(--bg-surface) 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:20px;"></div>
                </div>
                <div style="height:12px; width:${w2}; background:linear-gradient(90deg,var(--bg-surface) 25%,var(--border-color) 50%,var(--bg-surface) 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:4px; margin-left:28px;"></div>
            </div>`;
        return `
            <style>@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }</style>
            <p style="color:var(--text-secondary); margin-bottom:1.5rem;">
                <div style="height:14px; width:80%; background:linear-gradient(90deg,var(--bg-surface) 25%,var(--border-color) 50%,var(--bg-surface) 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; border-radius:4px;"></div>
            </p>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                ${skeletonItem('55%','75%')}
                ${skeletonItem('45%','65%')}
                ${skeletonItem('60%','80%')}
                ${skeletonItem('50%','70%')}
            </div>`;
    }

    function generateSyllabusIA() {
        const btn = document.getElementById('ai-btn');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader-2" style="width:14px;height:14px;animation:spin 1s linear infinite;"></i> Collaboration IA...';
        btn.disabled = true;

        // Show skeleton immediately in a SweetAlert modal
        Swal.fire({
            title: '✨ Génération en cours...',
            html: showSkeletonLoader(),
            width: '700px',
            showConfirmButton: false,
            allowOutsideClick: false,
            background: 'var(--bg-card)',
            color: 'var(--text-primary)',
        });

        const formData = new FormData();
        formData.append('action', 'generate_ai_syllabus');
        formData.append('titre', '<?php echo addslashes($formation['titre']); ?>');
        formData.append('domaine', '<?php echo addslashes($formation['domaine']); ?>');
        formData.append('niveau', '<?php echo addslashes($formation['niveau']); ?>');

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            
            if (data.success) {
                const syllabus = data.data.syllabus;
                const resume = data.data.resume_global;
                
                let html = `
                <style>
                    .swal-ai-custom { border-radius: 24px !important; padding: 2rem !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important; border: 1px solid var(--border-color); }
                    .swal2-title { font-size: 1.5rem !important; font-weight: 800 !important; color: var(--accent-primary) !important; margin-bottom: 0.5rem !important; }
                    .ai-chap-item { background: var(--bg-surface); padding: 1.25rem; border-radius: 14px; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.2s ease; margin-bottom: 0.85rem; display: block; text-align: left; }
                    .ai-chap-item:hover { border-color: var(--accent-primary); box-shadow: 0 6px 16px rgba(107, 52, 163, 0.1); transform: translateY(-2px); }
                    .ai-chap-title { color: var(--text-primary); font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 12px; }
                    .ai-chap-checkbox { width: 20px; height: 20px; accent-color: var(--accent-primary); cursor: pointer; flex-shrink: 0; }
                    .ai-chap-desc { font-size: 0.9rem; color: var(--text-secondary); margin: 8px 0 0 32px; line-height: 1.6; }
                    .ai-resume { font-size: 0.95rem; color: var(--text-primary); line-height: 1.6; margin-bottom: 1.5rem; text-align: left; background: linear-gradient(145deg, rgba(107,52,163,0.05) 0%, rgba(107,52,163,0.01) 100%); padding: 1.25rem; border-radius: 14px; border: 1px dashed rgba(107, 52, 163, 0.3); position: relative; }
                    .swal2-confirm { border-radius: 12px !important; font-weight: 700 !important; padding: 0.75rem 1.5rem !important; background: var(--gradient-primary) !important; box-shadow: var(--shadow-glow) !important; }
                    .swal2-cancel { border-radius: 12px !important; font-weight: 600 !important; padding: 0.75rem 1.5rem !important; }
                </style>
                <div class="ai-resume">
                    <span style="position:absolute; top:-12px; left:16px; background:var(--bg-card); padding:0 8px; font-size:0.8rem; font-weight:700; color:var(--accent-primary);">💡 Résumé de l'IA</span>
                    ${resume}
                </div>
                <div style="max-height: 420px; overflow-y: auto; padding-right: 10px; margin-right: -10px;">
                `;
                
                syllabus.forEach((chap, idx) => {
                    html += `
                        <label class="ai-chap-item">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div class="ai-chap-title">
                                    <input type="checkbox" class="ai-chap-checkbox" value="${idx}" checked>
                                    <span><span style="color:var(--accent-primary);">#${idx+1}</span> ${chap.chapitre}</span>
                                </div>
                                <span class="badge" style="font-size:0.75rem; font-weight:700; background:rgba(99,102,241,0.1); color:#6366f1; border:none; padding:4px 8px; border-radius:6px;">⏳ ${chap.duree}</span>
                            </div>
                            <p class="ai-chap-desc">${chap.description}</p>
                        </label>
                    `;
                });
                html += `</div>`;
                
                Swal.fire({
                    title: '✨ Syllabus Généré par IA',
                    html: html,
                    width: '700px',
                    showCancelButton: true,
                    confirmButtonText: '🪄 Ajouter ces chapitres',
                    cancelButtonText: 'Annuler',
                    background: 'var(--bg-card)',
                    color: 'var(--text-primary)',
                    customClass: { popup: 'swal-ai-custom' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const checkboxes = document.querySelectorAll('.ai-chap-checkbox');
                        const selectedChapters = [];
                        checkboxes.forEach(cb => {
                            if(cb.checked) selectedChapters.push(syllabus[parseInt(cb.value)]);
                        });
                        if(selectedChapters.length > 0) {
                            applySyllabus(resume, selectedChapters);
                        } else {
                            alert('Aucun chapitre sélectionné.');
                        }
                    }
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Erreur IA', text: data.message });
            }
        })
        .catch(err => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            Swal.fire({ icon: 'error', title: 'Connexion impossible', text: "Vérifiez votre clé API Groq." });
        });
    }


    async function applySyllabus(resume, chapters) {

        Swal.fire({ title: 'Application...', didOpen: () => { Swal.showLoading(); } });
        
        let htmlSyllabus = `<hr style="margin: 2rem 0; border: none; border-top: 1px dashed var(--border-color);"><h4 style="color:var(--accent-primary);">Syllabus Proposé</h4><p style="margin-bottom:1rem;">${resume}</p><ul style="list-style-type:none; padding:0; margin:0;">`;
        for (const chap of chapters) {
            htmlSyllabus += `
                <li style="margin-bottom: 1rem; background: var(--bg-card); padding: 1rem; border-radius: 8px; border-left: 4px solid var(--accent-primary);">
                    <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">${chap.chapitre} <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-secondary); float: right;">⏳ ${chap.duree}</span></div>
                    <div style="font-size: 0.95rem; color: var(--text-secondary);">${chap.description}</div>
                </li>
            `;
        }
        htmlSyllabus += `</ul>`;

        const formData = new FormData();
        formData.append('action', 'append_ai_syllabus');
        formData.append('id_formation', <?php echo $id_formation; ?>);
        formData.append('html_content', htmlSyllabus);
        
        try {
            const response = await fetch('ajax_handler.php', { method: 'POST', body: formData });
            const resultText = await response.text();
            const data = JSON.parse(resultText);
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Syllabus ajouté !',
                    text: 'Le syllabus a été inséré dans la description de la formation.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert("Erreur lors de l'enregistrement du syllabus : " + resultText);
            }
        } catch(e) {
            alert("Erreur JS / Serveur : " + e.message);
        }
    }

    function deleteResource(resId) {
        if (!confirm('Supprimer cette ressource ?')) return;

        const formData = new FormData();
        formData.append('action', 'delete_resource');
        formData.append('id_formation', <?php echo $id_formation; ?>);
        formData.append('resource_id', resId);

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erreur.');
            }
        });
    }
</script>

<!-- JS: SweetAlert, Confetti, Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function showStudentEmotions(idCandidat, nom) {
        Swal.fire({
            title: `Bilan IA - ${nom}`,
            html: `
                <div style="display:flex; flex-direction:column; align-items:center; gap: 1.5rem;">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="emotionsChart"></canvas>
                    </div>
                    <div id="ai-recommandations" style="text-align: left; width: 100%; min-height: 100px; background: var(--bg-surface); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                        <div style="text-align:center; color: var(--text-secondary);">
                            <i data-lucide="loader-2" style="width: 20px; height: 20px; animation: spin 1s linear infinite;"></i>
                            <p>Analyse cognitive en cours par Groq (Llama 3)...</p>
                        </div>
                    </div>
                </div>
            `,
            width: '600px',
            showConfirmButton: true,
            confirmButtonText: 'Fermer',
            confirmButtonColor: 'var(--accent-primary)',
            background: 'var(--bg-card)',
            color: 'var(--text-primary)',
            customClass: { popup: 'swal-ai-custom' },
            didOpen: () => {
                lucide.createIcons();
                // 1. Fetch data
                const formData = new FormData();
                formData.append('action', 'get_emotion_stats');
                formData.append('id_candidat', idCandidat);
                formData.append('id_formation', <?php echo $id_formation; ?>);

                fetch('ajax_handler.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.stats.length > 0) {
                        const labels = data.stats.map(s => s.emotion_detectee);
                        const values = data.stats.map(s => parseInt(s.count));

                        // 2. Draw Chart
                        const ctx = document.getElementById('emotionsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6366f1', '#64748b'],
                                    borderWidth: 0
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false }
                        });

                        // 3. Ask Groq
                        askGroqRecommandations(data.stats);
                    } else {
                        document.getElementById('ai-recommandations').innerHTML = '<p style="text-align:center;">Aucune donnée émotionnelle enregistrée pour cet étudiant (Il n\'a pas encore utilisé le laboratoire).</p>';
                    }
                }).catch(err => {
                    console.error(err);
                    document.getElementById('ai-recommandations').innerHTML = '<p style="color:#ef4444;">Erreur serveur.</p>';
                });
            }
        });
    }

    function askGroqRecommandations(stats) {
        const formData = new FormData();
        formData.append('action', 'analyze_student_emotions');
        formData.append('stats', JSON.stringify(stats));

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('ai-recommandations');
            if (data.success && data.data) {
                const info = data.data;
                // Support both snake_case and camelCase from AI
                const analysis = info.analyse_globale || info.analyseGlobale || "Analyse indisponible";
                const tips = info.conseils || info.tips || [];

                let html = `
                    <h4 style="color: var(--accent-primary); margin-bottom: 0.5rem; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="brain-circuit" style="width:18px;height:18px;"></i> Agent Pédagogique Aptus
                    </h4>
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">${analysis}</p>
                    <ul style="padding-left: 1.2rem; font-size: 0.9rem; font-weight: 500; color: var(--text-primary);">
                `;
                tips.forEach(c => {
                    html += `<li style="margin-bottom: 0.5rem;">${c}</li>`;
                });
                html += '</ul>';
                container.innerHTML = html;
                lucide.createIcons();
            } else {
                container.innerHTML = `<p style="color:#ef4444;">Erreur de l'Agent IA : ${data.message || 'Réponse invalide'}</p>`;
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            document.getElementById('ai-recommandations').innerHTML = `<p style="color:#ef4444;">Erreur réseau IA.</p>`;
        });
    }

    // Le reste du code existant...
    function switchTab(tab) {
