<?php
/** Autenticação, sessão com expiração por inatividade (RNF16) e controle de perfis. */
class Auth {
    const TIMEOUT = 1800; // 30 min
    public static function user(): ?array {
        if (empty($_SESSION['user'])) return null;
        if (time() - ($_SESSION['last'] ?? 0) > self::TIMEOUT) { self::logout(); session_start(); flash('Sessão expirada por inatividade. Entre novamente.', 'err'); return null; }
        $_SESSION['last'] = time();
        return $_SESSION['user'];
    }
    public static function attempt(string $email, string $senha): bool {
        if (($_SESSION['lock'] ?? 0) > time()) return false;
        $u = Database::one('SELECT * FROM usuarios WHERE email = ?', [mb_strtolower($email)]);
        if (!$u || !$u['ativo'] || !password_verify($senha, $u['senha_hash'])) {
            $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
            if ($_SESSION['fails'] >= 5) { $_SESSION['lock'] = time() + 300; $_SESSION['fails'] = 0; }
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['fails'] = 0;
        [$ref, $nome] = self::profile($u);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'perfil' => $u['perfil'], 'ref_id' => $ref, 'nome' => $nome];
        $_SESSION['last'] = time();
        Log::add((int)$u['id'], 'login');
        return true;
    }
    private static function profile(array $u): array {
        $t = ['paciente' => 'pacientes', 'medico' => 'medicos', 'recepcionista' => 'recepcionistas'][$u['perfil']];
        $r = Database::one("SELECT id, nome FROM $t WHERE usuario_id = ?", [$u['id']]);
        return [(int)($r['id'] ?? 0), $r['nome'] ?? $u['email']];
    }
    public static function logout(): void {
        if (!empty($_SESSION['user'])) Log::add($_SESSION['user']['id'], 'logout');
        $_SESSION = []; session_destroy();
    }
    public static function require(string ...$perfis): array {
        $u = self::user();
        if (!$u) redirect('/login');
        if ($perfis && !in_array($u['perfil'], $perfis, true)) { http_response_code(403); exit('Acesso negado para este perfil.'); }
        return $u;
    }
    public static function lockedSeconds(): int { return max(0, ($_SESSION['lock'] ?? 0) - time()); }
}
