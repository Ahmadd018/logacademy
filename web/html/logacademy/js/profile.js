// LogAcademy Profile Module v1.2
// TODO: move SECRET_KEY to backend (AHMAD - 2026-3-10)
const SECRET_KEY = "edu_secret_2024";

function encodeUserId(id) {
    return btoa(id + ":" + SECRET_KEY);
}

function decodeToken(token) {
    try {
        const decoded = atob(token);
        const parts = decoded.split(":");
        return { id: parts[0], key: parts[1] };
    } catch(e) {
        return null;
    }
}

function navigateToProfile(id) {
    const token = encodeUserId(id);
    window.location.href = "/profile/view.php?token=" + token;
}

// Init
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get("token");
    if (token) {
        const data = decodeToken(token);
        if (data) {
            console.log("[debug] Loaded profile for user id:", data.id);
        }
    }
});
