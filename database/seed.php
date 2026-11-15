<?php
/**
 * Popula o banco com dados DEMONSTRATIVOS (fictícios) — mesmos do simulador.
 * Uso: php database/seed.php        (senha de todos os usuários demo: vivere123)
 */
require __DIR__ . '/../app/bootstrap.php';
$pdo = Database::pdo();
if ((int)Database::val('SELECT COUNT(*) FROM usuarios') > 0) { echo "Banco já possui dados — seed ignorado.\n"; exit; }
$now = date('Y-m-d H:i:s'); $hash = password_hash('vivere123', PASSWORD_DEFAULT);
$mk = fn($email, $perfil) => Database::insert('INSERT INTO usuarios (email, senha_hash, perfil, ativo, criado_em) VALUES (?,?,?,1,?)', [$email, $hash, $perfil, $now]);

$esp = []; foreach (['Clínica Geral','Cardiologia','Dermatologia','Ginecologia','Ortopedia','Pediatria','Psiquiatria'] as $n) $esp[$n] = Database::insert('INSERT INTO especialidades (nome) VALUES (?)', [$n]);

$docs = [['Dr. Rafael Menezes','Cardiologia','CRM 45213-SP','rafael@vivere.demo'],['Dra. Beatriz Andrade','Dermatologia','CRM 51890-SP','beatriz@vivere.demo'],['Dr. Henrique Cordeiro','Clínica Geral','CRM 38221-SP','henrique@vivere.demo'],['Dra. Marina Lopes','Ginecologia','CRM 44710-SP','marina@vivere.demo'],['Dr. Otávio Ferraz','Ortopedia','CRM 39987-SP','otavio@vivere.demo'],['Dra. Sofia Nakamura','Pediatria','CRM 52276-SP','sofia@vivere.demo']];
$D = []; foreach ($docs as $i => [$n,$e,$crm,$mail]) $D[$i+1] = Database::insert('INSERT INTO medicos (usuario_id,nome,especialidade_id,crm) VALUES (?,?,?,?)', [$mk($mail,'medico'), $n, $esp[$e], $crm]);

$pats = [['Ana Beatriz Souza','32166498105','(11) 98221-4457','ana.souza@email.com','1994-03-14'],['João Pedro Lima','11577234088','(11) 97744-1120','joaopedro.lima@email.com','1987-11-02'],['Camila Duarte','40211866530','(11) 96650-8832','camila.duarte@email.com','1999-07-22'],['Fernando Rocha','25099411752','(11) 95521-6674','f.rocha@email.com','1979-01-09'],['Larissa Prado','18833655219','(11) 99110-3345','larissa.prado@email.com','2001-05-30'],['Eduardo Vasconcelos','37766022904','(11) 98899-7712','e.vasconcelos@email.com','1966-09-17']];
$P = []; foreach ($pats as $i => [$n,$cpf,$tel,$mail,$nasc]) $P[$i+1] = Database::insert('INSERT INTO pacientes (usuario_id,nome,cpf,telefone,email,nascimento) VALUES (?,?,?,?,?,?)', [$mk($mail,'paciente'), $n, $cpf, $tel, $mail, $nasc]);

Database::insert('INSERT INTO recepcionistas (usuario_id,nome) VALUES (?,?)', [$mk('carla@vivere.demo','recepcionista'), 'Carla Nogueira']);

$d = fn($off) => date('Y-m-d', strtotime(($off >= 0 ? "+$off" : $off) . ' days'));
$ap = [[1,1,$d(2),'09:00','confirmada'],[1,3,$d(-18),'14:30','realizada'],[1,2,$d(-60),'10:00','realizada'],[2,1,$d(1),'10:30','agendada'],[3,1,$d(1),'11:00','confirmada'],[4,1,$d(1),'15:30','agendada'],[5,5,$d(1),'09:30','agendada'],[6,1,$d(-14),'08:30','faltou'],[2,4,$d(6),'13:30','agendada'],[3,6,$d(4),'16:00','confirmada'],[1,1,$d(-82),'09:00','cancelada']];
foreach ($ap as [$p,$m,$dt,$h,$s]) Database::insert('INSERT INTO consultas (paciente_id,medico_id,data,hora,status,criado_em) VALUES (?,?,?,?,?,?)', [$P[$p],$D[$m],$dt,$h,$s,$now]);

$rec = [[1,3,$d(-18),'Check-up de rotina, sem queixas.','Exame físico normal. Solicitados exames laboratoriais de rotina. Retorno em 6 meses.'],[1,2,$d(-60),'Lesão avermelhada no antebraço.','Diagnóstico de dermatite de contato. Prescrito corticoide tópico por 7 dias.'],[2,1,$d(-100),'Palpitações ocasionais.','ECG sem alterações. Solicitado holter 24h para investigação.']];
foreach ($rec as [$p,$m,$dt,$q,$ev]) Database::insert('INSERT INTO prontuarios (paciente_id,medico_id,queixa,evolucao,criado_em) VALUES (?,?,?,?,?)', [$P[$p],$D[$m],$q,$ev,"$dt 10:00:00"]);
echo "Seed concluído. Logins demo (senha vivere123): ana.souza@email.com | rafael@vivere.demo | carla@vivere.demo\n";
