# 📦 IMPLEMENTAÇÃO DE ZIP PARA FOLDERS

## ✅ Status: COMPLETO

Implementada a funcionalidade de compressão de pastas em ZIP, mantendo a estrutura de diretórios.

---

## 🎯 O Que Foi Implementado

### 1. **FolderZipService** (Novo)

**Arquivo:** `app/Services/FolderZipService.php`

**Responsabilidades:**

- Criar arquivo ZIP com toda a estrutura de pastas
- Adicionar ficheiros recursivamente mantendo a hierarquia
- Gerenciar ficheiros temporários
- Limpar ZIPs antigos (> 1 hora)

**Métodos:**

```php
createZip(Folder $folder): string
  └─ Cria ZIP mantendo estrutura de diretórios
  └─ Retorna caminho do arquivo criado

getZipDownloadName(Folder $folder): string
  └─ Gera nome apropriado para download

cleanOldZips(): void
  └─ Remove ZIPs temporários antigos
```

**Como Funciona:**

1. Cria diretório `storage/app/temp/` se não existir
2. Abre novo arquivo ZIP com `ZipArchive`
3. Recursivamente adiciona:
    - Documentos da pasta com seu nome
    - Sub-pastas mantendo estrutura hierárquica
4. Retorna caminho do ZIP criado
5. Frontend pode opcionalmente chamar limpeza de ZIPs antigos

---

### 2. **FolderController - Método download()**

**Arquivo:** `app/Http/Controllers/Api/FolderController.php`

**Antes:** Retornava erro 501 (não implementado)

**Depois:** Implementa download completo de ZIP

```php
GET /api/pastas/{folder}/baixar
```

**Fluxo:**

1. Valida se usuário tem permissão de visualizar pasta
2. Cria ZIP usando FolderZipService
3. Registra ação no AuditLog (tipo: 'zip')
4. Retorna arquivo para download
5. Deleta arquivo temporário após envio (`deleteFileAfterSend`)

**Headers de Resposta:**

- Content-Type: application/zip
- Content-Disposition: attachment (força download)

**Resposta em Caso de Erro:**

```json
{
    "error": "Não foi possível criar o arquivo ZIP: ..."
}
```

---

### 3. **ShareLinkController - Método download()**

**Arquivo:** `app/Http/Controllers/Api/ShareLinkController.php`

**Antes:** Retornava erro 501 para Folders

**Depois:** Suporta download de ZIP para Folders via share link

```php
GET /api/compartilhamentos/{token}/download
```

**Fluxo para Folder:**

1. Valida token de share link
2. Verifica expiração
3. Verifica limite de downloads
4. Valida password (se configurado)
5. Cria ZIP da pasta
6. Incrementa contador de downloads
7. Registra no AuditLog
8. Retorna ZIP para download

**Suporta Agora:**

- ✅ Download de Documento (arquivo individual)
- ✅ Download de Pasta (ZIP com estrutura completa)

**Exemplo de Uso:**

```bash
# Obter informações do share link
GET /api/compartilhamentos/{token}

# Download com senha (se configurado)
GET /api/compartilhamentos/{token}/download?password=abc123
```

---

## 🗂️ Estrutura de Exemplo

**Pasta Original:**

```
Documentos/
├── relatorio.pdf
├── planilha.xlsx
├── Subfolder1/
│   ├── documento1.docx
│   ├── documento2.docx
│   └── SubFolder2/
│       └── arquivo.txt
└── Subfolder3/
    └── imagem.jpg
```

**ZIP Gerado:**

```
Documentos.zip
├── relatorio.pdf
├── planilha.xlsx
├── Subfolder1/
│   ├── documento1.docx
│   ├── documento2.docx
│   └── SubFolder2/
│       └── arquivo.txt
└── Subfolder3/
    └── imagem.jpg
```

---

## 🔒 Segurança

### ✅ Validações Implementadas

- Permissão de leitura obrigatória
- Verificação de expiração de share link
- Limite de downloads respeitado
- Password protection respeitada
- Verificação de existência de arquivo antes de adicionar ao ZIP

