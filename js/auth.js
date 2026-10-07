/**
 * GearShift - Authentication & Session Management
 * Pure Vanilla JavaScript
 */

let currentUserState = null;

/**
 * Fetch current user from session
 */
async function checkAuthState() {
    const res = await apiRequest('auth/me.php');
    if (res && res.success && res.data && res.data.authenticated) {
        currentUserState = res.data.user;
    } else {
        currentUserState = null;
    }
    updateNavAuthUI();
    return currentUserState;
}

/**
 * Dynamically adjust navigation bar according to session status
 */
function updateNavAuthUI() {
    const navActions = document.getElementById('nav-actions');
    const navMenu = document.getElementById('nav-menu');
    
    if (!navActions) return;

    if (currentUserState) {
        // Logged in
        const isCustomer = currentUserState.role === 'customer';
        const dashboardLink = isCustomer ? 'dashboard.html' : 'admin/dashboard.html';

        navActions.innerHTML = `
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <a href="${dashboardLink}" class="btn btn-outline btn-sm">
                    👤 ${currentUserState.name.split(' ')[0]}
                </a>
                <button type="button" class="btn btn-danger btn-sm" onclick="handleLogout()">
                    Logout
                </button>
            </div>
        `;

        if (navMenu && !navMenu.dataset.customized) {
            navMenu.dataset.customized = "true";
            if (isCustomer) {
                navMenu.innerHTML += `
                    <li class="nav-item"><a href="dashboard.html">Dashboard</a></li>
                    <li class="nav-item"><a href="vehicles.html">My Vehicles</a></li>
                    <li class="nav-item"><a href="my-bookings.html">My Bookings</a></li>
                `;
            } else {
                navMenu.innerHTML += `
                    <li class="nav-item"><a href="admin/dashboard.html">Admin Portal</a></li>
                `;
            }
        }
    } else {
        // Guest
        navActions.innerHTML = `
            <a href="login.html" class="btn btn-outline btn-sm">Login</a>
            <a href="register.html" class="btn btn-accent btn-sm">Register</a>
        `;
    }
}

/**
 * Route Guard: Require customer authentication
 */
async function requireCustomerAuth() {
    const user = await checkAuthState();
    if (!user) {
        window.location.href = 'login.html?redirect=' + encodeURIComponent(window.location.pathname);
        return null;
    }
    // Update user display in dashboard if element exists
    const userNameElements = document.querySelectorAll('.current-user-name');
    userNameElements.forEach(el => el.textContent = user.name);

    const userEmailElements = document.querySelectorAll('.current-user-email');
    userEmailElements.forEach(el => el.textContent = user.email);

    const userAvatarElements = document.querySelectorAll('.profile-avatar');
    userAvatarElements.forEach(el => el.textContent = user.name.charAt(0).toUpperCase());

    return user;
}

/**
 * Route Guard: Redirect to dashboard if already authenticated
 */
async function redirectIfLoggedIn() {
    const user = await checkAuthState();
    if (user) {
        if (user.role === 'admin') {
            window.location.href = 'admin/dashboard.html';
        } else {
            window.location.href = 'dashboard.html';
        }
    }
}

/**
 * Handle Login Form Submission
 */
async function handleLoginForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');

    const email = form.email.value.trim();
    const password = form.password.value;

    if (!email || !password) {
        showToast('error', 'Validation Error', 'Please enter both email and password.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Signing in...';

    const response = await apiRequest('auth/login.php', 'POST', { email, password });

    submitBtn.disabled = false;
    submitBtn.innerHTML = 'Sign In';

    if (response.success) {
        showToast('success', 'Welcome Back!', response.message);
        setTimeout(() => {
            window.location.href = response.data.redirect || 'dashboard.html';
        }, 800);
    } else {
        showToast('error', 'Login Failed', response.message);
    }
}

/**
 * Handle Registration Form Submission
 */
async function handleRegisterForm(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');

    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const phone = form.phone.value.trim();
    const password = form.password.value;
    const confirmPassword = form.confirm_password.value;

    // Client-side validations
    if (name.length < 2) {
        showToast('error', 'Invalid Name', 'Name must be at least 2 characters.');
        return;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        showToast('error', 'Invalid Email', 'Please enter a valid email address.');
        return;
    }

    if (phone.length < 7) {
        showToast('error', 'Invalid Phone', 'Phone number must be at least 7 digits.');
        return;
    }

    if (password.length < 6) {
        showToast('error', 'Weak Password', 'Password must be at least 6 characters.');
        return;
    }

    if (password !== confirmPassword) {
        showToast('error', 'Mismatch', 'Passwords do not match.');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Creating Account...';

    const response = await apiRequest('auth/register.php', 'POST', {
        name,
        email,
        phone,
        password,
        confirm_password: confirmPassword
    });

    submitBtn.disabled = false;
    submitBtn.innerHTML = 'Create Account';

    if (response.success) {
        showToast('success', 'Account Created!', response.message);
        setTimeout(() => {
            window.location.href = response.data.redirect || 'dashboard.html';
        }, 1000);
    } else {
        showToast('error', 'Registration Failed', response.message);
    }
}

/**
 * Handle Logout
 */
async function handleLogout() {
    showConfirm('Confirm Logout', 'Are you sure you want to log out of your session?', async () => {
        const res = await apiRequest('auth/logout.php', 'POST');
        showToast('info', 'Logged Out', 'You have been safely logged out.');
        setTimeout(() => {
            const inAdmin = window.location.pathname.includes('/admin/');
            window.location.href = inAdmin ? '../login.html' : 'login.html';
        }, 600);
    }, 'Logout', 'btn-danger');
}
