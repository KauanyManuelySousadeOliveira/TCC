<?php
class Consulta {
    const SELECT = 'SELECT c.*, p.nome AS paciente, m.nome AS medico, e.nome AS especialidade FROM consultas c JOIN pacientes p ON p.id = c.paciente_id JOIN medicos m ON m.id = c.medico_id JOIN especialidades e ON e.id = m.especialidade_id';
    public static function find(int $id): ?array { return Database::one(self::SELECT . ' WHERE c.id = ?', [$id]); }
    public static function doPaciente(int $pid): array { return Database::all(self::SELECT . ' WHERE c.paciente_id = ? ORDER BY c.data DESC, c.hora DESC', [$pid]); }
    public static function doMedico(int $mid, ?string $data = null): array {
        return $data ? Database::all(self::SELECT . ' WHERE c.medico_id = ? AND c.data = ? ORDER BY c.hora', [$mid, $data])
                     : Database::all(self::SELECT . ' WHERE c.medico_id = ? ORDER BY c.data DESC, c.hora DESC', [$mid]);
    }
    public static function agenda(?int $mid, ?string $data): array {
        $w = []; $p = [];
        if ($mid) { $w[] = 'c.medico_id = ?'; $p[] = $mid; }
        if ($data) { $w[] = 'c.data = ?'; $p[] = $data; }
        return Database::all(self::SELECT . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY c.data, c.hora', $p);
    }
    public static function horarioLivre(int $mid, string $data, string $hora, ?int $ignorar = null): bool {
        if (!in_array($hora, time_slots(), true)) return false;
        $t = Database::val("SELECT COUNT(*) FROM consultas WHERE medico_id=? AND data=? AND hora=? AND status <> 'cancelada' AND id <> ?", [$mid, $data, $hora, $ignorar ?? 0]);
        $b = Database::val('SELECT COUNT(*) FROM agendamentos WHERE medico_id=? AND data=? AND hora=? AND disponivel=0', [$mid, $data, $hora]);
        return !$t && !$b;
    }
    /** Mapa hora => 'livre'|'ocupado'|'bloqueado' para um médico em um dia. */
    public static function mapaDia(int $mid, string $data, ?int $ignorar = null): array {
        $out = []; $bl = Medico::bloqueados($mid, $data);
        $oc = array_column(Database::all("SELECT hora FROM consultas WHERE medico_id=? AND data=? AND status <> 'cancelada' AND id <> ?", [$mid, $data, $ignorar ?? 0]), 'hora');
        foreach (time_slots() as $h) $out[$h] = in_array($h, $bl, true) ? 'bloqueado' : (in_array($h, $oc, true) ? 'ocupado' : 'livre');
        if ($data === date('Y-m-d')) foreach ($out as $h => $s) if ($h <= date('H:i')) $out[$h] = 'ocupado';
        return $out;
    }
    /** RF30: impede conflito de agenda — verificação dentro de transação. */
    public static function marcar(int $pid, int $mid, string $data, string $hora): ?int {
        if ($data < date('Y-m-d') || !strtotime($data)) return null;
        $pdo = Database::pdo(); $pdo->beginTransaction();
        try {
            if (!self::horarioLivre($mid, $data, $hora)) { $pdo->rollBack(); return null; }
            $id = Database::insert('INSERT INTO consultas (paciente_id, medico_id, data, hora, status, criado_em) VALUES (?,?,?,?,?,?)', [$pid, $mid, $data, $hora, 'agendada', date('Y-m-d H:i:s')]);
            $pdo->commit(); return $id;
        } catch (Throwable $t) { $pdo->rollBack(); return null; }
    }
    public static function remarcar(int $id, string $data, string $hora): bool {
        $c = self::find($id); if (!$c || in_array($c['status'], ['cancelada', 'realizada', 'faltou'], true) || $data < date('Y-m-d')) return false;
        if (!self::horarioLivre((int)$c['medico_id'], $data, $hora, $id)) return false;
        Database::q("UPDATE consultas SET data=?, hora=?, status='agendada' WHERE id=?", [$data, $hora, $id]); return true;
    }
    public static function status(int $id, string $s): bool {
        if (!isset(STATUS_LABEL[$s])) return false;
        Database::q('UPDATE consultas SET status=? WHERE id=?', [$s, $id]); return true;
    }
    public static function notificar(int $id, string $evento): void {
        $c = self::find($id); if (!$c) return; $p = Paciente::find((int)$c['paciente_id']); if (!$p) return;
        Notifier::send($p['email'], "Vívere — consulta $evento", "Olá, {$p['nome']}.\n\nSua consulta com {$c['medico']} ({$c['especialidade']}) em " . fmt_date($c['data']) . " às {$c['hora']} foi $evento.\n\nClínica Vívere");
    }
}
