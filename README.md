# Laravel Log Viewer

A lightweight and efficient **Laravel-based Log Viewer** that allows you to view and analyze both **local and remote log files** directly from your browser. It supports large log files, clean pagination, and structured error visualization for easier debugging.

---

## 🚀 Features

- 📁 View local and remote log files (via URL)
- ⚡ Efficient log parsing with pagination
- 🧠 Smart session handling to prevent unnecessary re-fetching
- 🔍 Detailed error and stack trace visualization
- 🗂️ Manage multiple projects/log sources
- 🔄 Quick refresh and project management interface
---

## ⚙️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/laravel-log-viewer.git
   cd laravel-log-viewer

---

***Environment setup***
```
cp .env.example .env
```

```
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite
```

```
touch database/database.sqlite
```

***Run Migrations***
````
php artisan migrate
````

***Serve Application***
```
php artisan serve
```
