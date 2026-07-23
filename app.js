var currentPath = '/';

function $(id) { return document.getElementById(id); }
function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function toast(msg, err) {
    var t = $('toast'); t.textContent = msg;
    t.className = 'toast show' + (err ? ' error' : '');
    clearTimeout(t._t); t._t = setTimeout(function() { t.className = 'toast'; }, 3000);
}

/* ===== RING CHART HELPER ===== */
var CIRC = 2 * Math.PI * 34;
function setRing(id, pct, color) {
    var el = $(id); if (!el) return;
    var clamped = Math.max(0, Math.min(100, pct));
    el.style.strokeDashoffset = CIRC * (1 - clamped / 100);
    if (color) el.style.stroke = color;
}

/* ===== SYSTEM STATS ===== */
function loadStats() {
    fetch('stats.php').then(function(r) { return r.json(); }).then(function(d) {
        $('hostname').textContent = d.hostname;
        $('uptime').textContent = 'Uptime: ' + d.uptime;
        $('clock').textContent = d.time;

        var cpu = d.cpu;
        setRing('cpu-ring', cpu.usage);
        $('cpu-pct').textContent = cpu.usage + '%';
        $('cpu-cores').textContent = cpu.cores + ' cores';
        $('cpu-freq').textContent = cpu.freq;
        $('cpu-load').textContent = cpu.load1 + ' / ' + cpu.load5 + ' / ' + cpu.load15;
        $('cpu-procs').textContent = cpu.procs;

        var ram = d.ram;
        setRing('ram-ring', ram.usage);
        $('ram-pct').textContent = ram.usage + '%';
        $('ram-used').textContent = ram.used + ' GB';
        $('ram-total').textContent = ram.total + ' GB';
        $('ram-free').textContent = ram.free + ' GB';
        $('ram-swap').textContent = ram.swap_used + ' / ' + ram.swap_total + ' GB';

        var temp = d.temp;
        var tempPct = Math.min(temp.max, 100);
        var tempColor = temp.max > 80 ? '#e74c3c' : temp.max > 60 ? '#e17055' : '#00b894';
        setRing('temp-ring', tempPct, tempColor);
        $('temp-pct').textContent = temp.max + '\u00B0C';
        var sensorHtml = '';
        if (temp.sensors.length) {
            temp.sensors.forEach(function(s) {
                sensorHtml += '<div>' + esc(s.name) + ': ' + s.temp + '\u00B0C</div>';
            });
        } else {
            sensorHtml = 'No sensors';
        }
        $('temp-sensors').innerHTML = sensorHtml;

        var bat = d.battery;
        if (bat.percent < 0) {
            $('bat-ring').style.strokeDashoffset = CIRC;
            $('bat-pct').textContent = 'N/A';
            $('bat-charge').textContent = '';
        } else {
            setRing('bat-ring', bat.percent, bat.charging ? '#00b894' : '#6c5ce7');
            $('bat-pct').textContent = bat.percent + '%';
            $('bat-charge').textContent = bat.charging ? '\u26A1' : '';
        }
        $('bat-status').textContent = bat.status;
        $('bat-time').textContent = bat.time_left;
        $('bat-volt').textContent = bat.voltage;

        var disk = d.disk;
        $('disk-fill').style.width = disk.usage + '%';
        $('disk-pct').textContent = disk.usage + '%';
        $('disk-detail').textContent = disk.used + ' / ' + disk.total + ' (' + disk.free + ' free) ' + disk.mount;

    }).catch(function(e) { console.error('Stats:', e); });
}

