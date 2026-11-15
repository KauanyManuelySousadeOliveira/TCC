<?php
class AuthController {
    const HOME = ['paciente' => '/paciente', 'medico' => '/medico', 'recepcionista' => '/recepcao'];
    public function health(): void { try { Database::val('SELECT 1'); echo 'ok'; } catch (Throwable $t) { http_response_code(503); echo 'db'; } }
    public function home(): void { $u = Auth::user(); redirect($u ? self::HOME[$u['perfil']] : '/login'); }
    public function loginForm(): void { if (Auth::user()) $this->home(); render('auth/login', ['layout' => 'layout_auth', 'locked' => Auth::lockedSeconds()]); }
    public function login(): void {
        csrf_check();
        if (Auth::attempt(post('email'), (string)($_POST['senha'] ?? ''))) redirect(self::HOME[Auth::user()['perfil']]);
        Log::add(null, 'login_falhou', mb_substr(post('email'), 0, 100));
        flash(Auth::lockedSeconds() ? 'Muitas tentativas. Aguarde alguns minutos.' : 'E-mail ou senha incorretos.', 'err'); redirect('/login');
    }
    public function logout(): void { csrf_check(); Auth::logout(); session_start(); flash('Você saiu do sistema.'); redirect('/login'); }

    public function cadastroForm(): void { render('auth/cadastro', ['layout' => 'layout_auth', 'erros' => [], 'v' => []]); }
    public function cadastro(): void {
        csrf_check();
        [$id, $erros] = Paciente::criar($_POST);
        if ($erros) { render('auth/cadastro', ['layout' => 'layout_auth', 'erros' => $erros, 'v' => $_POST]); return; }
        Log::add(null, 'paciente_cadastrado', "paciente #$id"); flash('Cadastro concluído! Entre com seu e-mail e senha.'); redirect('/login');
    }

    public function esqueciForm(): void { render('auth/esqueci', ['layout' => 'layout_auth']); }
    public function esqueci(): void {
        csrf_check(); $u = Usuario::porEmail(post('email'));
        if ($u && $u['ativo']) {
            $t = Usuario::criarTokenReset((int)$u['id']);
            $link = (($_SERVER['HTTPS'] ?? '') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/redefinir-senha?token=' . $t;
            Notifier::send($u['email'], 'Vívere — redefinição de senha', "Para criar uma nova senha acesse (válido por 1 hora):\n$link");
            Log::add((int)$u['id'], 'reset_solicitado');
        }
        flash('Se o e-mail estiver cadastrado, enviaremos as instruções de redefinição.'); redirect('/login'); // resposta idêntica: não revela contas
    }
    public function resetForm(): void {
        $t = Usuario::tokenValido(get('token')); if (!$t) { flash('Link inválido ou expirado.', 'err'); redirect('/login'); }
        render('auth/reset', ['layout' => 'layout_auth', 'token' => get('token')]);
    }
    public function reset(): void {
        csrf_check(); $t = Usuario::tokenValido(post('token')); if (!$t) { flash('Link inválido ou expirado.', 'err'); redirect('/login'); }
        if (strlen((string)($_POST['senha'] ?? '')) < 8) { flash('A senha deve ter pelo menos 8 caracteres.', 'err'); redirect('/redefinir-senha?token=' . urlencode(post('token'))); }
        Usuario::trocarSenha((int)$t['usuario_id'], $_POST['senha']); Usuario::consumirToken((int)$t['id']);
        Log::add((int)$t['usuario_id'], 'senha_redefinida'); flash('Senha alterada. Entre com a nova senha.'); redirect('/login');
    }
}
