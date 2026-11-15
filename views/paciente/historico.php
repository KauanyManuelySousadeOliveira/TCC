<div class="page-head"><div><h1>Histórico</h1><p>Atendimentos anteriores e registros clínicos.</p></div></div>
<?php $mostrar = ['medico']; $acoes = null; require __DIR__ . '/../partials/consultas.php'; ?>
<div class="section-title">Registros clínicos</div><div class="card pad"><?php if (!$prontuarios): ?><span class="muted">Sem registros.</span><?php endif; foreach ($prontuarios as $r): ?>
<div class="record-entry"><div class="re-date"><?= e(fmt_date($r['criado_em'])) ?></div><div class="re-doc"><?= e($r['medico']) ?></div><div><b>Queixa:</b> <?= e($r['queixa']) ?></div><div><b>Evolução:</b> <?= e($r['evolucao']) ?></div></div><?php endforeach; ?></div>
