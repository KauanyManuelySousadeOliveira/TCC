<?php
class PacienteController {
    use Booking;
    private array $u;
    public function __construct() { $this->u = Auth::require('paciente'); }

    public function home(): void {
        $c = Consulta::doPaciente($this->u['ref_id']); $hoje = date('Y-m-d');
        $prox = array_values(array_filter($c, fn($x) => $x['data'] >= $hoje && $x['status'] !== 'cancelada')); usort($prox, fn($a, $b) => [$a['data'], $a['hora']] <=> [$b['data'], $b['hora']]);
        render('paciente/home', ['u' => $this->u, 'proximas' => $prox, 'realizadas' => count(array_filter($c, fn($x) => $x['status'] === 'realizada')), 'canceladas' => count(array_filter($c, fn($x) => $x['status'] === 'cancelada')), 'totalMedicos' => count(Medico::todos(true))]);
    }
    public function agendarForm(): void { render('paciente/agendar', $this->bookingData() + ['base' => '/paciente/agendar', 'post' => '/paciente/agendar', 'extra' => []]); }
    public function agendar(): void {
        csrf_check(); $id = Consulta::marcar($this->u['ref_id'], (int)post('med'), post('data'), post('hora'));
        if (!$id) { flash('Esse horário não está mais disponível. Escolha outro.', 'err'); redirect('/paciente/agendar?med=' . (int)post('med')); }
        Log::add($this->u['id'], 'consulta_agendada', "consulta #$id"); Consulta::notificar($id, 'agendada'); flash('Consulta agendada com sucesso!'); redirect('/paciente/consultas');
    }
    public function consultas(): void { render('paciente/consultas', ['lista' => Consulta::doPaciente($this->u['ref_id']), 'hoje' => date('Y-m-d')]); }
    private function minha(int $id): array { $c = Consulta::find($id); if (!$c || (int)$c['paciente_id'] !== $this->u['ref_id']) { http_response_code(404); exit('Consulta não encontrada.'); } return $c; }
    public function cancelar(string $id): void {
        csrf_check(); $c = $this->minha((int)$id);
        if (in_array($c['status'], ['agendada', 'confirmada'], true)) { Consulta::status((int)$id, 'cancelada'); Log::add($this->u['id'], 'consulta_cancelada', "consulta #$id"); Consulta::notificar((int)$id, 'cancelada'); flash('Consulta cancelada.'); }
        redirect('/paciente/consultas');
    }
    public function remarcarForm(string $id): void { $c = $this->minha((int)$id); render('paciente/agendar', $this->bookingData($c) + ['base' => "/paciente/consultas/$id/remarcar", 'post' => "/paciente/consultas/$id/remarcar", 'extra' => [], 'consulta' => $c]); }
    public function remarcar(string $id): void {
        csrf_check(); $this->minha((int)$id);
        if (Consulta::remarcar((int)$id, post('data'), post('hora'))) { Log::add($this->u['id'], 'consulta_remarcada', "consulta #$id"); Consulta::notificar((int)$id, 'remarcada'); flash('Consulta remarcada.'); redirect('/paciente/consultas'); }
        flash('Não foi possível remarcar para esse horário.', 'err'); redirect("/paciente/consultas/$id/remarcar");
    }
    public function historico(): void { render('paciente/historico', ['lista' => array_values(array_filter(Consulta::doPaciente($this->u['ref_id']), fn($c) => in_array($c['status'], ['realizada', 'faltou', 'cancelada'], true))), 'prontuarios' => Prontuario::doPaciente($this->u['ref_id'])]); }
    public function perfilForm(): void { render('paciente/perfil', ['p' => Paciente::find($this->u['ref_id'])]); }
    public function perfil(): void {
        csrf_check(); $e = Paciente::atualizar($this->u['ref_id'], post('telefone'), post('email'));
        if ($e) flash(implode(' ', $e), 'err'); else { Log::add($this->u['id'], 'dados_atualizados'); flash('Dados atualizados.'); }
        redirect('/paciente/perfil');
    }
}
