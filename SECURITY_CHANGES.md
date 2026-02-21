# Mudanças de Segurança: Migração de localStorage para Cookies HttpOnly

## 🔒 Resumo das Mudanças

O sistema foi atualizado para usar **cookies HttpOnly, Secure e SameSite** em vez de armazenar tokens no `localStorage`. Esta é uma mudança crítica de segurança que mitiga ataques XSS.

---

## ❌ Por que localStorage é perigoso?

```javascript
// ❌ localStorage é vulnerável a XSS
// Um atacante pode fazer isso:
localStorage.getItem("dms_access_token"); // Acesso direto ao token!
```

Se um atacante conseguir injetar JavaScript no site (via XSS), ele pode:

- Roubar o token diretamente do localStorage
- Fazer requisições em nome do usuário
- Acessar dados sensíveis

---

## ✅ Por que cookies HttpOnly são melhores?

```javascript
// ✅ Cookies HttpOnly não podem ser acessados por JavaScript
document.cookie; // Não inclui cookies HttpOnly!
```

**Atributos do Cookie:**

- **HttpOnly**: Bloqueia acesso por JavaScript (mitiga XSS)
- **Secure**: Cookie é enviado apenas em conexões HTTPS
- **SameSite=Lax**: Protege contra ataques CSRF

O navegador envia automaticamente o cookie no header `Cookie:` com cada requisição.

---

## 🔄 Como Funciona Agora?

### 1. **Login (POST /api/login)**

**Antes:**

```json
{
  "message": "Login efetuado com sucesso",
  "token": "1|KxZ9mP...", ← Token no JSON (inseguro)
  "user": { ... }
}
```

**Agora:**

```
Set-Cookie: api_token=1|KxZ9mP...; HttpOnly; Secure; SameSite=Lax; Path=/; Max-Age=86400
```

O frontend recebe apenas os dados do usuário, **não o token**. O navegador armazena o cookie automaticamente.

### 2. **Requisições Subsequentes**

O navegador envia automaticamente:

```
GET /api/user/profile
Cookie: api_token=1|KxZ9mP...
```

O Express/Laravel valida o token a partir do cookie (não do header).

### 3. **Logout (POST /api/logout)**

```
Set-Cookie: api_token=; Max-Age=-1; HttpOnly; Secure; SameSite=Lax
```

Cookie expirado = removido do navegador.

---

## 📝 Arquivos Modificados

### Frontend

#### [src/api/axios.ts](src/api/axios.ts)

**Mudanças:**

- ❌ Removido: `getStoredToken()`, `setStoredToken()`, `clearStoredToken()`
- ❌ Removido: `ACCESS_TOKEN_KEY`
- ❌ Removido: Interceptador de requisição que adicionava o token manualmente
- ✅ Adicionado: `withCredentials: true` para enviar cookies em requisições cross-origin

**Por quê?**

- JavaScript não precisa mais gerenciar o token
- O navegador envia o cookie automaticamente
- Reduz a superfície de ataque (menos código que manipula tokens)

#### [src/context/AuthContext.tsx](src/context/AuthContext.tsx)

**Mudanças:**

- ❌ Removido: Imports de `setStoredToken`, `getStoredToken`, `clearStoredToken`
- ✅ Modificado: `login()` - não armazena token, apenas atualiza state do usuário
- ✅ Modificado: `clearSession()` - não tenta limpar localStorage
- ✅ Modificado: `useEffect` - tenta carregar perfil sempre (cookie é validado automaticamente)

**Por quê?**

- Simplifica a lógica de autenticação
- Token é gerenciado exclusivamente pelo navegador
- Elimina pontos de vulnerabilidade no frontend

### Backend (Laravel)

#### [app/Http/Controllers/Api/User/Auth/LoginController.php](backend/app/Http/Controllers/Api/User/Auth/LoginController.php)

**Mudanças:**

- ✅ Token ainda é criado com `createToken()`
- ❌ Token NÃO é mais retornado no JSON da resposta
- ✅ Adicionado: Cookie HttpOnly com o token

**Configuração do Cookie:**

```php
$response->cookie(
    name: 'api_token',
    value: $token,
    minutes: 60 * 24, // 24 horas
    path: '/',
    secure: true,      // ✅ Apenas HTTPS
    httpOnly: true,    // ✅ JavaScript não consegue acessar
    sameSite: 'lax'    // ✅ Proteção CSRF
);
```

#### [app/Http/Controllers/Api/User/Auth/LogoutController.php](backend/app/Http/Controllers/Api/User/Auth/LogoutController.php)

**Mudanças:**

- ✅ Adicionado: Resposta com cookie expirado para remover do navegador

**Configuração:**

