<?php
// Session et variables
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Visionneuse de Cours - Aptus AI";

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../controller/TuteurDashboardController.php';

$formationC = new FormationController();
$tuteurC = new TuteurDashboardController();

$id_formation = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formation = $formationC->getFormationById($id_formation);

if (!$formation) {
    die("Formation introuvable.");
}

$resources = $tuteurC->getResources($id_formation);

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<div style="background: var(--bg-card); border-radius: 16px; padding: 2.5rem; margin-top: 2rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--text-primary);"><?php echo htmlspecialchars($formation['titre']); ?></h1>
            <p style="color: var(--text-secondary); margin:0;">
                Tuteur : <strong><?php echo htmlspecialchars($formation['tuteur_nom'] ?? 'Aptus'); ?></strong> | 
                Domaine : <strong><?php echo htmlspecialchars($formation['domaine']); ?></strong> | 
                Niveau : <strong><?php echo htmlspecialchars($formation['niveau']); ?></strong>
            </p>
        </div>
        <a href="formations_my.php" class="btn btn-secondary">Retour à mes cours</a>
    </div>

    <!-- Description de la formation (Contenu riche Quill.js) -->
    <div style="margin-bottom: 3rem; background: var(--bg-surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Description du cours</h3>
        <div style="font-size: 1.05rem; line-height: 1.6; color: var(--text-primary);">
            <?php 
                $clean_desc = preg_replace('/<!-- APTUS_RESOURCES: .*? -->/s', '', $formation['description']);
                echo $clean_desc; // Affiché tel quel (HTML) 
            ?>
        </div>
    </div>

    <!-- Ressources pédagogiques (Vidéos, PDFs, Quizzes) -->
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--text-primary);">Ressources Pédagogiques</h2>
        
        <?php if (empty($resources)): ?>
            <div style="text-align: center; padding: 2rem; background: var(--bg-surface); border-radius: 12px; border: 1px dashed var(--border-color); color: var(--text-secondary);">
                <i data-lucide="book-x" style="width: 48px; height: 48px; opacity: 0.5; margin-bottom: 1rem;"></i>
                <p>Aucune ressource pédagogique n'a encore été ajoutée par le tuteur.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php foreach ($resources as $res): ?>
                    <a href="<?php echo htmlspecialchars($res['url']); ?>" 
                       <?php if($res['type'] === 'pdf' || strpos($res['url'], 'data:') === 0): ?> download="<?php echo htmlspecialchars($res['titre']); ?>.pdf" <?php endif; ?>
                       target="_blank" style="text-decoration: none;">
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; transition: all 0.2s; display: flex; align-items: center; gap: 1rem; cursor: pointer;" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                            <div style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); flex-shrink: 0;">
                                <?php if ($res['type'] === 'video'): ?>
                                    <i data-lucide="video" style="width: 24px; height: 24px;"></i>
                                <?php elseif ($res['type'] === 'pdf'): ?>
                                    <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                                <?php elseif ($res['type'] === 'quiz'): ?>
                                    <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                                <?php else: ?>
                                    <i data-lucide="link" style="width: 24px; height: 24px;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="overflow: hidden;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary); font-size: 1.1rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"><?php echo htmlspecialchars($res['titre']); ?></h4>
                                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;"><?php echo htmlspecialchars($res['type']); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     CHATBOT FLOTTANT — Assistant IA du Tuteur
     ═══════════════════════════════════════════ -->
