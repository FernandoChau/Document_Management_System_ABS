import React, { useCallback, useEffect, useMemo, useState } from "react";
import {
  ReportDocument,
  ReportMonth,
  ReportYear,
  WeeklyReportsResponse,
  downloadReportDocument,
  getWeeklyReports,
} from "@/api/report.service";
import { ReportSummaryCards } from "@/components/reports/ReportSummaryCards";
import { WeeklyReportItem } from "@/components/reports/WeeklyReportItem";
import { DocumentQuickPreviewModal } from "@/components/reports/DocumentQuickPreviewModal";
import {
  AdjustmentsHorizontalIcon,
  ArrowPathIcon,
  CalendarDaysIcon,
  CalendarIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  DocumentMagnifyingGlassIcon,
  DocumentTextIcon,
  FolderIcon,
  InboxIcon,
  MagnifyingGlassIcon,
  Squares2X2Icon,
  XMarkIcon,
} from "@heroicons/react/24/outline";

const MONTH_NAMES = [
  { value: 1, label: "Janeiro" },
  { value: 2, label: "Fevereiro" },
  { value: 3, label: "Março" },
  { value: 4, label: "Abril" },
  { value: 5, label: "Maio" },
  { value: 6, label: "Junho" },
  { value: 7, label: "Julho" },
  { value: 8, label: "Agosto" },
  { value: 9, label: "Setembro" },
  { value: 10, label: "Outubro" },
  { value: 11, label: "Novembro" },
  { value: 12, label: "Dezembro" },
];

