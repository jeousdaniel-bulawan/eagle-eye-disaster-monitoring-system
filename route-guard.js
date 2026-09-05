function requireLogin(allowedRoles) {
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    const role = (localStorage.getItem('userRole') || '').replace(/['"]/g, '').toLowerCase().trim();

    if (!isLoggedIn || isLoggedIn !== 'true') {
        window.location.replace('login.html');
        return false;
    }

    if (allowedRoles && allowedRoles.length > 0 && !allowedRoles.includes(role)) {
        if (role === 'admin') {
            window.location.replace('admin.html');
        } else {
            window.location.replace('home.html');
        }
        return false;
    }

    return true;
}


function logout() {
    localStorage.clear();
    window.location.replace('login.html');
}
