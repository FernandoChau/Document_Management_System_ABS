# 🏗️ Arquitetura e Estrutura - DMS

## 📐 Diagrama de Camadas

```
┌─────────────────────────────────────────────────────────────────┐
│                    CLIENTE (Frontend/Postman)                   │
│                                                                 │
│  Browser / Mobile App / Postman / cURL                         │
└────────────────────┬────────────────────────────────────────────┘
                     │ HTTP/REST
                     │ Bearer Token (Sanctum)
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                         ROTEADOR (Routes)                       │
│                                                                 │
│  routes/api.php                                                 │
│  ├─ POST   /entrar              → LoginController              │
│  ├─ GET    /pastas              → FolderController::index      │
│  ├─ POST   /pastas              → FolderController::store      │
│  ├─ POST   /pastas/{id}/upload  → DocumentController::store    │
│  ├─ GET    /documentos/{id}     → DocumentController::show     │
│  ├─ GET    /documentos/{id}/baixar → DocumentController::download
│  └─ DELETE /documentos/{id}     → DocumentController::destroy  │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────────┐
│                    CONTROLLERS (API Endpoints)                  │
│                                                                 │
│  ┌─────────────────────────┐  ┌──────────────────────────────┐ │
│  │  FolderController       │  │  DocumentController          │ │
│  │                         │  │                              │ │
│  │  • index()              │  │  • store() [Upload]          │ │
│  │  • store() [Create]     │  │  • show() [Retrieve]         │ │
│  │  • download() [Zip]     │  │  • download() [Download]     │ │
│  │                         │  │  • destroy() [Soft Delete]   │ │
│  └────────────┬────────────┘  └────────────┬─────────────────┘ │
│               │                            │                    │
│               └──────────────┬─────────────┘                    │
│                              │                                  │
└──────────────────────────────┼──────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                    SERVICES (Lógica de Negócio)                 │
│                                                                 │
│  ┌─────────────────────────┐  ┌──────────────────────────────┐ │
│  │  FolderService          │  │  DocumentService             │ │
│  │                         │  │                              │ │
│  │  • createFolder()       │  │  • uploadFile()              │ │
│  │    └─ Gera ref code     │  │    └─ Calcula sequência      │ │
│  │    └─ Verifica dept     │  │    └─ Gera ref code          │ │
│  │    └─ Cria perms        │  │    └─ Armazena arquivo       │ │
│  │    └─ Transação         │  │    └─ Cria DocumentContent   │ │
│  │                         │  │    └─ Dispara Job            │ │
│  │                         │  │    └─ Transação              │ │
│  └────────────┬────────────┘  └────────────┬─────────────────┘ │
│               │                            │                    │
│               └──────────────┬─────────────┘                    │
│                              │                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │            AuditLogger (Rastreabilidade)                 │  │
│  │                                                          │  │
│  │  • log(user, action, resource)                           │  │
│  │    └─ Registra VIEW, DOWNLOAD, UPLOAD, DELETE           │  │
│  │    └─ INSERT INTO audit_logs                            │  │
│  └──────────────────────┬───────────────────────────────────┘  │
│                         │                                      │
└─────────────────────────┼──────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                         DATABASE (MySQL/PostgreSQL)             │
│                                                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │   folders    │  │  documents   │  │ audit_logs   │          │
│  │              │  │              │  │              │          │
│  │ • id (PK)    │  │ • id (PK)    │  │ • id (PK)    │          │
│  │ • name       │  │ • folder_id  │  │ • user_id    │          │
│  │ • parent_id  │  │ • name       │  │ • action     │          │
│  │ • is_root    │  │ • file_path  │  │ • resource_id│          │
│  │ • ref_code   │  │ • ref_code   │  │ • created_at │          │
│  │ • dept_id    │  │ • size       │  │              │          │
│  │              │  │ • user_id    │  │              │          │
│  │              │  │ • year       │  │              │          │
│  │              │  │ • seq_number │  │              │          │
│  │              │  │ • deleted_at │  │              │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                 │
│  ┌─────────────────────┐  ┌──────────────────────┐             │
│  │ document_contents   │  │ folder_permissions   │             │
│  │                     │  │                      │             │
│  │ • id (PK)          │  │ • id (PK)           │             │
│  │ • document_id       │  │ • folder_id         │             │
│  │ • extracted_text    │  │ • user_id           │             │
│  │ • created_at        │  │ • permission_level  │             │
│  └─────────────────────┘  └──────────────────────┘             │
└─────────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                   STORAGE (Filesystem)                          │
│                                                                 │
│  /storage/app/documents/                                        │
│  ├─ abc123def456ghi789...  (hash aleatório)                   │
│  ├─ xyz789uvw123pqr456...                                      │
│  ├─ qwe456rty789uio123...                                      │
│  └─ ...                                                         │
│                                                                 │
│  ✓ Arquivos seguros (fora do web root)                         │
│  ✓ Nomes aleatórios (sem exposição)                            │
│  ✓ Soft delete: arquivo permanece, BD marca deleted_at         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Fluxo de Upload Detalhado

```
User faz POST /pastas/{folder}/upload com files

                        ▼