export default function ReportsTable() {
  const [data, setData] = useState<WeeklyReportsResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [feedbackMessage, setFeedbackMessage] = useState<string | null>(null);

  // Filtros
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedYear, setSelectedYear] = useState<string>("all");
  const [selectedMonth, setSelectedMonth] = useState<string>("all");

  // Estados de expansão (Ano e Mês)
  const [expandedYears, setExpandedYears] = useState<Record<number, boolean>>({});
  const [expandedMonths, setExpandedMonths] = useState<Record<string, boolean>>({});

  // Modal de Pré-visualização Rápida
  const [previewDoc, setPreviewDoc] = useState<ReportDocument | null>(null);
  const [downloadingDocId, setDownloadingDocId] = useState<string | null>(null);

  // Carregar dados da API
  const fetchReports = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params: { ano?: number; mes?: number; busca?: string } = {};
      if (selectedYear !== "all") params.ano = Number(selectedYear);
      if (selectedMonth !== "all") params.mes = Number(selectedMonth);
      if (searchQuery.trim()) params.busca = searchQuery.trim();

      const response = await getWeeklyReports(params);
      setData(response);

      // Expandir o primeiro ano e primeiro mês por defeito para uma navegação instantânea
      if (response.years.length > 0) {
        const initialYears: Record<number, boolean> = {};
        const initialMonths: Record<string, boolean> = {};

        response.years.forEach((yr, idx) => {
          // Mantém expandido o ano mais recente
          initialYears[yr.year] = idx === 0;
          yr.months.forEach((mo, mIdx) => {
            initialMonths[`${yr.year}-${mo.month}`] = idx === 0 && mIdx === 0;
          });
        });

        setExpandedYears((prev) => (Object.keys(prev).length === 0 ? initialYears : prev));
        setExpandedMonths((prev) => (Object.keys(prev).length === 0 ? initialMonths : prev));
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : "Erro ao carregar relatórios semanais.";
      setError(msg);
    } finally {
      setLoading(false);
    }
  }, [selectedYear, selectedMonth, searchQuery]);

  useEffect(() => {
    fetchReports();
  }, [fetchReports]);

  // Alternar Ano
  const toggleYear = (year: number) => {
    setExpandedYears((prev) => ({
      ...prev,
      [year]: !prev[year],
    }));
  };

  // Alternar Mês
  const toggleMonth = (year: number, month: number) => {
    const key = `${year}-${month}`;
    setExpandedMonths((prev) => ({
      ...prev,
      [key]: !prev[key],
    }));
  };

  // Expandir Tudo / Recolher Tudo
  const handleExpandAll = () => {
    if (!data) return;
    const newYears: Record<number, boolean> = {};
    const newMonths: Record<string, boolean> = {};

    data.years.forEach((yr) => {
      newYears[yr.year] = true;
      yr.months.forEach((mo) => {
        newMonths[`${yr.year}-${mo.month}`] = true;
      });
    });

    setExpandedYears(newYears);
    setExpandedMonths(newMonths);
  };

  const handleCollapseAll = () => {
    setExpandedYears({});
    setExpandedMonths({});
  };

  // Download individual do documento
  const handleDownload = async (doc: ReportDocument) => {
    if (downloadingDocId) return;
    setDownloadingDocId(doc.id);
    try {
      const blob = await downloadReportDocument(doc.id);
      const url = window.URL.createObjectURL(blob);
      const link = window.document.createElement("a");
      link.href = url;
      link.download = doc.name;
      window.document.body.appendChild(link);
      link.click();
      window.document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      setFeedbackMessage(`Download de "${doc.name}" iniciado.`);
      setTimeout(() => setFeedbackMessage(null), 3000);
    } catch (err) {
      console.error("Erro no download:", err);
      alert(`Falha ao descarregar ${doc.name}`);
    } finally {
      setDownloadingDocId(null);
    }
  };

  // Filtragem de anos disponíveis no seletor
  const availableYears = useMemo(() => {
    return data?.summary.available_years || [new Date().getFullYear()];
  }, [data]);

  return (
    <div className="space-y-6">
      {/* Header da Página */}
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div className="flex items-center gap-2.5">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-white shadow-theme-xs">
              <InboxIcon className="size-6" />
            </div>
            <div>
              <h2 className="text-xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                Relatórios Semanais de Documentos
              </h2>
              <p className="text-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                Índice cronológico de todos os documentos que deram entrada na plataforma com periodicidade semanal.
              </p>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={() => fetchReports()}
            disabled={loading}
            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all duration-150"
            title="Atualizar dados"
          >
            <ArrowPathIcon className={`size-4 ${loading ? "animate-spin text-brand-500" : ""}`} />
            <span>Atualizar</span>
          </button>
        </div>
      </div>

      {/* Cartões KPI de Resumo */}
      <ReportSummaryCards summary={data?.summary ?? null} loading={loading && !data} />

      {/* Feedback Toast Notification */}
      {feedbackMessage && (
        <div className="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-3 text-sm text-white shadow-lg dark:bg-white dark:text-gray-900 animate-fade-in">
          <span className="flex h-2 w-2 rounded-full bg-brand-400" />
          <span>{feedbackMessage}</span>
        </div>
      )}

      {/* Barra de Filtros e Pesquisa */}
      <div className="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          {/* Campo de Pesquisa */}
          <div className="relative flex-1">
            <MagnifyingGlassIcon className="pointer-events-none absolute left-3.5 top-1/2 size-4.5 -translate-y-1/2 text-gray-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Pesquisar por código (ex: DC.doc1.pdf), nome do documento, pasta ou uploader..."
              className="h-11 w-full rounded-xl border border-gray-300 bg-white pl-10 pr-9 text-sm text-gray-800 placeholder-gray-400 shadow-theme-xs transition-colors focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery("")}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
              >
                <XMarkIcon className="size-4" />
              </button>
            )}
          </div>

          {/* Seletores de Ano e Mês */}
          <div className="flex flex-wrap items-center gap-3">
            {/* Seletor de Ano */}
            <div className="flex items-center gap-1.5">
              <span className="text-xs font-medium text-gray-500 dark:text-gray-400">Ano:</span>
              <select
                value={selectedYear}
                onChange={(e) => setSelectedYear(e.target.value)}
                className="h-10 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:border-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
              >
                <option value="all">Todos os Anos</option>
                {availableYears.map((yr) => (
                  <option key={yr} value={yr}>
                    Ano {yr}
                  </option>
                ))}
              </select>
            </div>

            {/* Seletor de Mês */}
            <div className="flex items-center gap-1.5">
              <span className="text-xs font-medium text-gray-500 dark:text-gray-400">Mês:</span>
              <select
                value={selectedMonth}
                onChange={(e) => setSelectedMonth(e.target.value)}
                className="h-10 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:border-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
              >
                <option value="all">Todos os Meses</option>
                {MONTH_NAMES.map((m) => (
                  <option key={m.value} value={m.value}>
                    {m.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Botões Expandir / Recolher Tudo */}
            <div className="flex items-center gap-1.5 border-l border-gray-200 pl-3 dark:border-gray-700">
              <button
                onClick={handleExpandAll}
                className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                title="Expandir todas as pastas de ano e mês"
              >
                <ChevronDownIcon className="size-3.5" />
                <span className="hidden sm:inline">Expandir Tudo</span>
              </button>
              <button
                onClick={handleCollapseAll}
                className="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                title="Recolher todas as pastas de ano e mês"
              >
                <ChevronUpIcon className="size-3.5" />
                <span className="hidden sm:inline">Recolher Tudo</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Estado de Erro */}
      {error && (
        <div className="rounded-2xl border border-red-200 bg-red-50 p-6 text-center dark:border-red-900/30 dark:bg-red-950/20">
          <p className="text-sm font-semibold text-red-700 dark:text-red-400">{error}</p>
          <button
            onClick={() => fetchReports()}
            className="mt-3 inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700"
          >
            Tentar Novamente
          </button>
        </div>
      )}

      {/* Estado de Carregamento (Skeleton) */}
      {loading && !data && (
        <div className="space-y-4">
          {[1, 2].map((i) => (
            <div
              key={i}
              className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] animate-pulse"
            >
              <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className="h-10 w-10 rounded-xl bg-gray-200 dark:bg-gray-700" />
                  <div className="h-6 w-32 rounded bg-gray-200 dark:bg-gray-700" />
                </div>
                <div className="h-6 w-20 rounded bg-gray-200 dark:bg-gray-700" />
              </div>
              <div className="space-y-3 pt-3">
                <div className="h-12 w-full rounded-xl bg-gray-100 dark:bg-gray-800" />
                <div className="h-12 w-full rounded-xl bg-gray-100 dark:bg-gray-800" />
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Estado Vazio (Sem documentos) */}
      {!loading && data && data.years.length === 0 && (
        <div className="rounded-2xl border border-dashed border-gray-300 bg-white py-16 px-6 text-center dark:border-gray-800 dark:bg-white/[0.02]">
          <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
            <DocumentMagnifyingGlassIcon className="size-8 text-gray-400" />
          </div>
          <h3 className="mt-4 text-base font-semibold text-gray-900 dark:text-white">
            Nenhum relatório semanal encontrado
          </h3>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
            {searchQuery || selectedYear !== "all" || selectedMonth !== "all"
              ? "Não foram encontrados documentos para os filtros especificados. Experimente limpar ou ajustar a sua pesquisa."
              : "Ainda não existem documentos submetidos na plataforma para gerar índices semanais."}
          </p>
          {(searchQuery || selectedYear !== "all" || selectedMonth !== "all") && (
            <button
              onClick={() => {
                setSearchQuery("");
                setSelectedYear("all");
                setSelectedMonth("all");
              }}
              className="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600"
            >
              Limpar Filtros de Pesquisa
            </button>
          )}
        </div>
      )}

      {/* Estrutura Hierárquica: Ano > Mês > Relatórios Semanais */}
      {!loading && data && data.years.length > 0 && (
        <div className="space-y-5">
          {data.years.map((yearNode) => {
            const isYearOpen = expandedYears[yearNode.year] ?? false;

            return (
              <div
                key={yearNode.year}
                className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all dark:border-gray-800 dark:bg-white/[0.03]"
              >
                {/* Cabeçalho do Ano */}
                <div
                  onClick={() => toggleYear(yearNode.year)}
                  className="flex items-center justify-between px-6 py-4 cursor-pointer bg-gradient-to-r from-gray-50/90 to-white dark:from-white/[0.04] dark:to-transparent border-b border-gray-200 dark:border-gray-800 hover:bg-gray-100/50 dark:hover:bg-white/[0.06] transition-colors"
                >
                  <div className="flex items-center gap-3.5">
                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-500 text-white shadow-sm">
                      <CalendarDaysIcon className="size-6" />
                    </div>
                    <div>
                      <div className="flex items-center gap-2.5">
                        <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                          Ano {yearNode.year}
                        </h3>
                        <span className="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-bold text-brand-800 dark:bg-brand-900/40 dark:text-brand-300">
                          {yearNode.count} {yearNode.count === 1 ? "documento" : "documentos"}
                        </span>
                      </div>
                      <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {yearNode.months.length} {yearNode.months.length === 1 ? "mês com registos" : "meses com registos"} • Volume total: {yearNode.size_formatted}
                      </p>
                    </div>
                  </div>

                  <div className="flex items-center gap-3">
                    <span className="text-xs text-gray-400 font-medium hidden sm:inline">
                      {isYearOpen ? "Recolher Ano" : "Expandir Ano"}
                    </span>
                    <button
                      className="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                      title={isYearOpen ? "Recolher" : "Expandir"}
                    >
                      {isYearOpen ? (
                        <ChevronUpIcon className="size-5" />
                      ) : (
                        <ChevronDownIcon className="size-5" />
                      )}
                    </button>
                  </div>
                </div>

                {/* Conteúdo do Ano: Lista de Meses */}
                {isYearOpen && (
                  <div className="p-4 sm:p-6 space-y-4 bg-gray-50/40 dark:bg-black/10">
                    {yearNode.months.map((monthNode) => {
                      const monthKey = `${yearNode.year}-${monthNode.month}`;
                      const isMonthOpen = expandedMonths[monthKey] ?? false;

                      return (
                        <div
                          key={monthKey}
                          className="rounded-xl border border-gray-200/90 bg-white overflow-hidden shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/40 transition-all"
                        >
                          {/* Cabeçalho do Mês */}
                          <div
                            onClick={() => toggleMonth(yearNode.year, monthNode.month)}
                            className="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/[0.03] border-b border-gray-100 dark:border-gray-800 transition-colors"
                          >
                            <div className="flex items-center gap-3">
                              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                <CalendarIcon className="size-5" />
                              </div>
                              <div>
                                <div className="flex items-center gap-2">
                                  <h4 className="text-sm font-bold text-gray-800 dark:text-gray-100">
                                    {monthNode.name} de {yearNode.year}
                                  </h4>
                                  <span className="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    {monthNode.count} docs
                                  </span>
                                </div>
                                <p className="text-xs text-gray-400 dark:text-gray-500">
                                  {monthNode.weeks.length} {monthNode.weeks.length === 1 ? "semana indexada" : "semanas indexadas"} • {monthNode.size_formatted}
                                </p>
                              </div>
                            </div>

                            <div className="flex items-center gap-2">
                              <span className="text-xs text-gray-400 hidden md:inline">
                                {isMonthOpen ? "Recolher Mês" : "Ver Semanas"}
                              </span>
                              <button
                                className="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                title={isMonthOpen ? "Recolher mês" : "Expandir mês"}
                              >
                                {isMonthOpen ? (
                                  <ChevronUpIcon className="size-4.5" />
                                ) : (
                                  <ChevronDownIcon className="size-4.5" />
                                )}
                              </button>
                            </div>
                          </div>

                          {/* Conteúdo do Mês: Lista de Relatórios Semanais */}
                          {isMonthOpen && (
                            <div className="p-4 space-y-4 bg-gray-50/30 dark:bg-gray-950/20">
                              {monthNode.weeks.map((weekNode) => (
                                <WeeklyReportItem
                                  key={weekNode.week_key}
                                  week={weekNode}
                                  year={yearNode.year}
                                  onPreviewDocument={(doc) => setPreviewDoc(doc)}
                                  onDownloadDocument={handleDownload}
                                  downloadingDocId={downloadingDocId}
                                  defaultExpanded={true}
                                />
                              ))}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {/* Modal de Pré-visualização Rápida de Documento */}
      <DocumentQuickPreviewModal
        document={previewDoc}
        isOpen={Boolean(previewDoc)}
        onClose={() => setPreviewDoc(null)}
        onDownload={handleDownload}
        isDownloading={downloadingDocId === previewDoc?.id}
      />
    </div>
  );
}
