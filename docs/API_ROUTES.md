# Rotas da API (Visão geral) 🇵🇹

> Todas as mensagens visíveis ao utilizador estão em **Português**.
> Os nomes das variáveis e parâmetros continuam em **Inglês** para desenvolvimento (ex.: `email`, `password`).

---

## Autenticação (público)

- POST /autenticacao/registar
    - O utilizador cria conta e fornece `password` (hash será guardado).
    - Body: { name, email, password }
    - Observação: conta criada via registo fica com `is_active = false` até ativação pelo Admin.

- POST /autenticacao/entrar
    - Login com `email` e `password`. Retorna 403 se a conta não está ativa (`is_active = false`) ou se o utilizador ainda não definiu password inicial (no caso de conta criada pelo Admin).
    - Body: { email, password }

- POST /autenticacao/recuperar-senha
    - Envia email com link/token para redefinição de password.
    - Body: { email }

- POST /autenticacao/redefinir-senha
    - Usa token enviado por email para definir uma nova password. Em caso de sucesso, `has_authenticated` será definido como `true`.
    - Body: { token, email, password, password_confirmation }

## Utilizadores (Admin)

- GET /utilizadores
    - Lista todos os utilizadores.

- GET /utilizadores/{id}
    - Mostra dados do utilizador.

- POST /utilizadores
    - Admin cria utilizador (sem `password`). O sistema envia automaticamente um email para que o utilizador defina a password (via `/autenticacao/redefinir-senha`).
    - Body: { name, email, role, phone, profession }

- PUT /utilizadores/{id}
    - Atualiza dados do utilizador.

- PUT /utilizadores/{id}/ativar
    - Ativa a conta (`is_active = true`) permitindo login após o utilizador ter `password` definido.

- PUT /utilizadores/{id}/desativar
    - Desativa a conta (`is_active = false`).

## Rotas autenticadas (protegidas)

- Protegidas por middleware: `auth:sanctum` e `check.auth.status` (verifica `is_active` e `has_authenticated`).

- POST /sair
- GET /minha-conta
- POST /atualizar-senha
- POST /definir-senha-inicial

### Autenticação de Dois Fatores

- POST /autenticacao-dois-fatores/ativar
    - Gera código QR e recovery codes para o utilizador ativar 2FA.
    - Response: { qr_code, secret, recovery_codes }

- POST /autenticacao-dois-fatores/confirmar
    - Confirma e guarda o secret do 2FA após validação de código.
    - Body: { code } (6 dígitos do autenticador)
    - Response: { recovery_codes }

- POST /autenticacao-dois-fatores/desativar
    - Desativa 2FA para a conta.

- GET /autenticacao-dois-fatores/estado
    - Retorna o estado atual do 2FA (ativado/desativado e contagem de recovery codes).

- POST /autenticacao-dois-fatores/regenerar-codigos
    - Regenera novos recovery codes.
    - Response: { recovery_codes }

- POST /autenticacao-dois-fatores/verificar
    - Verifica código de 6 dígitos ou recovery code durante login/ações sensíveis.
    - Body: { code } (pode ser código de 6 dígitos ou recovery code)

---

## Fluxo de Dois Fatores explicado

1. **Ativar 2FA**: Utilizador chama `POST /autenticacao-dois-fatores/ativar`
    - Sistema retorna QR code, secret e recovery codes
    - Utilizador escaneia QR code com autenticador (Google Authenticator, Authy, etc)

2. **Confirmar 2FA**: Utilizador chama `POST /autenticacao-dois-fatores/confirmar` com código de 6 dígitos
    - Sistema valida código e guarda recovery codes
    - 2FA agora está ativado para a conta

3. **Verificar 2FA**: Durante login ou ações sensíveis, utilizar `POST /autenticacao-dois-fatores/verificar` com:
    - Código de 6 dígitos (do autenticador), ou
    - Recovery code (para emergências)

4. **Desativar 2FA**: Utilizador chama `POST /autenticacao-dois-fatores/desativar` (requer estar autenticado)

5. **Regenerar Recovery Codes**: `POST /autenticacao-dois-fatores/regenerar-codigos` (quando os códigos estão prestes a esgotar)

---

## Fluxo e explicação passo a passo (autenticação geral)

1. Admin cria utilizador via `POST /utilizadores` (sem password):
    - O sistema grava o utilizador com `is_active = false` e `has_authenticated = false`.
    - O sistema envia automaticamente um email com um link/token para o utilizador definir password (via `/autenticacao/redefinir-senha`).
2. O utilizador abre o link, define password e o sistema define `has_authenticated = true`.
3. O Admin ativa a conta (`PUT /utilizadores/{id}/ativar`) — a partir daqui o utilizador pode iniciar sessão.

Para registos diretos (utilizador final via `/autenticacao/registar`):

- O utilizador define `password` ao criar a conta; contudo a conta fica com `is_active = false` até o Admin ativar.

---

## Notas importantes

- Todas as mensagens para o utilizador estão em Português e centralizadas em `resources/lang/pt/messages.php`.
- Os nomes das rotas agora estão em Português para facilitar a localização pelos utilizadores e pela equipa.
- O Admin nunca tem acesso às passwords dos utilizadores; o fluxo inicial utiliza o mecanismo de reset seguro.
- Se preferir, posso adicionar exemplos cURL e testes automatizados para cada fluxo.
