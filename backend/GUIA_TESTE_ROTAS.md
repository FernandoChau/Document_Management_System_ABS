# Guia de Teste - Rotas de Pastas e Documentos

## 📋 Visão Geral do Fluxo de Dados

```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEMA DE GESTÃO DOCUMENTAL                 │
└─────────────────────────────────────────────────────────────────┘

1. AUTENTICAÇÃO
   └─> POST /entrar (Login)
       └─> Retorna token Sanctum (Bearer token)

2. CRIAR PASTA (FOLDER)
   └─> POST /pastas
       ├─ Requer: name, parent_id (opcional), department_id
       ├─ Serviço: FolderService->createFolder()
       │   ├─ Gera reference_code (ex: "dt.subfolder")
       │   ├─ Define is_root = true/false
       │   └─ Cria permissão para o criador
       └─ Retorna: Folder com todos os dados

3. LISTAR PASTAS
   └─> GET /pastas?parent_id={id} ou GET /pastas
       ├─ Se parent_id: retorna pasta com filhos e documentos
       └─ Sem parent_id: retorna apenas pastas raiz

4. FAZER UPLOAD DE DOCUMENTOS
   └─> POST /pastas/{folder}/upload
       ├─ Body: formdata com files[]
       ├─ Serviço: DocumentService->uploadFile()
       │   ├─ Calcula número sequencial por pasta/ano
       │   ├─ Gera reference_code (ex: "dt.26.001.document")
       │   ├─ Armazena arquivo em storage/app/documents/
       │   └─ Cria registro Document no BD
       ├─ Auditoria: Registra ação UPLOAD
       └─ Retorna: Array de documentos criados

5. VER DOCUMENTO
   └─> GET /documentos/{document}
       ├─ Retorna documento com conteúdo extraído (eager load)
       ├─ Auditoria: Registra ação VIEW
       └─ Retorna: Document com relacionamento content

6. BAIXAR DOCUMENTO
   └─> GET /documentos/{document}/baixar
       ├─ Auditoria: Registra ação DOWNLOAD
       └─ Retorna: Arquivo para download

7. DELETAR DOCUMENTO (Soft Delete)
   └─> DELETE /documentos/{document}
       ├─ Auditoria: Registra ação SOFT_DELETE
       └─ Retorna: 204 No Content

8. BAIXAR PASTA (ZIP)
   └─> GET /pastas/{folder}/baixar
       └─ Status: 501 Not Implemented
```

---

## 🔐 Autenticação

Todas as rotas de pastas e documentos requerem autenticação.

### Login e obter token:

