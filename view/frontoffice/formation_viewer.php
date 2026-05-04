<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Visionneuse de Cours - Aptus AI";

require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();

$id_formation = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$id_user = SessionManager::getUserId();

// 🛠️ MVC COMPLIANCE : On délègue toute la logique au Controller
$formationC = new FormationController();
$data = $formationC->getFormationViewerData($id_formation, $id_user);

if (!$data)
    die("Formation introuvable.");

// Extraction des données pour le View (Variables propres et prêtes à l'affichage)
$formation = $data['formation'];
$resources = $data['resources'];
$current_progression = $data['current_progression'];
$viewed_chapters = $data['viewed_chapters'];
$clean_desc = $data['clean_desc'];
$word_count = $data['word_count'];
$min_read_seconds = $data['min_read_seconds'];
$has_chapters = $data['has_chapters'];
$total_chapters = $data['total_chapters'];

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- ══ Aptus Market Update ════════════════════════════════════ -->
<style>
    @keyframes slideDownFade {
        0% {
            opacity: 0;
            transform: translateY(-15px) scale(0.98);
        }

        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes starPulse {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }

        50% {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.8);
        }
    }
</style>
<div id="aptus-market-update" style="display:none;margin-bottom:1.5rem;border-radius:20px;padding:1.5rem;
     background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.05));
     backdrop-filter: blur(10px);
     border:1px solid rgba(99,102,241,0.3); box-shadow: 0 10px 30px rgba(99,102,241,0.15);
     animation: slideDownFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
    <div style="display:flex;align-items:flex-start;gap:1.25rem;">
        <div
            style="width:45px;height:45px;flex-shrink:0;border-radius:14px;display:flex;align-items:center;justify-content:center;
                    background:linear-gradient(135deg, #6366f1, #8b5cf6);font-size:1.3rem; animation: starPulse 2s infinite;">
            🔔</div>
        <div style="flex:1;">
            <div
                style="font-size:0.7rem;font-weight:800;letter-spacing:0.12em;color:var(--accent-primary);text-transform:uppercase;margin-bottom:0.4rem;">
                NOUVEAUTÉS APTUS — <?php echo $data['current_month_fr']; ?>
            </div>
            <h4 id="aptus-update-headline"
                style="margin:0 0 0.5rem;font-size:1.1rem;color:var(--text-primary);font-weight:800;"></h4>
            <p id="aptus-update-content" style="margin:0;font-size:0.9rem;line-height:1.6;color:var(--text-secondary);">
            </p>
        </div>
        <button onclick="document.getElementById('aptus-market-update').style.display='none'"
            style="background:none;border:none;cursor:pointer;color:var(--text-tertiary);font-size:1.3rem;flex-shrink:0;padding:0.2rem;line-height:1;
                       transition:all 0.2s; border-radius:50%; width:30px; height:30px; display:flex; align-items:center; justify-content:center;"
            onmouseover="this.style.color='white'; this.style.background='rgba(239, 68, 68, 0.8)'"
            onmouseout="this.style.color='var(--text-tertiary)'; this.style.background='none'">×</button>
    </div>
</div>

<!-- ══ Barre de progression ════════════════════════════════════ -->
<div style="background:var(--bg-card);border-radius:var(--radius-lg);padding:1.1rem 1.5rem;margin-bottom:1.5rem;
            border:1px solid var(--border-color);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;
            box-shadow:var(--shadow-sm);">
    <div style="flex-shrink:0;">
        <div
            style="font-size:0.68rem;font-weight:800;color:var(--accent-primary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.25rem;display:flex;align-items:center;gap:0.4rem;">
            <?php if ($has_chapters): ?>
                <span
                    style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--gradient-primary);"></span>
                Progression par chapitres
            <?php else: ?>
                <span
                    style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--gradient-primary);"></span>
                Progression de lecture
            <?php endif; ?>
        </div>
        <div style="font-size:0.75rem;color:var(--text-secondary);" id="progress-label">
            <?php if ($has_chapters): ?>
                <strong id="chapters-done"
                    style="color:var(--text-primary);"><?php echo count($viewed_chapters); ?></strong> / <strong
                    style="color:var(--text-primary);"><?php echo $total_chapters; ?></strong> chapitres complétés
            <?php else: ?>
                Temps estimé : <strong style="color:var(--text-primary);"><?php echo $data['reading_time_est']; ?>
                    min</strong>
            <?php endif; ?>
        </div>
    </div>
    <div style="flex:1;min-width:140px;">
        <div style="background:var(--bg-secondary);border-radius:var(--radius-full);height:8px;overflow:hidden;">
            <div id="dwell-bar"
                style="height:100%;width:<?php echo $current_progression; ?>%;border-radius:var(--radius-full);
                 background:var(--gradient-primary);transition:width 1.2s cubic-bezier(0.4,0,0.2,1);box-shadow:0 0 10px rgba(107,52,163,0.3);">
            </div>
        </div>
    </div>
    <div id="dwell-pct"
        style="font-size:1rem;font-weight:800;color:var(--accent-primary);flex-shrink:0;min-width:40px;text-align:right;">
        <?php echo $current_progression; ?>%
    </div>
    <div id="dwell-badge" style="display:<?php echo $current_progression >= 100 ? 'inline-flex' : 'none'; ?>;font-size:0.75rem;padding:0.25rem 0.85rem;
         border-radius:var(--radius-full);background: var(--accent-secondary-light);color: var(--accent-secondary);font-weight: 700;
         border:1px solid var(--accent-secondary);align-items:center;gap:0.3rem;">
        <span
            style="display:inline-block;width:6px;height:6px;border-radius:50%;background: var(--accent-secondary);"></span>
        Cours terminé
    </div>