<style>
    .chat-fab {
        position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
        width: 60px; height: 60px; border-radius: 50%;
        background: var(--gradient-primary); border: none; cursor: pointer;
        box-shadow: 0 8px 25px rgba(99,102,241,0.4);
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.3s, box-shadow 0.3s;
        color: white; font-size: 24px;
    }
    .chat-fab:hover { transform: scale(1.1); box-shadow: 0 12px 35px rgba(99,102,241,0.5); }
    .chat-window {
        position: fixed; bottom: 6rem; right: 2rem; z-index: 9998;
        width: 380px; max-height: 500px; border-radius: 16px;
        background: var(--bg-card); border: 1px solid var(--border-color);
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        display: none; flex-direction: column; overflow: hidden;
    }
    .chat-window.open { display: flex; animation: chatSlideUp 0.3s ease; }
    @keyframes chatSlideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .chat-header {
        padding: 1rem 1.25rem; background: var(--gradient-primary); color: white;
        display: flex; justify-content: space-between; align-items: center;
    }
    .chat-header h4 { margin: 0; font-size: 0.95rem; }
    .chat-header span { font-size: 0.75rem; opacity: 0.8; }
    .chat-messages {
        flex: 1; padding: 1rem; overflow-y: auto; max-height: 320px;
        display: flex; flex-direction: column; gap: 0.75rem;
    }
    .chat-msg {
        max-width: 85%; padding: 0.75rem 1rem; border-radius: 12px;
        font-size: 0.9rem; line-height: 1.5; word-wrap: break-word;
    }
    .chat-msg.user {
        align-self: flex-end; background: var(--accent-primary); color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-msg.ai {
        align-self: flex-start; background: var(--bg-surface); color: var(--text-primary);
        border: 1px solid var(--border-color); border-bottom-left-radius: 4px;
    }
    .chat-msg.ai .ai-badge {
        font-size: 0.7rem; font-weight: 700; color: var(--accent-primary);
        margin-bottom: 0.25rem; display: block;
    }
    .chat-input-area {
        padding: 0.75rem; border-top: 1px solid var(--border-color);
        display: flex; gap: 0.5rem;
    }
    .chat-input-area input {
        flex: 1; padding: 0.6rem 1rem; border-radius: 25px;
        border: 1px solid var(--border-color); background: var(--bg-surface);
        color: var(--text-primary); font-size: 0.9rem; outline: none;
    }
    .chat-input-area input:focus { border-color: var(--accent-primary); }
    .chat-input-area button {
        width: 38px; height: 38px; border-radius: 50%;
        background: var(--gradient-primary); border: none; cursor: pointer;
        color: white; display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .chat-typing { color: var(--text-secondary); font-size: 0.8rem; font-style: italic; padding: 0.5rem 1rem; }
</style>

<!-- Floating Chat Toggle Button -->
<button class="chat-fab" id="chat-fab" onclick="toggleChat()" title="Poser une question à l'IA">
    💬
</button>

<!-- Chat Window -->
<div class="chat-window" id="chat-window">
    <div class="chat-header">
        <div>
            <h4>🤖 Assistant IA du Tuteur</h4>
            <span><?php echo htmlspecialchars($formation['titre']); ?></span>
        </div>
        <button onclick="toggleChat()" style="background:none; border:none; color:white; font-size:18px; cursor:pointer;">✕</button>
    </div>
    <div class="chat-messages" id="chat-messages">
        <div class="chat-msg ai">
            <span class="ai-badge">🤖 Assistant IA</span>
            Bonjour ! Je suis l'assistant IA de votre tuteur pour <strong><?php echo htmlspecialchars($formation['titre']); ?></strong>. Posez-moi n'importe quelle question sur le cours !
        </div>
    </div>
    <div class="chat-input-area">
        <input type="text" id="chat-input" placeholder="Posez votre question..." onkeypress="if(event.key==='Enter') sendChatMsg()">
        <button onclick="sendChatMsg()" title="Envoyer">➤</button>
    </div>
</div>

<script>
    function toggleChat() {
        document.getElementById('chat-window').classList.toggle('open');
        if (document.getElementById('chat-window').classList.contains('open')) {
            document.getElementById('chat-input').focus();
        }
    }

    function sendChatMsg() {
        const input = document.getElementById('chat-input');
        const msg = input.value.trim();
        if (!msg) return;

        const messagesDiv = document.getElementById('chat-messages');

        // Show user message
        const userBubble = document.createElement('div');
        userBubble.className = 'chat-msg user';
        userBubble.textContent = msg;
        messagesDiv.appendChild(userBubble);
        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Show typing indicator
        const typing = document.createElement('div');
        typing.className = 'chat-typing';
        typing.id = 'typing-indicator';
        typing.textContent = '🤖 L\'IA réfléchit...';
        messagesDiv.appendChild(typing);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Send to backend
        const formData = new FormData();
        formData.append('action', 'send_chat_message');
        formData.append('formation_id', <?php echo $id_formation; ?>);
        formData.append('receiver_id', 1); // tutor id placeholder
        formData.append('content', msg);

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Remove typing indicator
            const ti = document.getElementById('typing-indicator');
            if (ti) ti.remove();

            if (data.success) {
                const aiBubble = document.createElement('div');
                aiBubble.className = 'chat-msg ai';
                aiBubble.innerHTML = '<span class="ai-badge">🤖 Assistant IA</span>' + data.ai_reply.replace(/\n/g, '<br>');
                messagesDiv.appendChild(aiBubble);
            } else {
                const errBubble = document.createElement('div');
                errBubble.className = 'chat-msg ai';
                errBubble.innerHTML = '<span class="ai-badge">⚠️ Erreur</span>' + (data.message || 'Impossible de contacter l\'IA.');
                messagesDiv.appendChild(errBubble);
            }
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        })
        .catch(err => {
            const ti = document.getElementById('typing-indicator');
            if (ti) ti.remove();
            const errBubble = document.createElement('div');
            errBubble.className = 'chat-msg ai';
            errBubble.innerHTML = '<span class="ai-badge">⚠️ Erreur</span>Connexion impossible. Réessayez.';
            messagesDiv.appendChild(errBubble);
        });
    }
</script>
