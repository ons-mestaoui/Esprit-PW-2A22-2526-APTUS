<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Visionneuse de Cours - Aptus AI";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurDashboardController.php';
require_once __DIR__ . '/../../controller/SessionManager.php';
SessionManager::start();

$formationC = new FormationController();
$tuteurC    = new TuteurDashboardController();

$id_formation = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formation    = $formationC->getFormationById($id_formation);
if (!$formation) die("Formation introuvable.");

$resources = $tuteurC->getResources($id_formation);
$id_user   = SessionManager::getUserId();

// ── Calcul mots & temps (pour mode dwell-time) ──────────────
$clean_desc        = preg_replace('/<!-- APTUS_RESOURCES: .*? -->/s', '', $formation['description']);
$word_count        = str_word_count(strip_tags($clean_desc));
// Plancher réaliste : minimum 3 minutes (180s), même pour les petits cours
$min_read_seconds  = max(180, (int)round($word_count / 4.17));

// ── Mode de progression ──────────────────────────────────────
// Si le tuteur a ajouté des ressources/chapitres → mode Chapitres
// Sinon → mode Dwell Time pur
$has_chapters      = !empty($resources);
$total_chapters    = $has_chapters ? count($resources) : 0;

// Charger la progression actuelle depuis la BDD (via le Controller)
require_once __DIR__ . '/../../controller/InscriptionController.php';
$inscriC = new InscriptionController();
$current_progression = $inscriC->getCurrentProgression($id_formation, $id_user);
$viewed_chapters     = $inscriC->getViewedChapters($id_user, $id_formation);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<!-- ══ Aptus Market Update ════════════════════════════════════ -->
<div id="aptus-market-update" style="display:none;margin-bottom:1.5rem;border-radius:var(--radius-lg);padding:1.25rem 1.5rem;
     background: var(--accent-primary-light);
     border:1px solid var(--accent-primary); box-shadow: var(--shadow-sm);">
    <div style="display:flex;align-items:flex-start;gap:1rem;">
        <div style="width:40px;height:40px;flex-shrink:0;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;
                    background:var(--gradient-primary);font-size:1.1rem;">✨</div>
        <div style="flex:1;">
            <div style="font-size:0.68rem;font-weight:800;letter-spacing:0.12em;color:var(--accent-primary);text-transform:uppercase;margin-bottom:0.3rem;">
                Aptus Market Update — <?php echo date('F Y'); ?>
            </div>
            <h4 id="aptus-update-headline" style="margin:0 0 0.35rem;font-size:0.95rem;color:var(--text-primary);font-weight:700;"></h4>
            <p  id="aptus-update-content"  style="margin:0;font-size:0.85rem;line-height:1.6;color:var(--text-secondary);"></p>
        </div>
        <button onclick="document.getElementById('aptus-market-update').style.display='none'"
                style="background:none;border:none;cursor:pointer;color:var(--text-tertiary);font-size:1.1rem;flex-shrink:0;padding:0.2rem;line-height:1;
                       transition:var(--transition-fast);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-tertiary)'">×</button>
    </div>
</div>

<!-- ══ Barre de progression (Seulement si chapitres présents) ════ -->
<?php if ($has_chapters): ?>
<div style="background:var(--bg-card);border-radius:var(--radius-lg);padding:1.1rem 1.5rem;margin-bottom:1.5rem;
            border:1px solid var(--border-color);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;
            box-shadow:var(--shadow-sm);">
    <div style="flex-shrink:0;">
        <div style="font-size:0.68rem;font-weight:800;color:var(--accent-primary);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.25rem;display:flex;align-items:center;gap:0.4rem;">
            <?php if ($has_chapters): ?>
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--gradient-primary);"></span> Progression par chapitres
            <?php else: ?>
                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--gradient-primary);"></span> Progression de lecture
            <?php endif; ?>
        </div>
        <div style="font-size:0.75rem;color:var(--text-secondary);" id="progress-label">
            <?php if ($has_chapters): ?>
                <strong id="chapters-done" style="color:var(--text-primary);"><?php echo count($viewed_chapters); ?></strong> / <strong style="color:var(--text-primary);"><?php echo $total_chapters; ?></strong> chapitres complétés
            <?php else: ?>
                Temps estimé : <strong style="color:var(--text-primary);"><?php echo max(1,round($word_count/250)); ?> min</strong>
            <?php endif; ?>
        </div>
    </div>
    <div style="flex:1;min-width:140px;">
        <div style="background:var(--bg-secondary);border-radius:var(--radius-full);height:8px;overflow:hidden;">
            <div id="dwell-bar" style="height:100%;width:<?php echo $current_progression; ?>%;border-radius:var(--radius-full);
                 background:var(--gradient-primary);transition:width 1.2s cubic-bezier(0.4,0,0.2,1);box-shadow:0 0 10px rgba(107,52,163,0.3);"></div>
        </div>
    </div>
    <div id="dwell-pct" style="font-size:1rem;font-weight:800;color:var(--accent-primary);flex-shrink:0;min-width:40px;text-align:right;">
        <?php echo $current_progression; ?>%
    </div>
    <div id="dwell-badge" style="display:<?php echo $current_progression >= 100 ? 'inline-flex' : 'none'; ?>;font-size:0.75rem;padding:0.25rem 0.85rem;
         border-radius:var(--radius-full);background: var(--accent-secondary-light);color: var(--accent-secondary);font-weight: 700;
         border:1px solid var(--accent-secondary);align-items:center;gap:0.3rem;">
        <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background: var(--accent-secondary);"></span>
        Cours terminé
    </div>
