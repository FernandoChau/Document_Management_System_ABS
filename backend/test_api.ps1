# Teste Rápido - Document Management System API
# Para usar no PowerShell (Windows)

# ===================== CONFIGURAÇÃO =====================
$BASE_URL = "http://localhost:8000/api"
$EMAIL = "user@example.com"
$PASSWORD = "password123"
$DEPT_ID = "dept-001"

# ===================== PASSO 1: LOGIN =====================
Write-Host "1️⃣  Fazendo login..." -ForegroundColor Green

$loginBody = @{
    email = $EMAIL
    password = $PASSWORD
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "$BASE_URL/entrar" `
    -Method Post `
    -ContentType "application/json" `
    -Body $loginBody

$TOKEN = $loginResponse.token
Write-Host "✓ Token obtido: $($TOKEN.Substring(0,30))..." -ForegroundColor Green
Write-Host ""

# ===================== PASSO 2: LISTAR PASTAS =====================
Write-Host "2️⃣  Listando pastas raiz..." -ForegroundColor Green

$foldersResponse = Invoke-RestMethod -Uri "$BASE_URL/pastas" `
    -Method Get `
    -Headers @{"Authorization" = "Bearer $TOKEN"}

Write-Host "✓ $($foldersResponse.Count) pasta(s) encontrada(s)" -ForegroundColor Green
$foldersResponse | ConvertTo-Json | Write-Host
Write-Host ""

# ===================== PASSO 3: CRIAR PASTA =====================
Write-Host "3️⃣  Criando nova pasta..." -ForegroundColor Green

$timestamp = [int][double]::Parse((Get-Date -UFormat %s))
$folderName = "Pasta Teste $timestamp"

$createFolderBody = @{
    name = $folderName
    department_id = $DEPT_ID
} | ConvertTo-Json

$createFolderResponse = Invoke-RestMethod -Uri "$BASE_URL/pastas" `
    -Method Post `
    -Headers @{"Authorization" = "Bearer $TOKEN"} `
    -ContentType "application/json" `
    -Body $createFolderBody

$ROOT_FOLDER_ID = $createFolderResponse.id
Write-Host "✓ Pasta criada: $ROOT_FOLDER_ID" -ForegroundColor Green
Write-Host "  Nome: $($createFolderResponse.name)" -ForegroundColor Yellow
Write-Host "  Reference: $($createFolderResponse.reference_code)" -ForegroundColor Yellow
Write-Host ""

# ===================== PASSO 4: CRIAR SUBPASTA =====================
Write-Host "4️⃣  Criando subpasta..." -ForegroundColor Green

$subfolderName = "Subpasta Teste $timestamp"

$createSubfolderBody = @{
    name = $subfolderName
    parent_id = $ROOT_FOLDER_ID
} | ConvertTo-Json

$createSubfolderResponse = Invoke-RestMethod -Uri "$BASE_URL/pastas" `
    -Method Post `
    -Headers @{"Authorization" = "Bearer $TOKEN"} `
    -ContentType "application/json" `
    -Body $createSubfolderBody

$CHILD_FOLDER_ID = $createSubfolderResponse.id
Write-Host "✓ Subpasta criada: $CHILD_FOLDER_ID" -ForegroundColor Green
Write-Host "  Nome: $($createSubfolderResponse.name)" -ForegroundColor Yellow
Write-Host "  Reference: $($createSubfolderResponse.reference_code)" -ForegroundColor Yellow
Write-Host ""

# ===================== PASSO 5: CRIAR ARQUIVOS DE TESTE =====================
Write-Host "5️⃣  Criando arquivos de teste..." -ForegroundColor Green

$testFile1 = "C:\temp\test_document.txt"
$testFile2 = "C:\temp\test_spreadsheet.csv"

# Criar pasta temp se não existir
if (-not (Test-Path "C:\temp")) {
    New-Item -ItemType Directory -Path "C:\temp" | Out-Null
}

# Arquivo 1
@"
Este é um documento de teste
Criado em: $(Get-Date)
Sistema de Gestão Documental
"@ | Out-File -FilePath $testFile1 -Encoding UTF8

Write-Host "✓ Arquivo criado: $testFile1" -ForegroundColor Green

# Arquivo 2
@"
id,nome,valor
1,Item A,100.00
2,Item B,200.00
3,Item C,300.00
"@ | Out-File -FilePath $testFile2 -Encoding UTF8

Write-Host "✓ Arquivo criado: $testFile2" -ForegroundColor Green
Write-Host ""

# ===================== PASSO 6: FAZER UPLOAD =====================
Write-Host "6️⃣  Fazendo upload de documentos..." -ForegroundColor Green

$form = @{
    files = @(
        (Get-Item -Path $testFile1),
        (Get-Item -Path $testFile2)
    )
}

$uploadResponse = Invoke-RestMethod -Uri "$BASE_URL/pastas/$CHILD_FOLDER_ID/upload" `
    -Method Post `
    -Headers @{"Authorization" = "Bearer $TOKEN"} `
    -Form $form

$DOCUMENT_ID = $uploadResponse.documents[0].id
$DOCUMENT_ID_2 = $uploadResponse.documents[1].id

Write-Host "✓ Upload realizado com sucesso!" -ForegroundColor Green
Write-Host "  Doc 1 ID: $DOCUMENT_ID" -ForegroundColor Yellow
Write-Host "  Doc 1 Reference: $($uploadResponse.documents[0].reference_code)" -ForegroundColor Yellow
Write-Host "  Doc 2 ID: $DOCUMENT_ID_2" -ForegroundColor Yellow
Write-Host "  Doc 2 Reference: $($uploadResponse.documents[1].reference_code)" -ForegroundColor Yellow
Write-Host ""

# ===================== PASSO 7: VER DOCUMENTO =====================
Write-Host "7️⃣  Recuperando documento..." -ForegroundColor Green

$viewDocumentResponse = Invoke-RestMethod -Uri "$BASE_URL/documentos/$DOCUMENT_ID" `
    -Method Get `
    -Headers @{"Authorization" = "Bearer $TOKEN"}

Write-Host "✓ Documento recuperado:" -ForegroundColor Green
Write-Host "  ID: $($viewDocumentResponse.id)" -ForegroundColor Yellow
Write-Host "  Nome: $($viewDocumentResponse.name)" -ForegroundColor Yellow
Write-Host "  Tamanho: $($viewDocumentResponse.size) bytes" -ForegroundColor Yellow
Write-Host "  MIME Type: $($viewDocumentResponse.mime_type)" -ForegroundColor Yellow
Write-Host "  Reference: $($viewDocumentResponse.reference_code)" -ForegroundColor Yellow
Write-Host ""

# ===================== PASSO 8: VER PASTA COM DOCUMENTOS =====================
Write-Host "8️⃣  Listando pasta com documentos..." -ForegroundColor Green

$viewFolderResponse = Invoke-RestMethod -Uri "$BASE_URL/pastas?parent_id=$CHILD_FOLDER_ID" `
    -Method Get `
    -Headers @{"Authorization" = "Bearer $TOKEN"}

$docCount = $viewFolderResponse.documents.Count
Write-Host "✓ Pasta contém $docCount documento(s)" -ForegroundColor Green
$viewFolderResponse.documents | ForEach-Object {
    Write-Host "  - $($_.name) ($($_.reference_code))" -ForegroundColor Yellow
}
Write-Host ""

# ===================== PASSO 9: BAIXAR DOCUMENTO =====================
Write-Host "9️⃣  Baixando documento..." -ForegroundColor Green

$downloadPath = "C:\temp\downloaded_document.txt"

Invoke-WebRequest -Uri "$BASE_URL/documentos/$DOCUMENT_ID/baixar" `
    -Method Get `
    -Headers @{"Authorization" = "Bearer $TOKEN"} `
    -OutFile $downloadPath

Write-Host "✓ Documento baixado com sucesso!" -ForegroundColor Green
Write-Host "  Salvo em: $downloadPath" -ForegroundColor Yellow
Write-Host "  Conteúdo:" -ForegroundColor Yellow
Get-Content -Path $downloadPath | Write-Host
Write-Host ""

# ===================== PASSO 10: DELETAR DOCUMENTO =====================
Write-Host "🔟 Deletando documento (soft delete)..." -ForegroundColor Green

$deleteResponse = Invoke-WebRequest -Uri "$BASE_URL/documentos/$DOCUMENT_ID" `
    -Method Delete `
    -Headers @{"Authorization" = "Bearer $TOKEN"}

Write-Host "✓ Documento deletado (soft delete)!" -ForegroundColor Green
Write-Host "  Status Code: $($deleteResponse.StatusCode)" -ForegroundColor Yellow
Write-Host ""

# ===================== RESUMO =====================
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "✅ TESTE COMPLETO FINALIZADO COM SUCESSO!" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host ""
Write-Host "Resumo do teste:" -ForegroundColor Cyan
Write-Host "  ✓ Login realizado" -ForegroundColor Yellow
Write-Host "  ✓ Pasta raiz criada" -ForegroundColor Yellow
Write-Host "  ✓ Subpasta criada" -ForegroundColor Yellow
Write-Host "  ✓ 2 documentos enviados" -ForegroundColor Yellow
Write-Host "  ✓ Documento recuperado e visualizado" -ForegroundColor Yellow
Write-Host "  ✓ Documento baixado" -ForegroundColor Yellow
Write-Host "  ✓ Documento deletado (soft delete)" -ForegroundColor Yellow
Write-Host ""
Write-Host "IDs para referência:" -ForegroundColor Cyan
Write-Host "  Root Folder ID:    $ROOT_FOLDER_ID" -ForegroundColor Gray
Write-Host "  Child Folder ID:   $CHILD_FOLDER_ID" -ForegroundColor Gray
Write-Host "  Document ID 1:     $DOCUMENT_ID" -ForegroundColor Gray
Write-Host "  Document ID 2:     $DOCUMENT_ID_2" -ForegroundColor Gray
Write-Host ""
Write-Host "Para rodar este script novamente:" -ForegroundColor Cyan
Write-Host "  powershell -ExecutionPolicy Bypass -File test_api.ps1" -ForegroundColor Gray
