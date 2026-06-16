import { useEffect, useState } from "react";
import PageMeta from "../../components/common/PageMeta";
import { useAuth } from "../../context/AuthContext";
import { getAdminDashboard, getUserDashboard, AdminDashboardData, UserDashboardData } from "../../api/dashboard.service";

import DashboardMetrics, { MetricItem } from "../../components/dashboard/DashboardMetrics";
import EvolutionChart from "../../components/dashboard/EvolutionChart";
import DocumentDistributionChart from "../../components/dashboard/DocumentDistributionChart";
import SystemUsageChart from "../../components/dashboard/SystemUsageChart";
import RecentActivities from "../../components/dashboard/RecentActivities";
import { GroupIcon } from "../../icons";

export default function Dashboard() {
  const { user } = useAuth();
  const isAdmin = user?.role === "admin";
  
  const [adminData, setAdminData] = useState<AdminDashboardData | null>(null);
  const [userData, setUserData] = useState<UserDashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDashboard = async () => {
      try {
        if (isAdmin) {
          const data = await getAdminDashboard();
          setAdminData(data);
        } else {
          const data = await getUserDashboard();
          setUserData(data);
        }
      } catch (error) {
        console.error("Error fetching dashboard data", error);
      } finally {
        setLoading(false);
      }
    };
    fetchDashboard();
  }, [isAdmin]);

  if (loading) {
    return <div className="flex h-64 items-center justify-center text-gray-500">A carregar dashboard...</div>;
  }

  const DocIcon = () => <svg className="size-6 text-gray-800 dark:text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>;
  const FolderIcon = () => <svg className="size-6 text-gray-800 dark:text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>;
  const ImageIcon = () => <svg className="size-6 text-gray-800 dark:text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>;
  const UserIcon = () => <GroupIcon className="size-6 text-gray-800 dark:text-white/90" />;

  let metrics: MetricItem[] = [];

  if (isAdmin && adminData) {
    metrics = [
      { title: "Total de Documentos", value: adminData.summary.total_documents || 0, icon: <DocIcon /> },
      { title: "Total de Pastas", value: adminData.summary.total_folders || 0, icon: <FolderIcon /> },
      { title: "Total de Imagens", value: adminData.summary.total_images || 0, icon: <ImageIcon /> },
      { title: "Utilizadores", value: adminData.summary.total_users || 0, icon: <UserIcon /> },
    ];
  } else if (!isAdmin && userData) {
    metrics = [
      { title: "Meus Documentos", value: userData.summary.my_documents || 0, icon: <DocIcon /> },
      { title: "Minhas Pastas", value: userData.summary.my_folders || 0, icon: <FolderIcon /> },
      { title: "Minhas Imagens", value: userData.summary.my_images || 0, icon: <ImageIcon /> },
      { title: "Meus Álbuns", value: userData.summary.my_albums || 0, icon: <ImageIcon /> },
    ];
  }

  return (
    <>
      <PageMeta
        title="Dashboard | ABS DMS V2.0"
        description="Dashboard de Gestão Documental"
      />
      <div className="grid grid-cols-12 gap-4 md:gap-6">
        <div className="col-span-12 space-y-6">
          <DashboardMetrics metrics={metrics} />
        </div>

        <div className="col-span-12 xl:col-span-7">
          <EvolutionChart 
            title={isAdmin ? "Evolução Documental" : "Meus Uploads por Mês"}
            seriesName="Documentos"
            data={isAdmin ? adminData?.evolution_chart || [] : userData?.evolution_chart || []}
          />
        </div>

        <div className="col-span-12 xl:col-span-5">
          <DocumentDistributionChart 
            data={isAdmin ? adminData?.distribution_chart || [] : userData?.distribution_chart || []}
          />
        </div>

        {isAdmin && adminData?.department_chart && adminData.department_chart.length > 0 && (
          <div className="col-span-12">
            <SystemUsageChart data={adminData.department_chart} />
          </div>
        )}

        <div className="col-span-12">
          <RecentActivities 
            title={isAdmin ? "Últimos Uploads" : "Meus Últimos Uploads"}
            data={isAdmin ? adminData?.recent_activities || [] : userData?.recent_activities || []}
          />
        </div>
      </div>
    </>
  );
}
