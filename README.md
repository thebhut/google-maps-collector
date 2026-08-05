# Google Maps Business Data Collection System

A complete, production-ready business lead collection system built with **Laravel 12** and a **Manifest V3 Chrome Extension**.

---

## 🌟 Key Features

- 🌐 **Chrome Extension (Manifest V3)**: Auto-scrolls Google Maps search results, auto-clicks business cards one by one, and extracts deep business profile details (name, full street address, phone, website, rating, reviews, coordinates, place ID).
- 🟢 **Live Visual Feedback**: Outlines business cards with a **White Border** (`🔍 Scanning...`) during inspection, turning **Bright Green** (`✓ Lead Found`) once saved.
- ⚡ **Direct Lead Ingestion**: Streams collected leads directly into Laravel backend API without complex API token setup.
- 🏢 **Smart Address & Location Parser**: Automatically extracts City, State, Postal Code, and Country from full street addresses.
- 📊 **Laravel Admin Dashboard**: Clean Tailwind CSS dark mode dashboard to search, filter, view, bulk-delete, and export business leads to CSV.
- 🛡️ **Duplicate Detection Engine**: Prevents duplicate records in MySQL while updating existing business records with updated info.

---

## 🏗️ Tech Stack

- **Backend**: Laravel 12, PHP 8.2+, MySQL
- **Frontend / Dashboard**: Blade, Tailwind CSS, Alpine.js
- **Browser Extension**: Vanilla JavaScript (Manifest V3), Custom DOM Parser

---

## 🚀 Quick Setup & Installation

### 1. Backend Setup (Laravel)

```bash
# Clone the repository
git clone https://github.com/thebhut/google-maps-collector.git
cd google-maps-collector

# Install PHP dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Configure MySQL database in .env
DB_DATABASE=google_maps_collector
DB_USERNAME=root
DB_PASSWORD=

# Run migrations & seeders
php artisan migrate --seed

# Start local server
php artisan serve
```

Access the Admin Dashboard at `http://127.0.0.1:8000/admin/dashboard`.
Default admin credentials (from seeder):
- **Email**: `admin@example.com`
- **Password**: `password`

---

### 2. Chrome Extension Setup

1. Open Google Chrome and navigate to `chrome://extensions`.
2. Enable **Developer mode** in the top right.
3. Click **Load unpacked** and select the `chrome-extension/` directory.
4. Open Google Maps (`https://www.google.com/maps`).
5. Perform a search (e.g. `seasoning masala company in bhavnagar`).
6. Click the Extension icon and click **[ Start Collection ]**.

---

## 📊 CSV Export & Bulk Operations

- **Export All**: Export all stored business leads to a streamed UTF-8 CSV file.
- **Filtered Export**: Export filtered business results based on search keywords, category, city, or minimum rating.
- **Bulk Delete**: Select multiple records using checkboxes for bulk removal.

---

## 📜 License

The MIT License (MIT).
