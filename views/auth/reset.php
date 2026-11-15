<form class="card pad" method="post" action="/redefinir-senha"><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
  <h2 style="margin-bottom:16px">Nova senha</h2>
  <div class="field"><label>Nova senha (mín. 8 caracteres)</label><input type="password" name="senha" minlength="8" required></div>
  <button class="btn btn-primary" style="width:100%;justify-content:center">Salvar senha</button>
</form>