```bash
POST http://localhost:8000/api/entrar
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Resposta:**

```json
{
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz..."
}
```

Use o token em todas as requisições subsequentes:

```bash
Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz...
```

---

## 📁 ROTAS DE PASTAS (FOLDERS)

### 1. Listar Pastas Raiz

```bash
GET http://localhost:8000/api/pastas
Authorization: Bearer {token}
```

**Resposta (200 OK):**

```json
[
    {
        "id": "123e4567-e89b-12d3-a456-426614174000",
        "name": "Financeiro",
        "parent_id": null,
        "department_id": "dept-001",
        "reference_code": "fin",
        "is_root": true,
        "created_at": "2026-01-14T10:00:00Z",
        "updated_at": "2026-01-14T10:00:00Z"
    },
    {
        "id": "223e4567-e89b-12d3-a456-426614174000",
        "name": "RH",
        "parent_id": null,
        "department_id": "dept-002",
        "reference_code": "rh",
        "is_root": true,
        "created_at": "2026-01-14T10:00:00Z",
        "updated_at": "2026-01-14T10:00:00Z"
    }
]
```

---

### 2. Ver Pasta com Subpastas e Documentos

```bash
GET http://localhost:8000/api/pastas?parent_id=123e4567-e89b-12d3-a456-426614174000
Authorization: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "name": "Financeiro",
    "parent_id": null,
    "department_id": "dept-001",
    "reference_code": "fin",
    "is_root": true,
    "children": [
        {
            "id": "323e4567-e89b-12d3-a456-426614174000",
            "name": "Faturas 2026",
            "parent_id": "123e4567-e89b-12d3-a456-426614174000",
            "reference_code": "fin.faturas-2026",
            "is_root": false
        }
    ],
    "documents": [
        {
            "id": "doc-123",
            "name": "Planilha Orçamento",
            "reference_code": "fin.26.001.planilha-orcamento",
            "year": 2026,
            "sequence_number": 1,
            "size": 51200,
            "mime_type": "application/vnd.ms-excel",
            "created_at": "2026-01-14T10:00:00Z"
        }
    ]
}
```

---

### 3. Criar Nova Pasta (Raiz)

```bash
POST http://localhost:8000/api/pastas
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Recursos Humanos",
  "department_id": "dept-003"
}
```

**Resposta (201 Created):**

```json
{
    "id": "423e4567-e89b-12d3-a456-426614174000",
    "name": "Recursos Humanos",
    "parent_id": null,
    "department_id": "dept-003",
    "reference_code": "recursos-humanos",
    "is_root": true,
    "created_at": "2026-01-26T14:30:00Z",
    "updated_at": "2026-01-26T14:30:00Z"
}
```

---

### 4. Criar Subpasta (Child Folder)

```bash
POST http://localhost:8000/api/pastas
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Contratos 2026",
  "parent_id": "123e4567-e89b-12d3-a456-426614174000"
}
```

**Resposta (201 Created):**

```json
{
    "id": "523e4567-e89b-12d3-a456-426614174000",
    "name": "Contratos 2026",
    "parent_id": "123e4567-e89b-12d3-a456-426614174000",
    "department_id": null,
    "reference_code": "fin.contratos-2026",
    "is_root": false,
    "created_at": "2026-01-26T14:35:00Z",
    "updated_at": "2026-01-26T14:35:00Z"
}
```

---

### 5. Baixar Pasta como ZIP

```bash
GET http://localhost:8000/api/pastas/123e4567-e89b-12d3-a456-426614174000/baixar
Authorization: Bearer {token}
```

**Resposta (501 Not Implemented):**

```json
{
    "message": "Zip download not implemented yet in this iteration"
}
```

---

## 📄 ROTAS DE DOCUMENTOS (DOCUMENTS)

### 1. Fazer Upload de Documentos (em uma Pasta)

```bash
POST http://localhost:8000/api/pastas/123e4567-e89b-12d3-a456-426614174000/upload
Authorization: Bearer {token}
Content-Type: multipart/form-data