/* ===== FILE MANAGER ===== */
function loadFiles(path) {
    if (path !== undefined) currentPath = path;
    $('path-input').value = currentPath;

    fetch('files.php?action=list&path=' + encodeURIComponent(currentPath))
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.error) { toast(d.error, true); return; }
        var tbody = $('file-tbody');
        tbody.innerHTML = '';

        if (currentPath !== '/') {
            var parent = currentPath.replace(/\/[^/]+\/?$/, '') || '/';
            var tr = document.createElement('tr');
            tr.innerHTML = '<td></td><td class="fico">\u2B06\uFE0F</td>' +
                '<td><span class="fname isdir" data-path="' + esc(parent) + '">.. parent</span></td>' +
                '<td>-</td><td>-</td><td></td>';
            tbody.appendChild(tr);
        }

        if (d.items.length === 0) {
            var tr2 = document.createElement('tr');
            tr2.innerHTML = '<td colspan="6" style="text-align:center;color:var(--dim);padding:30px">Empty folder</td>';
            tbody.appendChild(tr2);
        }

        d.items.forEach(function(item) {
            var fp = currentPath === '/' ? '/' + item.name : currentPath + '/' + item.name;
            var tr = document.createElement('tr');
            var dlBtn = !item.isDir ? '<a class="abtn" href="files.php?action=download&file=' + encodeURIComponent(fp) + '" title="Download">\u2B07 DL</a>' : '';
            tr.innerHTML =
                '<td><input type="checkbox" class="fchk" data-path="' + esc(fp) + '"></td>' +
                '<td class="fico">' + item.icon + '</td>' +
                '<td><span class="fname' + (item.isDir ? ' isdir' : '') + '" data-path="' + esc(fp) + '" data-type="' + (item.isDir ? 'dir' : 'file') + '">' + esc(item.name) + '</span></td>' +
                '<td>' + item.size + '</td>' +
                '<td>' + item.date + '</td>' +
                '<td>' + dlBtn + '<button class="abtn d" data-del="' + esc(fp) + '">\u2716</button></td>';
            tbody.appendChild(tr);
        });

        updateBtns();
    }).catch(function(e) { console.error('Files:', e); toast('Failed to load files', true); });
}

function updateBtns() {
    var checks = document.querySelectorAll('.fchk:checked');
    var n = checks.length;
    var all = document.querySelectorAll('.fchk');
    $('btn-zip').disabled = n === 0;
    $('btn-del').disabled = n === 0;
    $('check-all').checked = all.length > 0 && n === all.length;
    $('check-all').indeterminate = n > 0 && n < all.length;
}

function navigateTo(val) {
    val = (val || '/').trim();
    if (!val.startsWith('/')) val = '/' + val;
    val = val.replace(/\/+/g, '/');
    loadFiles(val);
}

function getSelectedPaths() {
    var paths = [];
    document.querySelectorAll('.fchk:checked').forEach(function(cb) { paths.push(cb.dataset.path); });
    return paths;
}

/* ===== EVENT LISTENERS ===== */

/* Path nav */
$('btn-go').onclick = function() { navigateTo($('path-input').value); };
$('path-input').onkeydown = function(e) { if (e.key === 'Enter') navigateTo(this.value); };
$('btn-home').onclick = function() { loadFiles('/'); };
$('btn-up').onclick = function() {
    if (currentPath === '/') return;
    loadFiles(currentPath.replace(/\/[^/]+\/?$/, '') || '/');
};
$('btn-refresh').onclick = function() { loadFiles(); };

/* Check all */
$('check-all').onchange = function() {
    var v = this.checked;
    document.querySelectorAll('.fchk').forEach(function(cb) { cb.checked = v; });
    updateBtns();
};
document.addEventListener('change', function(e) {
    if (e.target.classList && e.target.classList.contains('fchk')) updateBtns();
});

/* File name clicks (delegated) */
$('file-tbody').onclick = function(e) {
    var el = e.target.closest('.fname');
    if (el) {
        e.preventDefault();
        if (el.dataset.type === 'dir') {
            loadFiles(el.dataset.path);
        } else {
            window.open('files.php?action=download&file=' + encodeURIComponent(el.dataset.path), '_blank');
        }
        return;
    }
    var del = e.target.closest('[data-del]');
    if (del) {
        deleteOne(del.dataset.del);
    }
};

