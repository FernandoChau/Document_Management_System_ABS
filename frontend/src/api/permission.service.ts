import api from "./axios";

export interface PermissionAssignPayload {
  can_view?: boolean;
  can_update_metadata?: boolean;
  can_delete?: boolean;
  can_download?: boolean;
  can_share?: boolean;
  can_upload?: boolean; // apenas para pastas
  can_manage_permissions?: boolean;
  user_id?: string;
  group_id?: string;
}

export interface Permission {
  id: string;
  user_id?: string;
  group_id?: string;
  can_view: boolean;
  can_update_metadata: boolean;
  can_delete: boolean;
  can_download: boolean;
  can_share: boolean;
  can_upload?: boolean;
  can_manage_permissions: boolean;
  created_at?: string;
  updated_at?: string;
  user?: { id: string; name: string; email?: string };
  group?: { id: string; name: string };
}

export interface ListPermissionsResponse {
  status: string;
  data: Permission[];
  permissoes?: Permission[]; // fallback
}

/**
 * Lista as permissões de uma pasta
 */
export function listFolderPermissions(folderId: string) {
  return api.get<ListPermissionsResponse>(`/pastas/${folderId}/permissoes`);
}

/**
 * Cria/Atribui uma nova permissão a uma pasta
 */
export function createFolderPermission(folderId: string, payload: PermissionAssignPayload) {
  return api.post<Permission>(`/pastas/${folderId}/permissoes`, payload);
}

/**
 * Atualiza uma permissão existente de uma pasta
 */
export function updateFolderPermission(
  folderId: string,
  permissionId: string,
  payload: PermissionAssignPayload
) {
  return api.put<Permission>(
    `/pastas/${folderId}/permissoes/${permissionId}`,
    payload
  );
}

/**
 * Remove a permissão de uma pasta
 */
export function deleteFolderPermission(folderId: string, permissionId: string) {
  return api.delete(`/pastas/${folderId}/permissoes/${permissionId}`);
}

/**
 * Lista as permissões de um documento
 */
export function listDocumentPermissions(documentId: string) {
  return api.get<ListPermissionsResponse>(`/documentos/${documentId}/permissoes`);
}

/**
 * Cria/Atribui uma nova permissão a um documento
 */
export function createDocumentPermission(documentId: string, payload: PermissionAssignPayload) {
  return api.post<Permission>(`/documentos/${documentId}/permissoes`, payload);
}

/**
 * Atualiza uma permissão existente de um documento
 */
export function updateDocumentPermission(
  documentId: string,
  permissionId: string,
  payload: PermissionAssignPayload
) {
  return api.put<Permission>(
    `/documentos/${documentId}/permissoes/${permissionId}`,
    payload
  );
}

/**
 * Remove a permissão de um documento
 */
export function deleteDocumentPermission(documentId: string, permissionId: string) {
  return api.delete(`/documentos/${documentId}/permissoes/${permissionId}`);
}

/**
 * Função auxiliar para atribuir permissões (POST ou PUT baseado em se já existe)
 * @param itemType - 'folder' ou 'document'
 * @param itemId - ID da pasta ou documento
 * @param targetId - ID do utilizador ou grupo
 * @param targetType - 'user' ou 'group'
 * @param permissions - objeto com as permissões a atribuir
 */
export async function assignPermission(
  itemType: "folder" | "document",
  itemId: string,
  targetId: string,
  targetType: "user" | "group",
  permissions: Omit<PermissionAssignPayload, "user_id" | "group_id">
) {
  // DEBUG: Log all inputs
  console.log("[PermissionService.assignPermission] Inputs:", {
    itemType,
    itemId,
    itemIdLength: itemId?.length,
    itemIdTrimmed: itemId?.trim(),
    targetId,
    targetType,
    permissions
  });

  // Validação: itemId não pode estar vazio
  if (!itemId || itemId.trim() === "") {
    const resourceName = itemType === "folder" ? "pasta" : "documento";
    const errorMsg = `ID do ${resourceName} não pode estar vazio`;
    console.error("[PermissionService] Validation failed:", { itemId, resourceName, errorMsg });
    throw new Error(errorMsg);
  }

  try {
    // 1. Buscar permissões existentes
    const listFn = itemType === "folder" ? listFolderPermissions : listDocumentPermissions;
    console.log("[PermissionService] Fetching existing permissions for:", itemId);
    const listResponse = await listFn(itemId);
    const existingPermissions = Array.isArray(listResponse.data) 
      ? listResponse.data 
      : (listResponse.data.data || listResponse.data.permissoes || []);

    // 2. Encontrar se existe permissão para este target
    const existingPermission = existingPermissions.find((p: Permission) => {
      if (targetType === "user") {
        return p.user_id === targetId;
      }
      return p.group_id === targetId;
    });

    // 3. Construir payload
    const payload: PermissionAssignPayload = {
      ...permissions,
      [targetType === "user" ? "user_id" : "group_id"]: targetId,
    };

    console.log("[PermissionService] Built payload:", JSON.stringify(payload, null, 2));

    // 4. POST se não existe, PUT se existe
    if (!existingPermission) {
      console.log("[PermissionService] No existing permission found. Creating new (POST)...");
      const createFn =
        itemType === "folder" ? createFolderPermission : createDocumentPermission;
      const response = await createFn(itemId, payload);
      console.log("[PermissionService] POST response:", response.data);
      return response;
    } else {
      console.log("[PermissionService] Existing permission found. Updating (PUT)...");
      const updateFn =
        itemType === "folder" ? updateFolderPermission : updateDocumentPermission;
      const response = await updateFn(itemId, existingPermission.id, payload);
      console.log("[PermissionService] PUT response:", response.data);
      return response;
    }
  } catch (error) {
    console.error(`Erro ao atribuir permissão:`, error);
    throw error;
  }
}
