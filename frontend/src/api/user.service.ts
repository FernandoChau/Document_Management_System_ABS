import api from "./axios";

export type CreateUserDTO = {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export type LoginUserDTO = {
  email: string;
  password: string;
};

export async function createUser(data: CreateUserDTO) {
  return api.post("/register", data);
}

export async function loginUser(data: LoginUserDTO) {
  return api.post("/login", data);
}

export async function getUsers() {
  return api.get("/users");
}