</div>
<?php endif; ?>

<!-- ══ Contenu Principal ════════════════════════════════════════ -->
<div style="background:var(--bg-card);border-radius:16px;padding:2.5rem;border:1px solid var(--border-color);box-shadow:var(--shadow-sm);">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;border-bottom:1px solid var(--border-color);padding-bottom:1.5rem;">
        <div>
            <h1 style="font-size:2rem;margin-bottom:0.5rem;color:var(--text-primary);"><?php echo htmlspecialchars($formation['titre']); ?></h1>
            <p style="color:var(--text-secondary);margin:0;">
                Tuteur : <strong><?php echo htmlspecialchars($formation['tuteur_nom'] ?? 'Aptus'); ?></strong> |
                Domaine : <strong><?php echo htmlspecialchars($formation['domaine']); ?></strong> |
                Niveau : <strong><?php echo htmlspecialchars($formation['niveau']); ?></strong>
            </p>
        </div>
        <a href="formations_my.php" class="btn btn-secondary">Retour à mes cours</a>
    </div>

    <!-- Description -->
    <div style="margin-bottom:3rem;background:var(--bg-surface);padding:1.5rem;border-radius:12px;border:1px solid var(--border-color);">
        <h3 style="margin-bottom:1rem;color:var(--text-primary);">Description du cours</h3>
        <div style="font-size:1.05rem;line-height:1.6;color:var(--text-primary);"><?php echo $clean_desc; ?></div>
    </div>

    <!-- ══ Ressources / Chapitres ══════════════════════════════ -->
    <div>
        <h2 style="font-size:1.5rem;margin-bottom:1.5rem;color:var(--text-primary);">
            <?php echo $has_chapters ? '📚 Chapitres du cours' : 'Ressources Pédagogiques'; ?>
        </h2>
        <?php if (empty($resources)): ?>
            <div style="text-align:center;padding:2rem;background:var(--bg-surface);border-radius:12px;border:1px dashed var(--border-color);color:var(--text-secondary);">
                <p>Aucun chapitre ajouté par le tuteur pour le moment.</p>
            </div>
        <?php else: ?>
            <!-- Mode Chapitres : chaque ressource = un chapitre avec badge d'état -->
            <div style="display:flex;flex-direction:column;gap:0.6rem;">
                <?php foreach ($resources as $idx => $res):
                    $chapter_id = $res['id'] ?? $idx;
                    $is_done = in_array($chapter_id, $viewed_chapters);
                ?>
                <div class="chapter-card" data-chapter-index="<?php echo $idx; ?>"
                     data-chapter-id="<?php echo htmlspecialchars($chapter_id); ?>"
                     style="display:flex;align-items:center;gap:1rem;
                            background:<?php echo $is_done ? 'var(--accent-secondary-light)' : 'var(--bg-card)'; ?>;
                            border:1px solid <?php echo $is_done ? 'var(--accent-secondary)' : 'var(--border-color)'; ?>;
                            border-radius:var(--radius-md);padding:1rem 1.25rem;
                            transition:var(--transition-base);">

                    <!-- Numéro / Check -->
                    <div style="width:34px;height:34px;border-radius:var(--radius-full);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;
                                background:<?php echo $is_done ? 'var(--accent-secondary-light)' : 'var(--accent-primary-light)'; ?>;
                                color:<?php echo $is_done ? 'var(--accent-secondary)' : 'var(--accent-primary)'; ?>;">
                        <?php echo $is_done ? '✓' : ($idx + 1); ?>
                    </div>

                    <!-- Infos -->
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:0.65rem;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.2rem;">
                            Chapitre <?php echo $idx + 1; ?>
                            <span style="display:inline-block;padding:0.1rem 0.4rem;border-radius:4px;background:var(--accent-primary-light);color:var(--accent-primary);margin-left:0.4rem;">
                                <?php echo strtoupper(htmlspecialchars($res['type'])); ?>
                            </span>
                        </div>
                        <div style="font-size:0.95rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($res['titre']); ?>
                        </div>
                    </div>

                    <!-- Bouton accès -->
                    <a href="<?php echo htmlspecialchars($res['url']); ?>"
                       <?php if ($res['type'] === 'pdf' || strpos($res['url'], 'data:') === 0): ?> download="<?php echo htmlspecialchars($res['titre']); ?>.pdf" <?php endif; ?>
                       target="_blank"
                       onclick="markChapterOpened('<?php echo $chapter_id; ?>', <?php echo $idx; ?>)"
                       style="flex-shrink:0;padding:0.45rem 1rem;border-radius:var(--radius-sm);font-size:0.82rem;font-weight:700;text-decoration:none;
                              transition:var(--transition-fast);
                              <?php if ($is_done): ?>
                                   background: var(--accent-secondary-light);color: var(--accent-secondary);border:1px solid var(--accent-secondary);
                              <?php else: ?>
                                  background:var(--gradient-primary);color:white;border:none;box-shadow:0 2px 8px rgba(107,52,163,0.3);
                              <?php endif; ?>"
                       onmouseover="<?php if (!$is_done): ?>this.style.boxShadow='0 4px 14px rgba(107,52,163,0.4)';this.style.transform='translateY(-1px)';<?php endif; ?>"
                       onmouseout="this.style.boxShadow='<?php echo $is_done ? 'none' : '0 2px 8px rgba(107,52,163,0.3)'; ?>';this.style.transform='none';">
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
.chat-fab{position:fixed;bottom:2rem;right:2rem;z-index:9999;width:60px;height:60px;border-radius:50%;background:var(--gradient-primary);border:none;cursor:pointer;box-shadow: var(--shadow-lg);display:flex;align-items:center;justify-content:center;transition:transform 0.3s;color:white;font-size:24px;}
.chat-fab:hover{transform:scale(1.1);}
.chat-window{position:fixed;bottom:6rem;right:2rem;z-index:9998;width:380px;max-height:500px;border-radius:16px;background:var(--bg-card);border:1px solid var(--border-color);box-shadow:0 20px 60px rgba(0,0,0,0.15);display:none;flex-direction:column;overflow:hidden;}
.chat-window.open{display:flex;animation:chatSlideUp 0.3s ease;}
@keyframes chatSlideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
.chat-header{padding:1rem 1.25rem;background:var(--gradient-primary);color:white;display:flex;justify-content:space-between;align-items:center;}
.chat-messages{flex:1;padding:1rem;overflow-y:auto;max-height:320px;display:flex;flex-direction:column;gap:0.75rem;}
.chat-msg{max-width:85%;padding:0.75rem 1rem;border-radius:12px;font-size:0.9rem;line-height:1.5;word-wrap:break-word;}
.chat-msg.user{align-self:flex-end;background:var(--accent-primary);color:white;}
.chat-msg.ai{align-self:flex-start;background:var(--bg-surface);color:var(--text-primary);border:1px solid var(--border-color);}
.chat-msg.ai .ai-badge{font-size:0.7rem;font-weight:700;color:var(--accent-primary);margin-bottom:0.25rem;display:block;}
.chat-input-area{padding:0.75rem;border-top:1px solid var(--border-color);display:flex;gap:0.5rem;}
.chat-input-area input{flex:1;padding:0.6rem 1rem;border-radius:25px;border:1px solid var(--border-color);background:var(--bg-surface);color:var(--text-primary);font-size:0.9rem;outline:none;}
.chat-input-area button{width:38px;height:38px;border-radius:50%;background:var(--gradient-primary);border:none;cursor:pointer;color:white;display:flex;align-items:center;justify-content:center;}
</style>

