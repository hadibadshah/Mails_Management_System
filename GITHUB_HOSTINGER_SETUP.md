# 🚀 Automated GitHub to Hostinger Deployment Guide (CI/CD Pipeline)

Bhai, aapka idea **100% zabardast aur professional** hai! 
Har dafa manual files download karke Hostinger par upload karne se time zaya hota hai aur ghalti ka imkaan rehta hai. 

Is system ke lagne ke baad:
1. Aap AI Studio me **Publish / Export to GitHub** kareinge.
2. Code foran aapki GitHub repository me jayega.
3. GitHub Actions khud code ko verify & build karega.
4. GitHub Actions khud tamam updated files Hostinger ke `public_html/asim/` folder me upload kar dega!
5. **Database Protection:** Live `db/vault_data.sqlite` ko yeh script bilkul touch nahi karega, aapka purana sara data aur stock 100% safe rahega!

---

## 🛠️ Step-by-Step Setup (Sirf 3 Minute Ka Kaam):

### STEP 1: AI Studio Ko GitHub Sy Link / Export Karein
1. AI Studio ke top-right me **Settings** ya **Export** menu kholein.
2. **"Export to GitHub"** (ya "Push to GitHub") select karein.
3. Apna GitHub account connect karein aur repository ka naam rakhein (e.g. `hadi-digital-portal`).
4. Repositiory create ho kar sara code GitHub par chala jayega.

---

### STEP 2: Hostinger Se FTP Details Dekhein
1. Hostinger **hPanel** login karein (`hpanel.hostinger.com`).
2. Apni domain (`eztoolbox.xyz`) ke dashboard me jayein.
3. Left search bar me type karein: **"FTP Accounts"**.
4. Wahan aapko ye 3 cheezein milengi:
   - **FTP IP / Hostname:** (jaise `ftp.eztoolbox.xyz` ya IP address jaise `185.xxx.xxx.xxx`)
   - **FTP Username:** (jaise `u123456789` ya jo bhi aapka user ho)
   - **FTP Password:** (Aapka FTP password, agar bhool gaye hain to wahin se "Change Password" kar lein)

*(Tip: Make sure FTP user has access to `public_html`)*

---

### STEP 3: GitHub Repository Me Secrets Save Karein
1. Apni GitHub repository open karein browser me.
2. Upar **Settings** tab par click karein.
3. Left sidebar me **Secrets and variables** ➡️ **Actions** par click karein.
4. Green button **"New repository secret"** dabayein aur ye 3 secrets add karein:

| Secret Name | Value | Example |
|---|---|---|
| `HOSTINGER_FTP_SERVER` | Aapka FTP Hostname ya IP | `ftp.eztoolbox.xyz` |
| `HOSTINGER_FTP_USERNAME` | Aapka Hostinger FTP Username | `u123456789` |
| `HOSTINGER_FTP_PASSWORD` | Aapka FTP Password | `AapkaPassword123` |

*(Optional)*: Agar target folder `public_html/asim/` ke bajaye kuch aur ho to `HOSTINGER_TARGET_DIR` bana sakte hain, warna default `public_html/asim/` set hai.

---

### STEP 4: Test Karein!
1. GitHub par **Actions** tab kholein.
2. Wahan aapko **"Deploy to Hostinger via FTP"** workflow nazar aayega.
3. Aap wahin se **"Run workflow"** button daba kar bhi deploy kar sakte hain, YA jab bhi aap AI Studio se naya code push kareinge, yeh **KHUD-BA-KHUD** chal parega!
4. Green tick aate hi `https://asim.eztoolbox.xyz` live update ho jayegi!
