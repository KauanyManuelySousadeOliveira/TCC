<div class="page-head"><div><h1>Minha agenda</h1><p>Gerencie consultas e bloqueie horários (folgas, ausências).</p></div>
<form method="get" style="display:flex;gap:8px"><input type="date" name="data" value="<?= e($data) ?>" style="padding:8px;border:1px solid var(--sage-line);border-radius:7px"><button class="btn btn-secondary">Ir</button></form></div>
<div class="section-title">Consultas em <?= e(fmt_date($data)) ?></div>
<?php $mostrar = ['paciente']; $acoes = function ($c) { ob_start(); ?>
<form method="post" action="/medico/consultas/<?= (int)$c['id'] ?>/status" style="display:inline-flex;gap:6px"><?= csrf_field() ?>
<select name="status" style="padding:5px;border-radius:6px;border:1px solid var(--sage-line)"><?php foreach (STATUS_LABEL as $k => $l): ?><option value="<?= $k ?>" <?= $k === $c['status'] ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select><button class="btn btn-secondary btn-sm">Salvar</button></form>
<a class="btn btn-ghost btn-sm" href="/medico/pacientes/<?= (int)$c['paciente_id'] ?>">Prontuário</a><?php return ob_get_clean(); };
require __DIR__ . '/../partials/consultas.php'; ?>
<div class="section-title">Disponibilidade do dia</div>
<form method="post" action="/medico/agenda/bloquear" class="card pad"><?= csrf_field() ?><input type="hidden" name="data" value="<?= e($data) ?>">
<div class="slot-grid"><?php foreach ($mapa as $h => $s): ?><?php if ($s === 'ocupado'): ?><span class="slot taken" title="Consulta marcada"><?= e($h) ?></span><?php else: ?><button class="slot <?= $s === 'bloqueado' ? 'blocked' : '' ?>" name="hora" value="<?= e($h) ?>" title="<?= $s === 'bloqueado' ? 'Clique para liberar' : 'Clique para bloquear' ?>" style="<?= $s === 'bloqueado' ? 'cursor:pointer' : '' ?>"><?= e($h) ?></button><?php endif; ?><?php endforeach; ?></div>
<p class="muted" style="font-size:13px;margin-bottom:0">Horários livres bloqueiam ao clicar; horários cinza-claros (sem risco) estão bloqueados e liberam ao clicar. Riscados = consulta marcada.</p></form>