### ✅ Limpeza de Arquivos

- ZIPs temporários armazenados em `storage/app/temp/`
- Deletados após envio (`deleteFileAfterSend`)
- Limpeza automática de ZIPs > 1 hora (se chamado)

---

## 📊 Registro de Auditoria

Ambas as operações registram no AuditLog:

**FolderController:**

```php
AuditLogger::log($user, 'DOWNLOAD', $folder, ['type' => 'zip']);
```

**ShareLinkController:**

```php
AuditLogger::log(null, 'DOWNLOAD', $folder, [
    'via_share_link' => $shareLink->id,
    'ip' => $request->ip(),
    'type' => 'zip',
]);
```

Permite rastrear:

- Quem baixou
- Qual pasta
- Via share link ou acesso direto
- IP origem (para share links públicos)

---

## 🧪 Testes Recomendados

### 1. Download Direto de Pasta

```bash
# Fazer login
POST /api/entrar
{
  "email": "test@example.com",
  "password": "password123"
}

# Fazer download do ZIP
GET /api/pastas/{folder_id}/baixar
Authorization: Bearer {token}
```

### 2. Download via Share Link

```bash
# Criar share link da pasta
POST /api/compartilhamentos
{
  "shareable_type": "Folder",
  "shareable_id": "{folder_id}",
  "expires_in_hours": 24,
  "max_downloads": 5
}

# Response: {token: "..."}

# Download via share link (público)
GET /api/compartilhamentos/{token}/download
```

### 3. Share Link com Password

```bash
# Criar com password
POST /api/compartilhamentos
{
  "shareable_type": "Folder",
  "shareable_id": "{folder_id}",
  "password": "senhaSegura",
  "expires_in_hours": 24
}

# Download com password
GET /api/compartilhamentos/{token}/download?password=senhaSegura
```

---

## ⚙️ Configuração

### Dependências

- PHP ZipArchive (vem com PHP por padrão)
- Laravel Storage (já configurado)

### Diretórios Necessários

```
storage/
├── app/
│   ├── documents/     (ficheiros dos documentos)
│   ├── temp/          (ZIPs temporários)
│   └── ...
└── logs/
```

O diretório `temp/` é criado automaticamente se não existir.

---

## 📈 Performance

### Para Pastas Grandes

- ZipArchive trabalha em memória eficiente
- Ficheiros são adicionados por referência (não cópia)
- Streaming de dados ao cliente

### Otimizações Possíveis

1. Async job para ZIPs muito grandes (> 1GB)
2. Cache de ZIPs frequentes
3. Limpar temp files via CRON job
4. Comprimir documentos antes de armazenar

---

## 🚀 Endpoints Disponíveis

### Download Direto

```
GET /api/pastas/{folder_id}/baixar
  ├─ Requer autenticação
  ├─ Requer permissão can_view
  └─ Retorna: application/zip
```

### Via Share Link

```
GET /api/compartilhamentos/{token}/download
  ├─ Público (sem autenticação)
  ├─ Respeita expiração
  ├─ Respeita limite de downloads
  ├─ Valida password (se configurado)
  └─ Retorna: application/zip ou documento
```

---

## ✨ Resumo

| Funcionalidade          | Status | Notas                  |
| ----------------------- | ------ | ---------------------- |
| Criar ZIP de pasta      | ✅     | Mantém hierarquia      |
| Download direto         | ✅     | Autenticado            |
| Download via share link | ✅     | Público com validações |
| Expiração de link       | ✅     | Respeitada             |
| Limite de downloads     | ✅     | Respeitado             |
| Password protection     | ✅     | Hash bcrypt            |
| Auditoria               | ✅     | Registra ações         |
| Limpeza temp files      | ✅     | Manual + pós-envio     |
| Estrutura hierárquica   | ✅     | Mantida no ZIP         |

---

**Data de Implementação:** 02 de Fevereiro de 2026
**Status:** ✅ PRONTO PARA PRODUÇÃO
