<?php $f = flash(); ?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Vívere SGC</title>
<link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/extra.css"></head>
<body><div class="login-screen"><div class="login-box" style="max-width:480px">
<div class="login-hero"><div class="brand-name" style="display:flex;align-items:center;gap:10px;justify-content:center"><div class="pulse-wrap"><svg viewBox="0 0 120 24"><polyline class="pulse-line" points="0,12 22,12 28,3 34,21 40,12 46,12 120,12"/></svg></div> Vívere SGC</div><div class="brand-tag">Clínica Vívere — cuidado integral, todos os dias</div></div>
<?php if ($f): ?><div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
<?= $content ?></div></div></body></html>
