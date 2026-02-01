# 📊 Explicação Técnica - Fluxo de Dados

## 🎯 Resumo Executivo

Seu sistema é uma **API REST de Gestão Documental** com dois controllers principais:

1. **FolderController** - Gerencia pastas hierárquicas (árvore de diretórios)
2. **DocumentController** - Gerencia uploads e downloads de arquivos

Tudo usa autenticação Bearer Token (Laravel Sanctum).

---

## 🔄 Fluxo Completo de Dados

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           CLIENTE (Frontend/Postman)                         │
└──────────────────────────────┬──────────────────────────────────────────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
          ┌─────────▼────────┐   ┌───────▼──────────┐
          │  FolderController │   │DocumentController│
          └─────────┬────────┘   └───────┬──────────┘
                    │                     │
        ┌───────────┴──────────┐  ┌───────┴────────────┐
        │                      │  │                    │
   ┌────▼─────────┐   ┌───────▼──┴───┐   ┌────────────▼────┐
   │FolderService │   │DocumentService│   │  AuditLogger    │
   └────┬─────────┘   └───────┬───────┘   └────────┬────────┘
        │                     │                    │
        │  Transação BD       │  Transação BD      │  Insert
        │                     │                    │
   ┌────▼──────────────────────▼────────────────────▼──────┐
   │                   DATABASE (MySQL/PostgreSQL)         │
   │                                                        │
   │  folders │ documents │ document_contents │ audit_logs  │
   └────────────────────────────────────────────────────────┘
        │
   ┌────▼──────────────────────┐
   │  STORAGE (Filesystem)      │
   │  storage/app/documents/    │
   │  (Arquivos físicos)        │
   └────────────────────────────┘
```

---

## 📁 FolderController - Criação de Pastas

### Requisição: **POST /api/pastas**

```json
{
    "name": "Contratos 2026",
    "parent_id": "123e...", // opcional
    "department_id": "dept-001" // required se criar raiz
}
```

### Fluxo Interno:

```
1. Request entra no FolderController::store()
   │
   └─> Valida request:
       ├─ name: obrigatório, string, max 255
       ├─ parent_id: se presente, deve existir em folders
       └─ department_id: obrigatório se não tiver parent_id

2. Chama FolderService::createFolder()
   │
   └─> Inicia TRANSAÇÃO DATABASE:
       │
       ├─ if (parent_id):
       │   └─ Folder é SUBPASTA
       │      ├─ reference_code = parent.reference_code + '.' + slug(name)
       │      │  Exemplo: "fin" + "." + "contratos-2026" = "fin.contratos-2026"
       │      └─ is_root = false
       │
       └─ else:
           └─ Folder é RAIZ
              ├─ Valida se department existe
              ├─ reference_code = department.slug
              │  Exemplo: "fin"
              └─ is_root = true
              └─ department_id = department.id

3. Cria Permissão (se subpasta):
   └─> FolderPermission.create([
         'user_id' => creator,
         'permission_level' => 'manage'
       ])

4. Registra na Auditoria:
   └─> AuditLogger::log(user, 'CREATE', folder)
       └─> INSERT INTO audit_logs (user_id, action, resource_type, ...)

5. Commit transação e retorna Folder
   └─> response()->json($folder, 201)
```

### Resposta: **201 Created**

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

## 📄 DocumentController - Upload de Arquivos

### Requisição: **POST /api/pastas/{folder}/upload**

```
Content-Type: multipart/form-data

files: [documento1.pdf, documento2.xlsx]  (Array de arquivos)
```

### Fluxo Interno:

```
1. Request entra em DocumentController::store(Request $request, Folder $folder)
   │
   └─> Valida request:
       ├─ files: array, obrigatório
       └─ files.*: file, max 50MB cada