files: [arquivo1.pdf, arquivo2.xlsx, arquivo3.docx]
```

**Fluxo interno:**

1. **Validação**: Cada arquivo max 50MB
2. **Loop** para cada arquivo:
    - Calcula número sequencial (pessimistic lock)
    - Gera reference_code com padrão: `{folder_ref}.{yy}.{seq}.{filename}`
    - Armazena arquivo em: `storage/app/documents/{hash}`
    - Cria registro `Document` no BD
    - Registra auditoria com action "UPLOAD"
3. Dispara Job assíncrono `ExtractDocumentTextJob` (para extrair texto)

**Resposta (201 Created):**

```json
{
    "message": "Upload successful",
    "documents": [
        {
            "id": "doc-001",
            "folder_id": "123e4567-e89b-12d3-a456-426614174000",
            "name": "relatorio-vendas.pdf",
            "file_path": "documents/abc123def456",
            "reference_code": "fin.26.001.relatorio-vendas",
            "mime_type": "application/pdf",
            "size": 204800,
            "year": 2026,
            "sequence_number": 1,
            "user_id": "user-001",
            "created_at": "2026-01-26T14:40:00Z",
            "updated_at": "2026-01-26T14:40:00Z"
        },
        {
            "id": "doc-002",
            "folder_id": "123e4567-e89b-12d3-a456-426614174000",
            "name": "planilha-custos.xlsx",
            "file_path": "documents/xyz789uvw123",
            "reference_code": "fin.26.002.planilha-custos",
            "mime_type": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "size": 102400,
            "year": 2026,
            "sequence_number": 2,
            "user_id": "user-001",
            "created_at": "2026-01-26T14:40:00Z",
            "updated_at": "2026-01-26T14:40:00Z"
        }
    ]
}
```

---

### 2. Ver Documento (com conteúdo extraído)

```bash
GET http://localhost:8000/api/documentos/doc-001
Authorization: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "id": "doc-001",
    "folder_id": "123e4567-e89b-12d3-a456-426614174000",
    "name": "relatorio-vendas.pdf",
    "file_path": "documents/abc123def456",
    "reference_code": "fin.26.001.relatorio-vendas",
    "mime_type": "application/pdf",
    "size": 204800,
    "year": 2026,
    "sequence_number": 1,
    "user_id": "user-001",
    "created_at": "2026-01-26T14:40:00Z",
    "updated_at": "2026-01-26T14:40:00Z",
    "content": {
        "id": "content-001",
        "document_id": "doc-001",
        "extracted_text": "Janeiro de 2026\n\nRelatório de Vendas\n\nTotal Vendido: R$ 150.000,00...",
        "created_at": "2026-01-26T14:45:00Z"
    }
}
```

---

### 3. Baixar Documento (arquivo original)

```bash
GET http://localhost:8000/api/documentos/doc-001/baixar
Authorization: Bearer {token}
```

**Resposta:**

- Status: 200 OK
- Content-Type: Baseado no MIME type (application/pdf, application/vnd.openxml..., etc)
- Body: Arquivo em binary
- Headers incluem: `Content-Disposition: attachment; filename="relatorio-vendas.pdf"`

**Auditoria registrada:**

- Ação: "DOWNLOAD"
- Usuário: Autenticado
- Recurso: document_id
- Timestamp: Automático

---

### 4. Deletar Documento (Soft Delete)

```bash
DELETE http://localhost:8000/api/documentos/doc-001
Authorization: Bearer {token}
```

**Resposta (204 No Content):**
(Sem body)

**Auditoria registrada:**

- Ação: "SOFT_DELETE"
- Recurso: Document
- Timestamp: Automático

**Nota:**

- Soft delete apenas marca `deleted_at` no BD
- Arquivo permanece em storage
- Documento não aparece em listagens normais (exceto com `withTrashed()`)

---

## 🔍 Auditoria - Fluxo de Registro

Toda ação de usuário é registrada na tabela `audit_logs`:

```
┌──────────────┐
│  AuditLogger │
└────────┬─────┘
         │
    Registra:
    ├─ user_id (quem fez)
    ├─ action (VIEW, DOWNLOAD, UPLOAD, SOFT_DELETE)
    ├─ resource_type (Document, Folder)
    ├─ resource_id (ID do documento/pasta)
    ├─ metadata (contexto adicional)
    └─ created_at (timestamp automático)
