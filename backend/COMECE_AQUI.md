# 🎬 COMECE AQUI - 3 PASSOS

## Passo 1: Leia (5 minutos)

Abra e leia este arquivo:

```
📄 RESUMO_SIMPLES.md
```

## Passo 2: Teste (30 segundos)

Execute este comando no PowerShell:

```powershell
cd d:\Document_Management_System_ABS
./test_api.ps1
```

Você verá:

```
✓ Login realizado
✓ Pasta raiz criada
✓ Subpasta criada
✓ 2 documentos enviados
✓ Documento recuperado
✓ Documento baixado
✓ Documento deletado
```

## Passo 3: Entenda (20 minutos)

Abra e leia:

```
📄 GUIA_TESTE_ROTAS.md
```

---

## 📚 Documentos Disponíveis

| #   | Nome                          | O quê               | Tempo  |
| --- | ----------------------------- | ------------------- | ------ |
| 1️⃣  | **RESUMO_SIMPLES.md**         | Entender o sistema  | 5 min  |
| 2️⃣  | **QUICK_START.md**            | Começar testes      | 10 min |
| 3️⃣  | **GUIA_TESTE_ROTAS.md**       | Referência de rotas | 20 min |
| 4️⃣  | **FLUXO_DADOS_DETALHADO.md**  | Como funciona       | 15 min |
| 5️⃣  | **ARQUITETURA_VISUAL.md**     | Diagramas           | 15 min |
| 6️⃣  | **README_TESTE.md**           | Resumo executivo    | 10 min |
| 7️⃣  | **INDEX.md**                  | Índice e mapa       | 5 min  |
| 8️⃣  | **VISUAL_SUMMARY.txt**        | Resumo em ASCII     | 10 min |
| 9️⃣  | **LISTA_ARQUIVOS_CRIADOS.md** | Lista de tudo       | 10 min |

---

## 🧪 Scripts de Teste

| Script                      | Plataforma | Como rodar         |
| --------------------------- | ---------- | ------------------ |
| **test_api.ps1**            | Windows    | `./test_api.ps1`   |
| **test_api.sh**             | Linux/Mac  | `bash test_api.sh` |
| **postman_collection.json** | Postman    | Import no Postman  |

---

## 🎯 O Sistema em 30 Segundos

```
LOGIN → CRIA PASTA → UPLOAD → BAIXA → DELETA

Cada ação registrada em AUDITORIA
```

### Rotas Principais:

- `POST /entrar` - Login
- `POST /pastas` - Criar pasta
- `POST /pastas/{id}/upload` - Upload
- `GET /documentos/{id}` - Ver
- `GET /documentos/{id}/baixar` - Baixar
- `DELETE /documentos/{id}` - Deletar

---

## ✅ Pronto?

1. Execute: `./test_api.ps1`
2. Leia: `RESUMO_SIMPLES.md`
3. Explore: `GUIA_TESTE_ROTAS.md`

**Vamos lá! 🚀**
