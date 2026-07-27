# System Dashboard

A lightweight PHP-based system monitoring dashboard with a full-featured file manager. Runs on XAMPP/LAMP stack with no external dependencies.

## Features

### System Monitoring
- **CPU**: Usage %, core count, frequency, load average, process count
- **RAM**: Used / total / free, swap usage
- **Temperature**: Thermal sensor readings
- **Battery**: Charge %, status, time remaining, voltage
- **Disk**: Usage bar with used / total / free

### File Manager (FTP-like)
- Browse directories with path navigation
- Upload files (multi-file, drag & drop)
- Download files individually
- Download multiple files/folders as ZIP
- Create folders
- Delete files and folders (recursive)
- Mobile-friendly responsive UI

## Tech Stack
- **Backend**: PHP 8.2
- **Server**: Apache 2.4 (XAMPP)
- **Frontend**: Vanilla JS, HTML5, CSS3 (no frameworks)
- **Data**: `/proc/*` filesystem on Linux

## Installation

1. Place project in web root:
   ```bash
   cp -r sys/ /opt/lampp/htdocs/
   ```

2. Create upload directory with write permissions:
   ```bash
   mkdir -p /opt/lampp/htdocs/sys/files
   chmod 777 /opt/lampp/htdocs/sys/files
   ```

3. Start Apache:
   ```bash
   /opt/lampp/lampp start
   ```

4. Open in browser:
   ```
   http://localhost/sys/
   ```

## File Structure

```
sys/
├── index.php      # Main HTML page
├── style.css      # Styles + responsive breakpoints
├── app.js         # Frontend logic
├── stats.php      # System stats API (JSON)
├── files.php      # File manager API (JSON)
└── files/         # Upload storage directory
```

## API Endpoints

### `stats.php`
Returns system stats as JSON. No parameters.

```json
{
  "cpu": { "usage": 7.1, "cores": 8, "freq": "3760 MHz", "load1": 0.28, ... },
  "ram": { "usage": 39.8, "total": 7.42, "used": 2.95, ... },
  "battery": { "percent": 85, "status": "Discharging", "charging": false, ... },
  "temp": { "sensors": [...], "max": 52 },
  "disk": { "usage": 45, "total": "50 GB", "used": "22 GB", ... },
  "uptime": "4h 9m",
  "time": "2026-07-23 05:31:04",
  "hostname": "omarchy"
}
```

### `files.php`

| Action   | Method | Params                          | Returns          |
|----------|--------|---------------------------------|------------------|
| `list`   | GET    | `path`                          | File listing     |
| `upload` | POST   | `path`, `files[]` (multipart)   | Upload count     |
| `download` | GET  | `file`                          | Binary file      |
| `zip`    | POST   | `path`, `files[]`               | ZIP archive      |
| `delete` | POST   | `files[]`                       | Delete count     |
| `mkdir`  | POST   | `path`, `name`                  | Success/error    |

## Mobile Access

The UI is responsive with breakpoints at 900px, 600px, and 400px:
- **> 900px**: 4-column stat grid, full toolbar
- **600-900px**: 2-column grid, compact buttons
- **400-600px**: 2-column grid, path input on its own row
- **< 400px**: Single-column stat grid, hidden date column

To access from a phone on the same network:
1. Find your computer's local IP (`ip addr` or `ifconfig`)
2. Make sure Apache listens on that interface
3. Open `http://<your-ip>/sys/` on the phone

## Security Notes

- No authentication — only run on trusted networks
- File manager root is hardcoded to `/opt/lampp/htdocs/sys/files/`
- Path traversal protection via `realpath()` in `files.php`
- All uploads use `basename()` to strip directory components
- No file type restrictions (add validation if exposing publicly)

## Configuration

PHP upload limits (in `php.ini`):
```ini
upload_max_filesize = 40M
post_max_size = 40M
```

## Browser Compatibility

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari 14+, Chrome Android)

## License

Personal use project and No license specified.
