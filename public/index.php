<?php
/** Front controller — todas as requisições passam por aqui (padrão MVC). */
require __DIR__ . '/../app/bootstrap.php';

$routes = [
    ['GET', '/', 'AuthController@home'],
    ['GET', '/login', 'AuthController@loginForm'], ['POST', '/login', 'AuthController@login'], ['POST', '/logout', 'AuthController@logout'],
    ['GET', '/cadastro', 'AuthController@cadastroForm'], ['POST', '/cadastro', 'AuthController@cadastro'],
    ['GET', '/esqueci-senha', 'AuthController@esqueciForm'], ['POST', '/esqueci-senha', 'AuthController@esqueci'],
    ['GET', '/redefinir-senha', 'AuthController@resetForm'], ['POST', '/redefinir-senha', 'AuthController@reset'],
    // Paciente
    ['GET', '/paciente', 'PacienteController@home'],
    ['GET', '/paciente/agendar', 'PacienteController@agendarForm'], ['POST', '/paciente/agendar', 'PacienteController@agendar'],
    ['GET', '/paciente/consultas', 'PacienteController@consultas'],
    ['POST', '/paciente/consultas/{id}/cancelar', 'PacienteController@cancelar'],
    ['GET', '/paciente/consultas/{id}/remarcar', 'PacienteController@remarcarForm'], ['POST', '/paciente/consultas/{id}/remarcar', 'PacienteController@remarcar'],
    ['GET', '/paciente/historico', 'PacienteController@historico'],
    ['GET', '/paciente/perfil', 'PacienteController@perfilForm'], ['POST', '/paciente/perfil', 'PacienteController@perfil'],
    // Médico
    ['GET', '/medico', 'MedicoController@home'], ['GET', '/medico/agenda', 'MedicoController@agenda'], ['POST', '/medico/agenda/bloquear', 'MedicoController@bloquear'],
    ['POST', '/medico/consultas/{id}/status', 'MedicoController@status'],
    ['GET', '/medico/pacientes', 'MedicoController@pacientes'], ['GET', '/medico/pacientes/{id}', 'MedicoController@prontuario'], ['POST', '/medico/pacientes/{id}/prontuario', 'MedicoController@salvarProntuario'],
    // Recepcionista
    ['GET', '/recepcao', 'RecepcaoController@home'], ['GET', '/recepcao/agenda', 'RecepcaoController@agenda'],
    ['GET', '/recepcao/agendar', 'RecepcaoController@agendarForm'], ['POST', '/recepcao/agendar', 'RecepcaoController@agendar'],
    ['POST', '/recepcao/consultas/{id}/status', 'RecepcaoController@status'],
    ['GET', '/recepcao/pacientes', 'RecepcaoController@pacientes'], ['POST', '/recepcao/pacientes', 'RecepcaoController@criarPaciente'], ['POST', '/recepcao/pacientes/{id}/ativo', 'RecepcaoController@ativoPaciente'],
    ['GET', '/recepcao/medicos', 'RecepcaoController@medicos'], ['POST', '/recepcao/medicos', 'RecepcaoController@criarMedico'], ['POST', '/recepcao/medicos/{id}/ativo', 'RecepcaoController@ativoMedico'],
    ['POST', '/recepcao/especialidades', 'RecepcaoController@novaEspecialidade'],
    ['GET', '/recepcao/relatorios', 'RecepcaoController@relatorios'], ['GET', '/recepcao/relatorios.csv', 'RecepcaoController@csv'],
    ['GET', '/health', 'AuthController@health'],
];

$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
header('X-Content-Type-Options: nosniff'); header('X-Frame-Options: DENY'); header('Referrer-Policy: same-origin');
try {
    foreach ($routes as [$m, $pattern, $handler]) {
        $re = '#^' . preg_replace('#\{(\w+)\}#', '(\d+)', $pattern) . '$#';
        if ($m === $method && preg_match($re, $path, $mt)) {
            [$cls, $fn] = explode('@', $handler);
            (new $cls())->$fn(...array_slice($mt, 1));
            exit;
        }
    }
    http_response_code(404); render('404', ['layout' => 'layout_auth']);
} catch (Throwable $t) {
    error_log($t); http_response_code(500);
    echo getenv('APP_DEBUG') === '1' ? '<pre>' . e($t) . '</pre>' : 'Ocorreu um erro inesperado. Tente novamente em instantes.';
}
