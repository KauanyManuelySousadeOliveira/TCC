<div class="page-head"><div><h1>Minhas consultas</h1></div><a class="btn btn-primary" href="/paciente/agendar">➕ Agendar</a></div>
<?php $mostrar = ['medico']; $acoes = function ($c) { if (!in_array($c['status'], ['agendada', 'confirmada'], true)) return ''; ob_start(); ?>
<a class="btn btn-secondary btn-sm" href="/paciente/consultas/<?= (int)$c['id'] ?>/remarcar">Remarcar</a>
<form method="post" action="/paciente/consultas/<?= (int)$c['id'] ?>/cancelar" style="display:inline" onsubmit="return confirm('Cancelar esta consulta?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm">Cancelar</button></form><?php return ob_get_clean(); };
require __DIR__ . '/../partials/consultas.php'; ?>
