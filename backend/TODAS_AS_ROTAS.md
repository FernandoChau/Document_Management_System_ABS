# 📚 DOCUMENTAÇÃO COMPLETA DE ROTAS - DMS API

## 🎯 Resumo de Rotas

Total: **47 endpoints** disponíveis para teste completo do sistema.

---

## 📋 Índice de Rotas

1. [Departamentos](#departamentos)
2. [Pastas](#pastas)
3. [Documentos](#documentos)
4. [Conteúdo de Documentos](#conteúdo-de-documentos)
5. [Compartilhamentos](#compartilhamentos)
6. [Auditoria](#auditoria)
7. [Permissões de Pasta](#permissões-de-pasta)
8. [Dashboard & Relatórios](#dashboard--relatórios)
9. [Usuários](#usuários)

---

## 🏢 DEPARTAMENTOS

### 1. Listar Departamentos

```
GET /api/departamentos
Authentication: Bearer {token}
Query Parameters:
  - per_page (opcional): 15 (padrão)
```

**Resposta (200 OK):**

```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Financeiro",
      "description": "Departamento de Finanças",
      "slug": "fin",
      "folders_count": 5,
      "documents_count": 42,
      "created_at": "2026-01-26T14:30:00Z"
    }
  ],
  "pagination": {...}
}
```

### 2. Criar Departamento

```
POST /api/departamentos
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Recursos Humanos",
  "description": "Departamento de RH",
  "slug": "rh"
}
```

**Resposta (201 Created):**

```json
{
    "id": "uuid",
    "name": "Recursos Humanos",
    "slug": "rh",
    "created_at": "2026-01-26T14:35:00Z"
}
```

### 3. Ver Departamento com Pastas

```
GET /api/departamentos/{department}
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
  "id": "uuid",
  "name": "Financeiro",
  "slug": "fin",
  "folders": [
    {
      "id": "uuid",
      "name": "Contratos",
      "reference_code": "fin.contratos",
      "is_root": false
    }
  ],
  "documents": [...]
}
```

### 4. Atualizar Departamento

```
PUT /api/departamentos/{department}
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Novo Nome",
  "description": "Nova descrição"
}
```

### 5. Deletar Departamento (Soft Delete)

```
DELETE /api/departamentos/{department}
Authentication: Bearer {token}
```

**Resposta (204 No Content)**

### 6. Restaurar Departamento

```
POST /api/departamentos/{department}/restaurar
Authentication: Bearer {token}
```

### 7. Deletar Permanentemente

```
DELETE /api/departamentos/{department}/permanente
Authentication: Bearer {token}
```

### 8. Estatísticas do Departamento

```
GET /api/departamentos/{department}/estatisticas
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "total_documents": 42,
    "total_folders": 5,
    "total_size_gb": 2.5,
    "recent_uploads": [
        {
            "id": "uuid",
            "name": "documento.pdf",
            "created_at": "2026-01-26T14:40:00Z",
            "user_id": "uuid"
        }
    ]
}
```

---

## 📁 PASTAS

### 1. Listar Pastas Raiz

```
GET /api/pastas
Authentication: Bearer {token}
Query Parameters:
  - parent_id (opcional): Para listar subpastas de uma pasta específica
```

**Resposta (200 OK):**

```json
[
    {
        "id": "uuid",
        "name": "Financeiro Root",
        "reference_code": "fin",
        "is_root": true,
        "parent_id": null,
        "department_id": "uuid"
    }
]
```

### 2. Ver Pasta com Subpastas e Documentos

```
GET /api/pastas?parent_id={folder_id}
Authentication: Bearer {token}
```

### 3. Criar Pasta (Subpasta)

```
POST /api/pastas
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Contratos 2026",
  "parent_id": "uuid"
}
```

**Resposta (201 Created):**

```json
{
    "id": "uuid",
    "name": "Contratos 2026",
    "reference_code": "fin.contratos-2026",
    "parent_id": "uuid",
    "is_root": false
}
```

### 4. Ver Pasta Específica

```
GET /api/pastas/{folder}
Authentication: Bearer {token}
```

### 5. Atualizar Pasta

```
PUT /api/pastas/{folder}
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Novo Nome da Pasta"
}
```

### 6. Deletar Pasta (Soft Delete)

```
DELETE /api/pastas/{folder}
Authentication: Bearer {token}
```

### 7. Restaurar Pasta

```
POST /api/pastas/{folder}/restaurar
Authentication: Bearer {token}
```

### 8. Baixar Pasta como ZIP

```
GET /api/pastas/{folder}/baixar
Authentication: Bearer {token}
```

**Nota:** Retorna 501 Not Implemented (função não implementada nesta iteração)

### 9. Estatísticas da Pasta

```
GET /api/pastas/{folder}/estatisticas
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
  "total_documents": 15,
  "total_subfolders": 3,
  "total_size": 5242880,
  "recent_uploads": [...]
}
```

### 10. Fazer Upload de Documentos

```
POST /api/pastas/{folder}/upload
Authentication: Bearer {token}
Content-Type: multipart/form-data

files: [arquivo1.pdf, arquivo2.xlsx, arquivo3.docx]
```

**Resposta (201 Created):**

```json
{
    "message": "Upload successful",
    "documents": [
        {
            "id": "uuid",
            "name": "arquivo1.pdf",
            "reference_code": "fin.contratos-2026.26.001.arquivo1",
            "size": 204800,
            "mime_type": "application/pdf",
            "sequence_number": 1
        },
        {
            "id": "uuid",
            "name": "arquivo2.xlsx",
            "reference_code": "fin.contratos-2026.26.002.arquivo2",
            "size": 102400,
            "mime_type": "application/vnd.openxml...",
            "sequence_number": 2
        },
        {
            "id": "uuid",
            "name": "arquivo3.docx",
            "reference_code": "fin.contratos-2026.26.003.arquivo3",
            "size": 307200,
            "mime_type": "application/vnd.openxml...",
            "sequence_number": 3
        }
    ]
}
```

---

## 📄 DOCUMENTOS

### 1. Listar Documentos

```
GET /api/documentos
Authentication: Bearer {token}
Query Parameters:
  - folder_id (opcional): Filtrar por pasta
  - per_page (opcional): 15 (padrão)
```

### 2. Ver Documento

```
GET /api/documentos/{document}
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "id": "uuid",
    "folder_id": "uuid",
    "name": "arquivo1.pdf",
    "file_path": "documents/abc123def456",
    "reference_code": "fin.contratos-2026.26.001.arquivo1",
    "mime_type": "application/pdf",
    "size": 204800,
    "year": 2026,
    "sequence_number": 1,
    "user_id": "uuid",
    "deleted_at": null,
    "content": {
        "id": "uuid",
        "document_id": "uuid",
        "extracted_text": "Conteúdo extraído do PDF...",
        "extraction_status": "completed",
        "created_at": "2026-01-26T14:45:00Z"
    },
    "created_at": "2026-01-26T14:40:00Z"
}
```

### 3. Atualizar Documento

```
PUT /api/documentos/{document}
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Novo Nome do Arquivo"
}
```

### 4. Baixar Documento

```
GET /api/documentos/{document}/baixar
Authentication: Bearer {token}
```

**Resposta:** Arquivo original em binary

### 5. Deletar Documento (Soft Delete)

```
DELETE /api/documentos/{document}
Authentication: Bearer {token}
```

**Resposta (204 No Content)**

### 6. Restaurar Documento

```
POST /api/documentos/{document}/restaurar
Authentication: Bearer {token}
```

### 7. Estatísticas do Documento

```
GET /api/documentos/{document}/estatisticas
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "downloads": 5,
    "views": 12,
    "shares": 2
}
```

---

## 📝 CONTEÚDO DE DOCUMENTOS

### 1. Ver Conteúdo Extraído

```
GET /api/documentos/{document}/conteudo
Authentication: Bearer {token}
```

### 2. Atualizar Conteúdo Extraído (Manual)

```
PUT /api/documentos/{document}/conteudo
Authentication: Bearer {token}
Content-Type: application/json

{
  "extracted_text": "Novo conteúdo extraído...",
  "extraction_status": "completed"
}
```

### 3. Deletar Conteúdo Extraído

```
DELETE /api/documentos/{document}/conteudo
Authentication: Bearer {token}
```

### 4. Ver Status de Extração

```
GET /api/documentos/{document}/conteudo/status
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "status": "completed",
    "extracted_at": "2026-01-26T14:45:00Z",
    "has_text": true
}
```

### 5. Buscar em Conteúdo Extraído

```
POST /api/conteudo/buscar
Authentication: Bearer {token}
Content-Type: application/json

{
  "q": "palavra-chave",
  "per_page": 15
}
```

**Resposta (200 OK):**

```json
{
  "data": [
    {
      "id": "uuid",
      "document_id": "uuid",
      "extracted_text": "... palavra-chave ...",
      "document": {
        "id": "uuid",
        "name": "documento.pdf",
        "reference_code": "fin.26.001.documento"
      }
    }
  ],
  "pagination": {...}
}
```

---

## 🔗 COMPARTILHAMENTOS

### 1. Listar Links de Compartilhamento

```
GET /api/compartilhamentos
Authentication: Bearer {token}
Query Parameters:
  - per_page (opcional): 15 (padrão)
```

### 2. Criar Link de Compartilhamento

```
POST /api/compartilhamentos
Authentication: Bearer {token}
Content-Type: application/json

{
  "shareable_type": "Document",
  "shareable_id": "uuid",
  "expires_in_hours": 24,
  "password": "123456",
  "max_downloads": 10
}
```

**Resposta (201 Created):**

```json
{
    "id": "uuid",
    "token": "abcdef123456...",
    "shareable_type": "Document",
    "shareable_id": "uuid",
    "expires_at": "2026-01-27T14:40:00Z",
    "password": "hashed...",
    "max_downloads": 10,
    "downloads_count": 0,
    "created_by": "uuid",
    "created_at": "2026-01-26T14:40:00Z"
}
```

### 3. Ver Link de Compartilhamento (Público)

```
GET /api/compartilhamentos/{token}
Query Parameters:
  - password (opcional): Se o link for protegido
```

### 4. Download via Link (Público)

```
GET /api/compartilhamentos/{token}/download
Query Parameters:
  - password (opcional): Se o link for protegido
```

### 5. Atualizar Link

```
PUT /api/compartilhamentos/{shareLink}
Authentication: Bearer {token}
Content-Type: application/json

{
  "expires_in_hours": 48,
  "max_downloads": 20
}
```

### 6. Deletar Link

```
DELETE /api/compartilhamentos/{shareLink}
Authentication: Bearer {token}
```

---

## 📊 AUDITORIA

### 1. Listar Logs de Auditoria

```
GET /api/auditoria
Authentication: Bearer {token}
Query Parameters:
  - action (opcional): VIEW, DOWNLOAD, UPLOAD, SHARE, SOFT_DELETE, UPDATE_METADATA
  - resource_type (opcional): Document, Folder, Department
  - user_id (opcional): UUID do usuário
  - date_from (opcional): YYYY-MM-DD
  - date_to (opcional): YYYY-MM-DD
  - per_page (opcional): 15 (padrão)
```

**Resposta (200 OK):**

```json
{
  "data": [
    {
      "id": "uuid",
      "user_id": "uuid",
      "action": "UPLOAD",
      "resource_type": "Document",
      "resource_id": "uuid",
      "metadata": {},
      "user": {
        "id": "uuid",
        "name": "João Silva",
        "email": "joao@example.com"
      },
      "created_at": "2026-01-26T14:40:00Z"
    }
  ],
  "pagination": {...}
}
```

### 2. Ver Log Específico

```
GET /api/auditoria/{auditLog}
Authentication: Bearer {token}
```

### 3. Logs de um Documento

```
GET /api/auditoria/documento/{document}/logs
Authentication: Bearer {token}
Query Parameters:
  - action (opcional)
  - per_page (opcional)
```

### 4. Logs de uma Pasta

```
GET /api/auditoria/pasta/{folder}/logs
Authentication: Bearer {token}
Query Parameters:
  - action (opcional)
  - per_page (opcional)
```

### 5. Logs de um Usuário

```
GET /api/auditoria/usuario/{userId}/logs
Authentication: Bearer {token}
Query Parameters:
  - action (opcional)
  - per_page (opcional)
```

### 6. Logs de um Documento (via DocumentController)

```
GET /api/documentos/{document}/logs
Authentication: Bearer {token}
```

### 7. Estatísticas de Auditoria

```
GET /api/auditoria/estatisticas
Authentication: Bearer {token}
Query Parameters:
  - date_from (opcional): YYYY-MM-DD
  - date_to (opcional): YYYY-MM-DD
```

**Resposta (200 OK):**

```json
{
  "total_actions": 256,
  "by_action": {
    "VIEW": 120,
    "DOWNLOAD": 85,
    "UPLOAD": 42,
    "SOFT_DELETE": 9
  },
  "by_resource_type": {
    "Document": 200,
    "Folder": 50,
    "Department": 6
  },
  "recent_actions": [...]
}
```

---

## 🔐 PERMISSÕES DE PASTA

### 1. Listar Permissões de Uma Pasta

```
GET /api/pastas/{folder}/permissoes
Authentication: Bearer {token}
```

### 2. Conceder Permissão

```
POST /api/pastas/{folder}/permissoes
Authentication: Bearer {token}
Content-Type: application/json

{
  "user_id": "uuid",
  "permission_level": "view"
}
```

**Permission Levels:**

- `view`: Apenas visualizar
- `edit`: Visualizar e editar metadados
- `manage`: Controle total (criador automático tem isso)

**Resposta (201 Created):**

```json
{
    "id": "uuid",
    "folder_id": "uuid",
    "user_id": "uuid",
    "permission_level": "view",
    "created_at": "2026-01-26T14:40:00Z"
}
```

### 3. Atualizar Permissão

```
PUT /api/pastas/{folder}/permissoes/{permission}
Authentication: Bearer {token}
Content-Type: application/json

{
  "permission_level": "edit"
}
```

### 4. Remover Permissão

```
DELETE /api/pastas/{folder}/permissoes/{permission}
Authentication: Bearer {token}
```

### 5. Verificar Permissão de um Usuário

```
GET /api/pastas/{folder}/permissoes/usuario/{userId}/check
Authentication: Bearer {token}
```

**Resposta (200 OK):**

```json
{
    "permission_level": "view"
}
```

### 6. Listar Pastas de um Usuário

```
GET /api/permissoes/usuario/{userId}/pastas
Authentication: Bearer {token}
Query Parameters:
  - permission_level (opcional): view, edit, manage
  - per_page (opcional): 15 (padrão)
```

---

## 📈 DASHBOARD & RELATÓRIOS

### 1. Dashboard Geral

```
GET /api/dashboard
Authentication: Bearer {token}
Query Parameters:
  - date_from (opcional): YYYY-MM-DD
  - date_to (opcional): YYYY-MM-DD
```

**Resposta (200 OK):**

```json
{
  "summary": {
    "total_departments": 5,
    "total_folders": 24,
    "total_documents": 342,
    "total_size_gb": 12.5,
    "total_users_accessed": 18
  },
  "documents_by_type": [
    {
      "mime_type": "application/pdf",
      "count": 156,
      "total_size": 5242880
    }
  ],
  "uploads_by_date": [
    {
      "date": "2026-01-26",
      "count": 8
    }
  ],
  "recent_uploads": [...],
  "recent_downloads": [...],
  "documents_by_department": [...],
  "top_users": [...],
  "storage_usage": [...]
}
```

### 2. Dashboard por Departamento

```
GET /api/dashboard/departamento/{department}
Authentication: Bearer {token}
Query Parameters:
  - date_from (opcional): YYYY-MM-DD
  - date_to (opcional): YYYY-MM-DD
```

### 3. Dashboard por Pasta

```
GET /api/dashboard/pasta/{folder}
Authentication: Bearer {token}
Query Parameters:
  - date_from (opcional): YYYY-MM-DD
  - date_to (opcional): YYYY-MM-DD
```

---

## 👥 USUÁRIOS (Admin)

### 1. Listar Usuários

```
GET /api/utilizadores
Authentication: Bearer {token}
```

### 2. Ver Usuário Específico

```
GET /api/utilizadores/{id}
Authentication: Bearer {token}
```

### 3. Criar Usuário

```
POST /api/utilizadores
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Nome do Usuário",
  "email": "email@example.com",
  "password": "senha123"
}
```

### 4. Atualizar Usuário

```
PUT /api/utilizadores/{id}
Authentication: Bearer {token}
Content-Type: application/json

{
  "name": "Novo Nome",
  "email": "novo@example.com"
}
```

### 5. Desativar Usuário

```
PUT /api/utilizadores/{id}/desativar
Authentication: Bearer {token}
```

### 6. Ativar Usuário

```
PUT /api/utilizadores/{id}/ativar
Authentication: Bearer {token}
```

---

## 🎯 Resumo de Actions para Auditoria

- `VIEW`: Usuário visualizou um recurso
- `DOWNLOAD`: Usuário baixou um arquivo
- `UPLOAD`: Usuário fez upload de um arquivo
- `SHARE`: Usuário criou um link de compartilhamento
- `SOFT_DELETE`: Usuário deletou um recurso (soft delete)
- `UPDATE_METADATA`: Usuário atualizou metadados
- `CREATE`: Usuário criou um recurso
- `RESTORE`: Usuário restaurou um recurso deletado
- `FORCE_DELETE`: Usuário deletou permanentemente um recurso

---

## 🔄 Fluxo de Teste Recomendado

1. **Criar Departamento**
    - `POST /api/departamentos`
    - Nota: Pasta raiz é criada automaticamente

2. **Criar Subpasta**
    - `POST /api/pastas` com `parent_id`

3. **Fazer Upload (Batch)**
    - `POST /api/pastas/{folder}/upload`
    - Envie 3 arquivos
    - Verifique sequence_number (001, 002, 003)

4. **Ver Documento**
    - `GET /api/documentos/{document}`
    - Verifique se conteúdo extraído está disponível

5. **Baixar Documento**
    - `GET /api/documentos/{document}/baixar`
    - Verifique arquivo

6. **Criar Link de Compartilhamento**
    - `POST /api/compartilhamentos`
    - Com `expires_in_hours` e `password`

7. **Deletar Documento**
    - `DELETE /api/documentos/{document}`
    - `deleted_at` será preenchido

8. **Verificar Auditoria**
    - `GET /api/auditoria`
    - Verifique se tem 5+ registros (UPLOAD x3, VIEW, DOWNLOAD, SHARE, SOFT_DELETE)

9. **Ver Dashboard**
    - `GET /api/dashboard`
    - Verifique estatísticas

---

**Total de Rotas: 47 endpoints**