</div>

<!-- ══ Hero Section (Banner) ════════════════════════════════════ -->
<div class="formation-hero" style="
    position: relative; 
    height: 320px; 
    border-radius: 24px; 
    overflow: hidden; 
    margin-bottom: 2rem;
    background-image: url('<?php echo !empty($formation['image_base64']) ? $formation['image_base64'] : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2070'; ?>');
    background-size: cover;
    background-position: center;
    box-shadow: var(--shadow-lg);
">
    <!-- Overlay sombre pour lisibilité maximale -->
    <div style="
        position: absolute; 
        inset: 0; 
        background: linear-gradient(90deg, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.3) 100%);
        display: flex;
        align-items: center;
        padding: 0 4rem;
        justify-content: space-between;
    ">
        <div style="color: white;">
            <span style="
                background: linear-gradient(90deg, #0ea5e9 0%, #8b5cf6 100%); 
                padding: 6px 16px; 
                border-radius: 20px; 
                font-size: 0.75rem; 
                font-weight: 800; 
                text-transform: uppercase;
                margin-bottom: 1.2rem;
                display: inline-block;
                box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
                color: white;
            ">E-LEARNING</span>
            <h1
                style="font-size: 3.2rem; font-weight: 900; margin: 0 0 0.5rem 0; line-height: 1.1; color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                <?php echo htmlspecialchars($formation['titre']); ?>
            </h1>
            <div style="display: flex; align-items: center; gap: 0.8rem;">
                <div
                    style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.25); border:1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center;">
                    👤</div>
                <div style="display: flex; flex-direction: column;">
                    <span
                        style="font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; font-weight: 700;">Formateur</span>
                    <span style="font-size: 1.2rem; font-weight: 700;">Par
                        <?php echo htmlspecialchars($formation['tuteur_nom'] ?? 'Dupont'); ?></span>
                </div>
            </div>
        </div>

        <a href="formations_my.php" style="
            background: #0f172a;
            color: white;
            padding: 14px 28px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.95rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        " onmouseover="this.style.background='white'; this.style.color='black'; this.style.transform='translateY(-2px)'"
            onmouseout="this.style.background='#0f172a'; this.style.color='white'; this.style.transform='translateY(0)'">
            Quitter le cours
        </a>
    </div>
</div>