```

**Exemplo de audit_log:**

```json
{
    "id": 1,
    "user_id": "user-001",
    "action": "UPLOAD",
    "resource_type": "Document",
    "resource_id": "doc-001",
    "metadata": {},
    "created_at": "2026-01-26T14:40:00Z"
}
```

---

## 📊 Resumo dos Campos

### **Folder**

| Campo          | Tipo      | Descrição                                 |
| -------------- | --------- | ----------------------------------------- |
| id             | UUID      | Identificador único                       |
| name           | string    | Nome da pasta                             |
| parent_id      | UUID      | Referência ao pai (null = raiz)           |
| department_id  | UUID      | Departamento (apenas raízes)              |
| reference_code | string    | Código único gerado (ex: "fin.contratos") |
| is_root        | boolean   | true = pasta raiz, false = subpasta       |
| created_at     | timestamp | Data de criação                           |
| updated_at     | timestamp | Última atualização                        |

### **Document**

| Campo           | Tipo      | Descrição                                 |
| --------------- | --------- | ----------------------------------------- |
| id              | UUID      | Identificador único                       |
| folder_id       | UUID      | Pasta contendo o documento                |
| name            | string    | Nome original do arquivo                  |
| file_path       | string    | Caminho no storage                        |
| reference_code  | string    | Código único (ex: "fin.26.001.relatorio") |
| mime_type       | string    | Tipo MIME do arquivo                      |
| size            | integer   | Tamanho em bytes                          |
| year            | integer   | Ano do upload (para sequência)            |
| sequence_number | integer   | Número sequencial por pasta/ano           |
| user_id         | UUID      | Usuário que fez upload                    |
| deleted_at      | timestamp | NULL se ativo, preenchido se deletado     |
| created_at      | timestamp | Data de criação                           |
| updated_at      | timestamp | Última atualização                        |

### **DocumentContent** (Conteúdo extraído)

| Campo          | Tipo      | Descrição                    |
| -------------- | --------- | ---------------------------- |
| id             | UUID      | Identificador único          |
| document_id    | UUID      | Documento associado          |
| extracted_text | text      | Texto extraído (OCR/parsing) |
| created_at     | timestamp | Data de criação              |

---

## 🛠️ Teste Completo (Passo a Passo)

### Passo 1: Login

```bash
curl -X POST http://localhost:8000/api/entrar \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

Salve o `token` retornado.

### Passo 2: Listar Pastas

```bash
curl -X GET http://localhost:8000/api/pastas \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Copie um `id` de pasta raiz (ex: `123e4567-e89b-12d3-a456-426614174000`).

### Passo 3: Ver Subpastas e Documentos

```bash
curl -X GET "http://localhost:8000/api/pastas?parent_id=123e4567-e89b-12d3-a456-426614174000" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Passo 4: Criar Nova Subpasta

```bash
curl -X POST http://localhost:8000/api/pastas \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Minha Pasta de Teste",
    "parent_id": "123e4567-e89b-12d3-a456-426614174000"
  }'
```

Salve o `id` da nova pasta.

### Passo 5: Fazer Upload de Documentos

```bash
curl -X POST http://localhost:8000/api/pastas/NOVA_PASTA_ID/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "files=@documento1.pdf" \
  -F "files=@documento2.xlsx"
```

Copie um `document_id` retornado (ex: `doc-001`).

### Passo 6: Ver Documento

```bash
curl -X GET http://localhost:8000/api/documentos/doc-001 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Passo 7: Baixar Documento

```bash
curl -X GET http://localhost:8000/api/documentos/doc-001/baixar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o documento_baixado.pdf
```

### Passo 8: Deletar Documento

```bash
curl -X DELETE http://localhost:8000/api/documentos/doc-001 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🚨 Códigos de Erro

| Código | Situação                                        |
| ------ | ----------------------------------------------- |
| 200    | OK - Sucesso                                    |
| 201    | Created - Recurso criado                        |
| 204    | No Content - Deletado com sucesso               |
| 400    | Bad Request - Validação falhou                  |
| 401    | Unauthorized - Token inválido ou ausente        |
| 403    | Forbidden - Sem permissão                       |
| 404    | Not Found - Recurso não encontrado              |
| 422    | Unprocessable Entity - Dados inválidos          |
| 501    | Not Implemented - Função ainda não implementada |

---

## 💡 Notas Importantes

1. **Transações**: `uploadFile()` e `createFolder()` usam transações BD para garantir integridade.
2. **Lock Pessimista**: Ao calcular sequência, o número da pasta é "travado" evitando duplicatas em concorrência.
3. **Soft Delete**: Documentos não são fisicamente removidos, apenas marcados como deletados.
4. **Auditoria**: Toda ação é registrada com timestamp, usuário e tipo de recurso.
5. **Extração de Texto**: Job assíncrono `ExtractDocumentTextJob` executa após upload (ainda não implementado).
6. **Reference Code**: Gerado automaticamente seguindo padrão hierárquico (ex: `fin.contratos.26.001.relatorio`).
