<form class="card pad" method="post" action="/login"><?= csrf_field() ?>
  <h2 style="margin-bottom:16px">Entrar</h2>
  <div class="field"><label>E-mail</label><input type="email" name="email" required autofocus></div>
  <div class="field"><label>Senha</label><input type="password" name="senha" required></div>
  <button class="btn btn-primary" style="width:100%;justify-content:center" <?= $locked ? 'disabled' : '' ?>>Entrar</button>
  <p style="text-align:center;margin:14px 0 0;font-size:13px"><a href="/esqueci-senha">Esqueci minha senha</a> · <a href="/cadastro">Criar conta de paciente</a></p>
</form>
