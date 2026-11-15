<?php
/** Lógica compartilhada do fluxo de agendamento (paciente e recepção). */
trait Booking {
    /** Monta os dados da tela de agendamento a partir dos parâmetros GET (esp → médico → data → horário). */
    protected function bookingData(?array $consulta = null): array {
        $esp = (int)get('esp'); $med = (int)get('med'); $data = get('data');
        $m = null;
        if ($consulta) { $m = Medico::find((int)$consulta['medico_id']); $med = (int)$m['id']; $esp = (int)$m['especialidade_id']; }
        elseif ($med) { $m = Medico::find($med); if ($m) $esp = (int)$m['especialidade_id']; }
        $dias = next_days(14);
        if ($data !== '' && !in_array($data, $dias, true)) $data = '';
        return [
            'especialidades' => Medico::especialidades(), 'esp' => $esp, 'medicos' => $esp ? Medico::porEspecialidade($esp) : [],
            'med' => $m, 'dias' => $dias, 'data' => $data,
            'mapa' => ($m && $data) ? Consulta::mapaDia($med, $data, $consulta ? (int)$consulta['id'] : null) : [],
            'remarcar' => (bool)$consulta,
        ];
    }
}
