<?php
$u = Auth::user(); $perfil = $u['perfil'];
$nav = [
 'paciente' => [['/paciente','🏠','Início'],['/paciente/agendar','➕','Agendar consulta'],['/paciente/consultas','📅','Minhas consultas'],['/paciente/historico','🕘','Histórico'],['/paciente/perfil','👤','Meus dados']],
 'medico' => [['/medico','🏠','Início'],['/medico/agenda','📅','Minha agenda'],['/medico/pacientes','🧑‍🤝‍🧑','Meus pacientes']],
 'recepcionista' => [['/recepcao','🏠','Início'],['/recepcao/agenda','📅','Agenda geral'],['/recepcao/agendar','➕','Agendar'],['/recepcao/pacientes','🧑‍🤝‍🧑','Pacientes'],['/recepcao/medicos','🩺','Médicos'],['/recepcao/relatorios','📊','Relatórios']],
][$perfil];
$cur = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$f = flash();
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Vívere SGC') ?> — Vívere SGC</title>
<link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/extra.css"></head>
<body><div id="app">
<div class="topbar">
  <div class="brand"><div class="pulse-wrap"><svg viewBox="0 0 120 24"><polyline class="pulse-line" points="0,12 22,12 28,3 34,21 40,12 46,12 120,12"/></svg></div><div><div class="brand-name">Vívere SGC</div><div class="brand-tag">Clínica Vívere</div></div></div>
  <div class="topbar-spacer"></div>
  <div class="user-chip"><div class="avatar"><?= e(initials($u['nome'])) ?></div><div class="who"><div class="name"><?= e($u['nome']) ?></div><div class="role"><?= e(ucfirst($perfil)) ?></div></div></div>
  <form method="post" action="/logout"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" style="color:#fff">Sair</button></form>
</div>
<div class="body-wrap">
  <nav class="sidebar"><?php foreach ($nav as [$href, $ic, $label]): $active = $cur === $href || ($href !== $nav[0][0] && str_starts_with($cur, $href)); ?>
    <a class="nav-item <?= $active ? 'active' : '' ?>" href="<?= e($href) ?>" style="text-decoration:none"><span class="ic"><?= $ic ?></span><span><?= e($label) ?></span></a>
  <?php endforeach; ?></nav>
  <main class="content"><?php if ($f): ?><div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?><?= $content ?></main>
</div></div></body></html>