DocumentController::store()
    │
    ├─ Valida request
    │   ├─ files array obrigatório
    │   └─ each file max 50MB
    │
    └─ Loop PARA CADA ARQUIVO:
        │
        ├─► DocumentService::uploadFile(folder, file, user)
        │   │
        │   ├─ BEGIN TRANSACTION
        │   │
        │   ├─ **PASSO 1: Calcula Sequência Atômica**
        │   │   │
        │   │   ├─ SELECT MAX(sequence_number) FROM documents
        │   │   │   WHERE folder_id = ? AND year = 2026
        │   │   │   FOR UPDATE (Pessimistic Lock)
        │   │   │
        │   │   └─ sequence = lastDoc ? lastDoc.seq + 1 : 1
        │   │
        │   ├─ **PASSO 2: Gera Reference Code**
        │   │   │
        │   │   ├─ folder.reference_code     = "fin.contratos"
        │   │   ├─ short_year                = "26"
        │   │   ├─ padded_sequence           = "001"
        │   │   ├─ slugified_filename        = "relatorio-vendas"
        │   │   │
        │   │   └─ reference_code = "fin.contratos.26.001.relatorio-vendas"
        │   │
        │   ├─ **PASSO 3: Armazena Arquivo**
        │   │   │
        │   │   ├─ $path = $file->store('documents', 'local')
        │   │   │   │
        │   │   │   ├─ Cria hash aleatório: abc123def456
        │   │   │   ├─ Escreve em: storage/app/documents/abc123def456
        │   │   │   └─ Retorna: documents/abc123def456
        │   │   │
        │   │   └─ ✅ Arquivo seguro, fora do web root
        │   │
        │   ├─ **PASSO 4: Cria Registro Document**
        │   │   │
        │   │   └─ INSERT INTO documents (
        │   │       folder_id, name, file_path, reference_code,
        │   │       mime_type, size, year, sequence_number, user_id
        │   │     ) VALUES (...)
        │   │
        │   ├─ **PASSO 5: Dispara Job Assíncrono**
        │   │   │
        │   │   └─ ExtractDocumentTextJob::dispatch($document)
        │   │       │
        │   │       ├─ Aguarda na fila (Redis/Database)
        │   │       ├─ Job Worker processa em background
        │   │       ├─ OCR/PDF parsing extrai texto
        │   │       ├─ INSERT INTO document_contents (
        │   │       │   document_id, extracted_text
        │   │       │ )
        │   │       │
        │   │       └─ ✅ Texto disponível em DocumentContent
        │   │
        │   ├─ COMMIT TRANSACTION
        │   │
        │   └─ RETURN Document object
        │
        ├─► AuditLogger::log(user, 'UPLOAD', document)
        │   │
        │   └─ INSERT INTO audit_logs (
        │       user_id, action, resource_type, resource_id
        │     ) VALUES (user.id, 'UPLOAD', 'Document', doc.id)
        │
        └─ Adiciona ao array $documents[]

                        ▼

