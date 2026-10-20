import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router";
import { Modal } from "@/components/ui/modal";
import { ReportDocument, downloadReportDocument } from "@/api/report.service";
import Badge from "@/components/ui/badge/Badge";
import {
  ArrowDownTrayIcon,
  ArrowTopRightOnSquareIcon,
  ClockIcon,
  DocumentTextIcon,
  FolderIcon,
  UserCircleIcon,
  XMarkIcon,
} from "@heroicons/react/24/outline";

interface DocumentQuickPreviewModalProps {
  document: ReportDocument | null;
  isOpen: boolean;
  onClose: () => void;
  onDownload: (doc: ReportDocument) => void;
  isDownloading?: boolean;
}

export const DocumentQuickPreviewModal: React.FC<DocumentQuickPreviewModalProps> = ({
  document,
  isOpen,
  onClose,
  onDownload,
  isDownloading = false,
}) => {
  const navigate = useNavigate();
  const [blobUrl, setBlobUrl] = useState<string | null>(null);
  const [loadingBlob, setLoadingBlob] = useState(false);
  const [blobError, setBlobError] = useState<string | null>(null);

  useEffect(() => {
    if (!isOpen || !document) {
      if (blobUrl) {
        window.URL.revokeObjectURL(blobUrl);
        setBlobUrl(null);
      }
      setBlobError(null);
      return;
    }

    let isMounted = true;
    let currentUrl: string | null = null;

    const fetchBlob = async () => {
      setLoadingBlob(true);
      setBlobError(null);
      try {
        const blob = await downloadReportDocument(document.id);
        if (!isMounted) return;
        currentUrl = window.URL.createObjectURL(blob);
        setBlobUrl(currentUrl);
      } catch (err) {
        if (!isMounted) return;
        setBlobError("Não foi possível carregar a pré-visualização direta.");
      } finally {
        if (isMounted) setLoadingBlob(false);
      }
    };

    fetchBlob();

    return () => {
      isMounted = false;
      if (currentUrl) {
        window.URL.revokeObjectURL(currentUrl);
      }
    };
  }, [isOpen, document]);

  if (!isOpen || !document) return null;

  const isPdf =
    document.mime_type === "application/pdf" ||
    document.name.toLowerCase().endsWith(".pdf");
  const isImage =
    document.mime_type?.startsWith("image/") ||
    /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(document.name);

  const handleOpenFullViewer = () => {
    onClose();
    navigate(`/documento/${document.id}`);
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-4xl p-0 overflow-hidden">
      <div className="flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
          <div className="flex items-center gap-3 min-w-0 pr-4">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
              <DocumentTextIcon className="size-6" />
            </div>
            <div className="min-w-0">
              <h3 className="truncate text-base font-semibold text-gray-900 dark:text-white" title={document.name}>
                {document.name}
              </h3>
              <div className="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                <span className="font-mono font-medium text-brand-700 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/40 px-2 py-0.5 rounded">
                  {document.code}
                </span>
                <span>•</span>
                <span className="flex items-center gap-1">
                  <FolderIcon className="size-3.5" />
                  {document.folder.name}
                </span>
                <span>•</span>
                <span>{document.size_formatted}</span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <button
              onClick={handleOpenFullViewer}
              className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
              title="Abrir no visualizador avançado com zoom e rotação"
            >
              <ArrowTopRightOnSquareIcon className="size-3.5" />
              <span className="hidden sm:inline">Visualizador Completo</span>
            </button>
            <button
              onClick={() => onDownload(document)}
              disabled={isDownloading}
              className="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white shadow-theme-xs hover:bg-brand-600 disabled:opacity-50"
            >
              <ArrowDownTrayIcon className="size-3.5" />
              <span className="hidden sm:inline">Baixar</span>
            </button>
            <button
              onClick={onClose}
              className="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            >
              <XMarkIcon className="size-5" />
            </button>
          </div>
        </div>

        {/* Content Preview Body */}
        <div className="relative flex-1 overflow-y-auto p-6 bg-gray-100/50 dark:bg-gray-950/40 flex flex-col items-center justify-center min-h-[380px]">
          {loadingBlob && (
            <div className="flex flex-col items-center justify-center gap-3 py-16">
              <div className="size-10 animate-spin rounded-full border-4 border-brand-500 border-t-transparent" />
              <p className="text-sm font-medium text-gray-600 dark:text-gray-300">
                A carregar pré-visualização do documento...
              </p>
            </div>
          )}

          {!loadingBlob && isPdf && blobUrl && (
            <iframe
              src={`${blobUrl}#toolbar=1&navpanes=0`}
              title={document.name}
              className="w-full h-[60vh] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm bg-white"
            />
          )}

          {!loadingBlob && isImage && blobUrl && (
            <div className="flex items-center justify-center w-full max-h-[60vh] overflow-hidden rounded-xl bg-gray-900/5 p-2">
              <img
                src={blobUrl}
                alt={document.name}
                className="max-h-[55vh] max-w-full object-contain rounded-lg shadow-md"
              />
            </div>
          )}

          {!loadingBlob && !isPdf && !isImage && (
            <div className="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <DocumentTextIcon className="size-10" />
              </div>
              <h4 className="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                Ficheiro Office / Binário
              </h4>
              <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Este formato ({document.mime_type || "documento"}) pode ser aberto diretamente no Visualizador Avançado ou descarregado para edição no seu computador.
              </p>

              <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
                <button
                  onClick={handleOpenFullViewer}
                  className="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600"
                >
                  <ArrowTopRightOnSquareIcon className="size-4" />
                  Abrir no Visualizador
                </button>
                <button
                  onClick={() => onDownload(document)}
                  disabled={isDownloading}
                  className="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                >
                  <ArrowDownTrayIcon className="size-4" />
                  Baixar Ficheiro
                </button>
              </div>
            </div>
          )}

          {!loadingBlob && blobError && (
            <div className="text-center py-10">
              <p className="text-sm text-error-500 mb-4">{blobError}</p>
              <button
                onClick={handleOpenFullViewer}
                className="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white hover:bg-brand-600"
              >
                Tentar Abrir no Visualizador Completo
              </button>
            </div>
          )}
        </div>

        {/* Footer Meta Summary */}
        <div className="flex flex-wrap items-center justify-between border-t border-gray-200 px-6 py-3.5 text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-400">
          <div className="flex items-center gap-3">
            <span className="flex items-center gap-1.5">
              <span
                className="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold text-white uppercase"
                style={{ backgroundColor: document.uploader.avatar_color || "#4fb852" }}
              >
                {document.uploader.name.slice(0, 2).toUpperCase()}
              </span>
              Submetido por <strong className="text-gray-700 dark:text-gray-300">{document.uploader.name}</strong>
            </span>
            <span>•</span>
            <span className="flex items-center gap-1">
              <ClockIcon className="size-3.5" />
              {document.created_at_formatted}
            </span>
          </div>

          <div>
            <Badge variant="light" color="primary" size="sm">
              Índice Semanal Activo
            </Badge>
          </div>
        </div>
      </div>
    </Modal>
  );
};
