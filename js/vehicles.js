/**
 * GearShift - Customer Vehicle Management
 * Pure Vanilla JavaScript
 */

let userVehiclesCache = [];

/**
 * Load and render the current customer's vehicles
 */
async function loadVehicles() {
    const container = document.getElementById('vehicles-container');
    if (!container) return;

    container.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <div class="spinner" style="border-top-color: var(--brand-blue); width:32px; height:32px;"></div>
            <p style="margin-top: 1rem; color: var(--text-muted);">Loading your registered vehicles...</p>
        </div>
    `;

    const response = await apiRequest('vehicles/list.php');

    if (!response.success) {
        container.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">⚠️</div>
                <h3 class="empty-title">Failed to Load Vehicles</h3>
                <p class="empty-desc">${response.message}</p>
                <button class="btn btn-outline-dark" onclick="loadVehicles()">Try Again</button>
            </div>
        `;
        return;
    }

    userVehiclesCache = response.data || [];

    // Also update any stats counters if on dashboard
    const countBadge = document.getElementById('total-vehicles-count');
    if (countBadge) {
        countBadge.textContent = userVehiclesCache.length;
    }

    if (userVehiclesCache.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">🚗</div>
                <h3 class="empty-title">No Vehicles Registered Yet</h3>
                <p class="empty-desc">Add your car, bike, or SUV to easily book services and track service history.</p>
                <button type="button" class="btn btn-primary" onclick="openAddVehicleModal()">
                    + Add Your First Vehicle
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = userVehiclesCache.map(v => `
        <div class="vehicle-card">
            <div class="vehicle-card-header">
                <div>
                    <span style="font-size:0.8rem; text-transform:uppercase; color:#94a3b8; font-weight:600;">${escapeHtml(v.vehicle_type)}</span>
                </div>
                <div class="vehicle-plate">${escapeHtml(v.vehicle_number)}</div>
            </div>
            <div class="vehicle-card-body">
                <h4 class="vehicle-title">${escapeHtml(v.brand)} ${escapeHtml(v.model)}</h4>
                <div class="vehicle-specs">
                    <div class="spec-item">Year: <strong>${v.manufacturing_year}</strong></div>
                    <div class="spec-item">Fuel: <strong>${escapeHtml(v.fuel_type)}</strong></div>
                </div>
                <a href="booking.html?vehicle_id=${v.id}" class="btn btn-primary btn-sm btn-block">
                    ⚡ Book Service for this Vehicle
                </a>
            </div>
            <div class="vehicle-card-footer">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="openEditVehicleModal(${v.id})">
                    ✏️ Edit
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm" style="color:var(--danger); border-color:var(--danger-light);" onclick="handleDeleteVehicle(${v.id}, '${escapeHtml(v.vehicle_number)}')">
                    🗑️ Delete
                </button>
            </div>
        </div>
    `).join('');
}

/**
 * Open Modal to Add a New Vehicle
 */
function openAddVehicleModal() {
    const form = document.getElementById('vehicle-modal-form');
    if (form) {
        form.reset();
        form.id.value = '';
    }
    document.getElementById('vehicle-modal-title').textContent = 'Add New Vehicle';
    document.getElementById('vehicle-submit-btn').textContent = 'Save Vehicle';
    openModal('vehicle-modal');
}

/**
 * Open Modal to Edit an Existing Vehicle
 */
async function openEditVehicleModal(vehicleId) {
    const vehicle = userVehiclesCache.find(v => v.id == vehicleId);
    const form = document.getElementById('vehicle-modal-form');
    if (!form) return;

    if (vehicle) {
        populateVehicleForm(form, vehicle);
    } else {
        const res = await apiRequest(`vehicles/get.php?id=${vehicleId}`);
        if (res.success && res.data) {
            populateVehicleForm(form, res.data);
        } else {
            showToast('error', 'Error', 'Unable to retrieve vehicle information.');
            return;
        }
    }

    document.getElementById('vehicle-modal-title').textContent = 'Edit Vehicle';
    document.getElementById('vehicle-submit-btn').textContent = 'Update Vehicle';
    openModal('vehicle-modal');
}

function populateVehicleForm(form, v) {
    form.id.value = v.id;
    form.vehicle_number.value = v.vehicle_number;
    form.vehicle_type.value = v.vehicle_type;
    form.brand.value = v.brand;
    form.model.value = v.model;
    form.manufacturing_year.value = v.manufacturing_year;
    form.fuel_type.value = v.fuel_type;
}

/**
 * Handle Add/Edit Vehicle Form Submission
 */
async function handleVehicleForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('vehicle-submit-btn');

    const id = form.id.value;
    const vehicleNumber = form.vehicle_number.value.trim().toUpperCase();
    const vehicleType   = form.vehicle_type.value;
    const brand         = form.brand.value.trim();
    const model         = form.model.value.trim();
    const year          = parseInt(form.manufacturing_year.value, 10);
    const fuelType      = form.fuel_type.value;

    // Validation
    if (vehicleNumber.length < 3) {
        showToast('error', 'Validation Error', 'Vehicle registration number is required (min 3 chars).');
        return;
    }

    if (!brand || !model) {
        showToast('error', 'Validation Error', 'Please enter both brand and model.');
        return;
    }

    const currentYear = new Date().getFullYear() + 1;
    if (isNaN(year) || year < 1950 || year > currentYear) {
        showToast('error', 'Validation Error', `Year must be between 1950 and ${currentYear}.`);
        return;
    }

    const payload = {
        id: id ? parseInt(id, 10) : undefined,
        vehicle_number: vehicleNumber,
        vehicle_type: vehicleType,
        brand: brand,
        model: model,
        manufacturing_year: year,
        fuel_type: fuelType
    };

    const endpoint = id ? 'vehicles/update.php' : 'vehicles/add.php';

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Saving...';

    const response = await apiRequest(endpoint, 'POST', payload);

    submitBtn.disabled = false;
    submitBtn.textContent = id ? 'Update Vehicle' : 'Save Vehicle';

    if (response.success) {
        closeModal('vehicle-modal');
        showToast('success', 'Success', response.message);
        loadVehicles();
    } else {
        showToast('error', 'Failed', response.message);
    }
}

/**
 * Handle Delete Vehicle
 */
function handleDeleteVehicle(vehicleId, vehiclePlate) {
    showConfirm(
        'Delete Vehicle',
        `Are you sure you want to remove vehicle "${vehiclePlate}"? All associated past service records for this vehicle will also be removed.`,
        async () => {
            const res = await apiRequest('vehicles/delete.php', 'POST', { id: vehicleId });
            if (res.success) {
                showToast('success', 'Deleted', res.message);
                loadVehicles();
            } else {
                showToast('error', 'Error', res.message);
            }
        },
        'Delete Vehicle',
        'btn-danger'
    );
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
