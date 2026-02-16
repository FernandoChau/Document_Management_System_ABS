# 🎯 RESUMO SIMPLES - O que você tem?

## 📌 Em Uma Frase

**Um sistema para guardar documentos organizados em pastas, registrando quem acessa cada um.**

---

## 🏢 Estrutura

```
DEPARTAMENTO (ex: Financeiro)
    │
    └─ PASTA RAIZ
       │
       ├─ SUBPASTA 1 (ex: Contratos)
       │   ├─ Documento 1
       │   ├─ Documento 2
       │   └─ Documento 3
       │
       └─ SUBPASTA 2 (ex: Relatórios)
           ├─ Documento 4
           └─ Documento 5
```

Cada pasta e documento tem um código único:

- Pasta: `fin.contratos` (Financeiro > Contratos)
- Documento: `fin.contratos.26.001.contrato` (Sequencial)

---

## 🔑 3 Coisas que o Sistema Faz

### 1️⃣ ORGANIZA DOCUMENTOS

- Cria pastas e subpastas
- Nomes únicos automáticos
- Estrutura hierárquica

### 2️⃣ GERENCIA UPLOADS

- Faz upload de múltiplos arquivos
- Sequência automática (doc 1, 2, 3...)
- Armazena com segurança (nomes aleatórios)

### 3️⃣ REGISTRA TUDO

- Quem fez login
- Quem fez upload
- Quem viu documento
- Quem baixou arquivo
- Quem deletou

---

## 🚀 Fluxo Básico

```
┌─────────────┐
│ 1. Entrar   │ → Username + Senha → Recebe TOKEN
└──────┬──────┘
       │
┌──────▼──────────────┐
│ 2. Criar Pasta      │ → Nome + Departamento → Recebe ID
└──────┬──────────────┘
       │
┌──────▼──────────────────────┐
│ 3. Upload Documentos        │ → Arquivo(s) → Recebe documentos criados
└──────┬───────────────────────┘
       │
┌──────▼──────────────┐
│ 4. Ver Documento    │ → ID → Recebe dados + conteúdo extraído
└──────┬──────────────┘
       │
┌──────▼──────────────┐
│ 5. Baixar Arquivo   │ → ID → Recebe arquivo original
└──────┬──────────────┘
       │
┌──────▼──────────────┐
│ 6. Deletar          │ → ID → Marca como deletado (recuperável)
└─────────────────────┘

CADA AÇÃO É REGISTRADA NA AUDITORIA
```

---

## 📋 Rotas (Endpoints)

| O que fazer       | Código | Rota                      | Precisa autenticado |
| ----------------- | ------ | ------------------------- | ------------------- |
| Entrar            | POST   | `/entrar`                 | ❌ Não              |
| Ver pastas        | GET    | `/pastas`                 | ✅ Sim              |
| Criar pasta       | POST   | `/pastas`                 | ✅ Sim              |
| Enviar documento  | POST   | `/pastas/{id}/upload`     | ✅ Sim              |
| Ver documento     | GET    | `/documentos/{id}`        | ✅ Sim              |
| Baixar arquivo    | GET    | `/documentos/{id}/baixar` | ✅ Sim              |
| Deletar documento | DELETE | `/documentos/{id}`        | ✅ Sim              |

---

## 🔐 Segurança

✅ **Login obrigatório** - Token gerado após entrar
✅ **Nomes aleatórios** - Arquivo "contrato.pdf" vira "abc123def456" em storage
✅ **Fora do site** - Não acessa via URL, só via controlador
✅ **Audit log** - Rastreia cada ação
✅ **Soft delete** - Documento marcado como deletado, não removido (pode recuperar)

---

## 🎬 Como Testar (Escolha Uma)

### Opção 1: PowerShell (Windows) ⭐ Mais fácil

```powershell
cd d:\Document_Management_System_ABS
./test_api.ps1
```

### Opção 2: Postman (Visual)

1. Abra Postman
2. `File` → `Import` → `postman_collection.json`
3. Clique em "Login"
4. Teste as rotas

### Opção 3: Script manual

```bash
# Login
curl -X POST http://localhost:8000/api/entrar \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

---

## 📚 Arquivos Criados

| Arquivo                      | Para quê?                    |
| ---------------------------- | ---------------------------- |
| **QUICK_START.md**           | Começar em 5 minutos         |
| **GUIA_TESTE_ROTAS.md**      | Entender cada rota           |
| **FLUXO_DADOS_DETALHADO.md** | Como funciona internamente   |
| **ARQUITETURA_VISUAL.md**    | Diagramas e fluxos           |
| **test_api.ps1**             | Teste automático (Windows)   |
| **test_api.sh**              | Teste automático (Linux/Mac) |
| **postman_collection.json**  | Teste visual (Postman)       |

---

## 🎨 Exemplo Prático

### Você quer: Guardar contratos de 2026

```
1. Entra no sistema
   POST /entrar
   ├─ email: seu.email@empresa.com
   └─ password: sua-senha

   Recebe: token = "1|AbCdEfGh..."

