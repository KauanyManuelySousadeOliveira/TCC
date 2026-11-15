#!/usr/bin/env bash
# Teste de fumaça ponta a ponta (PHP embutido + SQLite). Uso: bash tests/smoke.sh
set -u; cd "$(dirname "$0")/.."
export DB_DRIVER=sqlite DB_SQLITE=/tmp/vivere_test.sqlite APP_DEBUG=1
rm -f $DB_SQLITE storage/mail.log; python3 tests/make_sqlite.py /tmp/schema_sqlite.sql
php -r 'require "app/bootstrap.php"; Database::pdo()->exec(file_get_contents("/tmp/schema_sqlite.sql"));' && php database/seed.php >/dev/null
php -S 127.0.0.1:8099 -t public public/index.php >/tmp/php-server.log 2>&1 & SRV=$!; sleep 1
B=http://127.0.0.1:8099; PASS=0; FAIL=0
ok(){ if [ "$2" = "1" ]; then PASS=$((PASS+1)); echo "  ✔ $1"; else FAIL=$((FAIL+1)); echo "  ✘ $1"; fi; }
tok(){ curl -s -b $1 -c $1 "$B$2" | grep -o 'name="_csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//'; }
login(){ J=$1; T=$(tok $J /login); curl -s -b $J -c $J -o /dev/null -w '%{http_code} %{redirect_url}' -d "_csrf=$T&email=$2&senha=$3" $B/login; }
has(){ grep -q "$2" <<<"$1" && echo 1 || echo 0; }
echo "Autenticação"
ok "rota protegida redireciona p/ login" $(curl -s -o /dev/null -w '%{redirect_url}' $B/paciente | grep -q /login && echo 1 || echo 0)
r=$(login /tmp/jp ana.souza@email.com errada); ok "senha errada rejeitada" $(has "$r" '/login')
r=$(login /tmp/jp ana.souza@email.com vivere123); ok "login paciente" $(has "$r" '/paciente')
ok "CSRF bloqueia POST sem token" $([ "$(curl -s -b /tmp/jp -o /dev/null -w '%{http_code}' -d x=1 $B/logout)" = 419 ] && echo 1 || echo 0)
echo "Paciente"
h=$(curl -s -b /tmp/jp $B/paciente); ok "dashboard paciente" $(has "$h" 'Olá, Ana')
D=$(date -d 'next monday' +%F); [ $(date +%u) -lt 5 ] && D=$(date -d tomorrow +%F); [ $(date +%u) -ge 6 ] && D=$(date -d 'next monday' +%F)
T=$(tok /tmp/jp "/paciente/agendar?med=3&data=$D")
c=$(curl -s -b /tmp/jp -c /tmp/jp -o /dev/null -w '%{redirect_url}' -d "_csrf=$T&med=3&data=$D&hora=15:00" $B/paciente/agendar); ok "agendar consulta" $(has "$c" '/paciente/consultas')
h=$(curl -s -b /tmp/jp "$B/paciente/agendar?med=3&data=$D"); ok "horário ocupado aparece riscado" $(has "$h" 'slot taken">15:00')
curl -s -b /tmp/jp -c /tmp/jp -d "_csrf=$T&med=3&data=$D&hora=15:00" $B/paciente/agendar -o /dev/null
n=$(php -r 'require "app/bootstrap.php"; echo Database::val("SELECT COUNT(*) FROM consultas WHERE medico_id=3 AND data=? AND hora=?",["'$D'","15:00"]);'); ok "conflito de horário impedido (RF30)" $([ "$n" = 1 ] && echo 1 || echo 0)
h=$(curl -s -b /tmp/jp $B/paciente/historico); ok "histórico mostra prontuário" $(has "$h" 'dermatite')
echo "Segurança de perfis"
ok "paciente não acessa /medico" $([ "$(curl -s -b /tmp/jp -o /dev/null -w '%{http_code}' $B/medico)" = 403 ] && echo 1 || echo 0)
echo "Médico"
r=$(login /tmp/jm rafael@vivere.demo vivere123); ok "login médico" $(has "$r" '/medico')
h=$(curl -s -b /tmp/jm $B/medico/pacientes); ok "lista pacientes do médico" $(has "$h" 'Ana Beatriz')
T=$(tok /tmp/jm /medico/pacientes/1)
curl -s -b /tmp/jm -o /dev/null -d "_csrf=$T&queixa=Dor de cabeça&evolucao=Orientado repouso" $B/medico/pacientes/1/prontuario
h=$(curl -s -b /tmp/jm $B/medico/pacientes/1); ok "evolução registrada no prontuário" $(has "$h" 'Orientado repouso')
ok "médico não vê paciente alheio" $([ "$(curl -s -b /tmp/jm -o /dev/null -w '%{http_code}' $B/medico/pacientes/5)" = 403 ] && echo 1 || echo 0)
T=$(tok /tmp/jm /medico/agenda); curl -s -b /tmp/jm -o /dev/null -d "_csrf=$T&data=$D&hora=10:00" $B/medico/agenda/bloquear
h=$(curl -s -b /tmp/jm "$B/medico/agenda?data=$D"); ok "bloqueio de horário" $(has "$h" 'title="Clique para liberar"')
echo "Recepção"
r=$(login /tmp/jr carla@vivere.demo vivere123); ok "login recepcionista" $(has "$r" '/recepcao')
h=$(curl -s -b /tmp/jr "$B/recepcao/relatorios?de=2020-01-01&ate=2099-12-31"); ok "relatório com taxa de comparecimento" $(has "$h" 'Taxa de comparecimento')
h=$(curl -s -b /tmp/jr "$B/recepcao/relatorios.csv?de=2020-01-01&ate=2099-12-31"); ok "exportação CSV" $(has "$h" 'Paciente;Médico')
T=$(tok /tmp/jr /recepcao/pacientes)
h=$(curl -s -b /tmp/jr -d "_csrf=$T&nome=Maria Teste Silva&cpf=529.982.247-25&telefone=(14) 99999-0000&email=maria@teste.com&senha=senha1234" $B/recepcao/pacientes -o /dev/null -w '%{redirect_url}'); ok "cadastro de paciente válido" $(has "$h" '/recepcao/pacientes')
h=$(curl -s -b /tmp/jr -d "_csrf=$T&nome=Outro Nome Aqui&cpf=111.111.111-11&telefone=(14) 99999-0000&email=x@teste.com&senha=senha1234" $B/recepcao/pacientes); ok "CPF inválido rejeitado" $(has "$h" 'CPF inválido')
h=$(curl -s -b /tmp/jr "$B/recepcao/pacientes?q=Maria"); ok "busca de paciente" $(has "$h" 'Maria Teste')
echo "Recuperação de senha"
T=$(tok /tmp/jx /esqueci-senha); curl -s -b /tmp/jx -o /dev/null -d "_csrf=$T&email=maria@teste.com" $B/esqueci-senha
L=$(grep -o 'http://[^ ]*redefinir-senha?token=[a-f0-9]*' storage/mail.log | tail -1); ok "e-mail de recuperação gerado" $([ -n "$L" ] && echo 1 || echo 0)
TK=${L##*token=}; T=$(tok /tmp/jx "/redefinir-senha?token=$TK"); curl -s -b /tmp/jx -o /dev/null -d "_csrf=$T&token=$TK&senha=novasenha99" $B/redefinir-senha
r=$(login /tmp/jy maria@teste.com novasenha99); ok "login com nova senha" $(has "$r" '/paciente')
kill $SRV 2>/dev/null; rm -f /tmp/jp /tmp/jm /tmp/jr /tmp/jx /tmp/jy
echo; echo "Resultado: $PASS ok, $FAIL falhas"; [ $FAIL -eq 0 ]