<button class="chat-fab" onclick="toggleChat()">💬</button>
<div class="chat-window" id="chat-window">
    <div class="chat-header">
        <div><h4 style="margin:0;font-size:0.95rem;">🤖 Assistant IA</h4><span style="font-size:0.75rem;opacity:0.8;"><?php echo htmlspecialchars($formation['titre']); ?></span></div>
        <button onclick="toggleChat()" style="background:none;border:none;color:white;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div class="chat-messages" id="chat-messages">
        <div class="chat-msg ai"><span class="ai-badge">🤖 Assistant IA</span>Bonjour ! Posez-moi vos questions sur <strong><?php echo htmlspecialchars($formation['titre']); ?></strong>.</div>
    </div>
    <div class="chat-input-area">
        <input type="text" id="chat-input" placeholder="Posez votre question..." onkeypress="if(event.key==='Enter') sendChatMsg()">
        <button onclick="sendChatMsg()">➤</button>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════
// SYSTÈME DE PROGRESSION HYBRIDE
// Mode A : Chapitres  → chaque ressource ouverte = 100/N %
// Mode B : Dwell Time → plancher MIN 3 minutes (180s)
// ═══════════════════════════════════════════════════════════
const HAS_CHAPTERS   = <?php echo $has_chapters ? 'true' : 'false'; ?>;
const TOTAL_CHAPTERS = <?php echo $total_chapters; ?>;
const FORMATION_ID   = <?php echo $id_formation; ?>;
const USER_ID        = <?php echo $id_user; ?>;
const WORD_COUNT     = <?php echo $word_count; ?>;
// Plancher réaliste : minimum 180s (3 min) même pour les courts textes
const MIN_READ_SEC   = <?php echo $min_read_seconds; ?>;

