<?php
class Medico {
    const SELECT = 'SELECT m.*, e.nome AS especialidade, u.ativo, u.email FROM medicos m JOIN especialidades e ON e.id = m.especialidade_id JOIN usuarios u ON u.id = m.usuario_id';
    public static function find(int $id): ?array { return Database::one(self::SELECT . ' WHERE m.id = ?', [$id]); }
    public static function todos(bool $soAtivos = false): array { return Database::all(self::SELECT . ($soAtivos ? ' WHERE u.ativo = 1' : '') . ' ORDER BY m.nome'); }
    public static function porEspecialidade(int $espId): array { return Database::all(self::SELECT . ' WHERE m.especialidade_id = ? AND u.ativo = 1 ORDER BY m.nome', [$espId]); }
    public static function especialidades(): array { return Database::all('SELECT * FROM especialidades ORDER BY nome'); }
    public static function criar(array $d): array {
        $err = []; $nome = trim($d['nome'] ?? ''); $email = mb_strtolower(trim($d['email'] ?? '')); $crm = trim($d['crm'] ?? ''); $esp = (int)($d['especialidade_id'] ?? 0); $senha = (string)($d['senha'] ?? '');
        if (mb_strlen($nome) < 5) $err[] = 'Informe o nome do médico.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'E-mail inválido.';
        if (!preg_match('/^CRM\s?\d{3,7}-[A-Z]{2}$/', $crm)) $err[] = 'CRM inválido (ex.: CRM 12345-SP).';
        if (!Database::val('SELECT COUNT(*) FROM especialidades WHERE id = ?', [$esp])) $err[] = 'Escolha uma especialidade.';
        if (strlen($senha) < 8) $err[] = 'A senha inicial deve ter pelo menos 8 caracteres.';
        if (!$err && Usuario::emailExiste($email)) $err[] = 'E-mail já cadastrado.';
        if (!$err && Database::val('SELECT COUNT(*) FROM medicos WHERE crm = ?', [$crm])) $err[] = 'CRM já cadastrado.';
        if ($err) return [null, $err];
        $uid = Usuario::criar($email, $senha, 'medico');
        return [Database::insert('INSERT INTO medicos (usuario_id, nome, especialidade_id, crm) VALUES (?,?,?,?)', [$uid, $nome, $esp, $crm]), []];
    }
    public static function novaEspecialidade(string $nome): bool {
        $nome = trim($nome); if (mb_strlen($nome) < 3) return false;
        if (Database::val('SELECT COUNT(*) FROM especialidades WHERE nome = ?', [$nome])) return false;
        Database::insert('INSERT INTO especialidades (nome) VALUES (?)', [$nome]); return true;
    }
    public static function bloqueados(int $mid, string $data): array { return array_column(Database::all('SELECT hora FROM agendamentos WHERE medico_id = ? AND data = ? AND disponivel = 0', [$mid, $data]), 'hora'); }
    public static function alternarBloqueio(int $mid, string $data, string $hora): bool {
        if (!in_array($hora, time_slots(), true)) return false;
        if (Database::val('SELECT COUNT(*) FROM agendamentos WHERE medico_id=? AND data=? AND hora=? AND disponivel=0', [$mid, $data, $hora])) {
            Database::q('DELETE FROM agendamentos WHERE medico_id=? AND data=? AND hora=?', [$mid, $data, $hora]); return false;
        }
        Database::insert('INSERT INTO agendamentos (medico_id, data, hora, disponivel) VALUES (?,?,?,0)', [$mid, $data, $hora]); return true;
    }
}
