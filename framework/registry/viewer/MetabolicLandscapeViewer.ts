namespace viewer {

    // Color palette for taxonomy categories
    const COLOR_PALETTE = [
        '#2563eb', '#dc2626', '#16a34a', '#ea580c', '#7c3aed',
        '#0891b2', '#c026d3', '#65a30d', '#0d9488', '#db2777',
        '#f59e0b', '#4f46e5', '#059669', '#e11d48', '#7c2d12',
        '#1d4ed8', '#b91c1c', '#15803d', '#c2410c', '#6d28d9',
        '#0e7490', '#a21caf', '#4d7c0f', '#0f766e', '#be185d',
        '#d97706', '#4338ca', '#047857', '#be123c', '#713f12',
        '#1e40af', '#991b1b', '#166534', '#9a3412', '#5b21b6',
        '#155e75', '#86198f', '#3f6212', '#115e59', '#9d174d'
    ];

    // EC number class labels
    const EC_LABELS = [
        'EC1 Oxidoreductases',
        'EC2 Transferases',
        'EC3 Hydrolases',
        'EC4 Lyases',
        'EC5 Isomerases',
        'EC6 Ligases',
        'EC7 Translocases'
    ];


    /**
     * a scatter point model that represent a microbial genome embedding result
    */
    export interface metabolic_embedding {
        /**
         * the genome assembly id
        */
        assembly_id: string;

        /**
         * umap/pca embedding dimension 1
        */
        x: number;
        /**
         * umap/pca embedding dimension 2
        */
        y: number;
        /**
         * umap/pca embedding dimension 3
        */
        z: number;

        /**
         * enzyme embedding raw data, length of 7, represent the emebdding result of ec number class 1-7
        */
        ec_number: number[];

        scientific_name: string;
        ncbi_taxid: string;

        kingdom: string;
        phylum: string;
        class: string;
        order: string;
        family: string;
        genus: string;
        species: string;
    }

    /**
      * Genome Embedding Visualization Module
      * Provides 3D scatter plot and radar chart visualization for microbial genome embedding data
      */
    export class GenomeEmbeddingViz {

        // State
        scatterChart = null;
        radarChart = null;
        currentData: metabolic_embedding[] = [];
        currentTaxonomyLevel = 'kingdom';
        colorMap = {};
        selectedGenome: metabolic_embedding = null;

        /**
         * Initialize charts
         */
        initCharts() {
            // Initialize scatter chart
            const scatterDom = document.getElementById('scatter-chart');
            this.scatterChart = echarts.init(scatterDom, null, { renderer: 'canvas' });

            // Initialize radar chart
            const radarDom = document.getElementById('radar-chart');
            this.radarChart = echarts.init(radarDom, null, { renderer: 'canvas' });

            // Handle resize
            window.addEventListener('resize', () => {
                if (this.scatterChart) this.scatterChart.resize();
                if (this.radarChart) this.radarChart.resize();
            });

            // Initialize empty radar chart
            this.updateRadarChart(null);

            // Bind taxonomy select change event
            document.getElementById('taxonomy-select').addEventListener('change', e => {
                this.setTaxonomyLevel(e.target.value);
            });
        }

        /**
         * Generate color map for unique values
         */
        generateColorMap(uniqueValues) {
            const map = {};
            uniqueValues.forEach((value, index) => {
                map[value] = COLOR_PALETTE[index % COLOR_PALETTE.length];
            });
            return map;
        }

        /**
         * Get unique values for a taxonomy level
         */
        getUniqueValues(data, level) {
            const values = new Set();
            data.forEach(item => {
                if (item[level]) {
                    values.add(item[level]);
                }
            });
            return Array.from(values).sort();
        }

        /**
         * Update legend
         */
        updateLegend() {
            const container = document.getElementById('legend-container');
            const uniqueValues = Object.keys(this.colorMap).sort();

            container.innerHTML = uniqueValues.map(value => `
            <div class="legend-item" data-value="${value}">
                <span class="legend-color" style="background-color: ${this.colorMap[value]}"></span>
                <span class="legend-label" title="${value}">${value || 'Unknown'}</span>
            </div>
        `).join('');
        }

        /**
         * Update stats bar
         */
        updateStats() {
            document.getElementById('stat-total').textContent = this.currentData.length.toString();
            document.getElementById('stat-colorby').textContent =
                this.currentTaxonomyLevel.charAt(0).toUpperCase() + this.currentTaxonomyLevel.slice(1);
            document.getElementById('stat-categories').textContent = Object.keys(this.colorMap).length.toString();
        }

        /**
         * Update 3D scatter chart
         */
        updateScatterChart() {
            let scatterChart = this.scatterChart;
            let currentData = this.currentData;

            if (!scatterChart || !currentData.length) return;

            const uniqueValues = this.getUniqueValues(currentData, this.currentTaxonomyLevel);
            this.colorMap = this.generateColorMap(uniqueValues);

            // Group data by taxonomy value
            const seriesMap = {};
            currentData.forEach(item => {
                const taxValue = item[this.currentTaxonomyLevel] || 'Unknown';
                if (!seriesMap[taxValue]) {
                    seriesMap[taxValue] = [];
                }
                seriesMap[taxValue].push([
                    item.x,
                    item.y,
                    item.z,
                    item.scientific_name,
                    item.ncbi_taxid,
                    item.assembly_id
                ]);
            });

            // Create series for each taxonomy group
            const series = Object.entries(seriesMap).map(([taxValue, data]) => ({
                name: taxValue,
                type: 'scatter3D',
                data: data.map(d => ({
                    value: [d[0], d[1], d[2]],
                    itemStyle: {
                        color: this.colorMap[taxValue]
                    },
                    genomeData: {
                        scientific_name: d[3],
                        ncbi_taxid: d[4],
                        assembly_id: d[5]
                    }
                })),
                symbolSize: 5,
                emphasis: {
                    itemStyle: {
                        borderWidth: 0.5,
                        borderColor: '#1a1d26'
                    }
                }
            }));

            const option = {
                tooltip: {
                    formatter: (params) => {
                        if (params.data && params.data.genomeData) {
                            const g = params.data.genomeData;
                            return `<strong>${g.scientific_name}</strong><br/>
                                <span style="color:#8b92a5">NCBI TaxID: ${g.ncbi_taxid}</span>`;
                        }
                        return '';
                    },
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#d8dce6',
                    borderWidth: 1,
                    padding: [8, 12],
                    textStyle: {
                        color: '#1a1d26',
                        fontSize: 12,
                        fontFamily: 'Space Grotesk'
                    }
                },
                xAxis3D: {
                    type: 'value',
                    name: 'UMAP-1',
                    nameTextStyle: {
                        color: '#5a6275',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    axisLabel: {
                        color: '#8b92a5',
                        fontSize: 10
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    }
                },
                yAxis3D: {
                    type: 'value',
                    name: 'UMAP-2',
                    nameTextStyle: {
                        color: '#5a6275',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    axisLabel: {
                        color: '#8b92a5',
                        fontSize: 10
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    }
                },
                zAxis3D: {
                    type: 'value',
                    name: 'UMAP-3',
                    nameTextStyle: {
                        color: '#5a6275',
                        fontSize: 11
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    axisLabel: {
                        color: '#8b92a5',
                        fontSize: 10
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    }
                },
                grid3D: {
                    // boxWidth: 120,
                    // boxHeight: 80,
                    // boxDepth: 80,
                    viewControl: {
                        autoRotate: false,
                        distance: 200,
                        alpha: 20,
                        beta: 40
                    },
                    light: {
                        main: {
                            intensity: 1.2,
                            shadow: false
                        },
                        ambient: {
                            intensity: 0.3
                        }
                    },
                    postEffect: {
                        enable: false,
                        bloom: {
                            enable: true,
                            intensity: 0.8
                        }
                    }
                },
                series: series
            };

            scatterChart.setOption(option, true);

            // Add click handler
            scatterChart.off('click');
            scatterChart.on('click', (params) => {
                if (params.data && params.data.genomeData) {
                    const assemblyId = params.data.genomeData.assembly_id;
                    const genome = currentData.find(g => g.assembly_id === assemblyId);
                    if (genome) {
                        this.selectGenome(genome);
                    }
                }
            });

            this.updateLegend();
            this.updateStats();
        }

        /**
         * Select a genome and display its details
         */
        selectGenome(genome) {
            this.selectedGenome = genome;

            // Update genome detail panel
            const detailContainer = document.getElementById('genome-detail');

            const taxonomyHtml = `
            <li><span class="taxonomy-rank">Kingdom</span><span class="taxonomy-value">${genome.kingdom || '-'}</span></li>
            <li><span class="taxonomy-rank">Phylum</span><span class="taxonomy-value">${genome.phylum || '-'}</span></li>
            <li><span class="taxonomy-rank">Class</span><span class="taxonomy-value">${genome.class || '-'}</span></li>
            <li><span class="taxonomy-rank">Order</span><span class="taxonomy-value">${genome.order || '-'}</span></li>
            <li><span class="taxonomy-rank">Family</span><span class="taxonomy-value">${genome.family || '-'}</span></li>
            <li><span class="taxonomy-rank">Genus</span><span class="taxonomy-value">${genome.genus || '-'}</span></li>
            <li><span class="taxonomy-rank">Species</span><span class="taxonomy-value">${genome.species || '-'}</span></li>
        `;

            detailContainer.innerHTML = `
            <div class="genome-card fade-in">
                <div class="genome-name">${genome.scientific_name}</div>
                <div class="genome-taxid">NCBI TaxID: ${genome.ncbi_taxid}</div>
            </div>
            <div class="section-title" style="margin-top: 0.5rem;">Taxonomy</div>
            <ul class="taxonomy-list">
                ${taxonomyHtml}
            </ul>
        `;

            // Update radar chart
            this.updateRadarChart(genome);

            // Highlight selected point in scatter
            this.highlightPoint(genome);
        }

        /**
         * Highlight selected point in scatter chart
         */
        highlightPoint(genome) {
            // Re-render with emphasis on selected point
            const uniqueValues = this.getUniqueValues(this.currentData, this.currentTaxonomyLevel);

            const seriesMap = {};
            this.currentData.forEach(item => {
                const taxValue = item[this.currentTaxonomyLevel] || 'Unknown';
                if (!seriesMap[taxValue]) {
                    seriesMap[taxValue] = [];
                }
                seriesMap[taxValue].push(item);
            });

            const series = Object.entries(seriesMap).map(([taxValue, data]) => ({
                name: taxValue,
                type: 'scatter3D',
                data: data.map(d => ({
                    value: [d.x, d.y, d.z],
                    itemStyle: {
                        color: this.colorMap[taxValue],
                        borderWidth: d.assembly_id === genome.assembly_id ? 3 : 0,
                        borderColor: '#1a1d26'
                    },
                    symbolSize: d.assembly_id === genome.assembly_id ? 20 : 12,
                    genomeData: {
                        scientific_name: d.scientific_name,
                        ncbi_taxid: d.ncbi_taxid,
                        assembly_id: d.assembly_id
                    }
                })),
                emphasis: {
                    itemStyle: {
                        borderWidth: 2,
                        borderColor: '#1a1d26'
                    }
                }
            }));

            this.scatterChart.setOption({ series: series });
        }

        /**
         * Update radar chart for EC number embedding
         */
        updateRadarChart(genome) {
            if (!this.radarChart) return;

            const ecData = genome ? genome.ec_number : [0, 0, 0, 0, 0, 0, 0];

            // Calculate max value for radar scale
            const maxVal = Math.max(...ecData, 1);

            const option = {
                radar: {
                    indicator: EC_LABELS.map((label, index) => ({
                        name: label,
                        max: maxVal * 1.2
                    })),
                    radius: '60%',
                    center: ['50%', '50%'],
                    axisName: {
                        color: '#5a6275',
                        fontSize: 9,
                        fontWeight: 500
                    },
                    axisLine: {
                        lineStyle: { color: '#d8dce6' }
                    },
                    splitLine: {
                        lineStyle: { color: '#eef1f6' }
                    },
                    splitArea: {
                        areaStyle: { color: ['transparent', 'rgba(13, 110, 253, 0.03)'] }
                    }
                },
                series: [{
                    type: 'radar',
                    data: [{
                        value: ecData,
                        name: genome ? genome.scientific_name : 'No selection',
                        symbol: 'circle',
                        symbolSize: 5,
                        lineStyle: {
                            width: 2,
                            color: '#2563eb'
                        },
                        areaStyle: {
                            color: 'rgba(37, 99, 235, 0.2)'
                        },
                        itemStyle: {
                            color: '#2563eb',
                            borderWidth: 2,
                            borderColor: '#fff'
                        }
                    }]
                }]
            };

            this.radarChart.setOption(option, true);
        }

        /**
         * Show/hide loading overlay
         */
        setLoading(isLoading) {
            const overlay = document.getElementById('loading-overlay');
            if (isLoading) {
                overlay.classList.add('active');
            } else {
                overlay.classList.remove('active');
            }
        }

        // ========================================
        // PUBLIC API
        // ========================================

        /**
         * Load embedding data and render visualization
         * @param {Array} data - Array of metabolic_embedding objects
         */
        loadData(data: metabolic_embedding[]) {
            if (!Array.isArray(data)) {
                console.error('Invalid data: expected an array of metabolic_embedding objects');
                return;
            }

            this.setLoading(true);
            this.currentData = data;
            this.selectedGenome = null;

            // Reset genome detail panel
            document.getElementById('genome-detail').innerHTML = `
            <div class="no-selection">
                <div class="no-selection-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4M12 16h.01"/>
                    </svg>
                </div>
                <p class="no-selection-text">Click a point in the scatter plot to view genome details</p>
            </div>
        `;

            // Reset radar chart
            this.updateRadarChart(null);

            // Update scatter chart
            setTimeout(() => {
                this.updateScatterChart();
                this.setLoading(false);
            }, 100);
        }

        /**
         * Set taxonomy level for coloring
         * @param {string} level - Taxonomy level (kingdom, phylum, class, order, family, genus, species)
         */
        setTaxonomyLevel(level) {
            const validLevels = ['kingdom', 'phylum', 'class', 'order', 'family', 'genus', 'species'];
            if (!validLevels.includes(level)) {
                console.error('Invalid taxonomy level:', level);
                return;
            }

            this.currentTaxonomyLevel = level;
            document.getElementById('taxonomy-select').value = level;
            this.updateScatterChart();
        }

        /**
         * Get currently selected genome
         * @returns {Object|null} Selected genome or null
         */
        getSelectedGenome() {
            return this.selectedGenome;
        }

        /**
         * Get chart instances for external manipulation
         * @returns {Object} Object containing scatterChart and radarChart instances
         */
        getChartInstances() {
            return {
                scatterChart: this.scatterChart,
                radarChart: this.radarChart
            };
        }

        /**
         * Resize charts
         */
        resizeCharts() {
            if (this.scatterChart) this.scatterChart.resize();
            if (this.radarChart) this.radarChart.resize();
        }

    }
}