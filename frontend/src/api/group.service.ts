import api from "./axios";

// ✅ Tipo para dados do Group
export type Group = {
  id: string;
  name: string;
  description?: string;
  created_by?: string;
  created_at?: string;
  updated_at?: string;
};

// ✅ Tipo para resposta da listagem de grupos
export type ListGroupsResponse = {
  status: string;
  groups: Group[];
};

// ✅ Tipo de resposta para um único grupo
export type ShowGroupResponse = {
  status: string;
  group: Group;
};

// ✅ Funções auxiliares
export function listGroups() {
  return api.get<ListGroupsResponse>("/grupos");
}

export function showGroup(id: string) {
  return api.get<ShowGroupResponse>(`/grupos/${id}`);
}

export function createGroup(payload: { name: string; description?: string }) {
  return api.post<Group>("/grupos", payload);
}

export function updateGroup(id: string, payload: { name: string; description?: string }) {
  return api.put<Group>(`/grupos/${id}`, payload);
}

export function deleteGroup(id: string) {
  return api.delete(`/grupos/${id}`);
}
