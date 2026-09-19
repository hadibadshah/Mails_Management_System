# HADI DIGITAL - Single-Tenant Account Distribution Portal & Khata Ledger
### Hostinger Deployment & Live Update Guide (PHP + SQLite)

**Target Subdomain:** `asim.eztoolbox.xyz`  
**Target Directory:** `public_html/asim/`

---

### 1. Hostinger vs AI Studio (Ahem Wazahat - Deployment Scene):

- **Hostinger Production Server (`hostinger_package/`):**
  - Hostinger shared hosting par chalne ke liye pure **PHP + SQLite** code hai.
  - Isme **koi build ya compile step nahi chahiye**! Aapko Hostinger par `npm` ya `build` chalane ki bilkul zaroorat nahi hoti.
  - Jab bhi aap Hostinger par update karte hain, aap sirf `hostinger_package/` me se updated files (`index.php`, `api.php`, `db.php`) ko Hostinger File Manager me upload / replace karte hain.

- **AI Studio Web Preview (`src/`):**
  - AI Studio ke is live preview screen ke liye React + Tailwind frontend hai.
  - Yahan code compile aur build hota hai taake aap screen par live test kar sakein.
  - Dono jagah (Hostinger PHP aur React) same logic, same UI aur same features rakhe gaye hain taake jo yahan dikhayi de, wahi Hostinger par mile!

---

### 2. Credentials & Login Details
- **Admin Login:**
  - Username: `Hadi`
  - Password: `91199119`
- **Client Login:**
  - Username: `rana asim`
  - Password: `rana@123`
- **Secure Extraction PIN:**
  - `1234`
- **Default Rate:** `18 PKR / mail` (Customizable per order / delivery batch)

---

### 3. Files List (Hostinger Package)
```
public_html/asim/
├── .htaccess             # Apache protection, disables directory listing, blocks direct .sqlite access
├── config.php            # Master credentials, PIN, managed domains, SQLite path
├── db.php                # PDO SQLite connector with auto-migration for settings, payments & replacements
├── auth.php              # Session hardening, CSRF tokens, role-based authorization
├── api.php               # Unified API router with financial ledger, orders, payments, replacements & search
├── index.php             # Modern single-page portal with Khata ledger, payments hub & single mail finder
└── db/
    └── .htaccess         # "Require all denied" to prevent direct browser downloads
```

---

### 4. Kaam Kaise Live Karein (Hostinger Par Files Replace Karne Ka Tareeqa):

Agar aapki website already `asim.eztoolbox.xyz` par chal rahi hai:

#### Tareeqa 1: Sirf Update Hui Files Upload Karein (Sab Se Aasan & 100% Safe)
1. Hostinger hPanel login karein aur **File Manager** kholein.
2. `public_html/asim/` folder me jayein.
3. Sirf ye files upload / overwrite kar dein:
   - `index.php` (Smart Stock Allocation modal, Client portal order history with Date/Time & CSV download, no WhatsApp text)
   - `api.php` (Maintenance APIs, order export, financial ledger live math)
   - `db.php` (Auto-created `settings` table & SQLite indexes)
4. **Mera Purana Database Data Safe Rahega?**
   - **Ji Haan 100%!** `db/vault_data.sqlite` ko delete ya replace na karein. Aapka pehla tamam stock, orders, wasooli (payments) aur replacements bilkul safe rehte hain.
5. Browser me `https://asim.eztoolbox.xyz` open karein aur `Ctrl + Shift + R` (Hard Refresh) dabayein.

---

### 5. Taaza Tareen Features (Latest Updates):

1. **Client Portal Order History (Saaf Suthra & Professional):**
   - **WhatsApp ka koi zikr nahi**: Client portal se tamam "WhatsApp" references hata diye gaye hain.
   - **Order Number**: Har order ka authentic number (e.g. `Order #1`, `Order #2`) show hota hai.
   - **Purchase Date & Time**: Exact date aur time (e.g. `19 Sep 2026, 07:30 PM`) format me display hota hai.
   - **CSV Download Button**: Har us order ke sath jisme quantity > 1 (1 se zyada mails) ho, direct **"Download CSV"** button diya gaya hai taake client aik click me apne us order ki tamam emails download kar sake.

2. **Smart Stock Allocation Modal (Available vs Sold):**
   - Jab aap CSV upload ya paste karte hain, to system fauran aapse poochta hai:
     - **Available Stock**: Fresh stock jo Rana Asim ke portal par live extraction ke liye available hoga (Bill me add nahi hoga).
     - **Sold Order (Delivered)**: Pehle se deliver kiya gaya batch jiska Order #, Date, Rate (18 default) aur Total Bill auto-calculate ho kar Khata ledger me add ho jayega aur Client Portal par CSV download ke sath show hoga.

3. **Client Portal Maintenance Mode & Admin Live Testing:**
   - Admin Panel me **"Client: LIVE"** button se Maintenance Mode ON/OFF karein.
   - **"Test Client View"** button se aap client ban kar bina logout kiye live testing kar sakte hain.

4. **Payments (Wasooli) Tracker & Replacements Auto-Deduction:**
   - Payment wasool hone par entry karein, pending balance khud kam ho jayega.
   - Faulty mails enter karein, bill se foran deduction ho jayegi.

