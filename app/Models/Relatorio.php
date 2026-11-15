<?php
class Relatorio {
    public static function porStatus(string $de, string $ate): array { return Database::all('SELECT status, COUNT(*) AS total FROM consultas WHERE data BETWEEN ? AND ? GROUP BY status', [$de, $ate]); }
    public static function porMedico(string $de, string $ate): array {
        return Database::all('SELECT m.nome, e.nome AS especialidade, COUNT(c.id) AS total, SUM(CASE WHEN c.status = ? THEN 1 ELSE 0 END) AS realizadas, SUM(CASE WHEN c.status = ? THEN 1 ELSE 0 END) AS faltas FROM consultas c JOIN medicos m ON m.id = c.medico_id JOIN especialidades e ON e.id = m.especialidade_id WHERE c.data BETWEEN ? AND ? GROUP BY m.id, m.nome, e.nome ORDER BY total DESC', ['realizada', 'faltou', $de, $ate]);
    }
    public static function porEspecialidade(string $de, string $ate): array {
        return Database::all('SELECT e.nome, COUNT(c.id) AS total FROM consultas c JOIN medicos m ON m.id = c.medico_id JOIN especialidades e ON e.id = m.especialidade_id WHERE c.data BETWEEN ? AND ? GROUP BY e.id, e.nome ORDER BY total DESC', [$de, $ate]);
    }
    /** Taxa de comparecimento = realizadas / (realizadas + faltas). */
    public static function comparecimento(string $de, string $ate): ?float {
        $r = Database::one('SELECT SUM(CASE WHEN status=? THEN 1 ELSE 0 END) AS ok, SUM(CASE WHEN status=? THEN 1 ELSE 0 END) AS falta FROM consultas WHERE data BETWEEN ? AND ?', ['realizada', 'faltou', $de, $ate]);
        $t = (int)$r['ok'] + (int)$r['falta']; return $t ? round(100 * (int)$r['ok'] / $t, 1) : null;
    }
}