let currentProg = <?php echo $current_progression; ?>;

const bar   = document.getElementById('dwell-bar');
const pctEl = document.getElementById('dwell-pct');
const badge = document.getElementById('dwell-badge');

function updateBar(pct) {
    pct = Math.min(100, Math.max(currentProg, pct));
    bar.style.width = pct + '%';
    pctEl.textContent = pct + '%';
    if      (pct < 30)  bar.style.background = 'linear-gradient(90deg,#ef4444,#f59e0b)';
    else if (pct < 70)  bar.style.background = 'linear-gradient(90deg,#f59e0b,#6366f1)';
    else                bar.style.background = 'linear-gradient(90deg,#6366f1,#10b981)';
    if (pct >= 100 && badge) badge.style.display = 'inline-flex';
}

function sendProgressAjax(pct, mode) {
    if (!USER_ID) return;
    if (pct <= currentProg) return; // pas de régression

    const fd = new FormData();
    fd.append('action',       'update_dwell_progression');
    fd.append('id_formation', FORMATION_ID);
    fd.append('id_user',      USER_ID);
    fd.append('new_prog',     pct);
    fd.append('mode',         mode); // 'chapter' ou 'dwell'

    fetch('ajax_handler.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => { if (d.success) { currentProg = d.progression; updateBar(currentProg); } })
        .catch(() => {});
}

// ─── MODE A : CHAPITRES ────────────────────────────────────
function markChapterOpened(chapterId, idx) {
    if (!HAS_CHAPTERS) return;
    
    // On envoie l'ID réel au serveur
    const fd = new FormData();
    fd.append('action',       'update_dwell_progression');
    fd.append('id_formation', FORMATION_ID);
    fd.append('id_user',      USER_ID);
    fd.append('chapter_id',   chapterId);
    fd.append('mode',         'chapter');

    fetch('ajax_handler.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => { 
            if (d.success) { 
                currentProg = d.progression; 
                updateBar(currentProg);
                
                // Mise à jour visuelle de la carte
                const card = document.querySelector(`.chapter-card[data-chapter-index="${idx}"]`);
                if (card) {
                    card.style.borderColor = 'var(--accent-secondary)';
                    card.style.background  = 'var(--accent-secondary-light)';
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
(function() {
    fetch('ajax_handler.php?action=self_healing_syllabus&titre=' + encodeURIComponent(<?php echo json_encode($formation['titre']); ?>))
        .then(r => r.json())
        .then(d => {
            if (d.success && d.has_update) {
                document.getElementById('aptus-update-headline').textContent = d.headline;
                document.getElementById('aptus-update-content').textContent  = d.content;
                const p = document.getElementById('aptus-market-update');
                p.style.cssText += ';display:block;opacity:0;transform:translateY(-8px);transition:opacity 0.5s ease,transform 0.5s ease;';
                setTimeout(() => { p.style.opacity='1'; p.style.transform='translateY(0)'; }, 100);
            }
        }).catch(() => {});
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
    const ub = document.createElement('div'); ub.className='chat-msg user'; ub.textContent=msg; div.appendChild(ub);
    input.value = '';
    const ti = document.createElement('div'); ti.className='chat-typing'; ti.id='typing-indicator'; ti.textContent='🤖 L\'IA réfléchit...'; div.appendChild(ti);
    div.scrollTop = div.scrollHeight;
    const fd = new FormData();
    fd.append('action','send_chat_message'); fd.append('formation_id',FORMATION_ID); fd.append('receiver_id',1); fd.append('content',msg);
    fetch('ajax_handler.php',{method:'POST',body:fd}).then(r=>r.json()).then(data=>{
        document.getElementById('typing-indicator')?.remove();
        const ab = document.createElement('div'); ab.className='chat-msg ai';
        ab.innerHTML='<span class="ai-badge">🤖 Assistant IA</span>'+(data.success ? data.ai_reply.replace(/\n/g,'<br>') : (data.message||'Erreur.'));
        div.appendChild(ab); div.scrollTop=div.scrollHeight;
    }).catch(()=>{ document.getElementById('typing-indicator')?.remove(); });
}
</script>
