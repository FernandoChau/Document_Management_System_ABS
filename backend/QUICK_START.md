# 🎬 QUICK START - 5 Minutos

## 1️⃣ Verifique se a API está rodando

```bash
curl http://localhost:8000/api/entrar
```

Se retornar erro de conexão, inicie Laravel:

```bash
php artisan serve
```

---

## 2️⃣ Escolha seu método de teste

### Opção A: PowerShell (Recomendado para Windows)

```powershell
cd d:\Document_Management_System_ABS
powershell -ExecutionPolicy Bypass -File test_api.ps1
```

**Resultado esperado:**

```
✓ Login realizado
✓ Pasta raiz criada
✓ Subpasta criada
✓ 2 documentos enviados
✓ Documento recuperado
✓ Documento baixado
✓ Documento deletado
```

### Opção B: Postman (Mais visual)

1. Abra Postman
2. Clique em `File` → `Import`
3. Selecione `postman_collection.json`
4. Configure as variáveis de ambiente:
    - `base_url`: `http://localhost:8000/api`
    - `token`: (será preenchido automaticamente após login)
5. Clique em "Login" (primeira requisição)
6. Execute as outras rotas na ordem

### Opção C: Bash (Linux/Mac)

```bash
cd /path/to/project
bash test_api.sh
```

### Opção D: cURL Manual

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/entrar \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}' | jq -r '.token')

# 2. Criar pasta
FOLDER=$(curl -s -X POST http://localhost:8000/api/pastas \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Teste","department_id":"dept-001"}' | jq -r '.id')

# 3. Upload (substitua arquivo real)
curl -X POST http://localhost:8000/api/pastas/$FOLDER/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "files=@seu_arquivo.pdf"
```

---

## 📊 O que cada teste faz

```
Login
  ↓ (obtém token)
├─ Listar pastas
├─ Criar pasta raiz
│   ↓
│   └─ Criar subpasta
│       ↓
│       └─ Upload 2 arquivos
│           ↓
│           ├─ Ver documento
│           ├─ Ver pasta com documentos
│           ├─ Baixar documento
│           └─ Deletar documento (soft delete)
```

---

## ✅ Checklist de Testes

- [ ] Login retorna token
- [ ] Listar pastas retorna array
- [ ] Criar pasta retorna folder com id
- [ ] Criar subpasta retorna reference_code hierárquico
- [ ] Upload retorna array com 2 documentos
- [ ] Ver documento retorna conteúdo
- [ ] Baixar documento salva arquivo
- [ ] Deletar documento retorna 204
- [ ] Auditoria registra todas as ações

---

## 🔍 Verificar Auditoria

Depois dos testes, verifique os logs:

```sql
-- Terminal Laravel
php artisan tinker

-- No Tinker:
>>> App\Models\AuditLog::latest()->limit(10)->get();

-- Resultado esperado:
[
  {id: 10, action: "SOFT_DELETE", user_id: "user-001", resource_type: "Document"},
  {id: 9, action: "DOWNLOAD", user_id: "user-001", resource_type: "Document"},
  {id: 8, action: "VIEW", user_id: "user-001", resource_type: "Document"},
  {id: 7, action: "UPLOAD", user_id: "user-001", resource_type: "Document"},
  {id: 6, action: "UPLOAD", user_id: "user-001", resource_type: "Document"},
  {id: 5, action: "CREATE", user_id: "user-001", resource_type: "Folder"},
  {id: 4, action: "CREATE", user_id: "user-001", resource_type: "Folder"},
]
```

---

## 📚 Documentação Detalhada

### Para Entender as Rotas

→ Leia: **GUIA_TESTE_ROTAS.md**

### Para Entender o Fluxo Interno

→ Leia: **FLUXO_DADOS_DETALHADO.md**

### Para Ver Diagramas e Arquitetura

→ Leia: **ARQUITETURA_VISUAL.md**

### Para Testes Completos

→ Use: **test_api.ps1** ou **test_api.sh**

---

## 🎯 Exemplo Prático Passo a Passo

### Com PowerShell:

```powershell
# 1. Login
$response = curl.exe -s http://localhost:8000/api/entrar `
  -H "Content-Type: application/json" `
  -d '{"email":"user@example.com","password":"password123"}' | ConvertFrom-Json

$token = $response.token

# 2. Criar pasta
$folder = curl.exe -s -X POST http://localhost:8000/api/pastas `
  -H "Authorization: Bearer $token" `
  -H "Content-Type: application/json" `
  -d '{"name":"Financeiro 2026","department_id":"dept-001"}' | ConvertFrom-Json

$folder_id = $folder.id
Write-Host "Pasta criada: $folder_id"

# 3. Criar arquivo de teste
"Conteúdo de teste" | Out-File -FilePath C:\temp\teste.txt

# 4. Upload
$form = @{
    files = Get-Item -Path C:\temp\teste.txt
}

$upload = curl.exe -s -X POST http://localhost:8000/api/pastas/$folder_id/upload `
  -H "Authorization: Bearer $token" `
  -F @form | ConvertFrom-Json

$doc_id = $upload.documents[0].id
Write-Host "Documento criado: $doc_id"
Write-Host "Reference Code: $($upload.documents[0].reference_code)"

# 5. Ver documento
curl.exe -s http://localhost:8000/api/documentos/$doc_id `
  -H "Authorization: Bearer $token" | ConvertFrom-Json | Format-Table

# 6. Deletar
curl.exe -s -X DELETE http://localhost:8000/api/documentos/$doc_id `
  -H "Authorization: Bearer $token"

Write-Host "✓ Teste completo!"
```

---

## 🐛 Resolução de Problemas

### Erro: "Connection refused"

**Problema**: API não está rodando
**Solução**:

```bash
php artisan serve
# A API estará em http://localhost:8000
```

### Erro: "Unauthorized" (401)

**Problema**: Token inválido ou não enviado
**Solução**:

1. Faça login novamente
2. Copie o token completo (com "1|")
3. Cole em `Authorization: Bearer TOKEN`

### Erro: "Department not found"

**Problema**: dept_id não existe
**Solução**:

```php
php artisan tinker
>>> App\Models\Department::pluck('id')->first()
# Use este ID no departament_id
```

### Erro: "Validation failed"

**Problema**: Request inválido
**Solução**: Verifique:

- `files`: array não vazio
- `files.*`: cada arquivo < 50MB
- `name`: string, < 255 caracteres
- `parent_id` ou `department_id`: IDs válidos

### Arquivo não baixa

**Problema**: Arquivo não encontrado em storage
**Solução**: Verifique se `storage/app/documents/` existe:

```bash
ls storage/app/documents/
# Ou no Windows:
dir storage\app\documents\
```

---

## 📈 Próximos Passos

1. ✅ **Testes Básicos** (este guia)
2. 📖 **Entender a Arquitetura** (FLUXO_DADOS_DETALHADO.md)
3. 🔧 **Customizar Conforme Necessário**
4. 🚀 **Deploy em Produção**
5. 📊 **Monitorar Auditoria**

---

## 💬 Resumo do que você aprendeu

✨ **Autenticação**: Login retorna token Bearer
✨ **Pastas**: Hierárquicas com reference codes
✨ **Documentos**: Upload batch, download, soft delete
✨ **Segurança**: Locks atômicos, auditoria imutável
✨ **Confiabilidade**: Transações, rollback automático

---

**Pronto para testar? Execute:**

```powershell
cd d:\Document_Management_System_ABS
./test_api.ps1
```

**Divirta-se! 🚀**
