<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');

function get_cpu() {
    $cores = 0;
    $cpuinfo = @file_get_contents('/proc/cpuinfo');
    if ($cpuinfo) {
        preg_match_all('/^processor/m', $cpuinfo, $m);
        $cores = count($m[0]);
    }

    $usage = 0;

    $out = @shell_exec("top -bn2 -d0.15 2>/dev/null | grep '^%Cpu'");
    if ($out) {
        $last = '';
        foreach (explode("\n", $out) as $line) {
            if (strpos($line, '%Cpu') === 0) $last = $line;
        }
        if ($last && preg_match('/(\d+\.?\d*)\s*id/', $last, $m)) {
            $usage = round(100 - (float)$m[1], 1);
        }
    }

    if ($usage == 0) {
        $s1 = @file_get_contents('/proc/stat');
        if ($s1) {
            preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $s1, $a);
            usleep(250000);
            $s2 = @file_get_contents('/proc/stat');
            if ($s2 && preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $s2, $b)) {
                $dt = array_sum(array_slice($b,1)) - array_sum(array_sum($a,1));
                $di = $b[4] - $a[4];
                if ($dt > 0) $usage = round((1 - $di / $dt) * 100, 1);
            }
        }
    }

    $freq = 'N/A';
    if ($cpuinfo && preg_match('/cpu MHz\s*:\s*([\d.]+)/', $cpuinfo, $fm)) {
        $freq = round($fm[1]) . ' MHz';
    }

    $load = sys_getloadavg();
    $procs = '?';
    $pout = @shell_exec("ps -e --no-headers | wc -l");
    if ($pout) $procs = trim($pout);

    return [
        'usage' => $usage,
        'cores' => max($cores, 1),
        'freq'  => $freq,
        'load1'  => round($load[0], 2),
        'load5'  => round($load[1], 2),
        'load15' => round($load[2], 2),
        'procs'  => $procs,
    ];
}

function get_ram() {
    $info = @file_get_contents('/proc/meminfo');
    $get = function($k) use ($info) {
        preg_match("/$k:\s+(\d+)/", $info, $m);
        return (int)($m[1] ?? 0);
    };
    $total = $get('MemTotal');
    $avail = $get('MemAvailable') ?: ($get('MemFree') + $get('Buffers') + $get('Cached'));
    $used = $total - $avail;
    $sTotal = $get('SwapTotal');
    $sFree  = $get('SwapFree');
    return [
        'usage'    => $total > 0 ? round($used / $total * 100, 1) : 0,
        'total'    => round($total / 1048576, 2),
        'used'     => round($used / 1048576, 2),
        'free'     => round($avail / 1048576, 2),
        'swap_total' => round($sTotal / 1048576, 2),
        'swap_used'  => round(($sTotal - $sFree) / 1048576, 2),
    ];
}

function get_battery() {
    $batPath = '/sys/class/power_supply/BAT0';
    if (!is_dir($batPath)) {
        $batPath = '/sys/class/power_supply/BAT1';
    }
    if (!is_dir($batPath)) {
        $acPath = '/sys/class/power_supply/AC';
        if (is_dir($acPath)) {
            $online = @file_get_contents($acPath . '/online');
            $status = trim($online ?? '0') === '1' ? 'AC Charging' : 'No Battery';
            return ['percent' => -1, 'status' => $status, 'charging' => false, 'time_left' => 'N/A'];
        }
        return ['percent' => -1, 'status' => 'No Battery', 'charging' => false, 'time_left' => 'N/A'];
    }

    $cap = (int) @file_get_contents($batPath . '/capacity');
    $status = trim(@file_get_contents($batPath . '/status') ?? 'Unknown');
    $charging = ($status === 'Charging');

    $voltage = (int) (@file_get_contents($batPath . '/voltage_now') ?? 0);
    $current = abs((int) (@file_get_contents($batPath . '/current_now') ?? 0));
    $energy_now = (int) (@file_get_contents($batPath . '/energy_now') ?? 0);
    $energy_full = (int) (@file_get_contents($batPath . '/energy_full') ?? 0);
    $charge_now = (int) (@file_get_contents($batPath . '/charge_now') ?? 0);
    $charge_full = (int) (@file_get_contents($batPath . '/charge_full') ?? 0);

    $timeLeft = 'N/A';
    if ($charging && $current > 0) {
        if ($energy_now > 0 && $energy_full > 0) {
            $remaining = $energy_full - $energy_now;
            $mins = round($remaining / $current * 60);
            $timeLeft = floor($mins / 60) . 'h ' . ($mins % 60) . 'm to full';
        } elseif ($charge_now > 0 && $charge_full > 0) {
            $remaining = $charge_full - $charge_now;
            $mins = round($remaining / $current * 60);
            $timeLeft = floor($mins / 60) . 'h ' . ($mins % 60) . 'm to full';
        }
    } elseif (!$charging && $current > 0 && $energy_now > 0) {
        $mins = round($energy_now / $current * 60);
        $timeLeft = floor($mins / 60) . 'h ' . ($mins % 60) . 'm left';
    }

    return [
        'percent'   => $cap,
        'status'    => $status,
        'charging'  => $charging,
        'time_left' => $timeLeft,
        'voltage'   => $voltage > 0 ? round($voltage / 1000000, 2) . ' V' : 'N/A',
    ];
}

