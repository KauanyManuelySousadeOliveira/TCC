<?php
/** Trilha de auditoria (RF29, RNF12, RNF21) e notificações por e-mail (RF12). */
class Log {
    public static function add(?int $uid, string $acao, string $detalhe = ''): void {
        Database::q('INSERT INTO logs (usuario_id, acao, detalhe, ip, criado_em) VALUES (?,?,?,?,?)',
            [$uid, $acao, $detalhe, $_SERVER['REMOTE_ADDR'] ?? 'cli', date('Y-m-d H:i:s')]);
    }
}
class Notifier {
    /** Envia e-mail; se o servidor não tiver SMTP, grava em storage/mail.log (útil em desenvolvimento). */
    public static function send(string $to, string $assunto, string $corpo): void {
        $from = getenv('MAIL_FROM') ?: 'no-reply@vivere.local';
        $ok = false;
        if (getenv('MAIL_ENABLED') === '1') $ok = @mail($to, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $corpo, "From: $from\r\nContent-Type: text/plain; charset=UTF-8");
        if (!$ok) @file_put_contents(__DIR__ . '/../../storage/mail.log', "[" . date('c') . "] para=$to | $assunto\n$corpo\n---\n", FILE_APPEND);
    }
}
