import React, { useState } from "react";
import { ReportDocument } from "@/api/report.service";
import {
  ArrowDownTrayIcon,
  ArrowTopRightOnSquareIcon,
  CheckIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  FolderIcon,
} from "@heroicons/react/24/outline";

interface DocumentRowProps {
  document: ReportDocument;
  onPreview: (doc: ReportDocument) => void;
  onDownload: (doc: ReportDocument) => void;
  isDownloading?: boolean;
}

export const DocumentRow: React.FC<DocumentRowProps> = ({
  document,
  onPreview,
  onDownload,
  isDownloading = false,
}) => {
  const [copied, setCopied] = useState(false);

  const handleCopyCode = (e: React.MouseEvent) => {
    e.stopPropagation();
    navigator.clipboard.writeText(document.code).catch(() => {});
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const getFileBadge = (name: string, mime: string) => {
    const ext = name.split(".").pop()?.toLowerCase() || "";
    if (ext === "pdf" || mime.includes("pdf")) {
      return {
        label: "PDF",
        bg: "bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border-red-200 dark:border-red-900/50",
      };
    }
    if (["doc", "docx", "odt"].includes(ext) || mime.includes("word")) {
      return {
        label: "DOC",
        bg: "bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-900/50",
      };
    }
    if (["xls", "xlsx", "ods", "csv"].includes(ext) || mime.includes("excel") || mime.includes("spreadsheet")) {
      return {
        label: "XLS",
        bg: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50",
      };
    }
    if (["jpg", "jpeg", "png", "webp", "gif", "svg"].includes(ext) || mime.startsWith("image/")) {
      return {
        label: "IMG",
        bg: "bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border-purple-200 dark:border-purple-900/50",
      };
    }
    return {
      label: ext.toUpperCase() || "FILE",
      bg: "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700",
    };
  };

  const badge = getFileBadge(document.name, document.mime_type);
  const uploaderInitials = document.uploader.name
    .split(" ")
    .map((n) => n[0])
    .filter(Boolean)
    .slice(0, 2)
    .join("")
    .toUpperCase();

  return (
    <tr
      onDoubleClick={() => onPreview(document)}
      className="group transition-colors duration-150 hover:bg-gray-50/80 dark:hover:bg-white/[0.02] border-b border-gray-100 dark:border-gray-800/60 last:border-0"
    >
      {/* 1. Código do Documento */}
      <td className="py-3 px-4 whitespace-nowrap">
        <div className="flex items-center gap-1.5">
          <span
            className="font-mono text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 group-hover:border-brand-300 dark:group-hover:border-brand-700 transition-colors"
            title={`Código de referência: ${document.code}`}
          >
            {document.code}
          </span>
          {/* <button
            onClick={handleCopyCode}
            className="p-1 rounded text-gray-400 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            title="Copiar código"
          >
            {copied ? (
              <CheckIcon className="size-3.5 text-brand-600 dark:text-brand-400" />
            ) : (
              <DocumentDuplicateIcon className="size-3.5" />
            )}
          </button> */}
        </div>
      </td>

      {/* 2. Nome do Documento & Pasta */}
      {/* <td className="py-3 px-4">
        <div className="flex items-center gap-3">
          <span
            className={`inline-flex items-center justify-center font-bold text-[10px] w-9 h-9 rounded-lg border shrink-0 ${badge.bg}`}
          >
            {badge.label}
          </span>
          <div className="min-w-0">
            <p
              onClick={() => onPreview(document)}
              className="text-sm font-medium text-gray-800 dark:text-white/90 hover:text-brand-600 dark:hover:text-brand-400 cursor-pointer transition-colors truncate max-w-xs md:max-w-sm lg:max-w-md"
              title={document.name}
            >
              {document.name}
            </p>
            <div className="flex items-center gap-1.5 mt-0.5 text-xs text-gray-400 dark:text-gray-500">
              <FolderIcon className="size-3.5 shrink-0" />
              <span className="truncate max-w-[200px]" title={document.folder.name}>
                {document.folder.name}
              </span>
            </div>
          </div>
        </div>
      </td> */}

      {/* 3. Quem Subiu */}
      <td className="py-3 px-4 whitespace-nowrap">
        <div className="flex items-center gap-2">
          <div
            className="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold text-white uppercase shadow-sm"
            style={{ backgroundColor: document.uploader.avatar_color || "#4fb852" }}
          >
            {uploaderInitials || "U"}
          </div>
          <div>
            <p className="text-xs font-medium text-gray-800 dark:text-gray-200">
              {document.uploader.name}
            </p>
            {document.uploader.email && (
              <p className="text-[11px] text-gray-400 truncate max-w-[150px]">
                {document.uploader.email}
              </p>
            )}
          </div>
        </div>
      </td>

      {/* 4. Quando Subiu */}
      <td className="py-3 px-4 whitespace-nowrap">
        <div className="text-xs">
          <p className="font-medium text-gray-700 dark:text-gray-300">
            {document.created_at_date}
          </p>
          <p className="text-gray-400 dark:text-gray-500 text-[11px]">
            às {document.created_at_time}
          </p>
        </div>
      </td>

      {/* 5. Tamanho */}
      {/* <td className="py-3 px-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono">
        {document.size_formatted}
      </td> */}

      {/* 6. Ações */}
      <td className="py-3 px-4 whitespace-nowrap text-right">
        <div className="flex items-center justify-end gap-1.5">
          <button
            onClick={() => onPreview(document)}
            className="flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-gray-500 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-brand-950/40 dark:hover:text-brand-400 transition-all duration-150"
            title="Pré-visualizar documento"
          >
            <EyeIcon className="size-4.5" />
          </button>

          <button
            onClick={() => onDownload(document)}
            disabled={isDownloading}
            className="flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-gray-500 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-brand-950/40 dark:hover:text-brand-400 transition-all duration-150 disabled:opacity-50"
            title="Descarregar documento"
          >
            {isDownloading ? (
              <div className="size-4 animate-spin rounded-full border-2 border-brand-500 border-t-transparent" />
            ) : (
              <ArrowDownTrayIcon className="size-4.5" />
            )}
          </button>

          {/* <a
            href={`/documento/${document.id}`}
            target="_blank"
            rel="noreferrer"
            className="flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-gray-400 hover:border-gray-200 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition-all duration-150"
            title="Abrir em nova aba"
          >
            <ArrowTopRightOnSquareIcon className="size-3.5" />
          </a> */}
        </div>
      </td>
    </tr>
  );
};