/* Select All button */
$('btn-selall').onclick = function() {
    var checks = document.querySelectorAll('.fchk');
    var allChecked = document.querySelectorAll('.fchk:checked').length === checks.length;
    checks.forEach(function(cb) { cb.checked = !allChecked; });
    updateBtns();
};

/* Upload */
function doUpload(files) {
    if (!files || !files.length) return;
    var bar = $('up-bar'), fill = $('up-fill'), txt = $('up-text');
    bar.hidden = false;
    fill.style.width = '0%';
    txt.textContent = 'Uploading ' + files.length + ' file(s)...';

    var fd = new FormData();
    fd.append('action', 'upload');
    fd.append('path', currentPath);
    for (var i = 0; i < files.length; i++) fd.append('files[]', files[i]);

    var xhr = new XMLHttpRequest();
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) fill.style.width = Math.round(e.loaded / e.total * 100) + '%';
    };
    xhr.onload = function() {
        try {
            var d = JSON.parse(xhr.responseText);
            txt.textContent = d.uploaded + ' file(s) uploaded';
            fill.style.width = '100%';
            toast(d.uploaded + ' file(s) uploaded');
        } catch (err) {
            txt.textContent = 'Upload failed';
            toast('Upload failed', true);
        }
        setTimeout(function() { bar.hidden = true; loadFiles(); }, 1200);
    };
    xhr.onerror = function() {
        txt.textContent = 'Upload failed';
        toast('Network error', true);
        setTimeout(function() { bar.hidden = true; }, 1500);
    };
    xhr.open('POST', 'files.php');
    xhr.send(fd);
}

$('file-input').onchange = function() { doUpload(this.files); this.value = ''; };

/* Drag & Drop */
var dz = $('drop-zone');
document.body.addEventListener('dragover', function(e) { e.preventDefault(); dz.classList.add('over'); });
document.body.addEventListener('dragleave', function(e) { if (!e.relatedTarget || e.relatedTarget === document.documentElement) dz.classList.remove('over'); });
document.body.addEventListener('drop', function(e) {
    e.preventDefault(); dz.classList.remove('over');
    if (e.dataTransfer.files.length) doUpload(e.dataTransfer.files);
});

/* Download ZIP */
$('btn-zip').onclick = function() {
    var paths = getSelectedPaths();
    if (!paths.length) { toast('No files selected', true); return; }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'files.php?action=zip';
    form.style.display = 'none';

    var pi = document.createElement('input');
    pi.type = 'hidden'; pi.name = 'path'; pi.value = currentPath;
    form.appendChild(pi);

    paths.forEach(function(p) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'files[]'; inp.value = p;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    toast('Creating ZIP with ' + paths.length + ' item(s)...');
};

/* Delete */
function deleteOne(path) {
    if (!confirm('Delete "' + path.split('/').pop() + '"?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('files[]', path);
    fetch('files.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) { toast('Deleted ' + d.deleted + ' item(s)'); loadFiles(); })
    .catch(function() { toast('Delete failed', true); });
}

$('btn-del').onclick = function() {
    var paths = getSelectedPaths();
    if (!paths.length) return;
    if (!confirm('Delete ' + paths.length + ' item(s)?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    paths.forEach(function(p) { fd.append('files[]', p); });
    fetch('files.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) { toast('Deleted ' + d.deleted + ' item(s)'); loadFiles(); })
    .catch(function() { toast('Delete failed', true); });
};

/* New Folder */
$('btn-mkdir').onclick = function() {
    var name = prompt('Folder name:');
    if (!name || !name.trim()) return;
    var fd = new FormData();
    fd.append('action', 'mkdir');
    fd.append('path', currentPath);
    fd.append('name', name.trim());
    fetch('files.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.ok) { toast('Folder created'); loadFiles(); }
        else toast(d.error || 'Failed', true);
    }).catch(function() { toast('Failed', true); });
};

/* ===== INIT ===== */
loadStats();
loadFiles('/');
setInterval(loadStats, 2000);
