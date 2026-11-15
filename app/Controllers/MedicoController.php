<?php
class MedicoController {
    private array $u;
    public function __construct() { $this->u = Auth::require('medico'); }
    public function home(): void {
        $hoje = date('Y-m-d'); $todas = Consulta::doMedico($this->u['ref_id']);
        render('medico/home', ['u' => $this->u, 'hoje' => Consulta::doMedico($this->u['ref_id'], $hoje), 'futuras' => count(array_filter($todas, fn($c) => $c['data'] >= $hoje && $c['status'] !== 'cancelada')), 'pacientes' => count(Prontuario::pacientesDoMedico($this->u['ref_id'])), 'realizadas' => count(array_filter($todas, fn($c) => $c['status'] === 'realizada'))]);
    }
    public function agenda(): void {
        $data = preg_match('/^\d{4}-\d{2}-\d{2}$/', get('data')) ? get('data') : date('Y-m-d');
        render('medico/agenda', ['data' => $data, 'lista' => Consulta::doMedico($this->u['ref_id'], $data), 'mapa' => Consulta::mapaDia($this->u['ref_id'], $data), 'bloqueados' => Medico::bloqueados($this->u['ref_id'], $data)]);
    }
    public function bloquear(): void {
        csrf_check(); $d = post('data'); $h = post('hora');
        if (Database::val("SELECT COUNT(*) FROM consultas WHERE medico_id=? AND data=? AND hora=? AND status <> 'cancelada'", [$this->u['ref_id'], $d, $h])) { flash('Já existe consulta nesse horário.', 'err'); redirect('/medico/agenda?data=' . urlencode($d)); }
        $b = Medico::alternarBloqueio($this->u['ref_id'], $d, $h); Log::add($this->u['id'], $b ? 'horario_bloqueado' : 'horario_liberado', "$d $h"); redirect('/medico/agenda?data=' . urlencode($d));
    }
    public function status(string $id): void {
        csrf_check(); $c = Consulta::find((int)$id);
        if (!$c || (int)$c['medico_id'] !== $this->u['ref_id']) { http_response_code(404); exit('Consulta não encontrada.'); }
        Consulta::status((int)$id, post('status')); Log::add($this->u['id'], 'status_consulta', "#$id → " . post('status')); redirect('/medico/agenda?data=' . $c['data']);
    }
    public function pacientes(): void { render('medico/pacientes', ['lista' => Prontuario::pacientesDoMedico($this->u['ref_id'])]); }
    public function prontuario(string $pid): void {
        if (!Prontuario::medicoAtende($this->u['ref_id'], (int)$pid)) { http_response_code(403); exit('Este paciente não está sob seus cuidados.'); }
        Log::add($this->u['id'], 'prontuario_acessado', "paciente #$pid");
        render('medico/prontuario', ['p' => Paciente::find((int)$pid), 'registros' => Prontuario::doPaciente((int)$pid), 'lista' => Prontuario::pacientesDoMedico($this->u['ref_id'])]);
    }
    public function salvarProntuario(string $pid): void {
        csrf_check(); if (!Prontuario::medicoAtende($this->u['ref_id'], (int)$pid)) { http_response_code(403); exit('Acesso negado.'); }
        if (Prontuario::criar((int)$pid, $this->u['ref_id'], post('queixa'), post('evolucao'))) { Log::add($this->u['id'], 'prontuario_registrado', "paciente #$pid"); flash('Evolução registrada no prontuário.'); } else flash('Preencha a queixa e a evolução.', 'err');
        redirect("/medico/pacientes/$pid");
    }
}
