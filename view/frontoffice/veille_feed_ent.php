<?php 
require_once dirname(__DIR__, 2) . '/controller/VeilleC.php';
$vc = new VeilleC();
$dbReports = $vc->afficherRapports();
$sidebarStats = $vc->getSidebarStats();

$pageTitle = "Veille du Marché"; $pageCSS = "veille.css"; $userRole = "Entreprise"; 

if (!isset($content)) {
    $content = __FILE__;
    include 'layout_front.php';
    exit();
}
?>
<!-- ECharts for AI Forecast -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
<!-- Included inside layout_front.php -->

<div class="page-header">
  <h1 class="page-header__title">
    <i data-lucide="line-chart" style="width:28px;height:28px;color:var(--accent-primary);"></i>
    Veille du Marché
  </h1>
  <p class="page-header__subtitle">Rapports, analyses et données du marché de l'emploi</p>
</div>

<?php
// Collect unique sector tags from all reports
$feedSecteurs = [];
foreach ($dbReports as $r) {
    if (!empty($r['secteur_principal'])) {
        foreach (explode(',', $r['secteur_principal']) as $s) {
            $s = trim($s);
            if ($s && !in_array($s, $feedSecteurs)) $feedSecteurs[] = $s;
        }
    }
}
?>

<style>
.feed-filter-bar { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px; align-items:center; }
.feed-filter-btn {
    padding: 6px 16px; font-size: 13px; font-weight: 600; border-radius: 20px;
    border: 1px solid var(--border-color); background: var(--bg-secondary);
    color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease;
}
.feed-filter-btn:hover { border-color: var(--accent-primary); color: var(--accent-primary); }
.feed-filter-btn.active { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); box-shadow: 0 2px 12px rgba(99,102,241,0.3); }
</style>

