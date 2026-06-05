import { useEffect, useState } from "react";
import {
  activateUser,
  AdminCreateUserDTO,
  createUserByAdmin,
  deactivateUser,
  listUsers,
  redefineUserPassword,
  showUser,
  User,
} from "../../../api/user.service";
// import { AlertCircleIcon } from "@heroicons/react/24/outline";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../ui/table";
import {
  MoreDotIcon,
  PencilIcon,
  EyeIcon,
  TrashBinIcon,
  PaperPlaneIcon,
} from "../../../icons";
import {
  AdjustmentsHorizontalIcon,
  CheckBadgeIcon,
  CheckCircleIcon,
  DocumentPlusIcon,
  FolderPlusIcon,
  KeyIcon,
  LockClosedIcon,
  MagnifyingGlassIcon,
  NoSymbolIcon,
  UserIcon,
  UserPlusIcon,
  UsersIcon,
} from "@heroicons/react/24/outline";
import { Dropdown } from "../../ui/dropdown/Dropdown";
import { DropdownItem } from "../../ui/dropdown/DropdownItem";
import Button from "../../ui/button/Button";
import { AlertCircle, AlertCircleIcon, Eye, UserCircle } from "lucide-react";
import { Modal } from "@/components/ui/modal";
import Label from "@/components/form/Label";
import Input from "@/components/form/input/InputField";
import Select from "@/components/form/Select";
import SelectInputs from "@/components/form/form-elements/SelectInputs";
import Checkbox from "@/components/form/input/Checkbox";
import CreateUserModal from "@/components/modals/usersModal/CreateUserModal";

import DeactivateUserModal from "@/components/modals/usersModal/DeactivateUserModal";
import ActiveUserModal from "@/components/modals/usersModal/ActiveUserModal";
import ResetUserPassword from "@/components/modals/usersModal/ResetUserPassword";
import UserLogsModal from "@/components/modals/usersModal/UserLogsModal";