function get_temp() {
    $temps = [];
    $zones = @glob('/sys/class/thermal/thermal_zone*');
    if ($zones) {
        foreach ($zones as $z) {
            $f = $z . '/temp';
            if (file_exists($f)) {
                $raw = (int) file_get_contents($f);
                $tf = $z . '/type';
                $type = file_exists($tf) ? trim(file_get_contents($tf)) : 'sensor';
                $temps[] = ['name' => $type, 'temp' => round($raw / 1000, 1)];
            }
        }
    }
    if (empty($temps)) {
        $inputs = @glob('/sys/class/hwmon/hwmon*/temp*_input');
        if ($inputs) {
            foreach ($inputs as $inp) {
                $raw = (int) @file_get_contents($inp);
                if ($raw > 0) {
                    $hm = dirname($inp);
                    $nm = @file_get_contents($hm . '/name');
                    $lf = str_replace('_input', '_label', $inp);
                    $lb = @file_get_contents($lf);
                    $temps[] = ['name' => trim($nm ?: 'hwmon') . ':' . trim($lb ?: ''), 'temp' => round($raw / 1000, 1)];
                }
            }
        }
    }
    $max = 0;
    foreach ($temps as $t) { if ($t['temp'] > $max) $max = $t['temp']; }
    return ['sensors' => $temps, 'max' => $max];
}

function get_disk() {
    $out = @shell_exec('df -B1 / 2>/dev/null');
    $lines = explode("\n", trim($out));
    if (count($lines) > 1) {
        $p = preg_split('/\s+/', $lines[1]);
        $total = (int)($p[1] ?? 0);
        $used  = (int)($p[2] ?? 0);
        return [
            'usage' => $total > 0 ? round($used / $total * 100, 1) : 0,
            'total' => formatBytes($total),
            'used'  => formatBytes($used),
            'free'  => formatBytes((int)($p[3] ?? 0)),
            'mount' => $p[5] ?? '/',
        ];
    }
    return ['usage' => 0, 'total' => '?', 'used' => '?', 'free' => '?', 'mount' => '/'];
}

function formatBytes($b) {
    if ($b >= 1073741824) return round($b / 1073741824, 1) . ' GB';
    if ($b >= 1048576) return round($b / 1048576, 1) . ' MB';
    if ($b >= 1024) return round($b / 1024, 1) . ' KB';
    return $b . ' B';
}

function get_uptime() {
    $lines = @file('/proc/uptime');
    $s = (float) ($lines[0] ?? 0);
    $d = floor($s / 86400);
    $h = floor(($s % 86400) / 3600);
    $m = floor(($s % 3600) / 60);
    $p = [];
    if ($d > 0) $p[] = $d . 'd';
    if ($h > 0) $p[] = $h . 'h';
    $p[] = $m . 'm';
    return implode(' ', $p);
}

echo json_encode([
    'cpu'      => get_cpu(),
    'ram'      => get_ram(),
    'battery'  => get_battery(),
    'temp'     => get_temp(),
    'disk'     => get_disk(),
    'uptime'   => get_uptime(),
    'time'     => date('Y-m-d H:i:s'),
    'hostname' => gethostname() ?: '?',
]);
