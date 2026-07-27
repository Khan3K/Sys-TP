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
<div class="note-section">
  <div class="note-controls">
  <button id="add-note" class="go-btn">Add Note</button>
  <button id="export-notes" class="go-btn">Export</button>
  <button id="import-notes" class="go-btn">Import</button>
  <button id="clear-notes" class="go-btn">Clear All</button>
</div>
  <div id="notes-list"></div>
</div>
<script>
// Utility functions for notes
async function loadNotes() {
  try {
    const resp = await fetch('notes.php?action=load');
    if (!resp.ok) throw new Error('Network error');
    const data = await resp.json();
    return data;
  } catch (e) {
    toast('Failed to load notes', true);
    return [];
  }
}
async function saveNotes(notes) {
  try {
    const resp = await fetch('notes.php?action=save', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(notes)
    });
    if (!resp.ok) throw new Error('Network error');
    toast('Notes saved');
  } catch (e) {
    toast('Failed to save notes', true);
  }
}
function renderNotes() {
  const notes = loadNotes();
  const list = document.getElementById('notes-list');
  list.innerHTML = '';
  notes.forEach((note, idx) => {
    const noteDiv = document.createElement('div');
    noteDiv.className = 'note-item';
    // Text area
    const ta = document.createElement('textarea');
    ta.className = 'note-text';
    ta.placeholder = 'Enter your note...';
    ta.value = note.text || '';
    ta.addEventListener('input', () => {
      notes[idx].text = ta.value;
      saveNotes(notes);
    });
    noteDiv.appendChild(ta);
    // Images container
    const imgContainer = document.createElement('div');
    imgContainer.className = 'note-images';
    (note.images || []).forEach((src, imgIdx) => {
      const wrap = document.createElement('div');
      wrap.className = 'note-image-wrapper';
      const img = document.createElement('img');
      img.src = src;
      img.className = 'note-image';
      wrap.appendChild(img);
      const copyImg = document.createElement('button');
      copyImg.className = 'go-btn';
      copyImg.textContent = 'Copy Image';
      copyImg.addEventListener('click', () => {
        navigator.clipboard.writeText(src).then(() => toast('Image copied'), () => toast('Copy failed', true));
      });
      wrap.appendChild(copyImg);
      const delImg = document.createElement('button');
      delImg.className = 'go-btn';
      delImg.textContent = 'Delete Image';
      delImg.addEventListener('click', () => {
        notes[idx].images.splice(imgIdx, 1);
        saveNotes(notes);
        renderNotes();
      });
      wrap.appendChild(delImg);
      imgContainer.appendChild(wrap);
    });
    noteDiv.appendChild(imgContainer);
    // Image upload input
    const imgInput = document.createElement('input');
    imgInput.type = 'file';
    imgInput.accept = 'image/*';
    imgInput.multiple = true;
    imgInput.addEventListener('change', function () {
      const files = Array.from(this.files);
      files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
          notes[idx].images = notes[idx].images || [];
          notes[idx].images.push(e.target.result);
          saveNotes(notes);
          renderNotes();
        };
        reader.readAsDataURL(file);
      });
      this.value = '';
    });
    noteDiv.appendChild(imgInput);
    // Action buttons
    const btnRow = document.createElement('div');
    btnRow.className = 'note-buttons';
    const copyBtn = document.createElement('button');
    copyBtn.className = 'go-btn';
    copyBtn.textContent = 'Copy Text';
    copyBtn.addEventListener('click', () => {
      navigator.clipboard.writeText(ta.value).then(() => toast('Note copied'), () => toast('Copy failed', true));
    });
    btnRow.appendChild(copyBtn);
    const delBtn = document.createElement('button');
    delBtn.className = 'go-btn';
    delBtn.textContent = 'Delete Note';
    delBtn.addEventListener('click', () => {
      notes.splice(idx, 1);
      saveNotes(notes);
      renderNotes();
    });
    btnRow.appendChild(delBtn);
    noteDiv.appendChild(btnRow);
    list.appendChild(noteDiv);
  });
}
// Add note handler
document.getElementById('add-note').addEventListener('click', () => {
  const notes = loadNotes();
  notes.push({text:'', images:[]});
  saveNotes(notes);
  renderNotes();
});
// Export notes
function exportNotes() {
  const notes = loadNotes();
  const data = JSON.stringify(notes, null, 2);
  const blob = new Blob([data], {type: 'application/json'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'notes.json';
  a.click();
  URL.revokeObjectURL(url);
}
document.getElementById('export-notes').addEventListener('click', exportNotes);
// Import notes
function importNotes() {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'application/json';
  input.onchange = e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
      try {
        const data = JSON.parse(ev.target.result);
        if (Array.isArray(data)) {
          saveNotes(data);
          renderNotes();
          toast('Notes imported');
        } else {
          toast('Invalid file format', true);
        }
      } catch (err) {
        toast('Import failed', true);
      }
    };
    reader.readAsText(file);
  };
  input.click();
}
document.getElementById('import-notes').addEventListener('click', importNotes);
// Clear all notes
document.getElementById('clear-notes').addEventListener('click', () => {
  if (!confirm('Delete all notes?')) return;
  localStorage.removeItem('user_notes');
  renderNotes();
  toast('All notes cleared');
});
// Initial render
renderNotes();
</script>

<script src="app.js?v=5"></script>
</body>
</html>
