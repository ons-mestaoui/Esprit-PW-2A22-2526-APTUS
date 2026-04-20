<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
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
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid #e5e7eb !important;
    }
    .badge-info {
        background: #ebf5ff;
        color: #3b82f6;
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
            <h3 style="margin-bottom: 1.5rem; color: var(--text-primary);">Validation Manuelle de Progression</h3>
            <?php if (empty($students)): ?>
                <p style="color: var(--text-secondary);">Aucun étudiant n'est inscrit à cette formation.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-secondary);">
                            <th style="padding: 1rem;">Étudiant</th>
                            <th style="padding: 1rem;">Statut</th>
                            <th style="padding: 1rem;">Progression</th>
                            <th style="padding: 1rem; text-align: right;">Action</th>
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
                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                    <button onclick="updateProg(<?php echo $s['id_user']; ?>, Math.max(0, parseInt(document.getElementById('prog-text-<?php echo $s['id_user']; ?>').innerText) - 20))" class="btn" style="padding: 0.25rem 0.5rem; background: var(--bg-tertiary); color: var(--text-primary);">-20%</button>
                                    <button onclick="updateProg(<?php echo $s['id_user']; ?>, Math.min(100, parseInt(document.getElementById('prog-text-<?php echo $s['id_user']; ?>').innerText) + 20))" class="btn" style="padding: 0.25rem 0.5rem; background: var(--bg-tertiary); color: var(--text-primary);">+20%</button>
                                    <button onclick="updateProg(<?php echo $s['id_user']; ?>, 100)" class="btn btn-primary" style="padding: 0.25rem 0.75rem;">100%</button>
                                </div>
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
                        <select name="type" id="resource_type" required onchange="toggleResourceInput()" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary);">
                            <option value="video">Vidéo (Lien YouTube, Vimeo...)</option>
                            <option value="pdf">Document PDF (Fichier)</option>
                            <option value="quiz">Quiz Externe (Google Forms, Typeform...)</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-secondary);">Titre</label>
                        <input type="text" name="titre" required placeholder="Ex: Chapitre 1 - Introduction" style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-primary);">
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
        const urlInput = document.getElementById('url_input');
        const fileInput = document.getElementById('file_input');
        
        if (type === 'pdf') {
            document.getElementById('url_container').style.display = 'none';
            document.getElementById('file_container').style.display = 'block';
            urlInput.removeAttribute('required');
            fileInput.setAttribute('required', 'required');
        } else {
            document.getElementById('url_container').style.display = 'block';
            document.getElementById('file_container').style.display = 'none';
            fileInput.removeAttribute('required');
            urlInput.setAttribute('required', 'required');
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
                alert('Erreur: ' + (data.message || 'Erreur inconnue'));
            }
        })
        .catch(err => {
            alert('Erreur réseau / serveur.');
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
                
                let html = `<p style="color:var(--text-secondary); margin-bottom:1.5rem;">${resume}</p>`;
                html += `<div style="display:flex; flex-direction:column; gap:0.75rem; max-height:400px; overflow-y:auto; text-align:left;">`;
                
                syllabus.forEach((chap, idx) => {
                    html += `
                        <label style="display:block; background:var(--bg-card); padding:1rem; border-radius:8px; border:1px solid var(--border-color); cursor:pointer;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:5px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <input type="checkbox" class="ai-chap-checkbox" value="${idx}" checked style="width:18px; height:18px; accent-color:var(--accent-primary);">
                                    <strong style="color:var(--accent-primary);">#${idx+1} ${chap.chapitre}</strong>
                                </div>
                                <span class="badge badge-info" style="font-size:0.7rem;">${chap.duree}</span>
                            </div>
                            <p style="font-size:0.85rem; margin:0; margin-left:28px; opacity:0.8;">${chap.description}</p>
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
            console.log("Raw response:", resultText);
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

<!-- Feature 3 : Canvas Confetti (ultra-léger ~7kb) -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
