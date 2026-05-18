import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";
import { LogEntry } from "@/api/folder-document.service";
import { AlertCircleIcon } from "lucide-react";

interface LogsModalProps {
    isOpen: boolean;
    onClose: () => void;
    itemName: string;
    logs: LogEntry[];
    isLoading: boolean;
    error: string | null;
}

function LogsModal({ isOpen, onClose, itemName, logs, isLoading, error }: LogsModalProps) {
    return (
        <Modal isOpen={isOpen} onClose={onClose} className="max-w-[800px] m-4">
            <div className="no-scrollbar relative w-full max-w-[800px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
                <div className="px-2 pr-14 mb-6">
                    <h4 className="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                        Logs - <span className="text-brand-500 font-medium">{itemName}</span>
                    </h4>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        Registo das ações efetuadas no item selecionado.
                    </p>
                </div>

                {isLoading ? (
                    <div className="flex flex-col items-center justify-center rounded-3xl border border-gray-200 bg-gray-50 p-8 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-500 mb-4"></div>
                        <p className="text-sm text-gray-500 dark:text-gray-400">A carregar logs...</p>
                    </div>
                ) : error ? (
                    <div className="rounded-3xl border border-red-200 bg-red-50 p-6 text-red-700 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300">
                        <div className="flex items-center gap-3">
                            <AlertCircleIcon className="h-5 w-5" />
                            <p>{error}</p>
                        </div>
                    </div>
                ) : logs.length === 0 ? (
                    <div className="rounded-3xl border border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                        Não existem logs para este item.
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-3xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <table className="min-w-full text-left text-sm">
                            <thead className="bg-gray-100 text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Data/Hora</th>
                                    <th className="px-4 py-3 font-medium">Utilizador</th>
                                    <th className="px-4 py-3 font-medium">Acção</th>
                                    <th className="px-4 py-3 font-medium">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white text-gray-700 dark:divide-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                {logs.map((log, index) => (
                                    <tr key={`${log.timestamp}-${index}`}>
                                        <td className="px-4 py-3 align-top text-theme-sm text-gray-600 dark:text-gray-300">
                                            {log.timestamp ? new Date(log.timestamp).toLocaleString("pt-PT", { dateStyle: "short", timeStyle: "short" }) : "—"}
                                        </td>
                                        <td className="px-4 py-3 align-top text-theme-sm text-gray-600 dark:text-gray-300">
                                            {log.utilizador || "—"}
                                        </td>
                                        <td className="px-4 py-3 align-top text-theme-sm text-gray-600 dark:text-gray-300">
                                            {log.acao || "—"}
                                        </td>
                                        <td className="px-4 py-3 align-top text-theme-sm text-gray-600 dark:text-gray-300">
                                            {log.detalhes || "—"}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                <div className="flex justify-end gap-3 px-2 mt-6">
                    <Button size="sm" variant="outline" onClick={onClose}>
                        Fechar
                    </Button>
                </div>
            </div>
        </Modal>
    );
}

export default LogsModal;
