/**
 * GearShift - Service Booking Flow
 * Pure Vanilla JavaScript
 */

let bookingVehicles = [];
let bookingServices = [];

/**
 * Initialize Booking Page
 */
async function initBookingPage() {
    // 1. Require customer authentication
    const user = await requireCustomerAuth();
    if (!user) return;

    // 2. Set min date on datepicker to today
    const dateInput = document.getElementById('booking-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        dateInput.value = today;
    }

    // 3. Load user's vehicles and all services simultaneously
    const [vehiclesRes, servicesRes] = await Promise.all([
        apiRequest('vehicles/list.php'),
        apiRequest('services/list.php')
    ]);

    bookingVehicles = (vehiclesRes.success && vehiclesRes.data) ? vehiclesRes.data : [];
    bookingServices = (servicesRes.success && servicesRes.data) ? servicesRes.data : [];

    populateVehicleSelect(bookingVehicles);
    populateServiceSelect(bookingServices);

    // 4. Check query params for pre-selected vehicle or service
    const urlParams = new URLSearchParams(window.location.search);
    const preServiceId = urlParams.get('service_id');
    const preVehicleId = urlParams.get('vehicle_id');

    if (preServiceId) {
        const sSelect = document.getElementById('booking-service');
        if (sSelect) {
            sSelect.value = preServiceId;
            updateBookingSummary();
        }
    }

    if (preVehicleId) {
        const vSelect = document.getElementById('booking-vehicle');
        if (vSelect) {
            vSelect.value = preVehicleId;
            updateBookingSummary();
        }
    }

    // Initial summary calculation
    updateBookingSummary();
}

/**
 * Populate vehicle select element
 */
function populateVehicleSelect(vehicles) {
    const select = document.getElementById('booking-vehicle');
    const noVehicleAlert = document.getElementById('no-vehicle-alert');
    if (!select) return;

    if (vehicles.length === 0) {
        select.innerHTML = '<option value="">-- No vehicles registered yet --</option>';
        select.disabled = true;
        if (noVehicleAlert) noVehicleAlert.style.display = 'block';
        return;
    }

    if (noVehicleAlert) noVehicleAlert.style.display = 'none';
    select.disabled = false;

    select.innerHTML = '<option value="">-- Select one of your vehicles --</option>' +
        vehicles.map(v => `
            <option value="${v.id}">
                ${escapeHtml(v.brand)} ${escapeHtml(v.model)} (${escapeHtml(v.vehicle_number)}) - ${escapeHtml(v.vehicle_type)}
            </option>
        `).join('');
}

/**
 * Populate service select element
 */
function populateServiceSelect(services) {
    const select = document.getElementById('booking-service');
    if (!select) return;

    select.innerHTML = '<option value="">-- Select a service package --</option>' +
        services.map(s => `
            <option value="${s.id}" data-price="${s.price}" data-duration="${escapeHtml(s.estimated_duration)}">
                ${escapeHtml(s.service_name)} - ${formatCurrency(s.price)} (${escapeHtml(s.estimated_duration)})
            </option>
        `).join('');
}

/**
 * Update dynamic booking summary card
 */
function updateBookingSummary() {
    const vSelect = document.getElementById('booking-vehicle');
    const sSelect = document.getElementById('booking-service');
    const dateInput = document.getElementById('booking-date');
    const timeSelect = document.getElementById('booking-time');

    const summaryVehicle = document.getElementById('summary-vehicle');
    const summaryService = document.getElementById('summary-service');
    const summaryDuration = document.getElementById('summary-duration');
    const summaryDate = document.getElementById('summary-date');
    const summaryTime = document.getElementById('summary-time');
    const summaryPrice = document.getElementById('summary-price');

    // Selected vehicle
    if (vSelect && vSelect.value) {
        const v = bookingVehicles.find(x => x.id == vSelect.value);
        if (v && summaryVehicle) {
            summaryVehicle.textContent = `${v.brand} ${v.model} [${v.vehicle_number}]`;
        }
    } else if (summaryVehicle) {
        summaryVehicle.textContent = 'None selected';
    }

    // Selected service
    if (sSelect && sSelect.value) {
        const s = bookingServices.find(x => x.id == sSelect.value);
        if (s) {
            if (summaryService) summaryService.textContent = s.service_name;
            if (summaryDuration) summaryDuration.textContent = s.estimated_duration;
            if (summaryPrice) summaryPrice.textContent = formatCurrency(s.price);
        }
    } else {
        if (summaryService) summaryService.textContent = 'None selected';
        if (summaryDuration) summaryDuration.textContent = '--';
        if (summaryPrice) summaryPrice.textContent = '$0.00';
    }

    // Date & Time
    if (summaryDate && dateInput) {
        summaryDate.textContent = dateInput.value ? formatDate(dateInput.value) : 'Not selected';
    }
    if (summaryTime && timeSelect) {
        summaryTime.textContent = timeSelect.value || 'Not selected';
    }
}

/**
 * Handle Service Booking Form Submission
 */
async function handleBookingSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('booking-submit-btn');

    const vehicleId = form.vehicle_id.value;
    const serviceId = form.service_id.value;
    const bookingDate = form.booking_date.value;
    const bookingTime = form.booking_time.value;
    const notes = form.notes.value.trim();

    if (!vehicleId) {
        showToast('error', 'Required Field', 'Please select a vehicle for this appointment.');
        return;
    }

    if (!serviceId) {
        showToast('error', 'Required Field', 'Please select a service package.');
        return;
    }

    if (!bookingDate) {
        showToast('error', 'Required Field', 'Please select an appointment date.');
        return;
    }

    if (!bookingTime) {
        showToast('error', 'Required Field', 'Please select a time slot.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Confirming Appointment...';

    const response = await apiRequest('bookings/create.php', 'POST', {
        vehicle_id: parseInt(vehicleId, 10),
        service_id: parseInt(serviceId, 10),
        booking_date: bookingDate,
        booking_time: bookingTime,
        notes: notes
    });

    submitBtn.disabled = false;
    submitBtn.innerHTML = '⚡ Confirm Service Appointment';

    if (response.success) {
        showToast('success', 'Booking Confirmed!', response.message);
        setTimeout(() => {
            window.location.href = 'my-bookings.html';
        }, 1200);
    } else {
        showToast('error', 'Booking Failed', response.message);
    }
}