function UserTable() {
  const [activeModal, setActiveModal] = useState<
    | "create"
    | "edit"
    | "view"
    | "delete"
    | "active"
    | "deactive"
    | "resetPassword"
    | null
  >(null);

  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [users, setUsers] = useState<User[]>([]);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [openMenuId, setOpenMenuId] = useState<string | null>(null);

  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setLoading(true);
        setError(null);

        console.log("🔄 Iniciando fetch de utilizadores...");

        const response = await listUsers();
        console.log("✅ Resposta do servidor:", response);

        const userData = response.data.users;
        console.log("📊 Dados processados:", userData);

        setUsers(Array.isArray(userData) ? userData : []);
        console.log("✅ Utilizadores carregados com sucesso");
      } catch (err) {
        console.error("❌ Erro ao carregar utilizadores:", err);
        const errorMsg =
          err instanceof Error
            ? err.message
            : "Falha ao carregar utilizadores. Tente novamente.";
        setError(errorMsg);
      } finally {
        setLoading(false);
      }
    };

    fetchUsers();
  }, []);

  const openCreateUserModal = () => {
    setActiveModal("create");
    closeDropdown();
  };
  const handleStoreUser = async (
    name: string,
    email: string,
    isActive: boolean,
    role: string,
    phone?: string,
    profession?: string,
  ): Promise<void> => {
    const newUser: AdminCreateUserDTO = {
      name,
      email,
      is_active: isActive,
      role: role as "admin" | "user",
      phone,
      profession,
    };

    // Deixa o erro propagar para o modal tratar (feedback de validação)
    const response = await createUserByAdmin(newUser);
    console.log("Resposta da criação:", response);

    // O backend retorna { status, message, user } — aceder a .user
    const createdUser = response.data.user;
    setUsers((prev) => [...prev, createdUser]);
    setActiveModal(null);
  };

  const handleView = async (userId?: string) => {
    if (!userId) {
      return;
    }

    try {
      const response = await showUser(userId);
      setSelectedUser(response.data.user);
      setActiveModal("view");
    } catch (error) {
      console.error("Erro ao carregar utilizador:", error);
    }
  };

  const openEditUserModal = (userId?: string) => {
    if (!userId) {
      return;
    }
    console.log("Editar usuário:", userId);
    closeDropdown();
    setActiveModal("edit");
  };
  const updateUser = (userId: string) => {};

  const openActiveUserModal = (userId?: string) => {
    if (!userId) {
      return;
    }
    setSelectedUser(users.find((user) => user.id === userId) || null);
    setActiveModal("active");
    closeDropdown();
  };
  const activeUser = async (userId: string) => {
    console.log("User id: ", userId);

    try {
      const response = await activateUser(userId);
      console.log("Resposta da ativacao:", response);

      // O backend retorna { status, message, user } — aceder a .user
      const updatedUser = response.data.user;
      setUsers((prev) =>
        prev.map((user) => (user.id === userId ? updatedUser : user)),
      );
    } catch (error) {
      console.error("Erro ao ativar utilizador:", error);
    }

    setActiveModal(null);
  };

  const openDeactiveUserModal = (userId?: string) => {
    if (!userId) {
      return;
    }
    setSelectedUser(users.find((user) => user.id === userId) || null);
    closeDropdown();
    setActiveModal("deactive");
  };
  const deactiveUser = async (userId: string) => {
    console.log("User id: ", userId);

    try {
      const response = await deactivateUser(userId);
      console.log("Resposta da desativacao:", response);

      // O backend retorna { status, message, user } — aceder a .user
      const updatedUser = response.data.user;
      setUsers((prev) =>
        prev.map((user) => (user.id === userId ? updatedUser : user)),
      );
    } catch (error) {
      console.error("Erro ao desativar utilizador:", error);
    }

    setActiveModal(null);
  };

  const openReseteUserModal = (userId?: string) => {
    if (!userId) {
      return;
    }
    setSelectedUser(users.find((user) => user.id === userId) || null);
    setActiveModal("resetPassword");
    closeDropdown();
  };
  const reseteUserPassword = async (userId: string, password: string) => {
    try {
      const response = await redefineUserPassword(userId, {
        password: password,
        password_confirmation: password,
      });
      console.log("Resposta da redefinicao de senha:", response);
    } catch (error) {
      console.error("Erro ao redefinir password:", error);
      throw error;
    }
  };

  const openDeleteUserModal = (userId?: string) => {
    if (!userId) {
      return;
    }
    console.log("Remover usuário:", userId);
    closeDropdown();
    setActiveModal("delete");
  };
  const deleteUserModal = (userId: string) => {};

  const toggleDropdown = (userId?: string) => {
    if (!userId) {
      return;
    }
    setOpenMenuId(openMenuId === userId ? null : userId);
  };
  const closeDropdown = () => {
    setOpenMenuId(null);
  };

  if (loading) {
    console.log("⏳ LOADING...");
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500 mx-auto"></div>
          {/* <p className="mt-4 text-gray-600 dark:text-gray-400">
            A carregar utilizadores...
          </p> */}
        </div>
      </div>
    );
  }
  if (error) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <AlertCircleIcon className="w-12 h-12 text-error-500 mx-auto" />
          <p className="mt-4 text-error-500">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="mt-4 px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600"
          >
            Tentar Novamente
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-h-[calc(100vh-110px)] overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
      <div className="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 className="text-lg flex items-center gap-2 text-gray-800 dark:text-white/90">
            <UsersIcon className="size-5" /> Utilizadores {/*({users.length})*/}
          </h3>
        </div>

        <div className="flex items-center gap-3">
          <button className="w-40 h-10 pl-2.5 flex items-center justify-start gap-2 rounded-full border border-gray-300 bg-white text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <MagnifyingGlassIcon className="size-4.5" />
            Pesquisar...
          </button>
          <button className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <AdjustmentsHorizontalIcon className="size-5" />
            Filtro
          </button>
          <Button
            onClick={openCreateUserModal}
            variant="primary"
            className="h-10 bg-brand-500 dark:bg-brand-500 dark:text-gray-950 dark:hover:opacity-95"
          >
            <UserPlusIcon className="size-5" />
          </Button>
        </div>
      </div>

      <div className="max-w-full max-h-[calc(100vh-200px)] overflow-x-auto">
        <Table>
          <TableHeader className="sticky border-gray-100 dark:border-gray-800 border-y">
            <TableRow>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Nome
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Email
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Função
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Profissão
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Status
              </TableCell>
              <TableCell
                isHeader
                className="py-3 w-5 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Acção
              </TableCell>
            </TableRow>
          </TableHeader>

          <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
            {users.length === 0 ? (
              <TableRow>
                <TableCell className="py-16 text-center" colSpan={6}>
                  <div className="flex flex-col items-center gap-2">
                    <UserIcon className="w-10 h-10 text-gray-300 dark:text-gray-600" />
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                      Nenhum utilizador encontrado
                    </p>
                  </div>
                </TableCell>
              </TableRow>
            ) : null}
            {users.map((user) => (
              <TableRow
                key={user.id}
                className="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800/30"
              >
                <TableCell className="py-2.5">
                  <div className="flex items-center gap-2">
                    <div className="h-[40px] w-[40px] bg-gray-100 text-gray-700 text-sm rounded-full flex items-center justify-center overflow-hidden">
                      <UserIcon className="size-6 text-gray-400 dark:text-gray-300" />
                    </div>
                    <div>
                      <p className="font-medium text-gray-700 text-theme-m dark:text-white/80">
                        {user.name}
                      </p>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="py-2.5 text-gray-500 text-theme-sm dark:text-gray-400">
                  {user.email}
                </TableCell>
                <TableCell className="py-2.5 text-gray-500 text-theme-sm dark:text-gray-400">
                  <span className="inline-block px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-theme-xs font-medium">
                    {user.role === "admin" ? (
                      <span className="flex items-center gap-1">
                        {" "}
                        <CheckBadgeIcon className="size-4.5" />{" "}
                        Administrador{" "}
                      </span>
                    ) : (
                      "👤 Usuário"
                    )}
                  </span>
                </TableCell>
                <TableCell className="py-2.5 text-gray-500 text-theme-sm dark:text-gray-400">
                  {user.profession || "N/A"}
                </TableCell>
                <TableCell className="py-2.5 text-gray-500 text-theme-sm dark:text-gray-400">
                  <span
                    className={`inline-block px-3 py-1 rounded-full text-theme-xs font-medium ${
                      user.is_active
                        ? "bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400"
                        : "bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400"
                    }`}
                  >
                    {user.is_active ? "✓ Ativo" : "✗ Inativo"}
                  </span>
                </TableCell>
                <TableCell className="py-2.5 pr-5 flex gap-2">
                  <button
                    onClick={() => handleView(user.id)}
                    className=" relative h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full"
                  >
                    <Eye className="size-5" />
                  </button>
                  <button
                    onClick={() => openEditUserModal(user.id)}
                    className=" relative h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full"
                  >
                    <PencilIcon className="size-5" />
                  </button>
                  <div>
                    <button
                      onClick={() => toggleDropdown(user.id)}
                      className="dropdown-toggle"
                    >
                      <MoreDotIcon className="text-gray-600 hover:text-gray-800 dark:hover:text-gray-300 size-5 mt-1.5" />
                    </button>
                    <Dropdown
                      isOpen={openMenuId === user.id}
                      onClose={closeDropdown}
                      className="w-40 p-2"
                    >
                      <DropdownItem
                        onItemClick={() =>
                          user.is_active
                            ? openDeactiveUserModal(user.id)
                            : openActiveUserModal(user.id)
                        }
                        className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                      >
                        {user.is_active ? (
                          <span className="flex items-center gap-1">
                            <NoSymbolIcon className="size-3.5" />
                            Desativar
                          </span>
                        ) : (
                          <span className="flex items-center gap-1">
                            <CheckCircleIcon className="size-4" />
                            Ativar
                          </span>
                        )}
                      </DropdownItem>
                      <DropdownItem
                        onItemClick={() => openReseteUserModal(user.id)}
                        className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-gray-400"
                      >
                        <LockClosedIcon className="size-4 text-gray-900" />
                        Mudar Senha
                      </DropdownItem>
                      <DropdownItem
                        onItemClick={() => openDeleteUserModal(user.id)}
                        className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                      >
                        <TrashBinIcon />
                        Remover
                      </DropdownItem>
                    </Dropdown>
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <CreateUserModal
        isOpen={activeModal === "create"}
        onClose={() => setActiveModal(null)}
        onSubmit={handleStoreUser}
      />

      <ActiveUserModal
        isOpen={activeModal === "active"}
        onClose={() => setActiveModal(null)}
        onSubmit={activeUser}
        userData={[selectedUser?.id || "", selectedUser?.name || ""]}
      />

      <DeactivateUserModal
        isOpen={activeModal === "deactive"}
        onClose={() => setActiveModal(null)}
        onSubmit={deactiveUser}
        userData={[selectedUser?.id || "", selectedUser?.name || ""]}
      />

       <ResetUserPassword
        isOpen={activeModal === "resetPassword"}
        onClose={() => setActiveModal(null)}
        onSubmit={reseteUserPassword}
        userData={[selectedUser?.id || "", selectedUser?.name || ""]}
      />

      <UserLogsModal
        isOpen={activeModal === "view"}
        onClose={() => setActiveModal(null)}
        userId={selectedUser?.id || ""}
        userName={selectedUser?.name || ""}
      />
    </div>
  );
}

export default UserTable;
