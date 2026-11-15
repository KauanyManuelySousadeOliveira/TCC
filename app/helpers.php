<?php
/** Funções utilitárias globais (View helpers, CSRF, flash, validações). */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $p = ''): string { return '/' . ltrim($p, '/'); }
function redirect(string $p): never { header('Location: ' . url($p)); exit; }
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(419); exit('Sessão expirada ou requisição inválida. Volte e tente novamente.');
    }
}
function post(string $k, string $d = ''): string { return trim((string)($_POST[$k] ?? $d)); }
function get(string $k, string $d = ''): string { return trim((string)($_GET[$k] ?? $d)); }
function fmt_date(string $iso): string { [$y, $m, $d] = explode('-', substr($iso, 0, 10)); return "$d/$m/$y"; }
function weekday_short(string $iso): string { return ['dom','seg','ter','qua','qui','sex','sáb'][(int)date('w', strtotime($iso . ' 12:00:00'))]; }
function initials(string $name): string {
    $n = preg_replace('/^(Dr\.|Dra\.)\s*/u', '', $name);
    $w = array_slice(array_filter(explode(' ', $n)), 0, 2);
    return mb_strtoupper(implode('', array_map(fn($x) => mb_substr($x, 0, 1), $w)));
}
const STATUS_LABEL = ['agendada'=>'Agendada','confirmada'=>'Confirmada','cancelada'=>'Cancelada','realizada'=>'Realizada','faltou'=>'Não compareceu'];
function status_badge(string $s): string { return '<span class="badge badge-' . e($s) . '">' . e(STATUS_LABEL[$s] ?? $s) . '</span>'; }
function only_digits(string $s): string { return preg_replace('/\D/', '', $s); }
function valid_cpf(string $cpf): bool {
    $c = only_digits($cpf);
    if (strlen($c) !== 11 || preg_match('/^(\d)\1{10}$/', $c)) return false;
    for ($t = 9; $t < 11; $t++) {
        $s = 0; for ($i = 0; $i < $t; $i++) $s += (int)$c[$i] * ($t + 1 - $i);
        if ((int)$c[$t] !== ((10 * $s) % 11) % 10) return false;
    }
    return true;
}
function fmt_cpf(string $c): string { $d = only_digits($c); return strlen($d) === 11 ? substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9) : $c; }
function time_slots(): array {
    $o = []; for ($h = 8; $h < 18; $h++) { if ($h === 12) continue; $o[] = sprintf('%02d:00', $h); $o[] = sprintf('%02d:30', $h); } return $o;
}
/** Próximos dias úteis a partir de hoje. */
function next_days(int $n): array {
    $o = []; $d = new DateTime('today');
    while (count($o) < $n) { if ((int)$d->format('N') < 6) $o[] = $d->format('Y-m-d'); $d->modify('+1 day'); }
    return $o;
}
function render(string $view, array $data = []): void {
    extract($data);
    ob_start(); require __DIR__ . '/../views/' . $view . '.php'; $content = ob_get_clean();
    $layout = $data['layout'] ?? 'layout';
    require __DIR__ . '/../views/' . $layout . '.php';
}
