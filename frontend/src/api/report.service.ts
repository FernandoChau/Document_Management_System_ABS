import api from "./axios";

export interface ReportDocument {
  id: string;
  name: string;
  code: string;
  mime_type: string;
  size: number;
  size_formatted: string;
  folder: {
    id?: string;
    name: string;
    reference_code: string;
  };
  uploader: {
    id?: string;
    name: string;
    email: string;
    avatar?: string | null;
    avatar_color: string;
  };
  created_at: string;
  created_at_formatted: string;
  created_at_date: string;
  created_at_time: string;
}

export interface ReportWeek {
  week_key: string;
  week_number: number;
  start_date: string;
  end_date: string;
  label: string;
  short_label: string;
  date_range: string;
  count: number;
  size_bytes: number;
  size_formatted: string;
  documents: ReportDocument[];
}

export interface ReportMonth {
  month: number;
  name: string;
  count: number;
  size_bytes: number;
  size_formatted: string;
  weeks: ReportWeek[];
}

export interface ReportYear {
  year: number;
  count: number;
  size_bytes: number;
  size_formatted: string;
  months: ReportMonth[];
}

export interface WeeklyReportsSummary {
  total_documents: number;
  total_size_bytes: number;
  total_size_formatted: string;
  total_weeks: number;
  available_years: number[];
}

export interface WeeklyReportsResponse {
  summary: WeeklyReportsSummary;
  years: ReportYear[];
}

export interface WeeklyReportsFilterParams {
  ano?: number | string;
  mes?: number | string;
  busca?: string;
  pasta_id?: string;
}

/**
 * Obter os relatórios com periodicidade semanal (Ano > Mês > Semana > Documentos)
 */
export const getWeeklyReports = async (
  params?: WeeklyReportsFilterParams,
): Promise<WeeklyReportsResponse> => {
  const response = await api.get<WeeklyReportsResponse>("/relatorios/semanal", {
    params,
  });
  return response.data;
};

/**
 * Exportar CSV do índice semanal de documentos
 */
export const exportWeeklyReportCsv = async (
  ano: number,
  semana: number,
): Promise<Blob> => {
  const response = await api.get("/relatorios/exportar-semana", {
    params: { ano, semana },
    responseType: "blob",
  });
  return response.data as Blob;
};

/**
 * Baixar documento individualmente
 */
export const downloadReportDocument = async (
  documentId: string,
): Promise<Blob> => {
  const response = await api.get(`/documentos/${documentId}/baixar`, {
    responseType: "blob",
  });
  return response.data as Blob;
};