<div class="veille-layout">
  <!-- ═══ MAIN FEED ═══ -->
  <div class="report-feed stagger">

    <!-- Header / Controls Bar -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <!-- Sector Filter Bar -->
        <?php if (!empty($feedSecteurs)): 
            $visibleSecteurs = array_slice($feedSecteurs, 0, 4);
        ?>
        <div class="feed-filter-bar" id="feed-filter-bar" style="margin-bottom:0;">
            <span style="font-size:13px; font-weight:600; color:var(--text-secondary);">
                <i data-lucide="tag" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> Secteur :
            </span>
            <button class="feed-filter-btn active" onclick="filterFeed('all', this)">Tous</button>
            <?php foreach ($visibleSecteurs as $sec): ?>
            <button class="feed-filter-btn" onclick="filterFeed('<?php echo htmlspecialchars(addslashes($sec)); ?>', this)"><?php echo htmlspecialchars($sec); ?></button>
            <?php endforeach; ?>
            
            <?php if (count($feedSecteurs) > 4): ?>
            <div style="position:relative; display:inline-block;" id="more-sectors-dropdown-container">
                <button class="feed-filter-btn" onclick="toggleMoreSectors()" id="btn-more-sectors">
                    <i data-lucide="more-horizontal" style="width:14px;height:14px;vertical-align:-2px;"></i> Voir plus
                </button>
                <!-- Dropdown Menu -->
                <div id="more-sectors-dropdown" style="display:none; position:absolute; top:100%; left:0; margin-top:8px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:12px; padding:12px; width:250px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:100;">
                    <input type="text" id="sector-search" placeholder="Rechercher un secteur..." class="input" style="width:100%; margin-bottom:12px; font-size:13px; padding:8px 12px; box-sizing: border-box;" onkeyup="searchSectors()">
                    <div style="max-height:200px; overflow-y:auto; display:flex; flex-direction:column; gap:4px;" id="sector-list-container">
                        <?php foreach ($feedSecteurs as $sec): ?>
                        <button class="feed-filter-btn" style="text-align:left; border:none; border-radius:6px; background:transparent; width:100%; justify-content:flex-start;" onclick="filterFeed('<?php echo htmlspecialchars(addslashes($sec)); ?>', this, true)"><?php echo htmlspecialchars($sec); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Sorting -->
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:13px; font-weight:600; color:var(--text-secondary);">Trier par :</span>
            <button class="btn btn-sm" style="background:var(--bg-secondary); border:1px solid var(--border-color); color:var(--text-primary); border-radius:8px;" onclick="toggleSortSalary()" id="btn-sort-salary" data-sort="desc">
                Salaire <i data-lucide="arrow-down" id="sort-icon" style="width:14px;height:14px;margin-left:4px;"></i>
            </button>
        </div>
    </div>
    
    <div id="feed-cards-container" style="display:flex; flex-direction:column; gap:var(--space-6);">


    <!-- Featured Report -->
    <?php if (count($dbReports) > 0): $featured = $dbReports[0]; 
      $featuredSecteurs = htmlspecialchars($featured['secteur_principal'] ?? '');
    ?>
    <article class="report-card report-card--featured animate-on-scroll feed-card" id="report-featured" data-secteurs="<?php echo $featuredSecteurs; ?>" data-salaire="<?php echo floatval($featured['salaire_moyen_global'] ?? 0); ?>">
      <div class="report-card__header">
        <div class="report-card__header-content">
          <h2 class="report-card__title"><?php echo htmlspecialchars($featured['titre']); ?></h2>
          <p class="report-card__excerpt">
            <?php echo htmlspecialchars(substr($featured['description'], 0, 300)) . '...'; ?>
          </p>
        </div>
      </div>
      <?php if (!empty($featured['image_couverture'])): ?>
        <img src="<?php echo $featured['image_couverture']; ?>" alt="Cover" class="report-card__image">
      <?php endif; ?>
      <div class="report-card__meta">
        <span class="report-card__meta-item">
          <i data-lucide="user" style="width:12px;height:12px;"></i> <?php echo htmlspecialchars($featured['auteur']); ?>
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo date('d M. Y', strtotime($featured['date_publication'])); ?>
        </span>
        <span class="report-card__meta-item report-card__meta-item--views">
          <i data-lucide="eye" style="width:12px;height:12px;"></i> <?php echo $featured['vues']; ?> vues
        </span>
        <?php if (!empty($featured['secteur_principal'])): foreach(explode(',', $featured['secteur_principal']) as $tag): $tag = trim($tag); if (!$tag) continue; ?>
          <span class="badge badge-info"><?php echo htmlspecialchars($tag); ?></span>
        <?php endforeach; endif; ?>
      </div>
      <div class="report-card__footer">
        <a href="veille_details.php?id=<?php echo $featured['id_rapport_marche']; ?>" class="btn btn-sm btn-primary">
          <i data-lucide="book-open" style="width:14px;height:14px;"></i> Lire le rapport
        </a>
        <div class="flex gap-2">
          <button class="btn btn-sm btn-ghost"><i data-lucide="bookmark" style="width:14px;height:14px;"></i></button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="share-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
    </article>
    <?php endif; ?>

    <!-- Regular Reports -->
    <?php
    for ($i = 1; $i < count($dbReports); $i++):
      $r = $dbReports[$i];
      $rSecteurs = htmlspecialchars($r['secteur_principal'] ?? '');
    ?>
    <article class="report-card animate-on-scroll feed-card" id="report-<?php echo $i; ?>" data-secteurs="<?php echo $rSecteurs; ?>" data-salaire="<?php echo floatval($r['salaire_moyen_global'] ?? 0); ?>">
      <div class="report-card__header">
        <div class="report-card__header-content">
          <h3 class="report-card__title"><?php echo htmlspecialchars($r['titre']); ?></h3>
          <p class="report-card__excerpt"><?php echo htmlspecialchars(substr($r['description'], 0, 150)) . '...'; ?></p>
        </div>
        <?php if (!empty($r['secteur_principal'])): $firstTag = trim(explode(',', $r['secteur_principal'])[0]); ?>
        <span class="badge badge-primary report-card__category"><?php echo htmlspecialchars($firstTag); ?></span>
        <?php endif; ?>
      </div>
      <?php if (!empty($r['image_couverture'])): ?>
        <img src="<?php echo $r['image_couverture']; ?>" alt="Cover" class="report-card__image">
      <?php endif; ?>
      <div class="report-card__meta">
        <span class="report-card__meta-item">
          <i data-lucide="user" style="width:12px;height:12px;"></i> <?php echo htmlspecialchars($r['auteur']); ?>
        </span>
        <span class="report-card__meta-item">
          <i data-lucide="calendar" style="width:12px;height:12px;"></i> <?php echo date('d M. Y', strtotime($r['date_publication'])); ?>
        </span>
        <span class="report-card__meta-item report-card__meta-item--views">
          <i data-lucide="eye" style="width:12px;height:12px;"></i> <?php echo $r['vues']; ?> vues
        </span>
      </div>
      <div class="report-card__footer">
        <a href="veille_details.php?id=<?php echo $r['id_rapport_marche']; ?>" class="btn btn-sm btn-secondary">
          <i data-lucide="book-open" style="width:14px;height:14px;"></i> Lire le rapport
        </a>
        <div class="flex gap-2">
          <button class="btn btn-sm btn-ghost"><i data-lucide="bookmark" style="width:14px;height:14px;"></i></button>
          <button class="btn btn-sm btn-ghost"><i data-lucide="share-2" style="width:14px;height:14px;"></i></button>
        </div>
      </div>
    </article>
    <?php endfor; ?>
    </div> <!-- /feed-cards-container -->

    <!-- AI Forecast Dashboard Section (Moved to Bottom) -->
    <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; margin-top: 32px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%); z-index: 0;"></div>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1;">
            <div>
                <h3 style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px; font-size:18px;">
                    <i data-lucide="line-chart" style="color: var(--accent-primary);"></i>
                    🔮 Prévisions Intelligentes du Marché
                </h3>
                <p style="color: var(--text-secondary); font-size: 14px; max-width: 600px;">
                    Notre IA analyse les données historiques par secteur pour prédire les tendances futures des salaires et de la demande.
                </p>
                <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
                    <label for="forecast-category" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Secteur :</label>
                    <select id="forecast-category" class="select" style="width: 250px; padding: 6px 12px; font-size: 13px;" onchange="loadAIForecast(false)">
                        <option value="">Tous les secteurs</option>
                        <?php foreach ($feedSecteurs as $sec): ?>
                            <option value="<?php echo htmlspecialchars($sec); ?>"><?php echo htmlspecialchars($sec); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-ai-sparkle" onclick="loadAIForecast(true)" id="btn-refresh-forecast">
                <i data-lucide="refresh-cw" style="width:14px;height:14px;margin-right:6px;"></i> Actualiser
            </button>
        </div>

        <div id="forecast-chart-container" style="width: 100%; height: 300px; margin-top: 24px; background: rgba(255,255,255,0.02); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <div id="forecast-placeholder" style="text-align: center; color: var(--text-tertiary);">
                <i data-lucide="sparkles" style="width:48px; height:48px; margin-bottom: 12px; opacity: 0.3;"></i>
                <p>Sélectionnez un secteur ou cliquez sur "Actualiser".</p>
            </div>
            <div id="echarts-forecast" style="width: 100%; height: 100%; display: none;"></div>
        </div>
    </div>
  </div>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="veille-sidebar">
    <!-- Quick Stats -->
    <div class="data-card-mini">
      <div class="data-card-mini__header">
        <span class="data-card-mini__title">Données analysées</span>
      </div>
      <div class="data-card-mini__value"><?php echo number_format($sidebarStats['donnees_total'], 0, ',', ' '); ?></div>
      <div class="data-card-mini__chart" id="sparkline-offres"></div>
    </div>

    <div class="data-card-mini">
      <div class="data-card-mini__header">
        <span class="data-card-mini__title">Salaire moyen global</span>
        <span class="badge badge-info">TND</span>
      </div>
      <div class="data-card-mini__value"><?php echo number_format($sidebarStats['salaire_moyen'], 0, ',', ' '); ?></div>
      <div class="data-card-mini__chart" id="sparkline-salary"></div>
    </div>

    <!-- Top Sectors Chart -->
    <div class="data-card-mini">
      <div class="data-card-mini__title" style="margin-bottom:var(--space-4);">Top Secteurs</div>
      <div class="simple-bar-chart">
        <?php 
        $colors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)'];
        $i = 0;
        if (!empty($sidebarStats['top_secteurs'])):
        foreach ($sidebarStats['top_secteurs'] as $sec => $count): 
            $pct = $sidebarStats['total_secteurs_tags'] > 0 ? round(($count / $sidebarStats['total_secteurs_tags']) * 100) : 0;
            $color = $colors[$i % count($colors)];
        ?>
        <div class="simple-bar-chart__row">
          <span class="simple-bar-chart__label"><?php echo htmlspecialchars($sec); ?></span>
          <div class="simple-bar-chart__bar"><div class="simple-bar-chart__fill" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>;"></div></div>
          <span class="simple-bar-chart__value"><?php echo $pct; ?>%</span>
        </div>
        <?php $i++; endforeach; else: ?>
        <p style="font-size:12px; color:var(--text-tertiary);">Aucune donnée disponible</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Trending Topics -->
    <div class="data-card-mini">
      <div class="data-card-mini__title" style="margin-bottom:var(--space-3);">Sujets tendance</div>
      <div class="trending-list">
        <?php 
        $rank = 1;
        if (!empty($sidebarStats['sujets_tendance'])):
        foreach ($sidebarStats['sujets_tendance'] as $sujet => $vues): 
        ?>
        <div class="trending-item">
            <span class="trending-item__rank"><?php echo $rank; ?></span>
            <span class="trending-item__text"><?php echo htmlspecialchars($sujet); ?></span>
            <span class="trending-item__count"><?php echo $vues; ?> vues</span>
        </div>
        <?php $rank++; endforeach; else: ?>
        <p style="font-size:12px; color:var(--text-tertiary);">Aucune donnée disponible</p>
        <?php endif; ?>
      </div>
    </div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  AptusCharts.sparkline('sparkline-offres', <?php echo json_encode($sidebarStats['sparkline_donnees']); ?>, 'var(--chart-2)');
  AptusCharts.sparkline('sparkline-salary', <?php echo json_encode($sidebarStats['sparkline_salaire']); ?>, 'var(--chart-3)');
});

