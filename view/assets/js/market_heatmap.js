
// Advanced ECharts Particle Swarm Heatmap
document.addEventListener('DOMContentLoaded', async () => {
    const mapContainer = document.getElementById('tunisia-heatmap');
    if (!mapContainer) return;

    // Set height to accommodate the map properly
    mapContainer.style.height = '600px';
    mapContainer.style.width = '100%';
    mapContainer.innerHTML = ''; // Clear loading text

    // Check if ECharts is loaded on this page, if not, load it dynamically
    if (typeof echarts === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js';
        script.onload = initMap;
        document.head.appendChild(script);
    } else {
        initMap();
    }

    async function initMap() {
        try {
            // Load Region Stats
            const resStats = await fetch('/aptus_first_official_version/view/backoffice/api_veille_ai.php?action=get_regional_stats');
            const dataStats = await resStats.json();
            
            // Load GeoJSON
            const resGeo = await fetch('/aptus_first_official_version/assets/js/tunisia.json');
            const geoJson = await resGeo.json();

            echarts.registerMap('Tunisia', geoJson);
            
            const chart = echarts.init(mapContainer);
            
            // Hardcoded coordinates for Tunisian governorates to place the particles
            const geoCoordMap = {
                'Tunis': [10.1815, 36.8065],
                'Ariana': [10.1956, 36.8625],
                'Ben Arous': [10.2269, 36.7531],
                'Manouba': [10.0956, 36.8081],
                'Nabeul': [10.7333, 36.4561],
                'Zaghouan': [10.1423, 36.4011],
                'Bizerte': [9.8739, 37.2744],
                'Béja': [9.1817, 36.7256],
                'Jendouba': [8.7802, 36.5011],
                'Le Kef': [8.7049, 36.1680],
                'Siliana': [9.3645, 35.8256],
                'Sousse': [10.6369, 35.8256],
                'Monastir': [10.8113, 35.7643],
                'Mahdia': [10.3359, 35.3353],
                'Sfax': [10.7603, 34.7406],
                'Kairouan': [10.0956, 35.6712],
                'Kasserine': [8.8365, 35.1676],
                'Sidi Bouzid': [9.4839, 35.0382],
                'Gabès': [10.1067, 33.8815],
                'Medenine': [10.4706, 33.3550],
                'Tataouine': [10.4518, 32.9211],
                'Gafsa': [8.7842, 34.4311],
                'Tozeur': [8.1336, 33.9197],
                'Kebili': [8.9690, 33.7044]
            };

            const mapData = [];
            const particleData = [];
            
            let maxSalary = 0;
            let maxReports = 0;

            if (dataStats.success && dataStats.data) {
                dataStats.data.forEach(stat => {
                    // Standardize region name formatting
                    const regionName = stat.region.trim();
                    const avgSalary = Math.round(stat.avg_salary);
                    const count = stat.report_count;

                    if (avgSalary > maxSalary) maxSalary = avgSalary;
                    if (count > maxReports) maxReports = count;

                    mapData.push({
                        name: regionName,
                        value: avgSalary,
                        reports: count
                    });

                    // Add to particle data if coordinates exist
                    if (geoCoordMap[regionName]) {
                        particleData.push({
                            name: regionName,
                            value: geoCoordMap[regionName].concat([count, avgSalary]) // [lon, lat, count, salary]
                        });
                    }
                });
            }

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const baseFill = isDark ? '#1e293b' : '#e8edf5';
            const baseBorder = isDark ? '#334155' : '#b8c4d4';
            const textColor = isDark ? '#f8fafc' : '#334155';

            const option = {
                backgroundColor: 'transparent',
                title: {
                    text: 'Activité du Marché en Temps Réel',
                    subtext: 'Nuage de particules basé sur les offres et salaires',
                    left: 'center',
                    textStyle: { color: textColor, fontSize: 18 }
                },
                tooltip: {
                    trigger: 'item',
                    formatter: function (params) {
                        if (params.seriesType === 'effectScatter') {
                            const data = params.data.value;
                            return `<strong>${params.name}</strong><br/>
                                    Rapports/Offres: <strong style="color:#4f46e5">${data[2]}</strong><br/>
                                    Salaire Moyen: <strong>${data[3]} TND</strong>`;
                        } else {
                            const val = params.value || 'N/A';
                            return `<strong>${params.name}</strong><br/>Salaire: ${val} TND`;
                        }
                    }
                },
                visualMap: {
                    min: 0,
                    max: maxSalary > 0 ? maxSalary : 5000,
                    left: 'left',
                    top: 'bottom',
                    text: ['Élevé', 'Faible'],
                    calculable: true,
                    inRange: {
                        color: ['#e0e7ff', '#818cf8', '#4338ca']
                    },
                    textStyle: { color: textColor }
                },
                geo: {
                    map: 'Tunisia',
                    roam: true, // Allow zooming
                    center: [9.4, 34.2], // Center on Tunisia
                    zoom: 1.2,
                    label: {
                        emphasis: { show: false }
                    },
                    itemStyle: {
                        normal: {
                            areaColor: baseFill,
                            borderColor: baseBorder
                        },
                        emphasis: {
                            areaColor: '#c7d2fe'
                        }
                    }
                },
                series: [
                    {
                        name: 'Salaires',
                        type: 'map',
                        geoIndex: 0,
                        data: mapData
                    },
                    {
                        name: 'Nuage de Rapports',
                        type: 'effectScatter',
                        coordinateSystem: 'geo',
                        data: particleData,
                        symbolSize: function (val) {
                            // Scale particle size based on report count (min 10, max 30)
                            let size = (val[2] / maxReports) * 30;
                            return size < 10 ? 10 : size;
                        },
                        showEffectOn: 'render',
                        rippleEffect: {
                            brushType: 'stroke',
                            scale: 4
                        },
                        itemStyle: {
                            normal: {
                                color: '#f59e0b', // Amber/Gold particles
                                shadowBlur: 10,
                                shadowColor: '#f59e0b'
                            }
                        },
                        zlevel: 1
                    }
                ]
            };

            chart.setOption(option);

            window.addEventListener('resize', () => {
                chart.resize();
            });

            // Listen for dynamic theme changes
            window.addEventListener('themeChanged', (e) => {
                const isDarkTheme = e.detail.theme === 'dark';
                const newBaseFill = isDarkTheme ? '#1e293b' : '#e8edf5';
                const newBaseBorder = isDarkTheme ? '#334155' : '#b8c4d4';
                const newTextColor = isDarkTheme ? '#f8fafc' : '#334155';

                chart.setOption({
                    title: { textStyle: { color: newTextColor } },
                    visualMap: { textStyle: { color: newTextColor } },
                    geo: { itemStyle: { normal: { areaColor: newBaseFill, borderColor: newBaseBorder } } }
                });
            });

        } catch (e) {
            console.error("Advanced Heatmap Error:", e);
            mapContainer.innerHTML = '<p>Erreur lors du chargement de la carte interactive.</p>';
        }
    }
});
