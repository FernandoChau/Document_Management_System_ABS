import React, { memo } from "react";
import { ViewerProps } from "../../types/preview.types";
import { DownloadIcon } from "@/icons";
import Button from "@/components/ui/button/Button";
import { formatBytes, formatDate } from "../../utils/preview.utils";
import { getFileExtension } from "../../utils/mime.utils";
import { Archive, Info, User, Calendar, Hash, FileArchive } from "lucide-react";
import { triggerBlobDownload } from "../../utils/preview.utils";

/**
 * ZipViewer — displays a rich informational panel for ZIP and archive files.
 * No content preview is attempted; instead, file details are showcased elegantly.
 */
const ZipViewer: React.FC<ViewerProps> = memo(({ document, fileBlob }) => {
  const handleDownload = () => {
    if (!fileBlob || !document) return;
    triggerBlobDownload(fileBlob, document.name);
  };

  const ext = document ? getFileExtension(document.name) : "ZIP";

  return (
    <div className="flex-1 flex flex-col items-center justify-center gap-8 p-8">
      {/* Main card */}
      <div className="w-full max-w-lg flex flex-col items-center gap-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-theme-lg p-10">
        {/* Archive icon with styled badge */}
        <div className="relative flex items-center justify-center">
          <div className="flex h-28 w-28 items-center justify-center rounded-3xl bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-500/20 dark:to-orange-600/10 shadow-theme-md">
            <Archive className="h-14 w-14 text-orange-500 dark:text-orange-400" />
          </div>
          <span className="absolute -bottom-1 -right-1 px-2 py-0.5 text-xs font-bold rounded-md bg-orange-500 text-white shadow">
            {ext}
          </span>
        </div>

        {/* File name */}
        <div className="text-center">
          <h2 className="text-xl font-semibold text-gray-900 dark:text-white/90 break-all">
            {document?.name ?? "Arquivo"}
          </h2>
          <p className="mt-1 text-sm text-orange-500 dark:text-orange-400 font-medium">
            Arquivo Comprimido
          </p>
        </div>

        {/* Info grid */}
        <div className="w-full grid grid-cols-2 gap-3">
          <InfoRow
            icon={<FileArchive className="size-3.5" />}
            label="Tamanho"
            value={formatBytes(document?.size ?? 0)}
          />
          <InfoRow
            icon={<Info className="size-3.5" />}
            label="Tipo"
            value={document?.mime_type ?? "—"}
          />
          <InfoRow
            icon={<Calendar className="size-3.5" />}
            label="Criado em"
            value={formatDate(document?.created_at)}
          />
          <InfoRow
            icon={<User className="size-3.5" />}
            label="Referência"
            value={document?.reference_code ?? "—"}
          />
        </div>

        {/* Notice banner */}
        <div className="w-full flex items-start gap-3 rounded-xl px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
          <Info className="size-4 text-gray-400 dark:text-gray-500 mt-0.5 shrink-0" />
          <p className="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
            Este tipo de ficheiro não suporta visualização prévia. Para ver o conteúdo, descarregue e extraia o arquivo localmente.
          </p>
        </div>

        {/* Actions */}
        <div className="flex gap-3 w-full justify-center">
          <Button
            variant="primary"
            onClick={handleDownload}
            startIcon={<DownloadIcon className="size-4" />}
            disabled={!document?.permissions?.can_download}
          >
            Descarregar
          </Button>
        </div>
      </div>
    </div>
  );
});

ZipViewer.displayName = "ZipViewer";

// Small helper component
interface InfoRowProps {
  icon: React.ReactNode;
  label: string;
  value: string;
}

const InfoRow: React.FC<InfoRowProps> = ({ icon, label, value }) => (
  <div className="flex flex-col gap-1 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600 px-3 py-2.5">
    <div className="flex items-center gap-1.5 text-gray-400 dark:text-gray-500">
      {icon}
      <span className="text-xs font-medium uppercase tracking-wide">{label}</span>
    </div>
    <p className="text-sm font-medium text-gray-700 dark:text-gray-300 truncate" title={value}>
      {value}
    </p>
  </div>
);

export default ZipViewer;
