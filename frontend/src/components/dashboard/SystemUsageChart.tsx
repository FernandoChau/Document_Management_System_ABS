import Chart from "react-apexcharts";
import { ApexOptions } from "apexcharts";
import { DashboardDepartmentChartItem } from "../../api/dashboard.service";

interface SystemUsageChartProps {
  data: DashboardDepartmentChartItem[];
}

export default function SystemUsageChart({ data }: SystemUsageChartProps) {
  const categories = data.map((d) => d.name);
  const seriesData = data.map((d) => d.documents_count);

  const options: ApexOptions = {
    colors: ["#4FB852"],
    chart: {
      fontFamily: "Outfit, sans-serif",
      type: "bar",
      height: 310,
      toolbar: {
        show: false,
      },
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: "45%",
        borderRadius: 4,
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      show: true,
      width: 4,
      colors: ["transparent"],
    },
    xaxis: {
      categories: categories,
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
    },
    yaxis: {
      title: {
        text: "Documentos",
      },
    },
    grid: {
      yaxis: {
        lines: {
          show: true,
        },
      },
    },
    fill: {
      opacity: 1,
    },
    tooltip: {
      y: {
        formatter: (val: number) => `${val} Documentos`,
      },
    },
  };

  const series = [
    {
      name: "Documentos",
      data: seriesData,
    },
  ];

  return (
    <div className="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
      <div className="flex flex-col gap-5 mb-6 sm:flex-row sm:justify-between">
        <div className="w-full">
          <h3 className="text-lg font-semibold text-gray-800 dark:text-white/90">
            Utilização por Departamento
          </h3>
          <p className="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
            Número de documentos por departamento
          </p>
        </div>
      </div>

      <div className="max-w-full overflow-x-auto custom-scrollbar">
        <div className="min-w-[650px] xl:min-w-full">
          {data.length > 0 ? (
            <Chart options={options} series={series} type="bar" height={310} />
          ) : (
            <p className="text-center text-gray-500 py-10">Nenhum dado disponível.</p>
          )}
        </div>
      </div>
    </div>
  );
}
