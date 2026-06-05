import { getUserLogs } from "@/api/user.service";
import Button from "@/components/ui/button/Button";
import { Modal } from "@/components/ui/modal";
import { 
  ArrowLeft, 
  ArrowRight, 
  Clock, 
  Download, 
  Eye, 
  FileText, 
  FolderOpen, 
  Info, 
  PlusCircle, 
  RefreshCw, 
  ShieldAlert, 
  Trash 
} from "lucide-react";
import React, { useEffect, useState } from "react";

interface UserLogsModalProps {
  isOpen: boolean;
  onClose: () => void;
  userId: string;
  userName: string;
}

interface AuditLogItem {
  id: string;
  action: string;
  resource_type: string;
  resource_id: string;
  metadata: Record<string, any> | null;
  created_at: string;
  resource?: {
    name?: string;
    [key: string]: any;
  } | null;
}

function UserLogsModal({ isOpen, onClose, userId, userName }: UserLogsModalProps) {
  const [logs, setLogs] = useState<AuditLogItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState("");
  
  // Pagination
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalLogs, setTotalLogs] = useState(0);

  const fetchLogs = async (pageNumber: number) => {
    if (!userId) return;
    setLoading(true);
    setErrorMsg("");
    try {
      const response = await getUserLogs(userId, pageNumber);
      // Wait, getUserLogs returns pagination object: { data: AuditLogItem[], current_page, last_page, total }
      // If we want specific page, let's verify if getUserLogs should take a page parameter.
      // Yes, we will pass query params if needed, but for now let's read what the backend returns.
      const paginatedData = response.data;
      setLogs(paginatedData.data || []);
      setCurrentPage(paginatedData.current_page || 1);
      setLastPage(paginatedData.last_page || 1);
      setTotalLogs(paginatedData.total || 0);
    } catch (err: any) {
      console.error("Erro ao carregar logs:", err);
      setErrorMsg("Não foi possível carregar os logs de auditoria.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (isOpen && userId) {
      setLogs([]);
      setCurrentPage(1);
      fetchLogs(1);
    }
  }, [isOpen, userId]);

  // Format date nicely
  const formatDate = (dateString: string) => {
    try {
      const date = new Date(dateString);
      return date.toLocaleString("pt-PT", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      });
    } catch {
      return dateString;
    }
  };

  // Get color and icon depending on the action type
  const getActionStyle = (action: string) => {
    const act = action.toUpperCase();
    if (act.includes("CREATE")) {
      return {
        bg: "bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border-blue-200 dark:border-blue-500/20",
        icon: <PlusCircle className="w-3.5 h-3.5" />,
        label: "Criação",
      };
    }
    if (act.includes("UPLOAD")) {
      return {
        bg: "bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400 border-sky-200 dark:border-sky-500/20",
        icon: <PlusCircle className="w-3.5 h-3.5" />,
        label: "Upload",
      };
    }
    if (act.includes("DOWNLOAD")) {
      return {
        bg: "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20",
        icon: <Download className="w-3.5 h-3.5" />,
        label: "Download",
      };
    }
    if (act.includes("VIEW")) {
      return {
        bg: "bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20",
        icon: <Eye className="w-3.5 h-3.5" />,
        label: "Visualização",
      };
    }
    if (act.includes("DELETE")) {
      return {
        bg: "bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border-rose-200 dark:border-rose-500/20",
        icon: <Trash className="w-3.5 h-3.5" />,
        label: "Remoção",
      };
    }
    if (act.includes("SHARE")) {
      return {
        bg: "bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400 border-purple-200 dark:border-purple-500/20",
        icon: <RefreshCw className="w-3.5 h-3.5" />,
        label: "Partilha",
      };
    }
    return {
      bg: "bg-gray-50 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400 border-gray-200 dark:border-gray-500/20",
      icon: <Info className="w-3.5 h-3.5" />,
      label: action,
    };
  };

  // Helper to parse resource name or metadata info
  const renderResourceDetail = (log: AuditLogItem) => {
    // 1. Check if resource was loaded from backend (standard morphTo)
    if (log.resource && log.resource.name) {
      return log.resource.name;
    }

    // 2. Check metadata fields
    if (log.metadata) {
      if (log.metadata.name) return log.metadata.name;
      if (log.metadata.title) return log.metadata.title;
      if (log.metadata.folder_name) return log.metadata.folder_name;
      if (log.metadata.details) return log.metadata.details;
      if (log.metadata.detalhes) return log.metadata.detalhes;
    }

    return `ID: ${log.resource_id.substring(0, 8)}...`;
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[850px] m-4">
      <div className="no-scrollbar relative w-full max-w-[850px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        
        {/* Modal Header */}
        <div className="px-2 pr-14 flex items-center justify-between mb-6">
          <div>
            <h4 className="flex items-center gap-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
              Logs de Auditoria - <span className="text-brand-500">{userName}</span>
            </h4>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Histórico de ações efetuadas por este utilizador no Gestor de Ficheiros.
            </p>
          </div>
          <Button
            size="sm"
            variant="outline"
            onClick={() => fetchLogs(currentPage)}
            className="flex items-center gap-1.5"
            disabled={loading}
          >
            <RefreshCw className={`w-3.5 h-3.5 ${loading ? "animate-spin" : ""}`} />
            Atualizar
          </Button>
        </div>

        {/* Modal Content */}
        <div className="px-2">
          {errorMsg && (
            <div className="flex items-start gap-2.5 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-600 dark:text-red-400 mb-4">
              <ShieldAlert className="w-5 h-5 shrink-0 text-red-500" />
              <span>{errorMsg}</span>
            </div>
          )}

          <div className="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden bg-gray-50/50 dark:bg-gray-900/50 max-h-[350px] overflow-y-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="border-b border-gray-100 dark:border-gray-800 bg-gray-100/50 dark:bg-gray-800/50">
                  <th className="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ação</th>
                  <th className="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recurso</th>
                  <th className="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nome / Detalhe</th>
                  <th className="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data e Hora</th>
                </tr>
              </thead>
              <tbody>
                {loading && logs.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="py-16 text-center">
                      <div className="flex flex-col items-center justify-center gap-2">
                        <span className="h-6 w-6 animate-spin rounded-full border-2 border-brand-500 border-t-transparent" />
                        <p className="text-sm text-gray-500 dark:text-gray-400">A carregar logs...</p>
                      </div>
                    </td>
                  </tr>
                ) : logs.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="py-16 text-center">
                      <div className="flex flex-col items-center justify-center gap-2">
                        <Clock className="w-10 h-10 text-gray-300 dark:text-gray-600" />
                        <p className="text-sm text-gray-500 dark:text-gray-400 font-medium">Nenhum log encontrado</p>
                        <p className="text-xs text-gray-400">Este utilizador ainda não realizou ações no sistema.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  logs.map((log) => {
                    const style = getActionStyle(log.action);
                    return (
                      <tr 
                        key={log.id} 
                        className="border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-100/30 dark:hover:bg-gray-800/10"
                      >
                        {/* Action badge */}
                        <td className="py-3 px-4">
                          <span className={`inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full border text-xs font-medium ${style.bg}`}>
                            {style.icon}
                            {style.label}
                          </span>
                        </td>
                        {/* Resource type */}
                        <td className="py-3 px-4 text-sm text-gray-600 dark:text-gray-300">
                          <span className="flex items-center gap-1.5">
                            {log.resource_type === "Folder" ? (
                              <>
                                <FolderOpen className="w-4 h-4 text-amber-500" />
                                Pasta
                              </>
                            ) : (
                              <>
                                <FileText className="w-4 h-4 text-blue-500" />
                                Ficheiro
                              </>
                            )}
                          </span>
                        </td>
                        {/* Resource Detail */}
                        <td className="py-3 px-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                          {renderResourceDetail(log)}
                        </td>
                        {/* Timestamp */}
                        <td className="py-3 px-4 text-sm text-gray-500 dark:text-gray-400">
                          {formatDate(log.created_at)}
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Modal Footer / Pagination */}
        <div className="flex items-center justify-between px-2 mt-6 border-t border-gray-100 dark:border-gray-800 pt-5">
          <div className="text-sm text-gray-500 dark:text-gray-400">
            Total de <span className="font-semibold text-gray-700 dark:text-gray-300">{totalLogs}</span> registos
          </div>
          
          <div className="flex items-center gap-2">
            <Button
              size="sm"
              variant="outline"
              disabled={currentPage <= 1 || loading}
              onClick={() => fetchLogs(currentPage - 1)}
              className="p-1 px-3 flex items-center gap-1"
            >
              <ArrowLeft className="w-3.5 h-3.5" />
              Anterior
            </Button>
            <span className="text-sm font-medium text-gray-600 dark:text-gray-300">
              Pág. {currentPage} de {lastPage}
            </span>
            <Button
              size="sm"
              variant="outline"
              disabled={currentPage >= lastPage || loading}
              onClick={() => fetchLogs(currentPage + 1)}
              className="p-1 px-3 flex items-center gap-1"
            >
              Próximo
              <ArrowRight className="w-3.5 h-3.5" />
            </Button>
          </div>
        </div>

      </div>
    </Modal>
  );
}

export default UserLogsModal;
