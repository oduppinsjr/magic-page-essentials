# Magic Page Tools & Enhancements

A custom utility plugin for WordPress designed to extend, optimize, and enhance functionality for websites using the [Magic Page Plugin](https://magicpageplugin.com/). This plugin delivers essential features missing from the core Magic Page implementation, including advanced page management tools, custom admin filters, and a real-time analytics dashboard.

---

## 🎛️ Features

- **Custom Admin Filters**  
  Adds additional filter options to the **Pages → All Pages** screen:
  - Filter by **Magic Page Group** (using `_group_id` post meta).
  - (Planned) Filter by **Magic Page Location** (using `_location_id` post meta).

- **Magic Page Analytics Dashboard**  
  Adds a powerful analytics dashboard under the **Magic Pages** menu featuring:
  - Six dynamic Chart.js charts:
    - Page visits over time
    - Visits by group
    - Visits by location
    - Top 10 most visited Magic Pages
    - 404 errors by URL
    - Device type breakdown
  - Live counters for:
    - Total Magic Pages
    - Total Groups
    - Total Page Visits
    - Average Visits per Page
    - Total Redirects Detected
  - Top 10 Magic Pages table by visit count

- **Frontend Visit Tracking**  
  Lightweight JavaScript injected on Magic Pages records page views via AJAX and stores visit data in post meta.

- **Extendable Plugin Architecture**  
  Structured for easy expansion to add new admin tools, metrics, filters, or workflow improvements.

---

## 📂 Project Structure

/magicpage-essentials/
├── assets/
│ ├── css/
│ │ └── magicpage-analytics.css
│ └── js/
│ └── magicpage-analytics.js
├── includes/
│ └── class-magicpage-analytics-dashboard.php
├── magicpage-frontend-analytics.js
├── magicpage-tools.php
└── readme.md


---

## 🚀 Installation

1. Upload the plugin files to `/wp-content/plugins/magicpage-essentials/`.
2. Activate via the **Plugins** menu in WordPress.
3. New functionality appears under **Magic Pages → Analytics** and **Pages → All Pages**.

---

## 📈 Usage

- Use the **Magic Page Group** filter in **Pages → All Pages** to quickly locate pages by group.
- View real-time visit data and performance trends under **Magic Pages → Analytics**.
- Visit data is captured automatically via a frontend JavaScript tracker.
- (Planned) Additional filters for location, visit logs, and per-page modals.

---

## ⚙️ Roadmap

- Extend admin filters to cover Magic Page Locations.
- Add per-page analytics modals from the Magic Pages list table.
- Log 404 errors and device types via frontend tracking.
- Export analytics data (CSV / JSON).
- Customizable date ranges and timeframes.
- Additional Magic Page management utilities.

---

## 📄 License

Proprietary utility plugin developed as a companion toolkit for Magic Page Plugin sites.  
Redistribution without permission is prohibited.

**Author:** [Odell Duppins Jr](https://github.com/oduppinsjr)  
**Company:** Duppins Technology

---

## 📞 Support

For feedback, improvements, or technical questions, submit an issue on this repository or contact via [Duppins Technology](https://duppinstech.com).
