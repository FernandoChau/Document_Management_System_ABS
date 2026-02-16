# 📚 Resumo Executivo - Document Management System

## O que você tem?

Um sistema **Laravel REST API** completo para gerenciar documentos em uma hierarquia de pastas, com autenticação, auditoria e uploads em lote.

---

## 🎯 Funcionalidades Principais

### 1️⃣ Pastas Hierárquicas

- **Pastas Raiz**: Associadas a departamentos (financeiro, RH, etc)
- **Subpastas**: Árvore de diretórios aninhada
- **Reference Code**: Identificadores únicos e legíveis
    - Raiz: `fin` (financeiro)
    - Subpasta: `fin.contratos` (contratos dentro de financeiro)
    - Subpasta aninhada: `fin.contratos.2026` (2026 dentro de contratos)

### 2️⃣ Upload de Documentos

- **Batch Upload**: Múltiplos arquivos por requisição
- **Limite**: 50MB por arquivo
- **Reference Code Automático**:
    - Formato: `{pasta}.{ano}.{sequencia}.{nome}`
    - Exemplo: `fin.contratos.26.001.contrato-xyz`
- **Sequência Atômica**: Lock pessimista evita duplicatas
- **Armazenamento Seguro**:
    - Nomes aleatórios (hash)
    - Fora do web root
    - Não acessível via URL direta

### 3️⃣ Operações em Documentos

- **Ver**: GET /documentos/{id} (com conteúdo extraído)
- **Baixar**: GET /documentos/{id}/baixar (arquivo original)
- **Deletar**: DELETE /documentos/{id} (soft delete = marcado como deletado)

### 4️⃣ Auditoria Completa

Toda ação é registrada:

- ✅ Quem fez (user_id)
- ✅ O quê (action: VIEW, DOWNLOAD, UPLOAD, CREATE, SOFT_DELETE)
- ✅ Em quê (resource_type: Document/Folder, resource_id)
- ✅ Quando (timestamp automático)

---

## 📊 Fluxo Básico (3 passos)

```
1. LOGIN
   POST /entrar {email, password}
   ← RECEBE: token

2. CRIAR PASTA
   POST /pastas {name, department_id}
   ← RECEBE: folder id

3. UPLOAD
   POST /pastas/{folder_id}/upload files[]
   ← RECEBE: documents array
```

---

## 📁 Arquivos Criados para Teste

| Arquivo                      | Descrição                                               |
| ---------------------------- | ------------------------------------------------------- |
| **GUIA_TESTE_ROTAS.md**      | Documentação completa de todas as rotas com exemplos    |
| **FLUXO_DADOS_DETALHADO.md** | Explicação técnica do fluxo interno, transações e locks |
| **ARQUITETURA_VISUAL.md**    | Diagramas ASCII da arquitetura, segurança, concorrência |
| **test_api.sh**              | Script bash completo para teste (Linux/Mac)             |
| **test_api.ps1**             | Script PowerShell para teste (Windows)                  |
| **postman_collection.json**  | Collection Postman com todas as rotas pré-configuradas  |

---

## 🚀 Como Começar a Testar

### Opção 1: Postman (Visual/Interativo)

```
1. Importe: postman_collection.json
2. Configure environment:
   - base_url: http://localhost:8000/api
   - token: (será preenchido automaticamente)
3. Clique em "Login" → token salvo
4. Teste outras rotas sequencialmente
```

### Opção 2: PowerShell (Windows)

```powershell
cd d:\Document_Management_System_ABS
powershell -ExecutionPolicy Bypass -File test_api.ps1
```

### Opção 3: Bash (Linux/Mac)

```bash
cd /path/to/project
bash test_api.sh
```

### Opção 4: Manual com cURL

```bash
# Login
curl -X POST http://localhost:8000/api/entrar \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Ver pastas (substitua TOKEN)
curl -X GET http://localhost:8000/api/pastas \
  -H "Authorization: Bearer TOKEN"
```

---

## 🔄 Fluxo de Dados Resumido

```
REQUEST ENTRA
    │
    ▼
CONTROLADOR (Valida, Autoriza)
    │
    ▼
SERVIÇO (Lógica, Transações, Locks)
    │
    ▼
BANCO DE DADOS (Insere/Atualiza)
    │
    ▼
STORAGE (Salva arquivo físico)
    │
    ▼
AUDITORIA (Registra ação)
    │
    ▼
RESPONSE (JSON)
```

---

## 🔐 Segurança

✅ **Autenticação**: Laravel Sanctum (Bearer Token)
✅ **Autorização**: Gates + Policies + FolderPermission
✅ **Armazenamento**: Arquivos com nomes aleatórios, fora do web root
✅ **Auditoria**: Imutável, não deletada
✅ **Concorrência**: Pessimistic locks evitam race conditions
✅ **Transações**: Garante integridade (rollback automático em erro)

---

## 📊 Modelos de Dados

### Folder

```json
{
    "id": "uuid",
    "name": "Contratos 2026",
    "parent_id": "uuid ou null",
    "department_id": "uuid ou null",
    "reference_code": "fin.contratos-2026",
    "is_root": false,
    "created_at": "2026-01-26T14:35:00Z"
}
```

### Document

