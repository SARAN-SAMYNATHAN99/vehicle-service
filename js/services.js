/**
 * GearShift - Services Catalog Management
 * Pure Vanilla JavaScript
 */

let allServicesCache = [];

/**
 * Load and render public services catalog
 */
async function loadServicesCatalog(containerId = 'services-container', limit = null) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <div class="spinner" style="border-top-color: var(--brand-blue); width:32px; height:32px;"></div>
            <p style="margin-top: 1rem; color: var(--text-muted);">Loading automotive services...</p>
        </div>
    `;

    const response = await apiRequest('services/list.php');

    if (!response.success || !response.data) {
        container.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">⚠️</div>
                <h3 class="empty-title">Services Currently Unavailable</h3>
                <p class="empty-desc">We could not load services at this time. Please make sure the database is configured.</p>
            </div>
        `;
        return;
    }

    allServicesCache = response.data;
    renderServicesList(allServicesCache, container, limit);
}

/**
 * Render array of services into container
 */
function renderServicesList(services, container, limit = null) {
    const displayList = limit ? services.slice(0, limit) : services;

    if (displayList.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">🔍</div>
                <h3 class="empty-title">No Services Found</h3>
                <p class="empty-desc">No services match your search query. Try another keyword.</p>
            </div>
        `;
        return;
    }

    const serviceIcons = {
        'General Service': '🛠️',
        'Oil Change': '🛢️',
        'Brake Service': '🛑',
        'Tyre Service': '🛞',
        'AC Service': '❄️',
        'Engine Check': '⚙️',
        'Battery Replacement': '🔋',
        'Wheel Alignment': '📐'
    };

    container.innerHTML = displayList.map(s => {
        const icon = serviceIcons[s.service_name] || '🔧';
        return `
            <div class="service-card">
                <div>
                    <div class="service-icon-box">${icon}</div>
                    <h3 class="service-name">${escapeHtml(s.service_name)}</h3>
                    <p class="service-desc">${escapeHtml(s.description)}</p>
                </div>
                <div>
                    <div class="service-meta">
                        <div>
                            <span style="font-size:0.75rem; color:var(--text-light); text-transform:uppercase; font-weight:700; display:block;">Estimated Cost</span>
                            <span class="service-price">${formatCurrency(s.price)}</span>
                        </div>
                        <span class="service-duration">⏱️ ${escapeHtml(s.estimated_duration)}</span>
                    </div>
                    <a href="booking.html?service_id=${s.id}" class="btn btn-accent btn-block">
                        Book This Service
                    </a>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Live search filter for services
 */
function filterServicesCatalog(searchQuery) {
    const container = document.getElementById('services-container');
    if (!container || !allServicesCache) return;

    const query = searchQuery.toLowerCase().trim();
    if (!query) {
        renderServicesList(allServicesCache, container);
        return;
    }

    const filtered = allServicesCache.filter(s => 
        s.service_name.toLowerCase().includes(query) ||
        s.description.toLowerCase().includes(query)
    );

    renderServicesList(filtered, container);
}