function filterFeed(sector, btnEl, isDropdown = false) {
    // Unset active from all buttons
    document.querySelectorAll('.feed-filter-btn').forEach(b => {
        b.classList.remove('active');
        if (b.closest('#sector-list-container')) {
            b.style.background = 'transparent';
            b.style.color = 'var(--text-secondary)';
        }
    });

    // Set active
    if (btnEl) {
        btnEl.classList.add('active');
        if (isDropdown) {
            btnEl.style.background = 'var(--bg-hover)';
            btnEl.style.color = 'var(--text-primary)';
            // Highlight the "Voir plus" button
            document.getElementById('btn-more-sectors').classList.add('active');
        }
    }

    const cards = document.querySelectorAll('.feed-card');
    cards.forEach(card => {
        if (sector === 'all') {
            card.style.display = '';
        } else {
            const secteurs = (card.dataset.secteurs || '').split(',').map(s => s.trim().toLowerCase());
            card.style.display = secteurs.includes(sector.toLowerCase()) ? '' : 'none';
        }
    });

    if (isDropdown) {
        document.getElementById('more-sectors-dropdown').style.display = 'none';
    }
}

function toggleMoreSectors() {
    const dropdown = document.getElementById('more-sectors-dropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    if (dropdown.style.display === 'block') {
        document.getElementById('sector-search').focus();
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const container = document.getElementById('more-sectors-dropdown-container');
    const dropdown = document.getElementById('more-sectors-dropdown');
    if (container && dropdown && !container.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

function searchSectors() {
    const input = document.getElementById('sector-search').value.toLowerCase();
    const buttons = document.getElementById('sector-list-container').querySelectorAll('button');
    buttons.forEach(btn => {
        const text = btn.innerText.toLowerCase();
        btn.style.display = text.includes(input) ? '' : 'none';
    });
}

let sortDirection = 'desc';
function toggleSortSalary() {
    const btn = document.getElementById('btn-sort-salary');
    const container = document.getElementById('feed-cards-container');
    const cards = Array.from(document.querySelectorAll('.feed-card'));
    
    sortDirection = sortDirection === 'desc' ? 'asc' : 'desc';
    
    btn.innerHTML = `Salaire <i data-lucide="arrow-${sortDirection === 'desc' ? 'down' : 'up'}" id="sort-icon" style="width:14px;height:14px;margin-left:4px;"></i>`;
    if (typeof lucide !== 'undefined') lucide.createIcons();

    cards.sort((a, b) => {
        const salA = parseFloat(a.dataset.salaire) || 0;
        const salB = parseFloat(b.dataset.salaire) || 0;
        return sortDirection === 'desc' ? salB - salA : salA - salB;
    });

    cards.forEach(card => {
        container.appendChild(card);
    });
}

// ── AI FORECAST LOGIC ────────────────────────────────
let forecastChart = null;
let forecastCache = {};

async function loadAIForecast(forceRefresh = false) {
    const btn = document.getElementById('btn-refresh-forecast');
    const placeholder = document.getElementById('forecast-placeholder');
    const chartEl = document.getElementById('echarts-forecast');
    const categorySelect = document.getElementById('forecast-category');
    
    const selectedCategory = categorySelect ? categorySelect.value : ''; 
    const cacheKey = selectedCategory || 'all';

    if (!forceRefresh && forecastCache[cacheKey]) {
        renderForecastChart(forecastCache[cacheKey], chartEl, placeholder);
        return;
    }

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin" style="width:14px;height:14px;margin-right:6px;"></i> Predicting...';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        // Pointing to backoffice API from frontoffice
        const response = await fetch(`../backoffice/api_veille_ai.php?action=get_forecast&secteur=${encodeURIComponent(selectedCategory)}`);
        const result = await response.json();

        if (result.success && result.forecast) {
            forecastCache[cacheKey] = result.forecast;
            renderForecastChart(result.forecast, chartEl, placeholder);
        } else {
            alert("Erreur de prédiction: " + (result.error || "Réponse invalide de l'API."));
        }
    } catch (e) {
        console.error(e);
        alert("Erreur lors de la génération des prévisions.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

function renderForecastChart(forecastData, chartEl, placeholder) {
    placeholder.style.display = 'none';
    chartEl.style.display = 'block';
    
    if (!forecastChart) {
        forecastChart = echarts.init(chartEl);
    }

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const months = forecastData.map(f => f.month);
    const salaries = forecastData.map(f => f.predicted_salary);
    const demands = forecastData.map(f => f.predicted_demand);

    const option = {
        tooltip: { trigger: 'axis' },
        legend: { data: ['Salaire Prédit (TND)', 'Demande Prédite (1-10)'], bottom: 0, textStyle: { color: isDark ? '#fff' : '#333' } },
        grid: { top: 40, left: 60, right: 60, bottom: 60 },
        xAxis: { type: 'category', data: months, axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } },
        yAxis: [
            { type: 'value', name: 'Salaire', axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } },
            { type: 'value', name: 'Demande', max: 10, position: 'right', axisLabel: { color: isDark ? '#94a3b8' : '#64748b' } }
        ],
        series: [
            {
                name: 'Salaire Prédit (TND)',
                type: 'line',
                smooth: true,
                data: salaries,
                itemStyle: { color: '#6366f1' },
                areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: 'rgba(99, 102, 241, 0.3)' }, { offset: 1, color: 'transparent' }]) }
            },
            {
                name: 'Demande Prédite (1-10)',
                type: 'bar',
                yAxisIndex: 1,
                data: demands,
                itemStyle: { color: '#a855f7', opacity: 0.6 }
            }
        ]
    };
    forecastChart.setOption(option);
    window.addEventListener('resize', () => forecastChart.resize());
}
</script>
