import React from "react";
import { WeeklyReportsSummary } from "@/api/report.service";
import {
  CalendarDaysIcon,
  DocumentDuplicateIcon,
  FolderArrowDownIcon,
  ServerStackIcon,
} from "@heroicons/react/24/outline";

interface ReportSummaryCardsProps {
  summary: WeeklyReportsSummary | null;
  loading?: boolean;
}

export const ReportSummaryCards: React.FC<ReportSummaryCardsProps> = ({
  summary,
  loading = false,
}) => {
  const cards = [
    {
      title: "Documentos Indexados",
      value: summary?.total_documents ?? 0,
      icon: <DocumentDuplicateIcon className="size-6 text-brand-600 dark:text-brand-400" />,
      bg: "bg-brand-50 dark:bg-brand-900/20",
      helper: "Total de ficheiros no arquivo semanal",
    },
    {
      title: "Semanas com Entradas",
      value: summary?.total_weeks ?? 0,
      icon: <CalendarDaysIcon className="size-6 text-indigo-600 dark:text-indigo-400" />,
      bg: "bg-indigo-50 dark:bg-indigo-900/20",
      helper: "Relatórios periódicos gerados",
    },
    {
      title: "Volume Total do Arquivo",
      value: summary?.total_size_formatted ?? "0 B",
      icon: <ServerStackIcon className="size-6 text-amber-600 dark:text-amber-400" />,
      bg: "bg-amber-50 dark:bg-amber-900/20",
      helper: "Espaço total ocupado pelos ficheiros",
    },
    {
      title: "Anos no Histórico",
      value: summary?.available_years?.length ?? 1,
      icon: <FolderArrowDownIcon className="size-6 text-emerald-600 dark:text-emerald-400" />,
      bg: "bg-emerald-50 dark:bg-emerald-900/20",
      helper: summary?.available_years?.join(", ") || "Ano corrente",
    },
  ];

  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-5">
      {cards.map((card, idx) => (
        <div
          key={idx}
          className="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
        >
          <div className="flex items-center justify-between">
            <div className={`flex h-12 w-12 items-center justify-center rounded-xl ${card.bg}`}>
              {card.icon}
            </div>
            <span className="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
              Índice
            </span>
          </div>

          <div className="mt-4">
            <span className="text-sm font-medium text-gray-500 dark:text-gray-400">
              {card.title}
            </span>
            {loading ? (
              <div className="mt-2 h-8 w-24 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
            ) : (
              <h4 className="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {card.value}
              </h4>
            )}
            <p className="mt-1 text-theme-xs text-gray-400 dark:text-gray-500">
              {card.helper}
            </p>
          </div>
        </div>
      ))}
    </div>
  );
};
