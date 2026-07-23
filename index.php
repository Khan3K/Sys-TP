<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<title>System Dashboard</title>
<link rel="stylesheet" href="style.css?v=5">
</head>
<body>

<header>
    <div class="hdr-left">
        <h1>&#9881; System Dashboard</h1>
        <span id="hostname" class="badge"></span>
    </div>
    <div class="hdr-right">
        <span id="uptime" class="uptime-badge"></span>
        <span id="clock" class="clock"></span>
    </div>
</header>

<div class="stats-grid">

    <div class="stat-card">
        <div class="ring-wrap">
            <svg class="ring" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="34" class="ring-bg"/>
                <circle cx="40" cy="40" r="34" class="ring-fg cpu-c" id="cpu-ring"/>
            </svg>
            <span class="ring-pct" id="cpu-pct">0%</span>
        </div>
        <div class="stat-body">
            <h3>CPU</h3>
            <div class="stat-row"><span class="sl">Cores</span><span class="sv" id="cpu-cores">-</span></div>
            <div class="stat-row"><span class="sl">Freq</span><span class="sv" id="cpu-freq">-</span></div>
            <div class="stat-row"><span class="sl">Load</span><span class="sv" id="cpu-load">-</span></div>
            <div class="stat-row"><span class="sl">Procs</span><span class="sv" id="cpu-procs">-</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="ring-wrap">
            <svg class="ring" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="34" class="ring-bg"/>
                <circle cx="40" cy="40" r="34" class="ring-fg ram-c" id="ram-ring"/>
            </svg>
            <span class="ring-pct" id="ram-pct">0%</span>
        </div>
        <div class="stat-body">
            <h3>RAM</h3>
            <div class="stat-row"><span class="sl">Used</span><span class="sv" id="ram-used">-</span></div>
            <div class="stat-row"><span class="sl">Total</span><span class="sv" id="ram-total">-</span></div>
            <div class="stat-row"><span class="sl">Free</span><span class="sv" id="ram-free">-</span></div>
            <div class="stat-row"><span class="sl">Swap</span><span class="sv" id="ram-swap">-</span></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="ring-wrap">
            <svg class="ring" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="34" class="ring-bg"/>
                <circle cx="40" cy="40" r="34" class="ring-fg temp-c" id="temp-ring"/>
            </svg>
            <span class="ring-pct" id="temp-pct">0&deg;</span>
        </div>
        <div class="stat-body">
            <h3>Temperature</h3>
            <div id="temp-sensors" class="sensor-list">-</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="ring-wrap">
            <svg class="ring" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="34" class="ring-bg"/>
                <circle cx="40" cy="40" r="34" class="ring-fg bat-c" id="bat-ring"/>
            </svg>
            <span class="ring-pct" id="bat-pct">N/A</span>
            <span class="ring-sub" id="bat-charge"></span>
        </div>
        <div class="stat-body">
            <h3>Battery</h3>
            <div class="stat-row"><span class="sl">Status</span><span class="sv" id="bat-status">-</span></div>
            <div class="stat-row"><span class="sl">Time</span><span class="sv" id="bat-time">-</span></div>
            <div class="stat-row"><span class="sl">Voltage</span><span class="sv" id="bat-volt">-</span></div>
        </div>
    </div>

    <div class="stat-card card-disk">
        <div class="disk-bar"><div class="disk-fill" id="disk-fill"></div></div>
        <div class="disk-row">
            <h3>Disk</h3>
            <span class="disk-pct" id="disk-pct">0%</span>
            <span class="disk-det" id="disk-detail">-</span>
        </div>
    </div>

</div>

<div class="fm">
    <div class="fm-nav">
        <button id="btn-home">&#8962;</button>
        <button id="btn-up">&#9650; Up</button>
        <input type="text" id="path-input" value="/" placeholder="Type path and press Enter..." spellcheck="false">
        <button id="btn-go" class="go-btn">&#10140; Go</button>
        <button id="btn-refresh">&#8635;</button>
    </div>

    <div class="fm-bar">
        <label class="upload-btn">
            &#10010; Upload
            <input type="file" id="file-input" multiple hidden>
        </label>
        <button id="btn-mkdir" class="tb-hide">&#128193; Folder</button>
        <button id="btn-selall" class="tb-hide">Select All</button>
        <button id="btn-zip" class="go-btn" disabled>&#128230; ZIP</button>
        <button id="btn-del" class="del-btn" disabled>&#128465;</button>
    </div>

    <div class="drop-zone" id="drop-zone">&#128229; Drag files here to upload</div>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th class="cc"><input type="checkbox" id="check-all"></th>
                    <th class="ci"></th>
                    <th class="cn">Name</th>
                    <th class="cs">Size</th>
                    <th class="cd">Modified</th>
                    <th class="ca">Actions</th>
                </tr>
            </thead>
            <tbody id="file-tbody"></tbody>
        </table>
    </div>

    <div id="up-bar" class="up-bar" hidden>
        <div class="up-track">
            <div class="up-fill-inner" id="up-fill"></div>
        </div>
        <span id="up-text">Uploading...</span>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="app.js?v=5"></script>
</body>
</html>
