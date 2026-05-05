<?php 
require_once '../../controller/offreC.php';
require_once '../../controller/candidatureC.php';
require_once '../../model/candidature.php';

$offreC = new offreC();
$candidatureC = new candidatureC();

if (!isset($_GET['id'])) {
    header('Location: jobs_feed.php');
    exit();
}

$id_offre = intval($_GET['id']);
$offre = $offreC->getOffreById($id_offre);

if (!$offre) {
    header('Location: jobs_feed.php');
    exit();
}

$pageTitle = "Candidature - " . $offre['titre']; 
$pageCSS = "feeds.css"; 
$userRole = "Candidat"; 

// --- TRAITEMENT DU FORMULAIRE (100% PHP) ---
$cand_errors = [];
$success = false;
$cv_cand_base64 = $_POST['cv_temp_base64'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reponses = trim($_POST['reponses_ques'] ?? '');
    $date_candidature = date('Y-m-d');
    
    if (empty($nom)) $cand_errors['nom'] = "Nom requis.";
    if (empty($prenom)) $cand_errors['prenom'] = "Prénom requis.";
    if (empty($email)) {
        $cand_errors['email'] = "Email requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $cand_errors['email'] = "Email invalide.";
    }
    
    $reponses_text = strip_tags(str_replace('&nbsp;', ' ', $reponses));
    if (empty(trim($reponses_text))) {
        $cand_errors['reponses'] = "La réponse est obligatoire.";
    } elseif (mb_strlen(trim($reponses_text)) < 10) {
        $cand_errors['reponses'] = "Minimum 10 caractères.";
    }
    
    if (isset($_FILES['cv_cand']) && $_FILES['cv_cand']['error'] === UPLOAD_ERR_OK) {
        $cv_cand_base64 = 'data:' . mime_content_type($_FILES['cv_cand']['tmp_name']) . ';base64,' . base64_encode(file_get_contents($_FILES['cv_cand']['tmp_name']));
    } 
    
    if (empty($cv_cand_base64)) {
        $cand_errors['cv_cand'] = "Veuillez joindre votre CV.";
    }
    
    if (empty($cand_errors)) {
        $id_candidat = 1; 
        if ($candidatureC->hasAlreadyApplied($id_candidat, $id_offre)) {
            $cand_errors['global'] = "Vous avez déjà postulé à cette offre.";
        } else {
            $nouvelleCandidature = new candidature($id_candidat, $id_offre, $nom, $prenom, $email, $date_candidature, $reponses, $cv_cand_base64, null, 'En attente');
            $candidatureC->addCandidature($nouvelleCandidature);
            $success = true;
        }
    }
}

