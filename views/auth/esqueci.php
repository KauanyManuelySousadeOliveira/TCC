<form class="card pad" method="post" action="/esqueci-senha"><?= csrf_field() ?>
  <h2 style="margin-bottom:6px">Recuperar senha</h2><p class="muted" style="margin-top:0">Informe seu e-mail para receber o link de redefinição.</p>
  <div class="field"><label>E-mail</label><input type="email" name="email" required></div>
  <button class="btn btn-primary" style="width:100%;justify-content:center">Enviar link</button>
  <p style="text-align:center;margin:14px 0 0;font-size:13px"><a href="/login">Voltar</a></p>
</form>
