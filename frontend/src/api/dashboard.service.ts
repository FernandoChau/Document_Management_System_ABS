import axios from "./axios";

export interface DashboardSummary {
  total_documents?: number;
  total_folders?: number;
  total_images?: number;
  total_albums?: number;
  total_users?: number;
  documents_this_month?: number;
  my_documents?: number;
  my_images?: number;
  my_albums?: number;
  my_folders?: number;
  total_size_documents?: number;
  total_size_images?: number;
  total_size_general?: number;
}

export interface DashboardEvolutionChartItem {
  date: string;
  count: number;
}

export interface DashboardDistributionChartItem {
  mime_type: string;
  count: number;
  total_size: string | number;
}

export interface DashboardDepartmentChartItem {
  id: string;
  name: string;
  documents_count: number;
}

export interface DashboardRecentActivityItem {
  id: string;
  name: string;
  size: number;
  mime_type: string;
  created_at: string;
  user_id?: string;
  folder_id?: string;
  folder?: any;
  uploader?: any;
}

export interface TopUserItem {
  user_id: string;
  actions_count: number;
  user: any;
}

export interface AdminDashboardData {
  summary: DashboardSummary;
  evolution_chart: DashboardEvolutionChartItem[];
  distribution_chart: DashboardDistributionChartItem[];
  department_chart: DashboardDepartmentChartItem[];
  recent_activities: DashboardRecentActivityItem[];
  top_users: TopUserItem[];
}

export interface UserDashboardData {
  summary: DashboardSummary;
  evolution_chart: DashboardEvolutionChartItem[];
  distribution_chart: DashboardDistributionChartItem[];
  recent_activities: DashboardRecentActivityItem[];
}

export const getAdminDashboard = async (period?: string): Promise<AdminDashboardData> => {
  const response = await axios.get("/dashboard/admin", { params: { period } });
  return response.data;
};

export const getUserDashboard = async (): Promise<UserDashboardData> => {
  const response = await axios.get("/dashboard/user");
  return response.data;
};