2. Cria pasta para contratos
   POST /pastas
   ├─ name: "Contratos 2026"
   ├─ department_id: "123-456" (Financeiro)

   Recebe:
   ├─ id: "999-888-777"
   └─ reference_code: "fin.contratos-2026"

3. Faz upload de 3 contratos
   POST /pastas/999-888-777/upload
   ├─ files: [contrato1.pdf, contrato2.pdf, contrato3.pdf]

   Recebe:
   ├─ Documento 1: reference_code = "fin.contratos-2026.26.001.contrato1"
   ├─ Documento 2: reference_code = "fin.contratos-2026.26.002.contrato2"
   └─ Documento 3: reference_code = "fin.contratos-2026.26.003.contrato3"

4. Seu gerente quer ver o primeiro
   GET /documentos/{doc1-id}

   Recebe documento completo com conteúdo extraído

5. Seu gerente baixa o arquivo
   GET /documentos/{doc1-id}/baixar

   Arquivo é transferido para o computador dele

6. Auditoria registra:
   ├─ Upload de 3 documentos às 14:40
   ├─ Visualização de documento por gerente às 15:10
   └─ Download de arquivo por gerente às 15:15
```

---

## 🧬 Dados Principais

### Pasta (Folder)

```json
{
    "id": "999-888-777",
    "name": "Contratos 2026",
    "reference_code": "fin.contratos-2026",
    "parent_id": null, // null = é raiz
    "is_root": true,
    "department_id": "123-456"
}
```

### Documento (Document)

```json
{
    "id": "doc-001",
    "folder_id": "999-888-777",
    "name": "contrato1.pdf",
    "reference_code": "fin.contratos-2026.26.001.contrato1",
    "size": 150000, // 150KB
    "year": 2026,
    "sequence_number": 1,
    "user_id": "user-001",
    "deleted_at": null // null = ativo, data = deletado
}
```

### Registro de Auditoria (AuditLog)

```json
{
    "id": 42,
    "user_id": "user-001",
    "action": "UPLOAD", // VIEW, DOWNLOAD, UPLOAD, CREATE, SOFT_DELETE
    "resource_type": "Document", // Document ou Folder
    "resource_id": "doc-001",
    "created_at": "2026-01-26T14:40:00Z"
}
```

---

## ❓ Perguntas Frequentes

### "Posso recuperar um documento deletado?"

✅ SIM! Soft delete só marca como deletado, não remove.

```php
// Para "restaurar":
UPDATE documents SET deleted_at = NULL WHERE id = ?
```

### "E se 2 pessoas subirem documentos ao mesmo tempo?"

✅ SEGURO! O sistema usa "locks pessimistas" que impedem duplicação de números.

### "Onde fica o arquivo fisicamente?"

📁 Em `storage/app/documents/{nome-aleatorio}`

- Nome aleatório protege o arquivo real
- Acesso só via controlador (registra na auditoria)
- Não é acessível via URL

### "Qual é o tamanho máximo?"

📦 50MB por arquivo

### "Quantos documentos posso subir de uma vez?"

♾️ Quantos couberem no timeout + 50MB cada

---

## 🎯 Checklist Rápido

- [ ] Li o `QUICK_START.md`
- [ ] Rodei o `test_api.ps1`
- [ ] Vi os arquivos criados em 6 segundos
- [ ] Criei uma pasta com sucesso
- [ ] Fiz upload de um documento
- [ ] Baixei o documento
- [ ] Verifiquei que está na auditoria

---

## 🏆 Resultado Final

Você tem um sistema:

- ✅ **Organizado**: Pastas hierárquicas com nomes únicos
- ✅ **Seguro**: Autenticação, nomes aleatórios, auditoria
- ✅ **Confiável**: Transações, locks, rollback automático
- ✅ **Rastreável**: Cada ação registrada com timestamp
- ✅ **Escalável**: Pronto para crescer

Agora é só **testar** e **começar a usar**! 🚀

---

## 📞 Próximos Passos

1. Rode o `test_api.ps1` agora
2. Leia `GUIA_TESTE_ROTAS.md` para entender melhor
3. Customize conforme necessário
4. Deploy em produção

**Let's go! 🎊**
