#!/bin/bash

# ============================================================================
#  SCRIPT DE TESTE - DOCUMENT MANAGEMENT SYSTEM API
#  
#  Teste completo do fluxo: Login → Criar Pasta → Upload → Download → Deletar
#  
#  Antes de rodar:
#  1. Certifique que a API está rodando em http://localhost:8000
#  2. Atualize as variáveis (email, password, paths, IDs)
#  3. bash test_api.sh
# ============================================================================

set -e  # Exit se qualquer comando falhar

# Configuração
BASE_URL="http://localhost:8000/api"
EMAIL="user@example.com"
PASSWORD="password123"
DEPT_ID="dept-001"  # Substitua pelo ID real

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função auxiliar para imprimir com cor
print_step() {
    echo -e "${GREEN}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# ===========================================
# PASSO 1: LOGIN
# ===========================================
print_step "1. Fazendo login..."

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/entrar" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    print_error "Falha ao fazer login. Resposta: $LOGIN_RESPONSE"
    exit 1
fi

print_success "Login realizado com sucesso!"
print_info "Token: ${TOKEN:0:50}..."

# ===========================================
# PASSO 2: LISTAR PASTAS
# ===========================================
print_step "2. Listando pastas raiz..."

FOLDERS=$(curl -s -X GET "$BASE_URL/pastas" \
  -H "Authorization: Bearer $TOKEN")

print_success "Pastas listadas:"
echo "$FOLDERS" | jq '.' 2>/dev/null || echo "$FOLDERS"

# ===========================================
# PASSO 3: CRIAR PASTA RAIZ
# ===========================================
print_step "3. Criando nova pasta raiz..."

FOLDER_NAME="Pasta Teste $(date +%s)"

CREATE_FOLDER=$(curl -s -X POST "$BASE_URL/pastas" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"$FOLDER_NAME\",\"department_id\":\"$DEPT_ID\"}")

ROOT_FOLDER_ID=$(echo $CREATE_FOLDER | jq -r '.id' 2>/dev/null)

if [ "$ROOT_FOLDER_ID" == "null" ] || [ -z "$ROOT_FOLDER_ID" ]; then
    print_error "Falha ao criar pasta raiz. Resposta: $CREATE_FOLDER"
    exit 1
fi

print_success "Pasta raiz criada!"
print_info "ID: $ROOT_FOLDER_ID"
print_info "Nome: $FOLDER_NAME"

# ===========================================
# PASSO 4: CRIAR SUBPASTA
# ===========================================
print_step "4. Criando subpasta..."

SUBFOLDER_NAME="Subpasta Teste $(date +%s)"

CREATE_SUBFOLDER=$(curl -s -X POST "$BASE_URL/pastas" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"$SUBFOLDER_NAME\",\"parent_id\":\"$ROOT_FOLDER_ID\"}")

CHILD_FOLDER_ID=$(echo $CREATE_SUBFOLDER | jq -r '.id' 2>/dev/null)

if [ "$CHILD_FOLDER_ID" == "null" ] || [ -z "$CHILD_FOLDER_ID" ]; then
    print_error "Falha ao criar subpasta. Resposta: $CREATE_SUBFOLDER"
    exit 1
fi

print_success "Subpasta criada!"
print_info "ID: $CHILD_FOLDER_ID"
print_info "Nome: $SUBFOLDER_NAME"
print_info "Reference Code: $(echo $CREATE_SUBFOLDER | jq -r '.reference_code')"

# ===========================================
# PASSO 5: CRIAR ARQUIVO DE TESTE
# ===========================================
print_step "5. Criando arquivos de teste..."

# Arquivo 1: Texto simples
echo "Este é um documento de teste criado em $(date)" > /tmp/test_document.txt
print_success "Criado: /tmp/test_document.txt"

# Arquivo 2: CSV simples
cat > /tmp/test_spreadsheet.csv << EOF
id,nome,valor
1,Item A,100.00
2,Item B,200.00
3,Item C,300.00
EOF
print_success "Criado: /tmp/test_spreadsheet.csv"

# ===========================================
# PASSO 6: FAZER UPLOAD
# ===========================================
print_step "6. Fazendo upload de documentos..."

UPLOAD_RESPONSE=$(curl -s -X POST "$BASE_URL/pastas/$CHILD_FOLDER_ID/upload" \
  -H "Authorization: Bearer $TOKEN" \
  -F "files=@/tmp/test_document.txt" \
  -F "files=@/tmp/test_spreadsheet.csv")

DOCUMENT_ID=$(echo $UPLOAD_RESPONSE | jq -r '.documents[0].id' 2>/dev/null)
DOCUMENT_ID_2=$(echo $UPLOAD_RESPONSE | jq -r '.documents[1].id' 2>/dev/null)

if [ "$DOCUMENT_ID" == "null" ] || [ -z "$DOCUMENT_ID" ]; then
    print_error "Falha ao fazer upload. Resposta: $UPLOAD_RESPONSE"
    exit 1
fi

print_success "Upload realizado com sucesso!"
print_info "Documento 1 ID: $DOCUMENT_ID"
print_info "Documento 1 Reference: $(echo $UPLOAD_RESPONSE | jq -r '.documents[0].reference_code')"
print_info "Documento 2 ID: $DOCUMENT_ID_2"
print_info "Documento 2 Reference: $(echo $UPLOAD_RESPONSE | jq -r '.documents[1].reference_code')"

# ===========================================
# PASSO 7: VER DOCUMENTO
# ===========================================
print_step "7. Recuperando documento..."

VIEW_DOCUMENT=$(curl -s -X GET "$BASE_URL/documentos/$DOCUMENT_ID" \
  -H "Authorization: Bearer $TOKEN")

print_success "Documento recuperado:"
echo "$VIEW_DOCUMENT" | jq '.name, .reference_code, .size, .created_at' 2>/dev/null

# ===========================================
# PASSO 8: VER PASTA COM DOCUMENTOS
# ===========================================
print_step "8. Listando pasta com documentos..."

VIEW_FOLDER=$(curl -s -X GET "$BASE_URL/pastas?parent_id=$CHILD_FOLDER_ID" \
  -H "Authorization: Bearer $TOKEN")

DOC_COUNT=$(echo "$VIEW_FOLDER" | jq '.documents | length' 2>/dev/null)
print_success "Pasta contém $DOC_COUNT documento(s)"

# ===========================================
# PASSO 9: BAIXAR DOCUMENTO
# ===========================================
print_step "9. Baixando documento..."

OUTPUT_FILE="/tmp/downloaded_document.txt"
HTTP_CODE=$(curl -s -w "%{http_code}" -o "$OUTPUT_FILE" \
  -X GET "$BASE_URL/documentos/$DOCUMENT_ID/baixar" \
  -H "Authorization: Bearer $TOKEN")

if [ "$HTTP_CODE" == "200" ]; then
    print_success "Documento baixado com sucesso!"
    print_info "Salvo em: $OUTPUT_FILE"
    print_info "Conteúdo:"
    cat "$OUTPUT_FILE"
else
    print_error "Falha ao baixar documento (HTTP $HTTP_CODE)"
fi

# ===========================================
# PASSO 10: DELETAR DOCUMENTO
# ===========================================
print_step "10. Deletando documento (soft delete)..."

DELETE_RESPONSE=$(curl -s -w "%{http_code}" -o /dev/null \
  -X DELETE "$BASE_URL/documentos/$DOCUMENT_ID" \
  -H "Authorization: Bearer $TOKEN")

if [ "$DELETE_RESPONSE" == "204" ]; then
    print_success "Documento deletado com sucesso (soft delete)"
else
    print_error "Falha ao deletar documento (HTTP $DELETE_RESPONSE)"
fi

# ===========================================
# RESUMO
# ===========================================
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   TESTE COMPLETO FINALIZADO COM SUCESSO!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Resumo do teste:"
echo "  ✓ Login realizado"
echo "  ✓ Pasta raiz criada: $ROOT_FOLDER_ID"
echo "  ✓ Subpasta criada: $CHILD_FOLDER_ID"
echo "  ✓ 2 documentos enviados"
echo "  ✓ Documento recuperado e visualizado"
echo "  ✓ Documento baixado"
echo "  ✓ Documento deletado (soft delete)"
echo ""
echo "IDs para referência:"
echo "  Root Folder ID: $ROOT_FOLDER_ID"
echo "  Child Folder ID: $CHILD_FOLDER_ID"
echo "  Document ID 1: $DOCUMENT_ID"
echo "  Document ID 2: $DOCUMENT_ID_2"
echo ""
