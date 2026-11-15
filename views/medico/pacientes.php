<div class="page-head"><div><h1>Meus pacientes</h1></div></div>
<div class="card"><?php if (!$lista): ?><div class="empty"><div class="em-title">Nenhum paciente ainda</div></div><?php else: ?><table class="table"><thead><tr><th>Nome</th><th>Telefone</th><th></th></tr></thead><tbody>
<?php foreach ($lista as $p): ?><tr><td><?= e($p['nome']) ?></td><td class="muted"><?= e($p['telefone']) ?></td><td style="text-align:right"><a class="btn btn-secondary btn-sm" href="/medico/pacientes/<?= (int)$p['id'] ?>">Abrir prontuário</a></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div>
