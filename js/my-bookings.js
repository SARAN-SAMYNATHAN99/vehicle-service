/**
 * GearShift - Customer Bookings History & Tracking
 * Pure Vanilla JavaScript
 */

let myBookingsCache = [];

/**
 * Load and render the customer's bookings
 */
async function loadMyBookings() {
    const tableBody = document.getElementById('bookings-tbody');
    const cardsContainer = document.getElementById('bookings-cards-container');
    if (!tableBody && !cardsContainer) return;

    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 2.5rem;">
                    <div class="spinner" style="border-top-color: var(--brand-blue); width:28px; height:28px;"></div>
                    <p style="margin-top: 0.75rem; color: var(--text-muted);">Loading your bookings...</p>
                </td>
            </tr>
        `;
    }

    const response = await apiRequest('bookings/my_bookings.php');

    if (!response.success) {
        showToast('error', 'Error', response.message);
        return;
    }

    myBookingsCache = response.data || [];
    filterAndRenderBookings();
}

/**
 * Filter bookings by status tab and search term
 */
function filterAndRenderBookings() {
    const statusFilter = document.getElementById('booking-status-filter')?.value || 'all';
    const searchTerm = document.getElementById('booking-search-input')?.value.toLowerCase().trim() || '';

    let filtered = myBookingsCache;

    if (statusFilter !== 'all') {
        filtered = filtered.filter(b => b.status === statusFilter);
    }

    if (searchTerm) {
        filtered = filtered.filter(b => 
            String(b.id).includes(searchTerm) ||
            b.vehicle_number.toLowerCase().includes(searchTerm) ||
            b.service_name.toLowerCase().includes(searchTerm) ||
            b.vehicle_brand.toLowerCase().includes(searchTerm) ||
            b.vehicle_model.toLowerCase().includes(searchTerm)
        );
    }

    renderBookingsTable(filtered);
}

/**
 * Render filtered bookings into table and cards
 */
function renderBookingsTable(bookings) {
    const tableBody = document.getElementById('bookings-tbody');
    const emptyState = document.getElementById('bookings-empty-state');

    if (bookings.length === 0) {
        if (tableBody) tableBody.innerHTML = '';
        if (emptyState) emptyState.style.display = 'block';
        return;
    }

    if (emptyState) emptyState.style.display = 'none';

    if (tableBody) {
        tableBody.innerHTML = bookings.map(b => {
            const canCancel = (b.status === 'Pending' || b.status === 'Confirmed');
            const statusClass = 'badge-' + b.status.toLowerCase().replace(/\s+/g, '');

            return `
                <tr>
                    <td><strong>#BKG-${String(b.id).padStart(5, '0')}</strong></td>
                    <td>
                        <div style="font-weight:600; color:var(--primary);">${escapeHtml(b.vehicle_brand)} ${escapeHtml(b.vehicle_model)}</div>
                        <span style="font-size:0.8rem; font-family:monospace; background:#e2e8f0; padding:2px 5px; border-radius:3px;">${escapeHtml(b.vehicle_number)}</span>
                    </td>
                    <td>
                        <div style="font-weight:500;">${escapeHtml(b.service_name)}</div>
                        <span style="font-size:0.8rem; color:var(--text-light);">⏱️ ${escapeHtml(b.estimated_duration)}</span>
                    </td>
                    <td>
                        <div>📅 ${formatDate(b.booking_date)}</div>
                        <div style="font-size:0.8rem; color:var(--text-muted);">🕒 ${escapeHtml(b.booking_time)}</div>
                    </td>
                    <td>
                        ${b.mechanic_name ? `
                            <div style="font-weight:500;">👨‍🔧 ${escapeHtml(b.mechanic_name)}</div>
                            <div style="font-size:0.75rem; color:var(--text-light);">${escapeHtml(b.mechanic_phone || '')}</div>
                        ` : `<span style="color:var(--text-light); font-size:0.85rem; font-style:italic;">Pending Assignment</span>`}
                    </td>
                    <td><strong>${formatCurrency(b.estimated_price)}</strong></td>
                    <td>
                        <span class="badge ${statusClass}">${escapeHtml(b.status)}</span>
                    </td>
                    <td>
                        <div style="display:flex; gap:0.4rem; justify-content:flex-end;">
                            <button type="button" class="btn btn-outline-dark btn-sm" onclick="openBookingDetailsModal(${b.id})">
                                Details
                            </button>
                            ${canCancel ? `
                                <button type="button" class="btn btn-danger btn-sm" onclick="openCancelModal(${b.id})">
                                    Cancel
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
}

/**
 * View complete booking details modal
 */
async function openBookingDetailsModal(bookingId) {
    const booking = myBookingsCache.find(b => b.id == bookingId);
    if (!booking) return;

    const modalBody = document.getElementById('details-modal-body');
    if (!modalBody) return;

    const statusSteps = ['Pending', 'Confirmed', 'In Service', 'Completed'];
    const currentStatusIdx = statusSteps.indexOf(booking.status);
    const isCancelled = booking.status === 'Cancelled';

    modalBody.innerHTML = `
        <!-- Timeline Stepper -->
        <div class="status-timeline">
            ${statusSteps.map((step, idx) => {
                let stepState = '';
                if (isCancelled) {
                    stepState = (step === 'Pending') ? 'completed' : '';
                } else {
                    if (idx < currentStatusIdx) stepState = 'completed';
                    else if (idx === currentStatusIdx) stepState = 'active';
                }
                return `
                    <div class="timeline-step ${stepState}">
                        <div class="step-indicator">${idx < currentStatusIdx ? '✓' : idx + 1}</div>
                        <div class="step-label">${step}</div>
                    </div>
                `;
            }).join('')}
        </div>

        ${isCancelled ? `
            <div style="background:#fee2e2; border-left:4px solid #ef4444; padding:0.75rem 1rem; border-radius:4px; margin-bottom:1.5rem; color:#991b1b; font-size:0.9rem;">
                <strong>Notice:</strong> This booking has been cancelled.
            </div>
        ` : ''}

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; font-size:0.92rem;">
            <div style="background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0;">
                <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--primary);">📋 Service Information</h4>
                <p style="margin-bottom:0.35rem;"><strong>Service:</strong> ${escapeHtml(booking.service_name)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Estimated Duration:</strong> ${escapeHtml(booking.estimated_duration)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Estimated Price:</strong> ${formatCurrency(booking.estimated_price)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Date & Time:</strong> ${formatDate(booking.booking_date)} at ${escapeHtml(booking.booking_time)}</p>
            </div>

            <div style="background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0;">
                <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--primary);">🚗 Vehicle Information</h4>
                <p style="margin-bottom:0.35rem;"><strong>Vehicle:</strong> ${escapeHtml(booking.vehicle_brand)} ${escapeHtml(booking.vehicle_model)}</p>
                <p style="margin-bottom:0.35rem;"><strong>License Plate:</strong> ${escapeHtml(booking.vehicle_number)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Type & Fuel:</strong> ${escapeHtml(booking.vehicle_type)} (${escapeHtml(booking.fuel_type)})</p>
                <p style="margin-bottom:0.35rem;"><strong>Year:</strong> ${booking.manufacturing_year}</p>
            </div>
        </div>

        <div style="margin-top:1.25rem; background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0; font-size:0.92rem;">
            <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:var(--primary);">👨‍🔧 Assigned Mechanic</h4>
            ${booking.mechanic_name ? `
                <p style="margin-bottom:0.35rem;"><strong>Name:</strong> ${escapeHtml(booking.mechanic_name)}</p>
                <p style="margin-bottom:0.35rem;"><strong>Specialization:</strong> ${escapeHtml(booking.mechanic_specialization || 'General Specialist')}</p>
                <p style="margin-bottom:0.35rem;"><strong>Phone:</strong> ${escapeHtml(booking.mechanic_phone || 'N/A')}</p>
            ` : `
                <p style="color:var(--text-light); margin:0;">A certified mechanic will be assigned to your appointment shortly once the slot is confirmed.</p>
            `}
        </div>

        ${booking.notes ? `
            <div style="margin-top:1.25rem; background:#f8fafc; padding:1rem; border-radius:8px; border:1px solid #e2e8f0; font-size:0.92rem;">
                <h4 style="font-size:0.95rem; margin-bottom:0.4rem; color:var(--primary);">📝 Customer Notes</h4>
                <p style="margin:0; white-space:pre-wrap; color:var(--text-muted);">${escapeHtml(booking.notes)}</p>
            </div>
        ` : ''}
    `;

    openModal('booking-details-modal');
}

/**
 * Open Cancel Booking Modal
 */
let currentCancelBookingId = null;

function openCancelModal(bookingId) {
    currentCancelBookingId = bookingId;
    const booking = myBookingsCache.find(b => b.id == bookingId);
    if (!booking) return;

    if (booking.status !== 'Pending' && booking.status !== 'Confirmed') {
        showToast('error', 'Cannot Cancel', 'Only Pending or Confirmed bookings may be cancelled.');
        return;
    }

    const cancelTitle = document.getElementById('cancel-modal-title');
    if (cancelTitle) {
        cancelTitle.textContent = `Cancel Booking #BKG-${String(bookingId).padStart(5, '0')}`;
    }

    const reasonInput = document.getElementById('cancel-reason');
    if (reasonInput) reasonInput.value = '';

    openModal('cancel-booking-modal');
}

/**
 * Submit cancellation
 */
async function submitBookingCancellation() {
    if (!currentCancelBookingId) return;

    const reason = document.getElementById('cancel-reason')?.value.trim() || '';
    const submitBtn = document.getElementById('confirm-cancel-btn');

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Cancelling...';
    }

    const response = await apiRequest('bookings/cancel.php', 'POST', {
        booking_id: currentCancelBookingId,
        reason: reason
    });

    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Yes, Cancel Appointment';
    }

    closeModal('cancel-booking-modal');

    if (response.success) {
        showToast('success', 'Cancelled', response.message);
        loadMyBookings();
    } else {
        showToast('error', 'Failed', response.message);
    }
}
