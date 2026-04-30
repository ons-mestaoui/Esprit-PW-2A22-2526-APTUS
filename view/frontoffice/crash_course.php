<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = "Generative Learning Path — Aptus AI";

require_once __DIR__ . '/../../config.php';

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>

<style>
/* ══ Crash Course UI ══════════════════════════════════════════ */
.crash-hero {
    text-align: center;
    padding: 3rem 1rem 2rem;
    background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(16,185,129,0.05) 100%);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    margin-bottom: 2rem;
}
.crash-hero h1 {
    font-size: 2.2rem; font-weight: 900;
    background: linear-gradient(135deg, #6366f1, #10b981);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    margin-bottom: 0.75rem;
}
.crash-hero p { color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 1.5rem; }
.rag-search-wrap {
    display: flex; gap: 0.75rem; max-width: 680px; margin: 0 auto;
}
.rag-search-wrap input {
    flex: 1; padding: 0.9rem 1.25rem; border-radius: 14px;
    border: 2px solid var(--border-color); background: var(--bg-surface);
    color: var(--text-primary); font-size: 1rem; outline: none;
    transition: border-color 0.2s;
}
.rag-search-wrap input:focus { border-color: var(--accent-primary); }
.rag-search-wrap button {
    padding: 0.9rem 1.75rem; border-radius: 14px; border: none;
    background: var(--gradient-primary); color: white;
    font-size: 1rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; gap: 0.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 15px rgba(99,102,241,0.35);
}
.rag-search-wrap button:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.4); }
.rag-search-wrap button:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

/* ── Suggestions rapides ── */
.quick-prompts { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; margin-top: 1rem; }
.quick-prompt {
    padding: 0.4rem 0.9rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600;
    border: 1px solid var(--border-color); background: var(--bg-card);
    color: var(--text-secondary); cursor: pointer; transition: all 0.2s;
}
.quick-prompt:hover { border-color: var(--accent-primary); color: var(--accent-primary); background: rgba(99,102,241,0.07); }

/* ── Résultat crash course ── */
#crash-result { animation: fadeInUp 0.5s ease; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }

.course-card-banner {
    border-radius: 16px; padding: 1.75rem 2rem; margin-bottom: 1.5rem;
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
    border: 1px solid rgba(99,102,241,0.3);
    box-shadow: 0 8px 30px rgba(99,102,241,0.15);
    color: white;
}
.module-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 0.75rem;
    display: flex; align-items: flex-start; gap: 1rem;
    transition: border-color 0.2s, transform 0.2s;
}
.module-card:hover { border-color: var(--accent-primary); transform: translateX(4px); }
.module-num {
    width: 32px; height: 32px; border-radius: 50%; background: var(--gradient-primary);
    color: white; display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.85rem; flex-shrink: 0;
}
.conseil-final {
    background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(99,102,241,0.05));
    border: 1px solid rgba(245,158,11,0.3); border-radius: 14px; padding: 1.25rem 1.5rem;
    margin-top: 1.5rem;
}