<!-- ══ Contenu Principal ════════════════════════════════════════ -->
<div
    style="background:var(--bg-card); border-radius:24px; padding:2.5rem; border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
    <div style="margin-bottom:3rem;">
        <h2 style="font-size:1.8rem; margin-bottom:1.5rem; color:var(--text-primary); font-weight: 800;">📖 Description
            du cours</h2>
        <div style="font-size:1.1rem; line-height:1.7; color:var(--text-primary);">
            <?php echo $clean_desc; ?>
        </div>
    </div>

    <hr style="border:none; border-top:1px solid var(--border-color); margin: 3rem 0;">

    <div>
        <h3 style="font-size:1.5rem; margin-bottom:1.8rem; color:var(--text-primary); font-weight: 800;">📚 Chapitres et
            Ressources</h3>
        <?php if (empty($resources)): ?>
            <div
                style="text-align:center; padding:2rem; background:var(--bg-surface); border-radius:16px; border:1px dashed var(--border-color); color:var(--text-secondary);">
                Aucune ressource disponible pour ce cours.
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:0.8rem;">
                <?php foreach ($resources as $idx => $res):
                    $chapter_id = $res['id'] ?? $idx;
                    $is_done = in_array($chapter_id, $viewed_chapters);
                    ?>
                    <div class="chapter-card"
                        style="background:var(--bg-surface); padding:1.25rem 1.5rem; border-radius:16px; border:1px solid <?php echo $is_done ? 'var(--accent-secondary)' : 'var(--border-color)'; ?>; display:flex; align-items:center; gap:1.2rem;">
                        <div
                            style="width:38px; height:38px; border-radius:50%; background:<?php echo $is_done ? 'var(--accent-secondary-light)' : 'var(--accent-primary-light)'; ?>; color:<?php echo $is_done ? 'var(--accent-secondary)' : 'var(--accent-primary)'; ?>; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.95rem; flex-shrink:0;">
                            <?php echo $is_done ? '✓' : ($idx + 1); ?>
                        </div>
                        <div style="flex:1;">
                            <div
                                style="font-size:0.65rem; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.2rem;">
                                Chapitre <?php echo $idx + 1; ?> • <?php echo strtoupper($res['type']); ?>
                            </div>
                            <h4 style="margin:0; font-size:1.05rem; color:var(--text-primary); font-weight: 600;">
                                <?php echo htmlspecialchars($res['titre']); ?></h4>
                        </div>
                        <a href="<?php echo htmlspecialchars($res['url']); ?>" target="_blank"
                            onclick="markChapterOpened('<?php echo $chapter_id; ?>', <?php echo $idx; ?>)"
                            style="padding: 0.6rem 1.2rem; border-radius: 12px; background: <?php echo $is_done ? 'var(--accent-secondary-light)' : 'var(--gradient-primary)'; ?>; color: <?php echo $is_done ? 'var(--accent-secondary)' : 'white'; ?>; font-weight:700; font-size:0.85rem; text-decoration:none; border: <?php echo $is_done ? '1px solid var(--accent-secondary)' : 'none'; ?>;">
                            <?php echo $is_done ? '✓ Vu' : 'Ouvrir →'; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ Chatbot ═══════════════════════════════════════════════ -->
