<div class="page-head"><div><h1>Agenda geral</h1></div>
<form method="get" style="display:flex;gap:8px;flex-wrap:wrap"><input type="date" name="data" value="<?= $todas ? '' : e($data) ?>" style="padding:8px;border:1px solid var(--sage-line);border-radius:7px">
<select name="med" style="padding:8px;border:1px solid var(--sage-line);border-radius:7px"><option value="">Todos os médicos</option><?php foreach ($medicos as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $med == $m['id'] ? 'selected' : '' ?>><?= e($m['nome']) ?></option><?php endforeach; ?></select>
<button class="btn btn-secondary">Filtrar</button><a class="btn btn-ghost" href="/recepcao/agenda?data=todas">Ver todas</a></form></div>
<?php $mostrar = ['paciente', 'medico']; $volta = $data; $acoes = function ($c) use ($volta) { ob_start(); ?>
<form method="post" action="/recepcao/consultas/<?= (int)$c['id'] ?>/status" style="display:inline-flex;gap:6px"><?= csrf_field() ?><input type="hidden" name="volta" value="<?= e($volta) ?>">
<select name="status" style="padding:5px;border-radius:6px;border:1px solid var(--sage-line)"><?php foreach (STATUS_LABEL as $k => $l): ?><option value="<?= $k ?>" <?= $k === $c['status'] ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select><button class="btn btn-secondary btn-sm">Salvar</button></form><?php return ob_get_clean(); };
require __DIR__ . '/../partials/consultas.php'; ?>