/* ── État chargement ── */
.rag-loading {
    text-align: center; padding: 3rem; color: var(--text-secondary);
}
.rag-loading .spinner {
    width: 48px; height: 48px; border: 4px solid var(--border-color);
    border-top-color: var(--accent-primary); border-radius: 50%;
    animation: spin 0.8s linear infinite; margin: 0 auto 1rem;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="crash-hero">
    <div style="font-size:0.75rem; font-weight:800; letter-spacing:0.12em; color:var(--accent-primary); text-transform:uppercase; margin-bottom:0.5rem;">
        ✨ Generative Learning Path — Powered by Llama-3 + RAG
    </div>
    <h1>Génère ton Crash Course<br>sur mesure en 5 secondes</h1>
    <p>Décris ton besoin urgent. L'IA scanne tout le catalogue Aptus et crée<br>
       une mini-formation éphémère personnalisée juste pour toi.</p>

    <div class="rag-search-wrap">
        <input type="text" id="rag-prompt"
               placeholder="Ex: Je passe un entretien sur la cybersécurité demain matin..."
               onkeypress="if(event.key==='Enter') generateCrash()">
        <button id="rag-btn" onclick="generateCrash()">
            <span>⚡</span> Générer
        </button>
    </div>

    <div class="quick-prompts">
        <span class="quick-prompt" onclick="setPrompt(this)">🔒 Entretien cybersécurité demain</span>
        <span class="quick-prompt" onclick="setPrompt(this)">🌐 Bases du cloud computing urgentes</span>
        <span class="quick-prompt" onclick="setPrompt(this)">🗄️ SQL pour un projet en 30 min</span>
        <span class="quick-prompt" onclick="setPrompt(this)">📊 Marketing digital pour une startup</span>
        <span class="quick-prompt" onclick="setPrompt(this)">⚛️ React pour mon stage demain</span>
    </div>
</div>

<!-- Zone de résultat -->
<div id="crash-output"></div>

<script>
function setPrompt(el) {
    document.getElementById('rag-prompt').value = el.textContent.trim().replace(/^[\p{Emoji}\s]+/u, '').trim();
}

function generateCrash() {
    const prompt = document.getElementById('rag-prompt').value.trim();
    if (!prompt) {
        document.getElementById('rag-prompt').focus();
        return;
    }

    const btn = document.getElementById('rag-btn');
    const output = document.getElementById('crash-output');

    btn.disabled = true;
    btn.innerHTML = '<span class="rag-loading" style="padding:0;"><div class="spinner" style="width:18px;height:18px;margin:0;border-width:2px;display:inline-block;vertical-align:middle;"></div></span> Analyse du catalogue...';

    output.innerHTML = `<div class="rag-loading">
        <div class="spinner"></div>
        <p style="font-size:1rem; font-weight:600; color:var(--text-primary);">L'IA RAG scanne tout le catalogue Aptus...</p>
        <p style="font-size:0.85rem; margin-top:0.25rem;">Extraction des chapitres pertinents en cours</p>
    </div>`;

    const fd = new FormData();
    fd.append('action', 'generate_crash_course');
    fd.append('prompt', prompt);

    fetch('ajax_handler.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<span>⚡</span> Générer';

        if (!data.success || !data.data) {
            output.innerHTML = `<div style="text-align:center;padding:2rem;color:#ef4444;">
                ⚠️ ${data.message || 'Erreur lors de la génération. Réessayez.'}</div>`;
            return;
        }

        const d = data.data;
        const modulesHTML = (d.modules || []).map((m, i) => `
            <div class="module-card">
                <div class="module-num">${i + 1}</div>
                <div style="flex:1;">
                    <div style="font-size:0.7rem; font-weight:700; color:var(--accent-primary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.2rem;">
                        📚 ${m.formation_titre || 'Source Aptus'} — ${m.chapitre || ''}
                        <span style="color:var(--text-secondary); margin-left:0.5rem;">⏱ ${m.duree || '10 min'}</span>
                    </div>
                    <div style="font-size:0.95rem; font-weight:600; color:var(--text-primary); margin-bottom:0.3rem;">${m.objectif || ''}</div>
                    ${m.formation_id ? `<a href="formation_viewer.php?id=${m.formation_id}" style="font-size:0.78rem; color:var(--accent-primary); text-decoration:none; font-weight:600;">→ Accéder à cette formation</a>` : ''}
                </div>
            </div>
        `).join('');

        output.innerHTML = `<div id="crash-result">
            <div class="course-card-banner">
                <div style="font-size:0.7rem; font-weight:800; letter-spacing:0.1em; color:rgba(255,255,255,0.5); text-transform:uppercase; margin-bottom:0.5rem;">
                    🎯 Mini-Formation Éphémère · ${d.estimated_time || '30 min'}
                </div>
                <h2 style="margin:0 0 0.5rem; font-size:1.6rem; font-weight:900; color:white;">${d.title || 'Crash Course Personnalisé'}</h2>
                <p style="margin:0; color:rgba(255,255,255,0.7); font-size:0.95rem;">${d.subtitle || ''}</p>
                <div style="margin-top:1rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                    <span style="background:rgba(99,102,241,0.3); color:rgba(255,255,255,0.9); padding:0.3rem 0.8rem; border-radius:999px; font-size:0.78rem; font-weight:700;">
                        ⚡ Générée à partir du catalogue Aptus
                    </span>
                    <span style="background:rgba(16,185,129,0.2); color:#10b981; padding:0.3rem 0.8rem; border-radius:999px; font-size:0.78rem; font-weight:700;">
                        📖 ${(d.modules || []).length} module(s) · ${d.estimated_time || '30 min'}
                    </span>
                </div>
            </div>

            <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:var(--text-primary);">📋 Plan de Formation</h3>
            ${modulesHTML}

            ${d.conseil_final ? `<div class="conseil-final">
                <div style="font-size:0.72rem; font-weight:800; letter-spacing:0.08em; color:#f59e0b; text-transform:uppercase; margin-bottom:0.4rem;">💡 Conseil Pro</div>
                <p style="margin:0; font-size:0.95rem; line-height:1.6; color:var(--text-primary);">${d.conseil_final}</p>
            </div>` : ''}

            <div style="text-align:center; margin-top:1.5rem;">
                <button onclick="generateCrash()" class="btn btn-secondary" style="margin-right:0.75rem;">
                    🔄 Nouvelle requête
                </button>
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ Sauvegarder le plan
                </button>
            </div>
        </div>`;
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>⚡</span> Générer';
        output.innerHTML = `<div style="text-align:center;padding:2rem;color:#ef4444;">⚠️ Erreur réseau : ${err.message}</div>`;
    });
}
</script>