```json
{
    "id": "uuid",
    "folder_id": "uuid",
    "name": "contrato-xyz.pdf",
    "file_path": "documents/abc123def456",
    "reference_code": "fin.contratos-2026.26.001.contrato-xyz",
    "mime_type": "application/pdf",
    "size": 204800,
    "year": 2026,
    "sequence_number": 1,
    "user_id": "uuid",
    "deleted_at": null,
    "created_at": "2026-01-26T14:40:00Z"
}
```

### AuditLog

```json
{
    "id": 1,
    "user_id": "uuid",
    "action": "UPLOAD",
    "resource_type": "Document",
    "resource_id": "uuid",
    "metadata": {},
    "created_at": "2026-01-26T14:40:00Z"
}
```

---

## 💡 Conceitos-Chave Explicados

### Reference Code

Identificador único legível que monta hierarquicamente:

- `fin` (pasta raiz de Financeiro)
- `fin.contratos` (subpasta dentro de Financeiro)
- `fin.contratos.26.001.contrato-xyz` (documento sequenciado)

### Pessimistic Lock

Trava linha no banco enquanto calcula sequência:

```
User A: SELECT MAX(seq) FROM documents ... FOR UPDATE
        ↓ (Consegue lock, calcula seq=6)
        INSERT document (seq=6)
        COMMIT (libera lock)

User B: SELECT MAX(seq) FROM documents ... FOR UPDATE
        ↓ (Aguarda lock de User A)
        ↓ (Consegue lock, vê novo max=6, calcula seq=7)
        INSERT document (seq=7)
        COMMIT (libera lock)
```

### Soft Delete

Marca como deletado sem remover:

```
DELETE /documentos/{id}
  ↓
UPDATE documents SET deleted_at = NOW()
  ↓
✅ Arquivo permanece em storage
✅ Dado não aparece em queries normais
✅ Pode ser "restaurado": SET deleted_at = NULL
```

### Eager Loading

Busca dados relacionados na mesma query:

```
GET /documentos/{id}
  ↓
$document->load('content')  ← Já carrega DocumentContent
  ↓
Retorna: { document, content: { extracted_text: "..." } }
```

---

## 🎯 Próximos Passos

### Implementar em Produção

- [ ] HTTPS/TLS
- [ ] Rate limiting
- [ ] Validação de permissões por pasta
- [ ] Implementar Job de OCR (ExtractDocumentTextJob)
- [ ] Implementar download de pasta como ZIP
- [ ] Backup automático

### Melhorias Futuras

- [ ] Full-text search em conteúdo extraído
- [ ] Versionamento de documentos
- [ ] Compartilhamento de pastas
- [ ] Notificações em tempo real
- [ ] Mobile app

---

## 📞 Endpoints Resumo

| Ação                    | Método | Rota                    | Autenticado |
| ----------------------- | ------ | ----------------------- | ----------- |
| Login                   | POST   | /entrar                 | ❌          |
| Listar pastas           | GET    | /pastas                 | ✅          |
| Criar pasta             | POST   | /pastas                 | ✅          |
| Ver pasta com subpastas | GET    | /pastas?parent_id={id}  | ✅          |
| Upload documentos       | POST   | /pastas/{id}/upload     | ✅          |
| Ver documento           | GET    | /documentos/{id}        | ✅          |
| Baixar documento        | GET    | /documentos/{id}/baixar | ✅          |
| Deletar documento       | DELETE | /documentos/{id}        | ✅          |
| Baixar pasta ZIP        | GET    | /pastas/{id}/baixar     | ✅ (501)    |

---

## ✨ Destaques da Implementação

✅ **Transações ACID**: Garante consistência em caso de erro
✅ **Locks Pessimistas**: Evita race conditions em alta concorrência
✅ **Soft Deletes**: Mantém auditoria, permite recuperação
✅ **Eager Loading**: Otimiza queries (sem N+1 problems)
✅ **Batch Upload**: Múltiplos arquivos por requisição
✅ **Reference Codes Hierárquicos**: Legível e único
✅ **Auditoria Imutável**: Log de cada ação do usuário
✅ **Armazenamento Seguro**: Nomes aleatórios, fora do web root

---

## 📖 Documentação Gerada

Você recebeu 6 documentos completos:

1. **GUIA_TESTE_ROTAS.md** - Como testar cada rota
2. **FLUXO_DADOS_DETALHADO.md** - Como funciona internamente
3. **ARQUITETURA_VISUAL.md** - Diagramas e fluxos
4. **test_api.ps1** - Script automático (Windows)
5. **test_api.sh** - Script automático (Linux/Mac)
6. **postman_collection.json** - Collection Postman

**Comece lendo:** `GUIA_TESTE_ROTAS.md` para overview
**Então teste:** Use `test_api.ps1` ou `postman_collection.json`
**Se quiser detalhes:** Leia `FLUXO_DADOS_DETALHADO.md` e `ARQUITETURA_VISUAL.md`

---

## 🎊 Conclusão

Seu sistema é bem arquitetado, seguro e escalável. A estrutura com:

- Controllers especializados
- Services com lógica de negócio
- Transações atômicas
- Locks para concorrência
- Auditoria completa

...garante que você pode crescer com confiança!

Agora é só testar com os scripts fornecidos e começar a usar. 🚀