2. Loop PARA CADA ARQUIVO:
   │
   ├─> Chama DocumentService::uploadFile(folder, file, uploader)
   │   │
   │   └─> Inicia TRANSAÇÃO DATABASE:
   │       │
   │       ├─ **PASSO 1: Calcula Sequência Atômica**
   │       │  │
   │       │  └─ Busca o último documento da pasta neste ano COM LOCK:
   │       │     Document::where('folder_id', $folder->id)
   │       │       ->where('year', 2026)
   │       │       ->lockForUpdate()  // Pessimistic Lock
   │       │       ->orderBy('sequence_number', 'desc')
   │       │       ->first();
   │       │
   │       │     Resultado: $lastDoc
   │       │     └─ Se existe: sequence = lastDoc.sequence + 1
   │       │     └─ Se não existe: sequence = 1
   │       │
   │       │     ⚠️ Lock evita race condition (2 uploads simultâneos)
   │       │
   │       ├─ **PASSO 2: Gera Reference Code**
   │       │  │
   │       │  └─ Padrão: {folder_ref}.{yy}.{seq}.{filename}
   │       │     │
   │       │     ├─ folder_ref = "fin.contratos-2026"
   │       │     ├─ yy = 26  (2026 últimos 2 dígitos)
   │       │     ├─ seq = "001" (padding com zeros)
   │       │     └─ filename = "documento-vendas.pdf"
   │       │
   │       │     Exemplo: "fin.contratos-2026.26.001.documento-vendas"
   │       │
   │       ├─ **PASSO 3: Armazena Arquivo Fisicamente**
   │       │  │
   │       │  └─ $path = $file->store('documents', 'local')
   │       │     │
   │       │     └─ Salva em: storage/app/documents/{hash_aleatorio}
   │       │        Exemplo: storage/app/documents/abc123def456ghi789
   │       │
   │       │     ✅ Arquivo seguro fora do web root
   │       │     ✅ Nome aleatório evita exposição
   │       │
   │       ├─ **PASSO 4: Cria Registro Document**
   │       │  │
   │       │  └─ Document::create([
   │       │       'folder_id' => folder.id,
   │       │       'name' => 'documento-vendas.pdf',  // original
   │       │       'file_path' => 'documents/abc123...',
   │       │       'reference_code' => 'fin.contratos-2026.26.001...',
   │       │       'mime_type' => 'application/pdf',
   │       │       'size' => 204800,
   │       │       'year' => 2026,
   │       │       'sequence_number' => 1,
   │       │       'user_id' => uploader.id
   │       │     ]);
   │       │
   │       └─ **PASSO 5: Dispara Job Assíncrono (Opcional)**
   │          │
   │          └─ ExtractDocumentTextJob::dispatch($document)
   │             └─ Executará em background queue
   │                ├─ OCR/PDF parsing
   │                ├─ Extrai texto
   │                └─ Salva em DocumentContent
   │
   ├─> Commit transação
   │
   ├─> Registra Auditoria:
   │   └─> AuditLogger::log(user, 'UPLOAD', document)
   │
   └─> Adiciona ao array $documents[]

3. Retorna todos os documentos criados
   └─> response()->json(['documents' => [...]], 201)
```

### Resposta: **201 Created**

```json
{
    "message": "Upload successful",
    "documents": [
        {
            "id": "doc-001",
            "folder_id": "523e...",
            "name": "relatorio.pdf",
            "file_path": "documents/abc123def456",
            "reference_code": "fin.contratos-2026.26.001.relatorio",
            "mime_type": "application/pdf",
            "size": 204800,
            "year": 2026,
            "sequence_number": 1,
            "user_id": "user-001",
            "created_at": "2026-01-26T14:40:00Z"
        },
        {
            "id": "doc-002",
            "folder_id": "523e...",
            "name": "planilha.xlsx",
            "file_path": "documents/xyz789uvw123",
            "reference_code": "fin.contratos-2026.26.002.planilha",
            "mime_type": "application/vnd.openxml...",
            "size": 102400,
            "year": 2026,
            "sequence_number": 2,
            "user_id": "user-001",
            "created_at": "2026-01-26T14:40:00Z"
        }
    ]
}
```

---

## 👁️ DocumentController - Ver Documento

### Requisição: **GET /api/documentos/{document_id}**

### Fluxo:

```
1. Busca Document pelo ID
   └─> Document::find($id) ou throw 404

2. Eager load relacionamento content (conteúdo extraído)
   └─> $document->load('content')
       └─> SELECT * FROM document_contents WHERE document_id = ?

3. Registra auditoria
   └─> AuditLogger::log(auth()->user(), 'VIEW', $document)

4. Retorna documento com conteúdo
   └─> response()->json($document)
```

### Resposta: **200 OK**

```json
{
    "id": "doc-001",
    "folder_id": "523e...",
    "name": "relatorio.pdf",
    "reference_code": "fin.contratos-2026.26.001.relatorio",
    "size": 204800,
    "mime_type": "application/pdf",
    "year": 2026,
    "sequence_number": 1,
    "user_id": "user-001",
    "created_at": "2026-01-26T14:40:00Z",
    "updated_at": "2026-01-26T14:40:00Z",
    "content": {
        "id": "content-001",
        "document_id": "doc-001",
        "extracted_text": "Janeiro de 2026\n\nRelatório de Vendas...",
        "created_at": "2026-01-26T14:45:00Z"
    }
}
```

---

## ⬇️ DocumentController - Baixar Documento

### Requisição: **GET /api/documentos/{document_id}/baixar**

### Fluxo:

```
1. Busca Document pelo ID
   └─> Document::find($id)

2. Registra auditoria
   └─> AuditLogger::log(auth()->user(), 'DOWNLOAD', $document)

