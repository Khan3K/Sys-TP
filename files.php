<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$BASE = realpath('/opt/lampp/htdocs/sys/files') ?: '/opt/lampp/htdocs/sys/files';
if (!is_dir($BASE)) mkdir($BASE, 0777, true);

function safePath($base, $path) {
    $path = '/' . trim($path, '/');
    $real = realpath($base . $path);
    if ($real === false) return false;
    if (strpos($real, $base) !== 0) return false;
    return $real;
}

function fileInfo($path) {
    $name = basename($path);
    $isDir = is_dir($path);
    $size = $isDir ? '-' : formatSize(filesize($path));
    $date = date('Y-m-d H:i:s', filemtime($path));
    $ext  = $isDir ? 'folder' : strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $icon = getIcon($ext, $isDir);
    return compact('name', 'size', 'date', 'ext', 'icon', 'isDir');
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function getIcon($ext, $isDir) {
    if ($isDir) return "\xF0\x9F\x93\x81";
    $icons = [
        'jpg' => "\xF0\x9F\x96\xB8", 'jpeg' => "\xF0\x9F\x96\xB8", 'png' => "\xF0\x9F\x96\xB8", 'gif' => "\xF0\x9F\x96\xB8", 'svg' => "\xF0\x9F\x96\xB8", 'webp' => "\xF0\x9F\x96\xB8",
        'mp4' => "\xF0\x9F\x8E\xAC", 'mkv' => "\xF0\x9F\x8E\xAC", 'avi' => "\xF0\x9F\x8E\xAC",
        'mp3' => "\xF0\x9F\x8E\xB5", 'wav' => "\xF0\x9F\x8E\xB5", 'flac' => "\xF0\x9F\x8E\xB5",
        'zip' => "\xF0\x9F\x93\xA6", 'tar' => "\xF0\x9F\x93\xA6", 'gz' => "\xF0\x9F\x93\xA6", 'rar' => "\xF0\x9F\x93\xA6", '7z' => "\xF0\x9F\x93\xA6",
        'pdf' => "\xF0\x9F\x93\x95",
        'doc' => "\xF0\x9F\x93\x96", 'docx' => "\xF0\x9F\x93\x96",
        'xls' => "\xF0\x9F\x93\x9A", 'xlsx' => "\xF0\x9F\x93\x9A",
        'php' => "\xF0\x9F\x96\xA7", 'js' => "\xF0\x9F\x96\xA7", 'html' => "\xF0\x9F\x96\xA7", 'css' => "\xF0\x9F\x96\xA7", 'py' => "\xF0\x9F\x96\xA7", 'java' => "\xF0\x9F\x96\xA7", 'c' => "\xF0\x9F\x96\xA7", 'cpp' => "\xF0\x9F\x96\xA7", 'rb' => "\xF0\x9F\x96\xA7",
        'txt' => "\xF0\x9F\x93\x9C", 'md' => "\xF0\x9F\x93\x9C", 'log' => "\xF0\x9F\x93\x9C", 'csv' => "\xF0\x9F\x93\x9C",
    ];
    return $icons[$ext] ?? "\xF0\x9F\x93\x84";
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'list':
        $dir = $_GET['path'] ?? '/';
        $real = safePath($BASE, $dir);
        if (!$real) { http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit; }

        $items = [];
        $entries = @scandir($real);
        if ($entries) {
            foreach ($entries as $e) {
                if ($e === '.' || $e === '..' || $e[0] === '.') continue;
                $fp = $real . '/' . $e;
                $items[] = fileInfo($fp);
            }
        }
        usort($items, function($a, $b) {
            if ($a['isDir'] && !$b['isDir']) return -1;
            if (!$a['isDir'] && $b['isDir']) return 1;
            return strcasecmp($a['name'], $b['name']);
        });

        echo json_encode(['path' => $dir, 'items' => $items]);
        break;

    case 'upload':
        $dir = $_POST['path'] ?? '/';
        $real = safePath($BASE, $dir);
        if (!$real) { http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit; }

        $uploaded = 0;
        if (!empty($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $i => $name) {
                if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                    $dest = $real . '/' . basename($name);
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $dest)) $uploaded++;
                }
            }
        }
        echo json_encode(['uploaded' => $uploaded]);
        break;

    case 'download':
        $file = $_GET['file'] ?? '';
        $real = safePath($BASE, $file);
        if (!$real || !is_file($real)) { http_response_code(404); echo json_encode(['error' => 'File not found']); exit; }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($real) . '"');
        header('Content-Length: ' . filesize($real));
        readfile($real);
        exit;

    case 'zip':
        $files = $_POST['files'] ?? [];
        $dir   = $_POST['path'] ?? '/';
        $realDir = safePath($BASE, $dir);

        if (!$realDir) { http_response_code(400); echo json_encode(['error' => 'Invalid path']); exit; }
        if (empty($files)) { http_response_code(400); echo json_encode(['error' => 'No files selected']); exit; }

        $zip = new ZipArchive();
        $zipName = tempnam(sys_get_temp_dir(), 'zip_') . '.zip';
        if ($zip->open($zipName, ZipArchive::CREATE) !== true) {
            echo json_encode(['error' => 'Could not create zip']); exit;
        }

        foreach ($files as $f) {
            $real = safePath($BASE, $f);
            if (!$real) continue;
            if (is_file($real)) {
                $zip->addFile($real, ltrim($f, '/'));
            } elseif (is_dir($real)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($real, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($iterator as $file) {
                    $relPath = ltrim(substr($file->getPathname(), strlen($BASE)), '/');
                    $zip->addFile($file->getPathname(), $relPath);
                }
            }
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="download.zip"');
        header('Content-Length: ' . filesize($zipName));
        readfile($zipName);
        unlink($zipName);
        exit;

    case 'delete':
        $files = $_POST['files'] ?? [];
        $deleted = 0;
        foreach ($files as $f) {
            $real = safePath($BASE, $f);
            if (!$real) continue;
            if (is_dir($real)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($real, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($iterator as $item) {
                    $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
                }
                if (rmdir($real)) $deleted++;
            } elseif (is_file($real)) {
                if (unlink($real)) $deleted++;
            }
        }
        echo json_encode(['deleted' => $deleted]);
        break;

    case 'mkdir':
        $name = $_POST['name'] ?? '';
        $path = $_POST['path'] ?? '/';
        $real = safePath($BASE, $path);
        if (!$real || empty($name)) {
            echo json_encode(['error' => 'Invalid name or path']); exit;
        }
        $dirPath = $real . '/' . basename($name);
        if (is_dir($dirPath)) {
            echo json_encode(['error' => 'Folder already exists']); exit;
        }
        mkdir($dirPath, 0777, true);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
