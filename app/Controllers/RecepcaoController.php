<?php
class RecepcaoController {
    use Booking;
    private array $u;
    public function __construct() { $this->u = Auth::require('recepcionista'); }
    public function home(): void {
        $hoje = date('Y-m-d'); $ag = Consulta::agenda(null, $hoje);
        render('recepcao/home', ['u' => $this->u, 'hoje' => $ag, 'pacientes' => count(Paciente::busca()), 'medicos' => count(Medico::todos(true)), 'pendentes' => count(array_filter($ag, fn($c) => $c['status'] === 'agendada'))]);
    }
    public function agenda(): void {
        $data = get('data') ?: date('Y-m-d'); $mid = (int)get('med') ?: null;
        render('recepcao/agenda', ['data' => $data, 'med' => $mid, 'medicos' => Medico::todos(), 'lista' => Consulta::agenda($mid, get('data') === 'todas' ? null : $data), 'todas' => get('data') === 'todas']);
    }
    public function status(string $id): void {
        csrf_check(); if (Consulta::status((int)$id, post('status'))) { Log::add($this->u['id'], 'status_consulta', "#$id → " . post('status')); if (post('status') === 'cancelada') Consulta::notificar((int)$id, 'cancelada'); else if (post('status') === 'confirmada') Consulta::notificar((int)$id, 'confirmada'); flash('Status atualizado.'); }
        redirect('/recepcao/agenda?data=' . urlencode(post('volta', date('Y-m-d'))));
    }
    public function agendarForm(): void {
        $pid = (int)get('pac'); render('recepcao/agendar', $this->bookingData() + ['pacientes' => Paciente::busca(), 'pac' => $pid ? Paciente::find($pid) : null, 'base' => '/recepcao/agendar', 'post' => '/recepcao/agendar', 'extra' => $pid ? ['pac' => $pid] : []]);
    }
    public function agendar(): void {
        csrf_check(); $pid = (int)post('pac'); if (!Paciente::find($pid)) { flash('Escolha o paciente.', 'err'); redirect('/recepcao/agendar'); }
        $id = Consulta::marcar($pid, (int)post('med'), post('data'), post('hora'));
        if (!$id) { flash('Horário indisponível. Escolha outro.', 'err'); redirect("/recepcao/agendar?pac=$pid&med=" . (int)post('med')); }
        Log::add($this->u['id'], 'consulta_agendada_recepcao', "consulta #$id"); Consulta::notificar($id, 'agendada'); flash('Consulta agendada para o paciente.'); redirect('/recepcao/agenda?data=' . post('data'));
    }
    public function pacientes(): void { render('recepcao/pacientes', ['lista' => Paciente::busca(get('q')), 'q' => get('q'), 'erros' => [], 'v' => []]); }
    public function criarPaciente(): void {
        csrf_check(); [$id, $erros] = Paciente::criar($_POST);
        if ($erros) { render('recepcao/pacientes', ['lista' => Paciente::busca(), 'q' => '', 'erros' => $erros, 'v' => $_POST]); return; }
        Log::add($this->u['id'], 'paciente_cadastrado_recepcao', "paciente #$id"); flash('Paciente cadastrado.'); redirect('/recepcao/pacientes');
    }
    public function ativoPaciente(string $id): void { csrf_check(); $p = Paciente::find((int)$id); if ($p) { Usuario::ativar((int)$p['usuario_id'], post('ativo') === '1'); Log::add($this->u['id'], 'usuario_ativo_alterado', "paciente #$id"); flash('Situação do paciente atualizada.'); } redirect('/recepcao/pacientes'); }
    public function medicos(): void { render('recepcao/medicos', ['lista' => Medico::todos(), 'especialidades' => Medico::especialidades(), 'erros' => [], 'v' => []]); }
    public function criarMedico(): void {
        csrf_check(); [$id, $erros] = Medico::criar($_POST);
        if ($erros) { render('recepcao/medicos', ['lista' => Medico::todos(), 'especialidades' => Medico::especialidades(), 'erros' => $erros, 'v' => $_POST]); return; }
        Log::add($this->u['id'], 'medico_cadastrado', "medico #$id"); flash('Médico cadastrado.'); redirect('/recepcao/medicos');
    }
    public function ativoMedico(string $id): void { csrf_check(); $m = Medico::find((int)$id); if ($m) { Usuario::ativar((int)$m['usuario_id'], post('ativo') === '1'); Log::add($this->u['id'], 'usuario_ativo_alterado', "medico #$id"); flash('Situação do médico atualizada.'); } redirect('/recepcao/medicos'); }
    public function novaEspecialidade(): void { csrf_check(); $ok = Medico::novaEspecialidade(post('nome')); flash($ok ? 'Especialidade criada.' : 'Nome inválido ou já existente.', $ok ? 'ok' : 'err'); redirect('/recepcao/medicos'); }
    private function periodo(): array { $de = preg_match('/^\d{4}-\d{2}-\d{2}$/', get('de')) ? get('de') : date('Y-m-01'); $ate = preg_match('/^\d{4}-\d{2}-\d{2}$/', get('ate')) ? get('ate') : date('Y-m-t'); return [$de, $ate]; }
    public function relatorios(): void { [$de, $ate] = $this->periodo(); render('recepcao/relatorios', ['de' => $de, 'ate' => $ate, 'status' => Relatorio::porStatus($de, $ate), 'medicos' => Relatorio::porMedico($de, $ate), 'esp' => Relatorio::porEspecialidade($de, $ate), 'taxa' => Relatorio::comparecimento($de, $ate)]); }
    /** RNF20: exportação em CSV (separador ; e BOM UTF-8 para abrir corretamente no Excel). */
    public function csv(): void {
        [$de, $ate] = $this->periodo(); header('Content-Type: text/csv; charset=UTF-8'); header("Content-Disposition: attachment; filename=consultas_{$de}_{$ate}.csv");
        $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF"); fputcsv($o, ['Data', 'Hora', 'Paciente', 'Médico', 'Especialidade', 'Status'], ';');
        foreach (Database::all(Consulta::SELECT . ' WHERE c.data BETWEEN ? AND ? ORDER BY c.data, c.hora', [$de, $ate]) as $c) fputcsv($o, array_map(fn($x) => preg_match('/^[=+\-@]/', (string)$x) ? "'" . $x : $x, [fmt_date($c['data']), $c['hora'], $c['paciente'], $c['medico'], $c['especialidade'], STATUS_LABEL[$c['status']]]), ';');
        exit;
    }
}
