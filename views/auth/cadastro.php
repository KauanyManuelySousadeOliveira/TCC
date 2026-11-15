<form class="card pad" method="post" action="/cadastro"><?= csrf_field() ?>
  <h2 style="margin-bottom:16px">Cadastro de paciente</h2>
  <?php foreach ($erros as $er): ?><div class="alert alert-err"><?= e($er) ?></div><?php endforeach; ?>
  <div class="field"><label>Nome completo</label><input name="nome" required value="<?= e($v['nome'] ?? '') ?>"></div>
  <div class="grid2"><div class="field"><label>CPF</label><input name="cpf" required placeholder="000.000.000-00" value="<?= e($v['cpf'] ?? '') ?>"></div>
  <div class="field"><label>Nascimento</label><input type="date" name="nascimento" value="<?= e($v['nascimento'] ?? '') ?>"></div></div>
  <div class="field"><label>Telefone</label><input name="telefone" required placeholder="(11) 90000-0000" value="<?= e($v['telefone'] ?? '') ?>"></div>
  <div class="field"><label>E-mail</label><input type="email" name="email" required value="<?= e($v['email'] ?? '') ?>"></div>
  <div class="field"><label>Senha (mín. 8 caracteres)</label><input type="password" name="senha" minlength="8" required></div>
  <button class="btn btn-primary" style="width:100%;justify-content:center">Cadastrar</button>
  <p style="text-align:center;margin:14px 0 0;font-size:13px"><a href="/login">Já tenho conta</a></p>
</form>