3. Retorna arquivo
   └─> response()->download(
         storage_path('app/' . $document->file_path),
         $document->name  // Nome exibido no download
       )

       Exemplo:
       ├─ Lê: storage/app/documents/abc123def456
       ├─ Envia ao cliente como: relatorio.pdf
       └─ Headers: Content-Disposition: attachment
```

### Resposta: **200 OK**

- Content-Type: application/pdf (ou outro MIME type)
- Content-Disposition: attachment; filename="relatorio.pdf"
- Body: Arquivo em binary

---

## 🗑️ DocumentController - Deletar Documento

### Requisição: **DELETE /api/documentos/{document_id}**

### Fluxo:

```
1. Busca Document pelo ID
   └─> Document::find($id)

2. Executa Soft Delete
   └─> $document->delete()
       └─> UPDATE documents SET deleted_at = NOW() WHERE id = ?
           (Arquivo NÃO é removido do storage)

3. Registra auditoria
   └─> AuditLogger::log(auth()->user(), 'SOFT_DELETE', $document)

4. Retorna 204 No Content
   └─> response()->noContent()
```

### Resposta: **204 No Content**

(Sem body)

**Efeitos:**

- deleted_at é preenchido com timestamp
- Documento não aparece em listagens normais
- Arquivo permanece em storage/app/documents/
- Pode ser "restaurado" com query `withoutTrashed()`

---

## 🔒 Fluxo de Auditoria

Toda ação usa o `AuditLogger`:

```php
AuditLogger::log($user, 'ACTION', $resource);
```

Isso cria um registro em `audit_logs`:

```
INSERT INTO audit_logs (
  user_id,           // ID do usuário autenticado
  action,            // 'UPLOAD', 'VIEW', 'DOWNLOAD', 'CREATE', 'SOFT_DELETE'
  resource_type,     // 'Document' ou 'Folder'
  resource_id,       // ID do documento ou pasta
  metadata,          // JSON com contexto adicional (opcional)
  created_at         // Timestamp automático
)
```

### Exemplo de auditoria de upload:

```json
{
    "id": 42,
    "user_id": "user-001",
    "action": "UPLOAD",
    "resource_type": "Document",
    "resource_id": "doc-001",
    "metadata": {},
    "created_at": "2026-01-26T14:40:00Z"
}
```

---

## 🔑 Conceitos-Chave

| Conceito             | Explicação                                                    |
| -------------------- | ------------------------------------------------------------- |
| **Transação**        | Agrupa operações BD. Se uma falha, reverte tudo.              |
| **Pessimistic Lock** | Trava row no BD enquanto calcula sequência. Evita duplicatas. |
| **Soft Delete**      | Marca deleted_at sem remover física (pode restaurar).         |
| **Reference Code**   | Identificador legível único (ex: "fin.26.001.relatorio").     |
| **MIME Type**        | Tipo de arquivo (pdf, xlsx, txt, etc).                        |
| **Eager Loading**    | `load('content')` traz dados relacionados na mesma query.     |
| **Auditoria**        | Log de quem fez o quê, quando e em qual recurso.              |

---

## 📊 Relacionamentos Entre Modelos

```
┌────────────────┐
│    Folder      │
├────────────────┤
│ id (UUID)      │
│ name           │
│ parent_id ─────┼──┐ (self-referencing)
│ is_root        │  │
│ reference_code │  │
│ department_id ─┼──┼──┐
└────────────────┘  │  │
                    │  │
                    │  │  ┌──────────────┐
                    └──┼──┤  Folder      │
                       │  │ (parent)     │
                       │  └──────────────┘
                       │
                       │  ┌──────────────┐
                       └──┤ Department   │
                          │ (container)  │
                          └──────────────┘

Folder ◄──── has many ────► Document
  │                            │
  │                            ├─ belongs to ──► Folder
  │                            ├─ belongs to ──► User (uploader)
  │                            └─ has one ──────► DocumentContent
  │
  ├─ belongs to ──► Folder (parent)
  ├─ has many ─────► Folder (children)
  ├─ belongs to ──► Department
  └─ has many ─────► FolderPermission
```

---

## 🚀 Resumo Executivo

Seu sistema é bem estruturado:

✅ **Segurança**:

- Arquivos em storage privado (não acessível via HTTP)
- Auditoria de toda ação
- Transações garantem integridade

✅ **Performance**:

- Pessimistic locks evitam race conditions
- Eager loading (`load('content')`) otimiza queries
- Soft delete é mais rápido que delete físico

✅ **Rastreabilidade**:

- Cada ação é registrada com user, timestamp, recurso
- Reference code hierárquico facilita consultas

✅ **Escalabilidade**:

- Estrutura pronta para jobs assíncrono (OCR)
- Transações garantem consistência em alta concorrência