<style>
    .chat-fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--gradient-primary);
        border: none;
        cursor: pointer;
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s;
        color: white;
        font-size: 24px;
    }

    .chat-fab:hover {
        transform: scale(1.1);
    }

    .chat-window {
        position: fixed;
        bottom: 6rem;
        right: 2rem;
        z-index: 9998;
        width: 380px;
        max-height: 500px;
        border-radius: 16px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        display: none;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-window.open {
        display: flex;
        animation: chatSlideUp 0.3s ease;
    }

    @keyframes chatSlideUp {
        from {
            transform: translateY(20px);
            opacity: 0
        }

        to {
            transform: translateY(0);
            opacity: 1
        }
    }

    .chat-header {
        padding: 1rem 1.25rem;
        background: var(--gradient-primary);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        max-height: 320px;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .chat-msg {
        max-width: 85%;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 0.9rem;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .chat-msg.user {
        align-self: flex-end;
        background: var(--accent-primary);
        color: white;
    }

    .chat-msg.ai {
        align-self: flex-start;
        background: var(--bg-surface);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .chat-msg.ai .ai-badge {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--accent-primary);
        margin-bottom: 0.25rem;
        display: block;
    }

    .chat-input-area {
        padding: 0.75rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 0.5rem;
    }

    .chat-input-area input {
        flex: 1;
        padding: 0.6rem 1rem;
        border-radius: 25px;
        border: 1px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-primary);
        font-size: 0.9rem;
        outline: none;
    }

    .chat-input-area button {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--gradient-primary);
        border: none;
        cursor: pointer;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<button class="chat-fab" onclick="toggleChat()">💬</button>
<div class="chat-window" id="chat-window">
    <div class="chat-header">
        <div>
            <h4 style="margin:0;font-size:0.95rem;">🤖 Assistant IA</h4><span
                style="font-size:0.75rem;opacity:0.8;"><?php echo htmlspecialchars($formation['titre']); ?></span>
        </div>
        <div style="display:flex; align-items:center;">
            <button type="button" onclick="genererFicheDepuisChat(event)" class="btn-generate-fiche"
                style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 10px; border-radius: 6px; font-size: 0.7rem; cursor: pointer; margin-right: 12px; transition: all 0.2s; display:flex; align-items:center; gap:5px; font-weight:600;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i data-lucide="file-text" style="width:14px; height:14px;"></i> Fiche
            </button>
            <button onclick="toggleChat()"
                style="background:none;border:none;color:white;font-size:18px;cursor:pointer;">✕</button>
        </div>
    </div>
    <div class="chat-messages" id="chat-messages">
        <div class="chat-msg ai"><span class="ai-badge">🤖 Assistant IA</span>Bonjour ! Posez-moi vos questions sur
            <strong><?php echo htmlspecialchars($formation['titre']); ?></strong>.</div>
    </div>
    <div class="chat-input-area">
        <input type="text" id="chat-input" placeholder="Posez votre question..."
            onkeypress="if(event.key==='Enter') sendChatMsg()">
        <button onclick="sendChatMsg()">➤</button>
    </div>
</div>

<script>
    // ═══════════════════════════════════════════════════════════
    // SYSTÈME DE PROGRESSION HYBRIDE
    // Mode A : Chapitres  → chaque ressource ouverte = 100/N %
    // Mode B : Dwell Time → plancher MIN 3 minutes (180s)
    // ═══════════════════════════════════════════════════════════
    const HAS_CHAPTERS = <?php echo $has_chapters ? 'true' : 'false'; ?>;
    const TOTAL_CHAPTERS = <?php echo $total_chapters; ?>;
    const FORMATION_ID = <?php echo $id_formation; ?>;
    const USER_ID = <?php echo $id_user; ?>;
    const WORD_COUNT = <?php echo $word_count; ?>;
    // Plancher réaliste : minimum 180s (3 min) même pour les courts textes
    const MIN_READ_SEC = <?php echo $min_read_seconds; ?>;

    let currentProg = <?php echo $current_progression; ?>;

    const bar = document.getElementById('dwell-bar');
    const pctEl = document.getElementById('dwell-pct');
    const badge = document.getElementById('dwell-badge');

    function updateBar(pct) {
        pct = Math.min(100, Math.max(currentProg, pct));
        bar.style.width = pct + '%';
        pctEl.textContent = pct + '%';
        if (pct < 30) bar.style.background = 'linear-gradient(90deg,#ef4444,#f59e0b)';
        else if (pct < 70) bar.style.background = 'linear-gradient(90deg,#f59e0b,#6366f1)';
        else bar.style.background = 'linear-gradient(90deg,#6366f1,#10b981)';
        if (pct >= 100 && badge) badge.style.display = 'inline-flex';
    }

    function sendProgressAjax(pct, mode) {
        if (!USER_ID) return;
        if (pct <= currentProg) return; // pas de régression

        const fd = new FormData();
        fd.append('action', 'update_dwell_progression');
        fd.append('id_formation', FORMATION_ID);
        fd.append('id_user', USER_ID);
        fd.append('new_prog', pct);
        fd.append('mode', mode); // 'chapter' ou 'dwell'

        fetch('ajax_handler.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { if (d.success) { currentProg = d.progression; updateBar(currentProg); } })
            .catch(() => { });
    }

    // ─── MODE A : CHAPITRES ────────────────────────────────────
    function markChapterOpened(chapterId, idx) {
        if (!HAS_CHAPTERS) return;

        // On envoie l'ID réel au serveur
        const fd = new FormData();
        fd.append('action', 'update_dwell_progression');
        fd.append('id_formation', FORMATION_ID);
        fd.append('id_user', USER_ID);
        fd.append('chapter_id', chapterId);
        fd.append('mode', 'chapter');

        fetch('ajax_handler.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    currentProg = d.progression;
                    updateBar(currentProg);

                    // Mise à jour visuelle de la carte
                    const card = document.querySelector(`.chapter-card[data-chapter-index="${idx}"]`);
                    if (card) {
                        card.style.borderColor = 'var(--accent-secondary)';
                        card.style.background = 'var(--accent-secondary-light)';
                        const num = card.querySelector('[style*="flex-shrink:0"]');
                        if (num) { num.style.color = 'var(--accent-secondary)'; num.style.background = 'var(--accent-secondary-light)'; num.textContent = '✓'; }
                        const btn = card.querySelector('a');
                        if (btn) { btn.textContent = '✓ Vu'; btn.style.color = 'var(--accent-secondary)'; btn.style.background = 'var(--accent-secondary-light)'; btn.style.border = '1px solid var(--accent-secondary)'; }
                    }

                    // Compter les chapitres finis visuellement
                    const doneCount = document.querySelectorAll('.chapter-card').filter(c => c.textContent.includes('✓')).length;
                    const doneLabel = document.getElementById('chapters-done');
                    if (doneLabel) {
                        // On recalcule dynamiquement basé sur le % renvoyé par le serveur
                        doneLabel.textContent = Math.round((currentProg / 100) * TOTAL_CHAPTERS);
                    }
                }
            });
    }

    // ─── MODE B : DWELL TIME (Désactivé si on attend des chapitres) ──
    if (!HAS_CHAPTERS) {
        // On ne fait rien : le tuteur doit d'abord ajouter du contenu pour activer la progression
    }

    // ─── SELF-HEALING SYLLABUS ─────────────────────────────────
    (function () {
        fetch('ajax_handler.php?action=self_healing_syllabus&titre=' + encodeURIComponent(<?php echo json_encode($formation['titre']); ?>))
            .then(r => r.json())
            .then(d => {
                if (d.success && d.has_update) {
                    document.getElementById('aptus-update-headline').textContent = d.headline;
                    document.getElementById('aptus-update-content').textContent = d.content;
                    const p = document.getElementById('aptus-market-update');
                    p.style.cssText += ';display:block;opacity:0;transform:translateY(-8px);transition:opacity 0.5s ease,transform 0.5s ease;';
                    setTimeout(() => { p.style.opacity = '1'; p.style.transform = 'translateY(0)'; }, 100);
                }
            }).catch(() => { });
    })();

    // ─── CHATBOT ───────────────────────────────────────────────
    function toggleChat() {
        document.getElementById('chat-window').classList.toggle('open');
        if (document.getElementById('chat-window').classList.contains('open'))
            document.getElementById('chat-input').focus();
    }

    function sendChatMsg() {
        const input = document.getElementById('chat-input');
        const msg = input.value.trim(); if (!msg) return;
        const div = document.getElementById('chat-messages');
        const ub = document.createElement('div'); ub.className = 'chat-msg user'; ub.textContent = msg; div.appendChild(ub);
        input.value = '';
        const ti = document.createElement('div'); ti.className = 'chat-typing'; ti.id = 'typing-indicator'; ti.textContent = '🤖 L\'IA réfléchit...'; div.appendChild(ti);
        div.scrollTop = div.scrollHeight;
        const fd = new FormData();
        fd.append('action', 'send_chat_message'); fd.append('formation_id', FORMATION_ID); fd.append('receiver_id', 1); fd.append('content', msg);
        fetch('ajax_handler.php', { method: 'POST', body: fd }).then(r => r.json()).then(data => {
            document.getElementById('typing-indicator')?.remove();
            const ab = document.createElement('div'); ab.className = 'chat-msg ai';
            ab.innerHTML = '<span class="ai-badge">🤖 Assistant IA</span>' + (data.success ? data.ai_reply.replace(/\n/g, '<br>') : (data.message || 'Erreur.'));
            div.appendChild(ab); div.scrollTop = div.scrollHeight;
        }).catch(() => { document.getElementById('typing-indicator')?.remove(); });
    }

    function genererFicheDepuisChat(event) {
        if (event) event.preventDefault();
        let chatElements = document.querySelectorAll('.chat-msg');
        if (chatElements.length <= 1) {
            Swal.fire({
                title: 'Oups !',
                text: "Posez d'abord quelques questions à l'assistant pour générer une fiche personnalisée.",
                icon: 'info'
            });
            return;
        }

        let historiqueChat = "";
        chatElements.forEach(el => {
            let sender = el.classList.contains('user') ? "Étudiant : " : "IA : ";
            let text = el.innerText.replace('🤖 Assistant IA', '').trim();
            historiqueChat += sender + text + "\n\n";
        });

        Swal.fire({
            title: 'Génération en cours...',
            html: `
            <div style="margin-top:1rem; text-align:center;">
                <p style="color:var(--text-secondary); font-size:0.9rem; margin-bottom:1.5rem;">Aptus IA analyse votre conversation pour créer une fiche de révision optimale.</p>
                <div style="width: 50px; height: 50px; border: 4px solid var(--accent-primary-light); border-top: 4px solid var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            </div>
        `,
            showConfirmButton: false,
            allowOutsideClick: false,
            customClass: { popup: 'radius-lg' }
        });

        fetch('ajax_handler.php?action=generate_fiche_from_chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_history: historiqueChat })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '✨ Votre Fiche de Révision',
                        html: `
                    <div id="contenu-fiche" style="text-align:left; max-height:480px; overflow-y:auto; padding:2.5rem; border-radius:20px; background:var(--bg-body); border:1px solid var(--border-color); box-shadow:inset 0 4px 20px rgba(0,0,0,0.03); margin-top:1rem;">
                        ${data.fiche_html}
                    </div>
                `,
                        width: '800px',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-download mr-2"></i> Télécharger en PDF',
                        cancelButtonText: 'Fermer',
                        confirmButtonColor: '#00A3DA'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            telechargerFichePDF();
                        }
                    });
                } else {
                    Swal.fire('Erreur', data.message || 'Une erreur est survenue lors de la génération.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Erreur réseau', err.message, 'error');
            });
    }

    /**
     * EXPORT PDF PREMIUM (Version Screenshot)
     * Cette version est robuste et fidèle au design Aptus.
     */
    function telechargerFichePDF() {
        const sourceElement = document.getElementById('contenu-fiche');
        
        if (!sourceElement) {
            alert("Erreur : La div 'contenu-fiche' est introuvable.");
            return;
        }

        const texteHtml = sourceElement.innerHTML.trim();

        // 🕵️ L'ÉTAPE DE DÉBOGAGE CRUCIALE
        console.log("Voici ce que le script essaie d'imprimer :", texteHtml);

        if (texteHtml === "") {
            alert("Attention : La div existe, mais elle est totalement vide au moment du clic !");
            return;
        }

        // 1. On fabrique une page web complète sous forme de texte (String)
        const contenuPourPDF = `
            <div style="background-color: #ffffff; color: #1e293b; font-family: Arial, sans-serif; padding: 40px 60px;">
                <!-- Header Premium -->
                <div style="border-bottom: 2px solid #00A3DA; padding-bottom: 20px; margin-bottom: 30px; overflow: hidden;">
                    <div style="float: left; width: 60%;">
                        <h2 style="margin: 0; color: #00A3DA; font-size: 22px; font-weight: bold;">Aptus Intelligence</h2>
                        <p style="margin: 5px 0 0 0; color: #64748b; font-size: 13px;">Votre assistant d'apprentissage nouvelle génération</p>
                    </div>
                    <div style="float: right; width: 35%; text-align: right;">
                        <h3 style="margin: 0; color: #0f172a; font-size: 16px; font-weight: bold;">Fiche de Révision</h3>
                        <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 12px;">Générée le ${new Date().toLocaleDateString('fr-FR')}</p>
                    </div>
                </div>

                <!-- Contenu de la Fiche -->
                <div id="fiche-corps" style="line-height: 1.6; font-size: 14px;">
                    ${texteHtml}
                </div>

                <!-- Pied de page -->
                <div style="margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; color: #94a3b8; font-size: 11px;">
                    © ${new Date().getFullYear()} Aptus Corp. Document pédagogique généré par IA.
                </div>

                <style>
                    #fiche-corps h1 { color: #00A3DA; text-align: center; text-transform: uppercase; margin-top: 40px; margin-bottom: 30px; font-size: 24px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
                    #fiche-corps h2, #fiche-corps h3 { 
                        color: #0f172a; 
                        border-left: 4px solid #00A3DA; 
                        padding-left: 15px; 
                        margin-top: 30px; 
                        margin-bottom: 15px;
                        font-size: 18px;
                    }
                    #fiche-corps p { margin-bottom: 12px; }
                    #fiche-corps ul { margin-bottom: 20px; }
                    #fiche-corps li { margin-bottom: 8px; }
                    #fiche-corps strong { color: #1e293b; }
                </style>
            </div>
        `;

        const options = {
            margin:       10,
            filename:     'Aptus_Fiche_Revision.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true }, 
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // 2. On donne directement le texte à l'outil ! Plus aucun problème de DOM ou d'écran.
        html2pdf().set(options).from(contenuPourPDF).save().then(() => {
            console.log("PDF téléchargé avec succès via la méthode String !");
        });
    }
</script>