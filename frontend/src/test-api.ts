/**
 * Arquivo de teste para debugar a API
 * Execute isto no console do navegador:
 *
 * 1. Abra o console (F12)
 * 2. Vá até a aba "Console"
 * 3. Copie e cole este código e execute
 */

import api from "./api/axios";

async function testApiUsers() {
  console.log("🧪 Testando API de utilizadores...");

  try {
    console.log("📡 Enviando requisição para /utilizadores...");
    const response = await api.get("/utilizadores");

    console.log("✅ Status:", response.status);
    console.log("✅ Headers:", response.headers);
    console.log("✅ Resposta completa:", response);
    console.log("✅ Dados (response.data):", response.data);

    // Verifica a estrutura
    if (response.data.data) {
      console.log("📊 Estrutura: { data: { data: [...] } }");
      console.log("📊 Quantidade de utilizadores:", response.data.data.length);
      console.log("📊 Primeiro utilizador:", response.data.data[0]);
    } else if (Array.isArray(response.data)) {
      console.log("📊 Estrutura: [...]");
      console.log("📊 Quantidade de utilizadores:", response.data.length);
      console.log("📊 Primeiro utilizador:", response.data[0]);
    } else {
      console.log("📊 Estrutura desconhecida:", response.data);
    }
  } catch (error) {
    console.error("❌ Erro:", error);
    if (error instanceof Error) {
      console.error("❌ Mensagem:", error.message);
    }
  }
}

// Exportar para usar no console
export { testApiUsers };

// Se estiver em um contexto que permite, executar automaticamente
if (typeof window !== "undefined") {
  (window as any).testApiUsers = testApiUsers;
  console.log(
    "✅ Função testApiUsers() disponível no console. Execute: testApiUsers()",
  );
}
