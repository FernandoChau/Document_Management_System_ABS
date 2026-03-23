// "use client";

import { useEffect, useState } from "react";
import { listUsers, showUser, User } from "../../../api/user.service";
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
  DocumentPlusIcon,
  FolderPlusIcon,
  KeyIcon,
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

function UserTable() {
  const [activeModal, setActiveModal] = useState<"create" | "edit" | "view" | "delete" | null> (null); 
  
  // ✅ ESTADO 1: Dados dos utilizadores
  const [users, setUsers] = useState<User[]>([]);

  // ✅ ESTADO 2: Estados de carregamento e erro
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // ✅ ESTADO 3: Controle do dropdown
  const [openMenuId, setOpenMenuId] = useState<string | null>(null);

  // ✅ PASSO 1: Carregar utilizadores ao montar o componente
  useEffect(() => {
    const fetchUsers = async () => {
      try {
        setLoading(true);
        setError(null);

        console.log("🔄 Iniciando fetch de utilizadores...");

        // Chama o serviço
        const response = await listUsers();
        console.log("✅ Resposta do servidor:", response);

        // O backend retorna { status, users: [...] }
        // Então extraímos do campo 'users'
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
  }, []); // Corre apenas na montagem do componente


  // ✅ PASSO 1: Função para gerenciar criação
  const handleCreate = () => {
    setActiveModal("create");
    closeDropdown();
    // TODO: Abrir modal de criação ou navegar para página de criação
  }

  // ✅ PASSO 2: Função para gerenciar edição
  const handleEdit = (userId: string) => {
    console.log("Editar usuário:", userId);
    closeDropdown();
    setActiveModal("edit");
    // TODO: Abrir modal de edição ou navegar para página de edição
  };

  // ✅ PASSO 3: Função para visualizar detalhes
  const handleView = async (userId: string) => {
    try {
      const response = await showUser(userId);
      // Backend retorna { status, user: {...} }
      const userData = response.data.user || response.data;
      console.log("Detalhes do usuário:", userData);
      closeDropdown();
      setActiveModal("view");
      // TODO: Abrir modal com detalhes
    } catch (err) {
      console.error("Erro ao buscar detalhes:", err);
    }
  };

  // ✅ PASSO 4: Função para remover
  const handleDelete = (userId: string) => {
    console.log("Remover usuário:", userId);
    closeDropdown();
    setActiveModal("delete");
    // TODO: Abrir confirmação e chamar delete
  };

  // ✅ Controle de dropdown
  const toggleDropdown = (userId: string) => {
    setOpenMenuId(openMenuId === userId ? null : userId);
  };

  const closeDropdown = () => {
    setOpenMenuId(null);
  };

  // ✅ Renderizar estado de carregamento
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

  // ✅ Renderizar estado de erro
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

  // ✅ Renderizar estado vazio
  if (users.length === 0) {
    console.log(
      "⚠️ Nenhum utilizador encontrado. users=",
      users,
      "loading=",
      loading,
      "error=",
      error,
    );
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <UserIcon className="w-12 h-12 text-gray-400 mx-auto" />
          <p className="mt-4 text-gray-600 dark:text-gray-400">
            Nenhum utilizador encontrado
          </p>
        </div>
      </div>
    );
  }

  // ✅ Renderizar tabela com dados reais
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
            onClick={handleCreate}
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
                    {user.role === "admin" ? <span className="flex items-center gap-1"> <CheckBadgeIcon className="size-4.5"/> Administrador </span> : "👤 Usuário"}
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
                    onClick={() => handleEdit(user.id)}
                    className=" relative h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full"
                  >
                    <PencilIcon className="size-5" />
                  </button>
                  <div >
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
                        onItemClick={() => handleDelete(user.id)}
                        className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                      >
                        <NoSymbolIcon className="size-3.5" />
                        Desativar
                      </DropdownItem>
                      <DropdownItem
                        onItemClick={() => handleDelete(user.id)}
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


      <Modal isOpen={activeModal === "create"} onClose={() => setActiveModal(null)} className="max-w-[700px] m-4">
        <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Criar Novo Utilizador
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Preencha as informacoes do novo utilizador e clique em "Salvar" para criar um novo utilizador na plataforma.
            </p>
          </div>

          <form className="flex flex-col">
            <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
              <div className="flex flex-col gap-4">
                <div>
                  <Label>Nome do Utilizador <span className="text-red-500">*</span></Label>
                  <Input
                    type="text"
                    placeholder="Nome do Utilizador"
                  />
                </div>

                <div>
                  <Label>Email <span className="text-red-500">*</span></Label>
                  <Input 
                    type="email"
                    placeholder="exemplo@abspro.co.mz"
                  />
                </div>

                <div>
                  <Label>Nr de telefone</Label>
                  <Input
                    type="text"
                    placeholder="8* 34 56 789"
                  />
                </div>

                <div>
                  <Label>Profissão</Label>
                  <Input 
                    type="text" 
                    placeholder="Tecnico de laboratorio"
                  />
                </div>

                <div>
                  <Label>Role <span className="text-red-500">*</span></Label>
                  <Select
                    options = {[
                      {value: "user", label: "Utilizador"},
                      {value: "admin", label: "Administrador"},
                    ]}
                    placeholder="Selecione o role"
                    // onChange={handleSelectChange}
                    className="dark:bg-dark-900"
                  />
                </div>

                <div className="flex items-center gap-2">
                  <span>Marque a caixa para ativar o utilizador <span className="text-red-500">*</span></span>
                  <Checkbox/>
                </div>
              </div>
              
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" variant="outline" onClick={() => setActiveModal(null)}>
                Fechar
              </Button>
              {/* <Button size="sm" onClick={handleSave}> */}
              <Button size="sm" onClick={() => setActiveModal(null)}>
                Salvar
              </Button>
            </div>
          </form>
        </div>
      </Modal>

      <Modal isOpen={activeModal === "edit"} onClose={() => setActiveModal(null)} className="max-w-[700px] m-4">
        <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Atualizar Utilizador ______
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Edite as informações do novo utilizador e clique em "Salvar Alterações" para editar o utilizador ______ .
            </p>
          </div>

          <form className="flex flex-col">
            <div className="custom-scrollbar h-[450px] overflow-y-auto px-2 pb-3">
              <div className="flex flex-col gap-4">
                <div>
                  <Label>Nome do Utilizador <span className="text-red-500">*</span></Label>
                  <Input
                    type="text"
                    placeholder="Nome do Utilizador"
                  />
                </div>

                <div>
                  <Label>Email <span className="text-red-500">*</span></Label>
                  <Input 
                    type="email"
                    placeholder="exemplo@abspro.co.mz"
                  />
                </div>

                <div>
                  <Label>Nr de telefone</Label>
                  <Input
                    type="text"
                    placeholder="8* 34 56 789"
                  />
                </div>

                <div>
                  <Label>Profissão</Label>
                  <Input 
                    type="text" 
                    placeholder="Tecnico de laboratorio"
                  />
                </div>

                <div>
                  <Label>Role <span className="text-red-500">*</span></Label>
                  <Select
                    options = {[
                      {value: "user", label: "Utilizador"},
                      {value: "admin", label: "Administrador"},
                    ]}
                    placeholder="Selecione o role"
                    // onChange={handleSelectChange}
                    className="dark:bg-dark-900"
                  />
                </div>
              </div>
              
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" variant="outline" onClick={() => setActiveModal(null)}>
                Fechar
              </Button>
              {/* <Button size="sm" onClick={handleSave}> */}
              <Button size="sm" onClick={() => setActiveModal(null)}>
                Salvar Alterações
              </Button>
            </div>
          </form>
        </div>
      </Modal>

      <Modal isOpen={activeModal === "delete"} onClose={() => setActiveModal(null)} className="max-w-[700px] m-4">
        <div className="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
          <div className="px-2 pr-14">
            <h4 className="flex  items-center gap-2 mb-2 text-2xl font-semibold text-red-500 dark:text-white/90">
              <AlertCircle className="size-5"/> Remover Utilizador <span className="text-red-500"> ______ </span>
            </h4>
            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
              Confirme as informações do utilizador e confirme a sua password de seguida clique em "Remover" para remover o utilizador <span className="text-red-500"> ______ </span>  .
            </p>
          </div>

          <form className="flex flex-col">
            <div className="custom-scrollbar h-fit overflow-y-auto px-2 pb-3">
              <div className="flex flex-col gap-4">
                <div>
                  <Label>Confirme a sua password <span className="text-red-500">*</span></Label>
                  <Input
                    type="password"
                    placeholder="Nome do Utilizador"
                  />
                </div>
              </div>
              
            </div>
            <div className="flex items-center gap-3 px-2 mt-6 lg:justify-end">
              <Button size="sm" variant="outline" onClick={() => setActiveModal(null)}>
                Fechar
              </Button>
              {/* <Button size="sm" onClick={handleSave}> */}
              <Button className="bg-red-500 hover:bg-red-600" size="sm" onClick={() => setActiveModal(null)}>
                Remover 
              </Button>
            </div>
          </form>
        </div>
      </Modal>
    </div>
    
  );
}

export default UserTable;
