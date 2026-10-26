import React, { useState } from "react";
import { ReportDocument, ReportWeek, exportWeeklyReportPdf } from "@/api/report.service";
import { DocumentRow } from "./DocumentRow";
import {
  ArrowDownTrayIcon,
  CalendarIcon,
  ChevronDownIcon,
  ChevronUpIcon,
} from "@heroicons/react/24/outline";

interface WeeklyReportItemProps {
  week: ReportWeek;
  year: number;
  onPreviewDocument: (doc: ReportDocument) => void;
  onDownloadDocument: (doc: ReportDocument) => void;
  downloadingDocId: string | null;
  defaultExpanded?: boolean;
}

export const WeeklyReportItem: React.FC<WeeklyReportItemProps> = ({
  week,
  year,
  onPreviewDocument,
  onDownloadDocument,
  downloadingDocId,
  // defaultExpanded = false,
}) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const [isExporting, setIsExporting] = useState(false);

  const handleExportPdf = async (e: React.MouseEvent) => {
    e.stopPropagation();
    if (isExporting) return;
    setIsExporting(true);
    try {
      const blob = await exportWeeklyReportPdf(year, week.week_number);
      const url = window.URL.createObjectURL(blob);
      const a = window.document.createElement("a");
      a.href = url;
      a.download = `indice_semanal_W${week.week_number}_${year}.pdf`;
      window.document.body.appendChild(a);
      a.click();
      window.document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error("Erro ao exportar PDF:", err);
      alert("Não foi possível exportar o índice semanal em PDF.");
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs transition-all duration-200 dark:border-gray-800 dark:bg-white/[0.02]">
      {/* Week Header Bar */}
      <div
        onClick={() => setIsExpanded(!isExpanded)}
        className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 cursor-pointer hover:bg-gray-50/70 dark:hover:bg-white/[0.02] border-b border-gray-100 dark:border-gray-800/80 transition-colors"
      >
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
            <CalendarIcon className="size-5" />
          </div>

          <div>
            <div className="flex flex-wrap items-center gap-2">
              <h4 className="text-sm font-semibold text-gray-900 dark:text-white">
                {week.label}
              </h4>
              <span className="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-gray-500 dark:bg-indigo-900/30 dark:text-gray-400">
                {week.count} {week.count === 1 ? "documento" : "documentos"}
              </span>
            </div>
            <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
              <span>Período: <strong className="text-gray-700 dark:text-gray-300">{week.date_range}</strong></span>
              <span>•</span>
              <span>Volume: {week.size_formatted}</span>
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2 self-end sm:self-auto" onClick={(e) => e.stopPropagation()}>
          <button
            onClick={handleExportPdf}
            disabled={isExporting}
            className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 disabled:opacity-50 transition-all duration-150"
            title="Descarregar índice semanal em PDF"
          >
            {isExporting ? (
              <div className="size-3.5 animate-spin rounded-full border-2 border-brand-500 border-t-transparent" />
            ) : (
              <ArrowDownTrayIcon className="size-3.5 text-gray-500 dark:text-gray-400" />
            )}
            <span>Exportar Índice PDF</span>
          </button>

          <button
            onClick={() => setIsExpanded(!isExpanded)}
            className="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition-colors"
            title={isExpanded ? "Recolher semana" : "Expandir semana"}
          >
            {isExpanded ? (
              <ChevronUpIcon className="size-5" />
            ) : (
              <ChevronDownIcon className="size-5" />
            )}
          </button>
        </div>
      </div>

      {/* Documents Table */}
      {isExpanded && (
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-gray-100 bg-gray-50/60 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400">
                <th className="py-2.5 px-4">Código</th>
                {/* <th className="py-2.5 px-4">Documento / Pasta</th> */}
                <th className="py-2.5 px-4">Quem Subiu</th>
                <th className="py-2.5 px-4">Quando Subiu</th>
                {/* <th className="py-2.5 px-4">Tamanho</th> */}
                <th className="py-2.5 px-4 text-right">Ações</th>
              </tr>
            </thead>
            <tbody>
              {week.documents.map((doc) => (
                <DocumentRow
                  key={doc.id}
                  document={doc}
                  onPreview={onPreviewDocument}
                  onDownload={onDownloadDocument}
                  isDownloading={downloadingDocId === doc.id}
                />
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};