```php
$response->cookie(
    name: 'api_token',
    value: '',
    minutes: -1, // ⏰ Expira imediatamente
    ...
);
```

---

## ⚙️ Requisitos de Configuração

### 1. **CORS com Credenciais** (CRÍTICO!)

⚠️ **ESTE É O ERRO QUE VOCÊ ENFRENTOU!**

Quando você usa `withCredentials: true` (para enviar cookies), o servidor **não pode usar `Access-Control-Allow-Origin: *`**. Precisa especificar um domínio exato.

#### O Erro

```
Access to XMLHttpRequest at 'http://localhost:8000/api/entrar' from origin 'http://localhost:5173'
has been blocked by CORS policy: Response to preflight request doesn't pass access control check:
The value of the 'Access-Control-Allow-Origin' header in the response must not be the wildcard '*'
when the request's credentials mode is 'include'.
```

#### A Solução

Foi criado um middleware customizado: **[EnableCorsWithCredentials.php](backend/app/Http/Middleware/EnableCorsWithCredentials.php)**

**O middleware:**

```php
// ✅ Especifica domínio exato (não wildcard)
Access-Control-Allow-Origin: http://localhost:5173

// ✅ Permite credenciais (cookies)
Access-Control-Allow-Credentials: true

// ✅ Permite os métodos necessários
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS

// ✅ Permite headers necessários
Access-Control-Allow-Headers: Accept, Authorization, Content-Type, X-CSRF-Token
```

#### Configuração em Produção

**Em [bootstrap/app.php](backend/bootstrap/app.php):**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(append: [
        \App\Http\Middleware\EnableCorsWithCredentials::class,
    ]);
})
```

**Alterar domínios permitidos (depende do seu ambiente):**

```php
$allowedOrigins = [
    'http://localhost:5173',  // Desenvolvimento
    'https://seu-dominio.com', // Produção
    // Adicione seus domínios aqui
];
```

### 2. **HTTPS em Produção** (OBRIGATÓRIO)

Cookies com `Secure=true` só funcionam em HTTPS.

```
✅ Em desenvolvimento: localhost funciona sem HTTPS (navegador permite)
⚠️ Em produção: HTTPS é OBRIGATÓRIO
```

**Verificar configuração:**

```php
// Em LoginController e LogoutController
$response->cookie(
    name: 'api_token',
    ...
    secure: true,  // ← Apenas HTTPS em produção
    httpOnly: true,
    sameSite: 'lax'
);
```

---

## 🧪 Como Testar

### 1. **Verificar se o Cookie é Definido**

No navegador:

1. Abrir DevTools (F12)
2. Ir para tab "Application" > "Cookies"
3. Após login, procurar por cookie `api_token`
4. Confirmar atributos: `HttpOnly`, `Secure`, `SameSite`

### 2. **Verificar se o Navegador Envia o Cookie**

1. Abrir DevTools > "Network"
2. Fazer uma requisição (ex: GET /api/user/profile)
3. Clicar na requisição
4. No tab "Cookies" -> deve mostrar `api_token` sendo enviado

### 3. **Verificar que JavaScript Não Consegue Acessar**

```javascript
// No console:
console.log(document.cookie);
// ❌ Não incluirá api_token (está protegido)
```

---

## 🔍 Diferenças Resumidas

| Aspecto                 | localStorage      | Cookies HttpOnly |
| ----------------------- | ----------------- | ---------------- |
| Acessível por JS        | ✅ Sim (PERIGOSO) | ❌ Não (SEGURO)  |
| Protección XSS          | ❌ Nenhuma        | ✅ Excelente     |
| Enviado automaticamente | ❌ Manual         | ✅ Automático    |
| Proteção CSRF           | ❌ Nenhuma        | ✅ SameSite      |
| Apenas HTTPS            | ❌ Não            | ✅ Sim (Secure)  |
| Complexidade frontend   | ⚠️ Alta           | ✅ Baixa         |

---

## ⚠️ Notas Importantes

1. **Desenvolvimento Local**: Em localhost, o navegador pode permitir cookies sem HTTPS. Em produção, é obrigatório.

2. **Same-Site Cookies**: Se frontend e backend estão em domínios diferentes:
   - Use `SameSite=None; Secure` no backend
   - Frontedn com `withCredentials: true`

3. **Validação no Backend**: O Laravel/Sanctum deve validar o token do cookie automaticamente. Se usar custom middleware, certifique-se de ler o token do cookie:

```php
$token = $request->cookie('api_token');
// Validar token...
```

4. **Refresh de Token**: Se implementar refresh tokens, seguir o mesmo padrão (cookie HttpOnly).

---

## 📚 Referências de Segurança

- [OWASP: XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [OWASP: CSRF Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [MDN: HttpOnly Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#httponlyoptional)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)