RESPONSE 201 Created com array de documents
```

---

## 🔐 Fluxo de Autenticação e Autorização

```
                    User Agent
                         │
                         ▼
        ┌─────────────────────────────────┐
        │  POST /api/entrar               │
        │  {email, password}              │
        └────────────────┬────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────┐
        │  LoginController::login()       │
        └────────────────┬────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────┐
        │  Sanctum Authentication         │
        │  • Hash password com bcrypt     │
        │  • Valida credenciais           │
        │  • Gera token Bearer            │
        └────────────────┬────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────┐
        │  RESPONSE 200                   │
        │  {token: "1|AbCdEf..."}         │
        └─────────────────────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────┐
        │  User salva token no cliente    │
        └─────────────────────────────────┘


PRÓXIMAS REQUISIÇÕES:
──────────────────────

        ┌─────────────────────────────────┐
        │  GET /api/pastas                │
        │  Headers: {                     │
        │    Authorization: "Bearer ..."  │
        │  }                              │
        └────────────────┬────────────────┘
                         │
                         ▼
        ┌─────────────────────────────────┐
        │  middleware('auth:sanctum')     │
        │  • Extrai token do header       │
        │  • Busca token em tokens table  │
        │  • Valida expiração             │
        │  • Carrega User                 │
        └────────────────┬────────────────┘
                         │
         ┌───────────────┴───────────────┐
         │ Token válido?                 │
         │                               │
      SIM│                          NÃO  │
         ▼                               ▼
    Continua             RESPONSE 401 Unauthorized
    Requisição                    │
                                  └─ "Token inválido"


SE TOKEN VÁLIDO:
────────────────

    ┌─────────────────────────────────┐
    │  Request $request->user() carregado
    │  $user = User do token          │
    └────────────────┬────────────────┘
                     │
                     ▼
    ┌─────────────────────────────────┐
    │  FolderController::index()      │
    │  • Usuário autenticado          │
    │  • Pode acessar recursos        │
    └─────────────────────────────────┘
```

---

## 📊 Estado de um Documento ao Longo do Tempo

```
TIMELINE:

[T0] Upload realizado
├─ inserted_at = NOW()
├─ deleted_at = NULL
├─ content = NULL (ainda não extraído)
└─ Estado: ATIVO, PENDENTE DE EXTRAÇÃO

                    ▼

[T0+30s] Job ExtractDocumentTextJob executa
├─ OCR/Parsing do arquivo
├─ Extrai texto completo
├─ INSERT INTO document_contents
└─ Estado: ATIVO, CONTEÚDO DISPONÍVEL

                    ▼

[T1] User solicita GET /documentos/{id}
├─ Fetch document com eager load content
├─ Registra action = 'VIEW'
├─ INSERT INTO audit_logs
└─ Estado: VISUALIZADO (audit log criado)

                    ▼

[T2] User solicita download
├─ Fetch document
├─ Retorna arquivo de storage
├─ Registra action = 'DOWNLOAD'
├─ INSERT INTO audit_logs
└─ Estado: BAIXADO (audit log criado)

                    ▼

[T3] User deleta documento
├─ UPDATE documents SET deleted_at = NOW()
├─ Arquivo PERMANECE em storage
├─ Registra action = 'SOFT_DELETE'
├─ INSERT INTO audit_logs
└─ Estado: DELETADO (soft delete)

                    ▼

