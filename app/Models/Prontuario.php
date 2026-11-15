<?php
class Prontuario {
    public static function doPaciente(int $pid): array {
        return Database::all('SELECT r.*, m.nome AS medico FROM prontuarios r JOIN medicos m ON m.id = r.medico_id WHERE r.paciente_id = ? ORDER BY r.criado_em DESC, r.id DESC', [$pid]);
    }
    public static function criar(int $pid, int $mid, string $queixa, string $evolucao): bool {
        if (mb_strlen($queixa) < 3 || mb_strlen($evolucao) < 3) return false;
        Database::insert('INSERT INTO prontuarios (paciente_id, medico_id, queixa, evolucao, criado_em) VALUES (?,?,?,?,?)', [$pid, $mid, $queixa, $evolucao, date('Y-m-d H:i:s')]); return true;
    }
    /** Pacientes já atendidos/agendados por um médico (RF14). */
    public static function pacientesDoMedico(int $mid): array {
        return Database::all('SELECT DISTINCT p.id, p.nome, p.telefone, p.cpf FROM pacientes p JOIN consultas c ON c.paciente_id = p.id WHERE c.medico_id = ? ORDER BY p.nome', [$mid]);
    }
    public static function medicoAtende(int $mid, int $pid): bool { return (bool)Database::val('SELECT COUNT(*) FROM consultas WHERE medico_id=? AND paciente_id=?', [$mid, $pid]); }
}
