import React, { memo } from "react";
import { Document } from "@/api/folder-document.service";
import { DocumentType } from "../../types/preview.types";
import { formatBytes, formatDate } from "../../utils/preview.utils";
import { getDocumentTypeLabel, getFileExtension } from "../../utils/mime.utils";
import Badge from "@/components/ui/badge/Badge";
import {
  FileText,
  HardDrive,
  Calendar,
  User,
  RefreshCw,
  Tag,
  Shield,
  Hash,
  Folder,
  Info,
} from "lucide-react";
import clsx from "clsx";

interface MetadataPanelProps {
  document: Document | null;
  documentType: DocumentType;
  isOpen: boolean;
  onClose: () => void;
}

interface MetaRowProps {
  icon: React.ReactNode;
  label: string;
  value: React.ReactNode;
}

const MetaRow: React.FC<MetaRowProps> = ({ icon, label, value }) => (
  <div className="flex flex-col gap-0.5">
    <div className="flex items-center gap-1.5 text-gray-400 dark:text-gray-500">
      <span className="text-xs">{icon}</span>
      <span className="text-xs font-medium uppercase tracking-wide">{label}</span>
    </div>
    <div className="text-sm text-gray-700 dark:text-gray-300 font-medium break-words">
      {value || <span className="text-gray-400 dark:text-gray-500">—</span>}
    </div>
  </div>
);

const SectionLabel: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <p className="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-4 mb-1 first:mt-0">
    {children}
  </p>
);

/**
 * MetadataPanel — collapsible sidebar that displays full document metadata.
 * Completely decoupled from preview logic.
 */
const MetadataPanel: React.FC<MetadataPanelProps> = memo(
  ({ document, documentType, isOpen, onClose }) => {
    return (
      <aside
        className={clsx(
          "flex flex-col shrink-0 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden transition-all duration-200 ease-in-out",
          isOpen ? "w-72 opacity-100" : "w-0 opacity-0 pointer-events-none"
        )}
        aria-label="Painel de metadados"
        aria-hidden={!isOpen}
      >
        {/* Panel header */}
        <div className="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
          <div className="flex items-center gap-2">
            <Info className="size-4 text-brand-500" />
            <span className="text-sm font-semibold text-gray-800 dark:text-white/90">
              Detalhes
            </span>
          </div>
          <button
            onClick={onClose}
            className="h-6 w-6 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            aria-label="Fechar painel lateral"
          >
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path
                d="M1 1L11 11M11 1L1 11"
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
              />
            </svg>
          </button>
        </div>

        {/* Panel body */}
        <div className="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
          {!document ? (
            <div className="animate-pulse flex flex-col gap-3">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="flex flex-col gap-1">
                  <div className="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded" />
                  <div className="h-4 w-full bg-gray-200 dark:bg-gray-700 rounded" />
                </div>
              ))}
            </div>
          ) : (
            <>
              {/* File info */}
              <SectionLabel>Ficheiro</SectionLabel>
              <MetaRow
                icon={<FileText className="size-3" />}
                label="Nome"
                value={document.name}
              />
              <MetaRow
                icon={<Tag className="size-3" />}
                label="Tipo"
                value={
                  <div className="flex items-center gap-2">
                    <span>{getDocumentTypeLabel(documentType)}</span>
                    <Badge variant="light" color="light" size="sm">
                      {getFileExtension(document.name)}
                    </Badge>
                  </div>
                }
              />
              <MetaRow
                icon={<HardDrive className="size-3" />}
                label="Tamanho"
                value={formatBytes(document.size ?? 0)}
              />
              <MetaRow
                icon={<Hash className="size-3" />}
                label="Referência"
                value={
                  <span className="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                    {document.reference_code}
                  </span>
                }
              />

              {/* Dates */}
              <SectionLabel>Datas</SectionLabel>
              <MetaRow
                icon={<Calendar className="size-3" />}
                label="Criado em"
                value={formatDate(document.created_at)}
              />
              <MetaRow
                icon={<RefreshCw className="size-3" />}
                label="Atualizado em"
                value={formatDate(document.updated_at)}
              />

              {/* Ownership */}
              <SectionLabel>Autoria</SectionLabel>
              <MetaRow
                icon={<User className="size-3" />}
                label="Carregado por"
                value={(document as any).uploader_name ?? "—"}
              />
              <MetaRow
                icon={<Folder className="size-3" />}
                label="Pasta"
                value={(document as any).folder?.name ?? document.folder_id}
              />

              {/* Permissions */}
              {document.permissions && (
                <>
                  <SectionLabel>Permissões</SectionLabel>
                  <div className="flex flex-wrap gap-1.5">
                    <PermBadge label="Ver" active={document.permissions.can_view} />
                    <PermBadge label="Download" active={document.permissions.can_download} />
                    <PermBadge label="Partilhar" active={document.permissions.can_share} />
                    <PermBadge label="Editar" active={document.permissions.can_update_metadata} />
                    <PermBadge label="Apagar" active={document.permissions.can_delete} />
                  </div>
                </>
              )}
            </>
          )}
        </div>
      </aside>
    );
  }
);

MetadataPanel.displayName = "MetadataPanel";

// Small permission badge helper
const PermBadge: React.FC<{ label: string; active: boolean }> = ({ label, active }) => (
  <span
    className={clsx(
      "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium",
      active
        ? "bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400"
        : "bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500"
    )}
  >
    <span className={clsx("h-1.5 w-1.5 rounded-full", active ? "bg-success-500" : "bg-gray-400")} />
    {label}
  </span>
);

export default MetadataPanel;
