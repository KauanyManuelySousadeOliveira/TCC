<?php
class Paciente {
    const SELECT = 'SELECT p.*, u.ativo FROM pacientes p JOIN usuarios u ON u.id = p.usuario_id';
    public static function find(int $id): ?array { return Database::one(self::SELECT . ' WHERE p.id = ?', [$id]); }
    public static function busca(string $q = ''): array {
        if ($q === '') return Database::all(self::SELECT . ' ORDER BY p.nome');
        $l = '%' . $q . '%'; $d = '%' . only_digits($q) . '%';
        return Database::all(self::SELECT . ' WHERE p.nome LIKE ? OR p.telefone LIKE ? OR p.cpf LIKE ? ORDER BY p.nome', [$l, $l, strlen(only_digits($q)) ? $d : $l]);
    }
    /** Valida (RNF14) e cadastra paciente + usuário. Retorna [id|null, erros[]]. */
    public static function criar(array $d): array {
        $err = [];
        $nome = trim($d['nome'] ?? ''); $email = mb_strtolower(trim($d['email'] ?? '')); $cpf = only_digits($d['cpf'] ?? ''); $tel = trim($d['telefone'] ?? ''); $senha = (string)($d['senha'] ?? '');
        if (mb_strlen($nome) < 5 || !str_contains($nome, ' ')) $err[] = 'Informe o nome completo.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'E-mail inválido.';
        if (!valid_cpf($cpf)) $err[] = 'CPF inválido.';
        if (strlen(only_digits($tel)) < 10) $err[] = 'Telefone inválido.';
        if (strlen($senha) < 8) $err[] = 'A senha deve ter pelo menos 8 caracteres.';
        $nasc = $d['nascimento'] ?? ''; if ($nasc !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nasc)) $err[] = 'Data de nascimento inválida.';
        if (!$err && Usuario::emailExiste($email)) $err[] = 'Já existe um cadastro com este e-mail.';
        if (!$err && Database::val('SELECT COUNT(*) FROM pacientes WHERE cpf = ?', [$cpf])) $err[] = 'Já existe um paciente com este CPF.';
        if ($err) return [null, $err];
        $pdo = Database::pdo(); $pdo->beginTransaction();
        try {
            $uid = Usuario::criar($email, $senha, 'paciente');
            $id = Database::insert('INSERT INTO pacientes (usuario_id, nome, cpf, telefone, email, nascimento) VALUES (?,?,?,?,?,?)', [$uid, $nome, $cpf, $tel, $email, $nasc ?: null]);
            $pdo->commit(); return [$id, []];
        } catch (Throwable $t) { $pdo->rollBack(); return [null, ['Não foi possível concluir o cadastro.']]; }
    }
    public static function atualizar(int $id, string $tel, string $email): array {
        if (strlen(only_digits($tel)) < 10) return ['Telefone inválido.'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['E-mail inválido.'];
        Database::q('UPDATE pacientes SET telefone = ?, email = ? WHERE id = ?', [$tel, mb_strtolower($email), $id]); return [];
    }
}
