<?php /* Fluxo: especialidade → médico → data → horário. Vars: $base,$post,$extra,$especialidades,$esp,$medicos,$med,$dias,$data,$mapa,$remarcar */
$q = fn(array $p) => $base . '?' . http_build_query(array_filter($p + $extra, fn($x) => $x !== '' && $x !== null && $x !== 0));
$step = $med ? ($data ? 4 : 3) : ($esp ? 2 : 1); ?>
<div class="stepper"><?php foreach ([1 => 'Especialidade', 2 => 'Médico', 3 => 'Data', 4 => 'Horário'] as $n => $l): ?><div class="step-pill <?= $n == $step ? 'active' : ($n < $step ? 'done' : '') ?>"><?= $n ?>. <?= $l ?></div><?php endforeach; ?></div>
<?php if (!$remarcar): ?><div class="section-title">Especialidade</div><div class="pick-grid"><?php foreach ($especialidades as $e_): ?>
<a class="pick-card <?= $esp == $e_['id'] ? 'selected' : '' ?>" href="<?= e($q(['esp' => $e_['id']])) ?>" style="text-decoration:none;color:inherit"><b><?= e($e_['nome']) ?></b></a><?php endforeach; ?></div>
<?php if ($esp): ?><div class="section-title">Médico</div><div class="pick-grid"><?php foreach ($medicos as $m): ?>
<a class="pick-card <?= $med && $med['id'] == $m['id'] ? 'selected' : '' ?>" href="<?= e($q(['esp' => $esp, 'med' => $m['id']])) ?>" style="text-decoration:none;color:inherit"><b><?= e($m['nome']) ?></b><div class="muted" style="font-size:12px"><?= e($m['crm']) ?></div></a><?php endforeach; ?></div><?php endif; endif; ?>
<?php if ($med): ?><div class="section-title">Data — <?= e($med['nome']) ?></div><div class="pick-grid"><?php foreach ($dias as $d): ?>
<a class="pick-card <?= $data === $d ? 'selected' : '' ?>" href="<?= e($q(['esp' => $esp, 'med' => $med['id'], 'data' => $d])) ?>" style="text-decoration:none;color:inherit;text-align:center"><b><?= e(fmt_date($d)) ?></b><div class="muted"><?= e(weekday_short($d)) ?></div></a><?php endforeach; ?></div><?php endif; ?>
<?php if ($med && $data): ?><div class="section-title">Horários em <?= e(fmt_date($data)) ?></div>
<form method="post" action="<?= e($post) ?>"><?= csrf_field() ?><input type="hidden" name="med" value="<?= (int)$med['id'] ?>"><input type="hidden" name="data" value="<?= e($data) ?>">
<?php foreach ($extra as $k => $v): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?>
<div class="slot-grid"><?php foreach ($mapa as $h => $s): ?><?php if ($s === 'livre'): ?><button class="slot" name="hora" value="<?= e($h) ?>"><?= e($h) ?></button><?php else: ?><span class="slot <?= $s === 'bloqueado' ? 'blocked' : 'taken' ?>"><?= e($h) ?></span><?php endif; ?><?php endforeach; ?></div></form>
<p class="muted" style="font-size:13px">Clique em um horário livre para <?= $remarcar ? 'remarcar' : 'confirmar o agendamento' ?>.</p><?php endif; ?>
