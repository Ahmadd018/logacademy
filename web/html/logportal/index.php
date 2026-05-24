<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogAcademy — Tələbə Portalı</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans+Arabic:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117; --bg2: #161b27; --bg3: #1e2535;
            --border: #2a3347; --accent: #3b82f6; --accent2: #60a5fa;
            --text: #e2e8f0; --text2: #94a3b8; --danger: #ef4444;
            --success: #22c55e;
            --font-mono: 'IBM Plex Mono', monospace;
            --font-sans: 'IBM Plex Sans Arabic', sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: var(--font-sans); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: var(--bg2); border-bottom: 1px solid var(--border); padding: 0 2rem; height: 56px; display: flex; align-items: center; justify-content: space-between; }
        .brand { font-family: var(--font-mono); font-size: 1.1rem; font-weight: 600; color: var(--accent2); text-decoration: none; }
        .brand::before { content: '> '; color: var(--accent); }
        .nav-links { display: flex; gap: 1.5rem; }
        .nav-links a { color: var(--text2); text-decoration: none; font-size: 0.875rem; font-family: var(--font-mono); }
        .container { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem; flex: 1; }
        .card { background: var(--bg2); border: 1px solid var(--border); border-radius: 6px; padding: 1.5rem; margin-bottom: 1rem; }
        .card-title { font-family: var(--font-mono); font-size: 0.75rem; color: var(--accent); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-size: 0.8rem; color: var(--text2); margin-bottom: 0.4rem; font-family: var(--font-mono); }
        input[type="text"], input[type="password"] { width: 100%; background: var(--bg3); border: 1px solid var(--border); color: var(--text); padding: 0.6rem 0.8rem; border-radius: 4px; font-family: var(--font-mono); font-size: 0.875rem; outline: none; }
        input:focus { border-color: var(--accent); }
        .btn { display: inline-block; padding: 0.6rem 1.2rem; border-radius: 4px; border: none; cursor: pointer; font-family: var(--font-mono); font-size: 0.875rem; }
        .btn-primary { background: var(--accent); color: #fff; width: 100%; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-family: var(--font-mono); font-size: 1.25rem; }
        .page-header p { color: var(--text2); font-size: 0.875rem; }
        .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid var(--danger); color: var(--danger); padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.875rem; font-family: var(--font-mono); }
        .footer { background: var(--bg2); border-top: 1px solid var(--border); padding: 1rem 2rem; text-align: center; font-size: 0.75rem; color: var(--text2); font-family: var(--font-mono); }
        .harvested { background: var(--bg3); border: 1px solid var(--border); border-radius: 4px; padding: 1rem; margin-top: 1rem; font-family: var(--font-mono); font-size: 0.8rem; display: none; }
        .harvested .cred-item { color: var(--success); margin: 0.2rem 0; }
        #log { max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body>
<nav class="navbar">
    <a class="brand" href="#">LogAcademy</a>
    <div class="nav-links">
        <a href="#">Axtarış</a>
        <a href="#">Giriş</a>
        <a href="#">Qeydiyyat</a>
    </div>
</nav>

<main class="container">
    <div class="page-header">
        <h1>// Giriş</h1>
        <p>Portaldan istifadə etmək üçün daxil olun</p>
    </div>

    <div class="card" style="max-width:400px;">
        <div class="card-title">Hesaba daxil ol</div>
        <div id="error" class="alert-danger" style="display:none;">Sessiya müddəti bitib. Yenidən daxil olun.</div>
        <form id="fakeForm">
            <div class="form-group">
                <label>İstifadəçi adı</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label>Şifrə</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Daxil ol</button>
        </form>
        <div style="margin-top:1rem; font-size:0.8rem; color:var(--text2); text-align:center;">
            Hesabınız yoxdur? <a href="#" style="color:var(--accent2)">Qeydiyyat</a>
        </div>
    </div>

    <!-- Attacker view: harvested credentials shown on page for demo -->
    <div class="card" style="max-width:400px; margin-top:1rem; border-color:var(--danger);">
        <div class="card-title" style="color:var(--danger);">⚠ Attacker görünüşü — toplanan məlumatlar</div>
        <div id="log">
            <p style="color:var(--text2); font-size:0.8rem; font-family:var(--font-mono);">Hələ məlumat yoxdur...</p>
        </div>
    </div>
</main>

<footer class="footer">
    <p>© 2024 LogAcademy Tələbə Portalı. Bütün hüquqlar qorunur.</p>
</footer>

<script>
const log = document.getElementById('log');

document.getElementById('fakeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const user = document.getElementById('username').value;
    const pass = document.getElementById('password').value;

    // Show harvested creds in attacker panel
    const now = new Date().toLocaleTimeString();
    const entry = document.createElement('div');
    entry.style.cssText = 'font-family:var(--font-mono); font-size:0.8rem; padding:0.4rem 0; border-bottom:1px solid var(--border);';
    entry.innerHTML = `
        <span style="color:var(--text2);">[${now}]</span>
        <span style="color:var(--success);"> user: ${user}</span>
        <span style="color:var(--danger);"> pass: ${pass}</span>
    `;

    // Clear placeholder
    if (log.querySelector('p')) log.innerHTML = '';
    log.appendChild(entry);

    // Show error to victim (simulates session expired)
    document.getElementById('error').style.display = 'block';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';

    // Redirect to real site after 2s
    setTimeout(() => {
        window.location.href = 'http://logacademy.local/login.php';
    }, 2000);
});
</script>
</body>
</html>