if (!isset($content)) {
    $content = __FILE__;
    echo '<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">';
    echo '<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>';
    require_once 'layout_front.php';
} else {
?>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.2);
        --premium-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
    }
    .apply-header {
        position: relative;
        height: 350px;
        border-radius: 0 0 50px 50px;
        overflow: hidden;
        margin-bottom: -100px;
        z-index: 1;
    }
    .apply-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.6);
    }
    .apply-header-content {
        position: absolute;
        bottom: 120px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        width: 100%;
        padding: 0 2rem;
    }
    .main-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2.5rem;
        position: relative;
        z-index: 2;
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    .side-info {
        position: sticky;
        top: 2rem;
        height: fit-content;
    }
    .glass-card {
        background: var(--bg-card);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border-color);
        border-radius: 30px;
        box-shadow: var(--premium-shadow);
        overflow: hidden;
    }
    .form-input {
        background: var(--bg-secondary) !important;
        border: 2px solid transparent !important;
        border-radius: 16px !important;
        padding: 1rem 1.25rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        font-size: 1rem !important;
        color: var(--text-primary) !important;
    }
    .form-input:focus {
        border-color: var(--accent-primary) !important;
        background: var(--bg-card) !important;
        box-shadow: 0 0 0 4px rgba(168, 100, 228, 0.1) !important;
        transform: translateY(-2px);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stagger-1 { animation: fadeInUp 0.5s ease forwards; }
    .stagger-2 { animation: fadeInUp 0.5s ease 0.1s forwards; opacity: 0; }
</style>

<div style="min-height: 100vh; background: var(--bg-secondary); padding-bottom: 5rem;">
    <!-- Hero Banner -->
    <div class="apply-header">
        <?php if (!empty($offre['img_post'])): ?>
            <img src="<?php echo htmlspecialchars($offre['img_post']); ?>" alt="Banner">
        <?php else: ?>
            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #4fb5ff 0%, #a864e4 100%);"></div>
        <?php endif; ?>
        <div class="apply-header-content">
            <h1 style="color: white; font-size: 3rem; font-weight: 900; letter-spacing: -0.03em; margin-bottom: 0.5rem; text-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                Rejoignez <?php echo htmlspecialchars($offre['nom_entreprise']); ?>
            </h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; font-weight: 500;">Propulsez votre carrière avec nous</p>
        </div>
    </div>

    <div class="main-grid">
        <!-- Sidebar Info -->
        <aside class="side-info stagger-1">
            <div class="glass-card" style="padding: 2rem;">
                <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 60px; height: 60px; background: rgba(168, 100, 228, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                        <i data-lucide="briefcase" style="width: 30px; height: 30px;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-tertiary); font-weight: 700;">Poste visé</div>
                        <div style="font-weight: 800; color: var(--text-primary); font-size: 1.1rem;"><?php echo htmlspecialchars($offre['titre']); ?></div>
                    </div>
                </div>

                <div style="display: grid; gap: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-size: 0.95rem;">
                        <i data-lucide="map-pin" style="width: 18px; height: 18px;"></i>
                        <span><?php echo htmlspecialchars($offre['lieu'] ?? 'Tunis, Tunisie'); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-size: 0.95rem;">
                        <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
                        <span><?php echo htmlspecialchars($offre['type'] ?? 'Temps plein'); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-size: 0.95rem;">
                        <i data-lucide="layers" style="width: 18px; height: 18px;"></i>
                        <span><?php echo htmlspecialchars($offre['domaine']); ?></span>
                    </div>
                </div>

                <div style="margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <a href="job_details.php?id=<?php echo $id_offre; ?>" style="display: flex; align-items: center; gap: 0.5rem; color: var(--accent-primary); text-decoration: none; font-weight: 700; font-size: 0.9rem;">
                        <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Retour à la description de l'offre
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Form -->
        <main class="stagger-2">
            <?php if ($success): ?>
                <div class="glass-card" style="padding: 5rem 3rem; text-align: center;">
                    <div style="width: 100px; height: 100px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);">
                        <i data-lucide="check" style="width: 50px; height: 50px;"></i>
                    </div>
                    <h2 style="font-size: 2.5rem; font-weight: 900; color: var(--text-primary); margin-bottom: 1rem;">Candidature envoyée !</h2>
                    <p style="color: var(--text-secondary); font-size: 1.2rem; margin-bottom: 3rem;">Bonne chance ! L'équipe vous recontactera très prochainement.</p>
                    <a href="jobs_feed.php" class="btn btn-primary" style="padding: 1.25rem 3rem; border-radius: 18px; font-weight: 800; text-decoration: none;">Découvrir d'autres offres</a>
                </div>
            <?php else: ?>
                <div class="glass-card" style="padding: 3.5rem;">
                    <form action="" method="POST" enctype="multipart/form-data" id="applyForm" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <input type="hidden" name="submit_application" value="1">
                        <input type="hidden" name="reponses_ques" id="hidden_reponses_ques" value="<?php echo htmlspecialchars($_POST['reponses_ques'] ?? ''); ?>">
                        <input type="hidden" name="cv_temp_base64" value="<?php echo htmlspecialchars($cv_cand_base64 ?? ''); ?>">

                        <div style="grid-column: span 1;">
                            <label style="display: block; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">Prénom</label>
                            <input type="text" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" placeholder="Votre prénom" class="form-input" style="width: 100%;">
                            <?php if(isset($cand_errors['prenom'])): ?><p style="color:#ef4444; font-size:0.8rem; margin-top:0.6rem; font-weight:600;"><?php echo $cand_errors['prenom']; ?></p><?php endif; ?>
                        </div>

                        <div style="grid-column: span 1;">
                            <label style="display: block; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">Nom</label>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" placeholder="Votre nom" class="form-input" style="width: 100%;">
                            <?php if(isset($cand_errors['nom'])): ?><p style="color:#ef4444; font-size:0.8rem; margin-top:0.6rem; font-weight:600;"><?php echo $cand_errors['nom']; ?></p><?php endif; ?>
                        </div>

                        <div style="grid-column: span 2;">
                            <label style="display: block; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">Email professionnel</label>
                            <div style="position: relative;">
                                <i data-lucide="mail" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; color: var(--text-tertiary);"></i>
                                <input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="*****@*****.***" class="form-input" style="width: 100%; padding-left: 3.5rem !important;">
                            </div>
                            <?php if(isset($cand_errors['email'])): ?><p style="color:#ef4444; font-size:0.8rem; margin-top:0.6rem; font-weight:600;"><?php echo $cand_errors['email']; ?></p><?php endif; ?>
                        </div>

                        <div style="grid-column: span 2; margin-top: 1rem;">
                            <div style="background: rgba(168, 100, 228, 0.05); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(168, 100, 228, 0.1); margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 10px; height: 10px; background: var(--accent-primary); border-radius: 50%;"></div>
                                        <span id="question-text" style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($offre['question'] ?? 'Parlez-nous de vous'); ?></span>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <button type="button" id="btn-read-question" style="display: flex; align-items: center; gap: 0.5rem; color: #a864e4; border: 1px solid rgba(168, 100, 228, 0.3); padding: 0.5rem 1rem; border-radius: 20px; transition: all 0.3s; font-weight: 600; font-size: 0.85rem; background: rgba(168, 100, 228, 0.1); cursor: pointer;" title="Écouter la question" onmouseover="this.style.background='rgba(168, 100, 228, 0.2)';" onmouseout="this.style.background='rgba(168, 100, 228, 0.1)';">
                                            <i data-lucide="volume-2" id="read-icon" style="width: 18px; height: 18px;"></i>
                                            <span id="read-text">Écouter</span>
                                        </button>
                                        <button type="button" id="btn-dictation" style="display: flex; align-items: center; gap: 0.5rem; color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.5rem 1rem; border-radius: 20px; transition: all 0.3s; font-weight: 600; font-size: 0.85rem; background: rgba(16, 185, 129, 0.1); cursor: pointer;" title="Dicter la réponse au lieu d'écrire" onmouseover="this.style.background='rgba(16, 185, 129, 0.2)';" onmouseout="this.style.background='rgba(16, 185, 129, 0.1)';">
                                            <i data-lucide="mic" id="mic-icon" style="width: 18px; height: 18px;"></i>
                                            <span id="dictation-text">Dicter ma réponse</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="quill-editor" style="height: 250px; background: var(--bg-card); border-radius: 14px; border: 1px solid var(--border-color); color: var(--text-primary);"><?php echo $_POST['reponses_ques'] ?? ''; ?></div>
                                <?php if(isset($cand_errors['reponses'])): ?><p style="color:#ef4444; font-size:0.8rem; margin-top:1rem; font-weight:600;"><?php echo $cand_errors['reponses']; ?></p><?php endif; ?>
                            </div>
                        </div>

                        <div style="grid-column: span 2;">
                            <label style="display: block; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem;">Curriculum Vitae (PDF)</label>
                            <div style="display: flex; gap: 1.5rem; align-items: center;">
                                <div id="cv-btn" style="flex: 1; position: relative;">
                                    <input type="file" name="cv_cand" id="cv_input" accept=".pdf" style="display: none;">
                                    <button type="button" onclick="document.getElementById('cv_input').click()" style="width: 100%; padding: 2rem; border: 2px dashed var(--border-color); border-radius: 20px; background: var(--bg-secondary); color: var(--text-secondary); cursor: pointer; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; gap: 0.75rem;" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.background='rgba(168, 100, 228, 0.03)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-secondary)';">
                                        <i data-lucide="upload-cloud" style="width: 32px; height: 32px; color: var(--accent-primary);"></i>
                                        <span id="cv_filename" style="font-weight: 600;"><?php echo !empty($cv_cand_base64) ? "✅ CV déjà chargé" : "Sélectionner mon CV"; ?></span>
                                    </button>
                                </div>
                            </div>
                            <?php if(isset($cand_errors['cv_cand'])): ?><p style="color:#ef4444; font-size:0.8rem; margin-top:0.8rem; font-weight:600;"><?php echo $cand_errors['cv_cand']; ?></p><?php endif; ?>
                        </div>

                        <?php if(isset($cand_errors['global'])): ?>
                            <div style="grid-column: span 2; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1.25rem; border-radius: 16px; font-weight: 700; text-align: center; border: 1px solid rgba(239, 68, 68, 0.2);">
                                <?php echo $cand_errors['global']; ?>
                            </div>
                        <?php endif; ?>

                        <div style="grid-column: span 2; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; border-radius: 20px; font-size: 1.25rem; font-weight: 900; border: none; color: white;">
                                Soumettre ma candidature
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();

    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Rédigez votre réponse avec soin...',
        modules: {
            toolbar: [
                [{ 'font': [] }, { 'size': [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

    const form = document.getElementById('applyForm');
    if(form) {
        form.addEventListener('submit', function(e) {
            document.getElementById('hidden_reponses_ques').value = quill.root.innerHTML;
        });
    }

    const cvInput = document.getElementById('cv_input');
    const cvFilename = document.getElementById('cv_filename');
    if(cvInput) {
        cvInput.addEventListener('change', (e) => {
            if(e.target.files.length > 0) {
                cvFilename.textContent = "📄 " + e.target.files[0].name;
                cvFilename.style.color = "var(--accent-primary)";
            }
        });
    }

    // --- SPEECH TO TEXT LOGIC ---
    const btnDictation = document.getElementById('btn-dictation');
    const dictationText = document.getElementById('dictation-text');
    
    // Check support
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (SpeechRecognition && btnDictation) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'fr-FR'; // Support français
        recognition.continuous = true;
        recognition.interimResults = true;
        
        let isRecording = false;
        let dictationAnimation = null;
        
        recognition.onstart = function() {
            isRecording = true;
            btnDictation.style.background = 'rgba(239, 68, 68, 0.1)';
            btnDictation.style.color = '#ef4444';
            btnDictation.style.borderColor = '#ef4444';
            dictationText.innerText = 'Écoute en cours...';
            
            // Pulsing animation
            dictationAnimation = btnDictation.animate([
                { boxShadow: '0 0 0 0 rgba(239, 68, 68, 0.4)' },
                { boxShadow: '0 0 0 10px rgba(239, 68, 68, 0)' }
            ], {
                duration: 1500,
                iterations: Infinity
            });
        };
        
        recognition.onresult = function(event) {
            let finalTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                }
            }
            
            if (finalTranscript) {
                const range = quill.getSelection(true);
                quill.insertText(range.index, finalTranscript + ' ', 'user');
                quill.setSelection(range.index + finalTranscript.length + 1);
            }
        };
        
        recognition.onend = function() {
            isRecording = false;
            btnDictation.style.background = 'rgba(16, 185, 129, 0.1)';
            btnDictation.style.color = '#10b981';
            btnDictation.style.borderColor = 'rgba(16, 185, 129, 0.3)';
            dictationText.innerText = 'Dicter ma réponse';
            if(dictationAnimation) dictationAnimation.cancel();
        };
        
        recognition.onerror = function(event) {
            console.error('Erreur reconnaissance vocale:', event.error);
            isRecording = false;
            btnDictation.style.background = 'rgba(16, 185, 129, 0.1)';
            btnDictation.style.color = '#10b981';
            dictationText.innerText = 'Erreur (Dicter)';
            if(dictationAnimation) dictationAnimation.cancel();
        };
        
        btnDictation.addEventListener('click', function() {
            if (isRecording) {
                recognition.stop();
            } else {
                quill.focus(); // Focus editor before speaking
                recognition.start();
            }
        });
    } else if (btnDictation) {
        btnDictation.style.display = 'none'; // Hide if not supported
    }

    // --- TEXT TO SPEECH LOGIC (READ QUESTION) ---
    const btnRead = document.getElementById('btn-read-question');
    const questionTextElement = document.getElementById('question-text');
    const readIcon = document.getElementById('read-icon');
    const readText = document.getElementById('read-text');

    if ('speechSynthesis' in window && btnRead && questionTextElement) {
        let isPlaying = false;
        let utterance = null;
        let readAnimation = null;

        btnRead.addEventListener('click', function() {
            if (isPlaying) {
                window.speechSynthesis.cancel();
                isPlaying = false;
                readText.innerText = 'Écouter';
                readIcon.setAttribute('data-lucide', 'volume-2');
                lucide.createIcons();
                btnRead.style.background = 'rgba(168, 100, 228, 0.1)';
                btnRead.style.color = '#a864e4';
                btnRead.style.borderColor = 'rgba(168, 100, 228, 0.3)';
                if(readAnimation) readAnimation.cancel();
            } else {
                window.speechSynthesis.cancel(); // Stop any previous
                const textToRead = questionTextElement.innerText || questionTextElement.textContent;
                utterance = new SpeechSynthesisUtterance(textToRead);
                utterance.lang = 'fr-FR'; 
                
                utterance.onstart = function() {
                    isPlaying = true;
                    readText.innerText = 'Arrêter';
                    readIcon.setAttribute('data-lucide', 'square');
                    lucide.createIcons();
                    btnRead.style.background = 'rgba(168, 100, 228, 0.15)';
                    
                    readAnimation = btnRead.animate([
                        { boxShadow: '0 0 0 0 rgba(168, 100, 228, 0.4)' },
                        { boxShadow: '0 0 0 10px rgba(168, 100, 228, 0)' }
                    ], {
                        duration: 1500,
                        iterations: Infinity
                    });
                };
                
                utterance.onend = function() {
                    isPlaying = false;
                    readText.innerText = 'Écouter';
                    readIcon.setAttribute('data-lucide', 'volume-2');
                    lucide.createIcons();
                    btnRead.style.background = 'rgba(168, 100, 228, 0.1)';
                    if(readAnimation) readAnimation.cancel();
                };
                
                utterance.onerror = function() {
                    isPlaying = false;
                    readText.innerText = 'Écouter';
                    readIcon.setAttribute('data-lucide', 'volume-2');
                    lucide.createIcons();
                    btnRead.style.background = 'rgba(168, 100, 228, 0.1)';
                    if(readAnimation) readAnimation.cancel();
                };

                window.speechSynthesis.speak(utterance);
            }
        });
    } else if (btnRead) {
        btnRead.style.display = 'none';
    }

});
</script>

<style>
/* Custom Quill Styles */
.ql-toolbar.ql-snow {
    border: none;
    background: var(--bg-secondary);
    border-radius: 14px 14px 0 0;
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}
.ql-container.ql-snow {
    border: none;
    border-radius: 0 0 14px 14px;
    font-size: 1rem;
    font-family: inherit;
}
.ql-editor {
    padding: 1.25rem;
}
.ql-editor.ql-blank::before {
    color: var(--text-tertiary);
    font-style: normal;
    opacity: 0.5;
}
</style>

<?php } ?>
