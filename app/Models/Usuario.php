<?php
class Usuario {
    public static function criar(string $email, string $senha, string $perfil): int {
        return Database::insert('INSERT INTO usuarios (email, senha_hash, perfil, ativo, criado_em) VALUES (?,?,?,1,?)',
            [mb_strtolower($email), password_hash($senha, PASSWORD_DEFAULT), $perfil, date('Y-m-d H:i:s')]);
    }
    public static function porEmail(string $email): ?array { return Database::one('SELECT * FROM usuarios WHERE email = ?', [mb_strtolower($email)]); }
    public static function emailExiste(string $email): bool { return (bool)self::porEmail($email); }
    public static function ativar(int $id, bool $ativo): void { Database::q('UPDATE usuarios SET ativo = ? WHERE id = ?', [$ativo ? 1 : 0, $id]); }
    public static function trocarSenha(int $id, string $senha): void { Database::q('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($senha, PASSWORD_DEFAULT), $id]); }

    /** Recuperação de senha (RF04): token aleatório, guardado apenas como hash, válido por 1 h. */
    public static function criarTokenReset(int $uid): string {
        $t = bin2hex(random_bytes(24));
        Database::q('INSERT INTO reset_senhas (usuario_id, token_hash, expira_em, usado) VALUES (?,?,?,0)', [$uid, hash('sha256', $t), date('Y-m-d H:i:s', time() + 3600)]);
        return $t;
    }
    public static function tokenValido(string $t): ?array {
        return Database::one('SELECT * FROM reset_senhas WHERE token_hash = ? AND usado = 0 AND expira_em > ?', [hash('sha256', $t), date('Y-m-d H:i:s')]);
    }
    public static function consumirToken(int $id): void { Database::q('UPDATE reset_senhas SET usado = 1 WHERE id = ?', [$id]); }
}