[T4] Query normal não mostra documento
├─ SELECT * FROM documents WHERE deleted_at IS NULL
├─ Documento NÃO aparece
├─ Arquivo ainda está em storage
└─ Pode ser "restaurado" com:
    UPDATE documents SET deleted_at = NULL
```

---

## 🔒 Segurança em Camadas

```
┌─────────────────────────────────────┐
│  Camada 1: HTTP / Transport Layer    │
├─────────────────────────────────────┤
│  • HTTPS em produção (certificado)   │
│  • TLS 1.2+ (encriptação em trânsito)│
└─────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────┐
│  Camada 2: Autenticação              │
├─────────────────────────────────────┤
│  • Sanctum Bearer Token              │
│  • Token com expiração               │
│  • Verificação em cada request       │
└─────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────┐
│  Camada 3: Autorização               │
├─────────────────────────────────────┤
│  • Gates (can 'view', 'manage')      │
│  • Policies (Autoriza ação x recurso)│
│  • FolderPermission table            │
└─────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────┐
│  Camada 4: Armazenamento             │
├─────────────────────────────────────┤
│  • Arquivos fora do web root         │
│  • Nomes aleatórios (hash)           │
│  • Sem acesso direto via URL         │
│  • Download via controlador (log)    │
└─────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────┐
│  Camada 5: Auditoria                 │
├─────────────────────────────────────┤
│  • Todos os acessos registrados      │
│  • Quem, O quê, Quando               │
│  • Imutável (audit logs nunca delete)│
└─────────────────────────────────────┘
```

---

## 📈 Fluxo de Concorrência (Race Condition Evitada)

```
CENÁRIO: 2 users fazem upload na mesma pasta ao mesmo tempo

User A: POST /pastas/xyz/upload [file1.pdf]
User B: POST /pastas/xyz/upload [file2.xlsx]

        │                   │
        ▼                   ▼
    [T=0ms] Ambos chegam ao DocumentService::uploadFile()

        │                   │
        ▼                   ▼
    Ambos executam:
    SELECT MAX(sequence_number)
    FROM documents
    WHERE folder_id = 'xyz' AND year = 2026
    FOR UPDATE ← PESSIMISTIC LOCK!

        │
        ├─ User A consegue lock primeiro
        │   ├─ Encontra lastDoc.sequence = 5
        │   ├─ Calcula: sequence = 6
        │   ├─ Gera ref_code "...26.006.file1-pdf"
        │   ├─ INSERT document (sequence=6)
        │   └─ COMMIT TRANSACTION, libera lock
        │
        └─ User B aguarda...
            [lock em espera]

                        ▼ [T=50ms]

            User B consegue lock
            ├─ Encontra lastDoc.sequence = 6 (novo!)
            ├─ Calcula: sequence = 7
            ├─ Gera ref_code "...26.007.file2-xlsx"
            ├─ INSERT document (sequence=7)
            └─ COMMIT TRANSACTION, libera lock

RESULTADO:
✅ Sem duplicação de sequence_number
✅ Referência codes únicos
✅ Ordem mantida
✅ Race condition evitada!
```

---

## 🎯 Casos de Uso e Rotas

| Caso de Uso       | Método | Rota                    | Controller         | Serviço         |
| ----------------- | ------ | ----------------------- | ------------------ | --------------- |
| Fazer login       | POST   | /entrar                 | LoginController    | Sanctum         |
| Ver pastas        | GET    | /pastas                 | FolderController   | -               |
| Criar pasta       | POST   | /pastas                 | FolderController   | FolderService   |
| Upload            | POST   | /pastas/{id}/upload     | DocumentController | DocumentService |
| Ver documento     | GET    | /documentos/{id}        | DocumentController | -               |
| Baixar arquivo    | GET    | /documentos/{id}/baixar | DocumentController | -               |
| Deletar documento | DELETE | /documentos/{id}        | DocumentController | -               |
| Baixar pasta ZIP  | GET    | /pastas/{id}/baixar     | FolderController   | - (501)         |
