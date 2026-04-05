/**
 * Validação de regras de permissões
 * Regra principal: can_view (visualizar) é obrigatório se qualquer outra permissão é selecionada
 */

export interface PermissionValidationResult {
  isValid: boolean;
  errors: string[];
}

export interface PermissionPayload {
  can_view?: boolean;
  can_update_metadata?: boolean;
  can_delete?: boolean;
  can_download?: boolean;
  can_share?: boolean;
  can_upload?: boolean; // apenas para pastas
}

/**
 * Valida as permissões selecionadas
 * Regra: Se alguma permissão além de can_view está selecionada, can_view deve estar marcado
 */
export function validatePermissions(permissions: PermissionPayload): PermissionValidationResult {
  const errors: string[] = [];

  // Verifica se há outras permissões além de can_view
  const otherPermissions = Object.entries(permissions)
    .filter(([key, value]) => key !== "can_view" && value === true)
    .map(([key]) => key);

  // Se há outras permissões e can_view não está marcado, erro
  if (otherPermissions.length > 0 && !permissions.can_view) {
    errors.push("Visualizar (can_view) é obrigatório quando outras permissões são atribuídas");
  }

  return {
    isValid: errors.length === 0,
    errors,
  };
}

/**
 * Valida as permissões a partir de um array de chaves de permissão
 */
export function validatePermissionKeys(selectedKeys: string[]): PermissionValidationResult {
  const otherPermissions = selectedKeys.filter((key) => key !== "can_view");

  if (otherPermissions.length > 0 && !selectedKeys.includes("can_view")) {
    return {
      isValid: false,
      errors: ["Visualizar (can_view) é obrigatório quando outras permissões são atribuídas"],
    };
  }

  return {
    isValid: true,
    errors: [],
  };
}

/**
 * Retorna mensagem de erro formatada ou string vazia
 */
export function getValidationErrorMessage(selectedKeys: string[]): string {
  const validation = validatePermissionKeys(selectedKeys);
  return validation.errors[0] || "";
}
