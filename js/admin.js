/**
 * GearShift - Admin Portal Management Controller
 * Pure Vanilla JavaScript
 */

let adminMechanicsCache = [];
let adminServicesCache = [];
let adminBookingsCache = [];

/**
 * Route Guard: Verify Admin Privileges
 */
async function requireAdminAuth() {
    const res = await apiRequest('auth/me.php');
    if (!res || !res.success || !res.data || !res.data.authenticated || res.data.user.role !== 'admin') {
        showToast('error', 'Access Denied', 'Administrator privileges are required.');
        setTimeout(() => {
            window.location.href = '../login.html?redirect=admin';
        }, 1000);
        return null;
    }

    const admin = res.data.user;
    const adminNameElements = document.querySelectorAll('.admin-name-display');
    adminNameElements.forEach(el => el.textContent = admin.name);

    return admin;
}

/**
 * ----------------------------------------------------
 * 1. Admin Dashboard Statistics
 * ----------------------------------------------------
 */
async function loadAdminDashboardStats() {
    const admin = await requireAdminAuth();
    if (!admin) return;

    const res = await apiRequest('admin/stats.php');
    if (!res.success) {
        showToast('error', 'Error', res.message);
        return;
    }

    const stats = res.data.stats;
    const recent = res.data.recent_bookings;

    // Update KPI counters
    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    setVal('kpi-customers', stats.total_customers);
    setVal('kpi-vehicles', stats.total_vehicles);
    setVal('kpi-bookings', stats.total_bookings);
    setVal('kpi-pending', stats.pending_bookings);
    setVal('kpi-completed', stats.completed_services);
    setVal('kpi-services', stats.total_services);
    setVal('kpi-mechanics', stats.total_mechanics);
    setVal('kpi-revenue', formatCurrency(stats.total_revenue));

    // Populate recent bookings table
    const recentTbody = document.getElementById('recent-bookings-tbody');
    if (recentTbody) {
        if (!recent || recent.length === 0) {
            recentTbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:1.5rem; color:var(--text-muted);">No bookings found yet.</td></tr>`;
            return;
        }

        recentTbody.innerHTML = recent.map(b => {
            const statusClass = 'badge-' + b.status.toLowerCase().replace(/\s+/g, '');
            return `
                <tr>
                    <td><strong>#BKG-${String(b.id).padStart(5, '0')}</strong></td>
                    <td>${escapeHtml(b.customer_name)}</td>
                    <td>
                        <strong>${escapeHtml(b.vehicle_brand)} ${escapeHtml(b.vehicle_model)}</strong>
                        <span style="font-size:0.75rem; font-family:monospace; background:#e2e8f0; padding:1px 4px; border-radius:3px;">${escapeHtml(b.vehicle_number)}</span>
                    </td>
                    <td>${escapeHtml(b.service_name)}</td>
                    <td>📅 ${formatDate(b.booking_date)} <span style="font-size:0.8rem; color:var(--text-light);">${b.booking_time}</span></td>
                    <td>${formatCurrency(b.estimated_price)}</td>
                    <td><span class="badge ${statusClass}">${b.status}</span></td>
                </tr>
            `;
        }).join('');
    }
}

/**
 * ----------------------------------------------------
 * 2. Admin Bookings Management
 * ----------------------------------------------------
 */
async function loadAdminBookings() {
    const admin = await requireAdminAuth();
    if (!admin) return;

    // Load mechanics for quick assignment dropdowns
    const mRes = await apiRequest('mechanics/list.php');
    if (mRes.success) {
        adminMechanicsCache = mRes.data || [];
    }

    await fetchAndRenderAdminBookings();
}

