<div class="page-head"><div><h1>Meus dados</h1></div></div>
<form class="card pad" method="post" action="/paciente/perfil" style="max-width:560px"><?= csrf_field() ?>
<div class="field"><label>Nome</label><input value="<?= e($p['nome']) ?>" disabled></div><div class="field"><label>CPF</label><input value="<?= e(fmt_cpf($p['cpf'])) ?>" disabled></div>
<div class="field"><label>Telefone</label><input name="telefone" required value="<?= e($p['telefone']) ?>"></div><div class="field"><label>E-mail</label><input type="email" name="email" required value="<?= e($p['email']) ?>"></div>
<button class="btn btn-primary">Salvar alterações</button></form>