async function fetchAndRenderAdminBookings() {
    const statusFilter = document.getElementById('admin-booking-status-filter')?.value || '';
    const search = document.getElementById('admin-booking-search')?.value.trim() || '';

    const tbody = document.getElementById('admin-bookings-tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:2rem;"><div class="spinner" style="border-top-color:var(--brand-blue);"></div></td></tr>`;

    let url = 'bookings/list_all.php?';
    if (statusFilter && statusFilter !== 'all') url += `status=${encodeURIComponent(statusFilter)}&`;
    if (search) url += `search=${encodeURIComponent(search)}`;

    const res = await apiRequest(url);
    if (!res.success) {
        showToast('error', 'Error', res.message);
        return;
    }

    adminBookingsCache = res.data || [];

    if (adminBookingsCache.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">No matching bookings found.</td></tr>`;
        return;
    }

    tbody.innerHTML = adminBookingsCache.map(b => {
        const statusClass = 'badge-' + b.status.toLowerCase().replace(/\s+/g, '');

        // Mechanics options
        const mechanicsOptions = `
            <option value="">-- Assign Mechanic --</option>
            ${adminMechanicsCache.map(m => `
                <option value="${m.id}" ${b.mechanic_id == m.id ? 'selected' : ''}>
                    ${escapeHtml(m.name)} (${escapeHtml(m.availability)})
                </option>
            `).join('')}
        `;

        return `
            <tr>
                <td><strong>#BKG-${String(b.id).padStart(5, '0')}</strong></td>
                <td>
                    <div style="font-weight:600;">${escapeHtml(b.customer_name)}</div>
                    <div style="font-size:0.8rem; color:var(--text-light);">${escapeHtml(b.customer_phone || '')}</div>
                </td>
                <td>
                    <div>${escapeHtml(b.vehicle_brand)} ${escapeHtml(b.vehicle_model)}</div>
                    <span style="font-size:0.75rem; font-family:monospace; background:#e2e8f0; padding:1px 4px; border-radius:3px;">${escapeHtml(b.vehicle_number)}</span>
                </td>
                <td>
                    <div style="font-weight:500;">${escapeHtml(b.service_name)}</div>
                    <span style="font-size:0.8rem; color:var(--text-light);">${formatCurrency(b.estimated_price)}</span>
                </td>
                <td>
                    <div>📅 ${formatDate(b.booking_date)}</div>
                    <div style="font-size:0.8rem; color:var(--text-light);">🕒 ${escapeHtml(b.booking_time)}</div>
                </td>
                <td>
                    <select class="form-control form-control-sm" style="min-width:140px; font-size:0.85rem;" onchange="handleAdminAssignMechanic(${b.id}, this.value)">
                        ${mechanicsOptions}
                    </select>
                </td>
                <td>
                    <select class="form-control form-control-sm" style="min-width:125px; font-weight:600; font-size:0.85rem;" onchange="handleAdminChangeStatus(${b.id}, this.value)">
                        <option value="Pending" ${b.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Confirmed" ${b.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                        <option value="In Service" ${b.status === 'In Service' ? 'selected' : ''}>In Service</option>
                        <option value="Completed" ${b.status === 'Completed' ? 'selected' : ''}>Completed</option>
                        <option value="Cancelled" ${b.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="openAdminBookingDetailsModal(${b.id})">
                        View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

async function handleAdminAssignMechanic(bookingId, mechanicId) {
    const res = await apiRequest('bookings/assign_mechanic.php', 'POST', {
        booking_id: bookingId,
        mechanic_id: mechanicId ? parseInt(mechanicId, 10) : 0
    });

    if (res.success) {
        showToast('success', 'Assigned', res.message);
        fetchAndRenderAdminBookings();
    } else {
        showToast('error', 'Error', res.message);
    }
}

async function handleAdminChangeStatus(bookingId, newStatus) {
    const res = await apiRequest('bookings/update_status.php', 'POST', {
        booking_id: bookingId,
        status: newStatus
    });

    if (res.success) {
        showToast('success', 'Updated', res.message);
        fetchAndRenderAdminBookings();
    } else {
        showToast('error', 'Error', res.message);
    }
}

function openAdminBookingDetailsModal(bookingId) {
    const b = adminBookingsCache.find(x => x.id == bookingId);
    if (!b) return;

    const modalBody = document.getElementById('admin-booking-details-body');
    if (!modalBody) return;

    modalBody.innerHTML = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; font-size:0.92rem;">
            <div style="background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0;">
                <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--primary);">👤 Customer Profile</h4>
                <p style="margin-bottom:0.35rem;"><strong>Name:</strong> ${escapeHtml(b.customer_name)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Email:</strong> ${escapeHtml(b.customer_email || 'N/A')}</p>
                <p style="margin-bottom:0.35rem;"><strong>Phone:</strong> ${escapeHtml(b.customer_phone || 'N/A')}</p>
            </div>
            <div style="background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0;">
                <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--primary);">🚗 Vehicle Details</h4>
                <p style="margin-bottom:0.35rem;"><strong>Vehicle:</strong> ${escapeHtml(b.vehicle_brand)} ${escapeHtml(b.vehicle_model)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Registration Plate:</strong> ${escapeHtml(b.vehicle_number)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Type:</strong> ${escapeHtml(b.vehicle_type)}</p>
            </div>
        </div>

        <div style="margin-top:1.25rem; background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0; font-size:0.92rem;">
            <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--primary);">🛠️ Service Details</h4>
            <p style="margin-bottom:0.35rem;"><strong>Service:</strong> ${escapeHtml(b.service_name)}</p>
            <p style="margin-bottom:0.35rem;"><strong>Price:</strong> ${formatCurrency(b.estimated_price)}</p>
            <p style="margin-bottom:0.35rem;"><strong>Appointment:</strong> ${formatDate(b.booking_date)} at ${escapeHtml(b.booking_time)}</p>
            <p style="margin-bottom:0.35rem;"><strong>Current Status:</strong> <span class="badge badge-${b.status.toLowerCase().replace(/\s+/g, '')}">${b.status}</span></p>
        </div>

        ${b.notes ? `
            <div style="margin-top:1.25rem; background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0; font-size:0.92rem;">
                <h4 style="font-size:0.95rem; margin-bottom:0.4rem; color:var(--primary);">📝 Customer Instructions / Notes</h4>
                <p style="margin:0; white-space:pre-wrap; color:var(--text-muted);">${escapeHtml(b.notes)}</p>
            </div>
        ` : ''}
    `;

    openModal('admin-booking-details-modal');
}

/**
 * ----------------------------------------------------
 * 3. Admin Services Management
 * ----------------------------------------------------
 */
async function loadAdminServices() {
    const admin = await requireAdminAuth();
    if (!admin) return;

    const tbody = document.getElementById('admin-services-tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:2rem;"><div class="spinner" style="border-top-color:var(--brand-blue);"></div></td></tr>`;

    const res = await apiRequest('services/list.php');
    if (!res.success) {
        showToast('error', 'Error', res.message);
        return;
    }

    adminServicesCache = res.data || [];

    tbody.innerHTML = adminServicesCache.map(s => `
        <tr>
            <td><strong>#SRV-${s.id}</strong></td>
            <td><strong>${escapeHtml(s.service_name)}</strong></td>
            <td style="max-width:300px; color:var(--text-muted);">${escapeHtml(s.description)}</td>
            <td><strong>${formatCurrency(s.price)}</strong></td>
            <td>⏱️ ${escapeHtml(s.estimated_duration)}</td>
            <td>
                <div style="display:flex; gap:0.4rem; justify-content:flex-end;">
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="openAdminEditServiceModal(${s.id})">
                        ✏️ Edit
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="handleAdminDeleteService(${s.id}, '${escapeHtml(s.service_name)}')">
                        🗑️ Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openAdminAddServiceModal() {
    const form = document.getElementById('service-modal-form');
    if (form) {
        form.reset();
        form.id.value = '';
    }
    document.getElementById('service-modal-title').textContent = 'Add New Service Package';
    openModal('service-modal');
}

function openAdminEditServiceModal(serviceId) {
    const s = adminServicesCache.find(x => x.id == serviceId);
    if (!s) return;

    const form = document.getElementById('service-modal-form');
    if (!form) return;

    form.id.value = s.id;
    form.service_name.value = s.service_name;
    form.description.value = s.description;
    form.price.value = s.price;
    form.estimated_duration.value = s.estimated_duration;

    document.getElementById('service-modal-title').textContent = 'Edit Service Package';
    openModal('service-modal');
}

async function handleAdminServiceForm(event) {
    event.preventDefault();
    const form = event.target;
    const id = form.id.value;
    const name = form.service_name.value.trim();
    const desc = form.description.value.trim();
    const price = parseFloat(form.price.value);
    const duration = form.estimated_duration.value.trim();

    if (!name || !desc || isNaN(price) || price <= 0 || !duration) {
        showToast('error', 'Validation Error', 'All fields are required and price must be greater than 0.');
        return;
    }

    const payload = {
        id: id ? parseInt(id, 10) : undefined,
        service_name: name,
        description: desc,
        price: price,
        estimated_duration: duration
    };

    const endpoint = id ? 'services/update.php' : 'services/add.php';
    const res = await apiRequest(endpoint, 'POST', payload);

    if (res.success) {
        closeModal('service-modal');
        showToast('success', 'Saved', res.message);
        loadAdminServices();
    } else {
        showToast('error', 'Error', res.message);
    }
}

function handleAdminDeleteService(serviceId, serviceName) {
    showConfirm(
        'Delete Service',
        `Are you sure you want to delete service package "${serviceName}"?`,
        async () => {
            const res = await apiRequest('services/delete.php', 'POST', { id: serviceId });
            if (res.success) {
                showToast('success', 'Deleted', res.message);
                loadAdminServices();
            } else {
                showToast('error', 'Error', res.message);
            }
        },
        'Delete Service',
        'btn-danger'
    );
}

/**
 * ----------------------------------------------------
 * 4. Admin Mechanics Management
 * ----------------------------------------------------
 */
async function loadAdminMechanics() {
    const admin = await requireAdminAuth();
    if (!admin) return;

    const tbody = document.getElementById('admin-mechanics-tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:2rem;"><div class="spinner" style="border-top-color:var(--brand-blue);"></div></td></tr>`;

    const res = await apiRequest('mechanics/list.php');
    if (!res.success) {
        showToast('error', 'Error', res.message);
        return;
    }

    adminMechanicsCache = res.data || [];

    tbody.innerHTML = adminMechanicsCache.map(m => {
        const availClass = 'badge-' + m.availability.toLowerCase().replace(/\s+/g, '');
        return `
            <tr>
                <td><strong>#MEC-${m.id}</strong></td>
                <td><strong>👨‍🔧 ${escapeHtml(m.name)}</strong></td>
                <td>${escapeHtml(m.phone)}</td>
                <td>${escapeHtml(m.specialization)}</td>
                <td><span class="badge ${availClass}">${m.availability}</span></td>
                <td>
                    <div style="display:flex; gap:0.4rem; justify-content:flex-end;">
                        <button type="button" class="btn btn-outline-dark btn-sm" onclick="openAdminEditMechanicModal(${m.id})">
                            ✏️ Edit
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="handleAdminDeleteMechanic(${m.id}, '${escapeHtml(m.name)}')">
                            🗑️ Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function openAdminAddMechanicModal() {
    const form = document.getElementById('mechanic-modal-form');
    if (form) {
        form.reset();
        form.id.value = '';
    }
    document.getElementById('mechanic-modal-title').textContent = 'Add New Mechanic';
    openModal('mechanic-modal');
}

function openAdminEditMechanicModal(mechId) {
    const m = adminMechanicsCache.find(x => x.id == mechId);
    if (!m) return;

    const form = document.getElementById('mechanic-modal-form');
    if (!form) return;

    form.id.value = m.id;
    form.name.value = m.name;
    form.phone.value = m.phone;
    form.specialization.value = m.specialization;
    form.availability.value = m.availability;

    document.getElementById('mechanic-modal-title').textContent = 'Edit Mechanic';
    openModal('mechanic-modal');
}

async function handleAdminMechanicForm(event) {
    event.preventDefault();
    const form = event.target;
    const id = form.id.value;
    const name = form.name.value.trim();
    const phone = form.phone.value.trim();
    const spec = form.specialization.value.trim();
    const avail = form.availability.value;

    if (!name || !phone || !spec) {
        showToast('error', 'Validation Error', 'Name, phone, and specialization are required.');
        return;
    }

    const payload = {
        id: id ? parseInt(id, 10) : undefined,
        name: name,
        phone: phone,
        specialization: spec,
        availability: avail
    };

    const endpoint = id ? 'mechanics/update.php' : 'mechanics/add.php';
    const res = await apiRequest(endpoint, 'POST', payload);

    if (res.success) {
        closeModal('mechanic-modal');
        showToast('success', 'Saved', res.message);
        loadAdminMechanics();
    } else {
        showToast('error', 'Error', res.message);
    }
}

function handleAdminDeleteMechanic(mechId, mechName) {
    showConfirm(
        'Delete Mechanic',
        `Are you sure you want to remove mechanic "${mechName}"?`,
        async () => {
            const res = await apiRequest('mechanics/delete.php', 'POST', { id: mechId });
            if (res.success) {
                showToast('success', 'Deleted', res.message);
                loadAdminMechanics();
            } else {
                showToast('error', 'Error', res.message);
            }
        },
        'Delete Mechanic',
        'btn-danger'
    );
}

/**
 * ----------------------------------------------------
 * 5. Admin Customers Management
 * ----------------------------------------------------
 */
async function loadAdminCustomers() {
    const admin = await requireAdminAuth();
    if (!admin) return;

    const search = document.getElementById('customer-search-input')?.value.trim() || '';
    const tbody = document.getElementById('admin-customers-tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem;"><div class="spinner" style="border-top-color:var(--brand-blue);"></div></td></tr>`;

    let url = 'admin/customers.php';
    if (search) url += `?search=${encodeURIComponent(search)}`;

    const res = await apiRequest(url);
    if (!res.success) {
        showToast('error', 'Error', res.message);
        return;
    }

    const customers = res.data || [];

    if (customers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted);">No customers found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = customers.map(c => `
        <tr>
            <td><strong>#CUST-${c.id}</strong></td>
            <td><strong>👤 ${escapeHtml(c.name)}</strong></td>
            <td>${escapeHtml(c.email)}</td>
            <td>${escapeHtml(c.phone)}</td>
            <td><span class="badge" style="background:#e0f2fe; color:#0369a1;">${c.vehicle_count} Vehicles</span></td>
            <td><span class="badge" style="background:#fef3c7; color:#b45309;">${c.booking_count} Bookings</span></td>
            <td>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="openAdminCustomerDetailsModal(${c.id})">
                    View Portfolio
                </button>
            </td>
        </tr>
    `).join('');
}

async function openAdminCustomerDetailsModal(customerId) {
    const res = await apiRequest(`admin/customer_details.php?id=${customerId}`);
    if (!res.success) {
        showToast('error', 'Error', res.message);
        return;
    }

    const { customer, vehicles, bookings } = res.data;
    const body = document.getElementById('customer-details-modal-body');
    if (!body) return;

    body.innerHTML = `
        <div style="background:#f8fafc; padding:1.25rem; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:1.5rem;">
            <h3 style="font-size:1.15rem; margin-bottom:0.4rem; color:var(--primary);">👤 ${escapeHtml(customer.name)}</h3>
            <p style="margin:0 0 0.25rem 0;"><strong>Email:</strong> ${escapeHtml(customer.email)}</p>
            <p style="margin:0 0 0.25rem 0;"><strong>Phone:</strong> ${escapeHtml(customer.phone)}</p>
            <p style="margin:0; font-size:0.85rem; color:var(--text-light);">Member Since: ${formatDate(customer.created_at)}</p>
        </div>

        <h4 style="font-size:1rem; margin-bottom:0.75rem; color:var(--primary);">🚗 Registered Vehicles (${vehicles.length})</h4>
        ${vehicles.length === 0 ? `<p style="color:var(--text-muted); font-size:0.9rem;">No vehicles registered yet.</p>` : `
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1.5rem;">
                ${vehicles.map(v => `
                    <div style="background:#ffffff; border:1px solid #e2e8f0; padding:0.75rem 1rem; border-radius:6px;">
                        <strong style="color:var(--primary); font-size:0.95rem;">${escapeHtml(v.brand)} ${escapeHtml(v.model)}</strong>
                        <div style="font-family:monospace; font-size:0.85rem; color:#475569;">${escapeHtml(v.vehicle_number)} (${escapeHtml(v.vehicle_type)})</div>
                        <div style="font-size:0.8rem; color:var(--text-light);">Year: ${v.manufacturing_year} | Fuel: ${v.fuel_type}</div>
                    </div>
                `).join('')}
            </div>
        `}

        <h4 style="font-size:1rem; margin-bottom:0.75rem; color:var(--primary);">📅 Booking History (${bookings.length})</h4>
        ${bookings.length === 0 ? `<p style="color:var(--text-muted); font-size:0.9rem;">No service appointments booked yet.</p>` : `
            <div class="table-responsive">
                <table class="table" style="font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bookings.map(b => `
                            <tr>
                                <td>#BKG-${String(b.id).padStart(5, '0')}</td>
                                <td>${escapeHtml(b.vehicle_brand)} ${escapeHtml(b.vehicle_model)}</td>
                                <td>${escapeHtml(b.service_name)}</td>
                                <td>${formatDate(b.booking_date)} ${escapeHtml(b.booking_time)}</td>
                                <td>${formatCurrency(b.estimated_price)}</td>
                                <td><span class="badge badge-${b.status.toLowerCase().replace(/\s+/g, '')}">${b.status}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `}
    `;

    openModal('customer-details-modal');
}
