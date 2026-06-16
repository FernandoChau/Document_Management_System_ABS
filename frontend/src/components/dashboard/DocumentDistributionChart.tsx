import React from "react";
import Chart from "react-apexcharts";
import { ApexOptions } from "apexcharts";
import { DashboardDistributionChartItem } from "../../api/dashboard.service";
import { getFileExtension } from "./RecentActivities";

interface DocumentDistributionChartProps {
  data: DashboardDistributionChartItem[];
}

export default function DocumentDistributionChart({ data }: DocumentDistributionChartProps) {
  const series = data.map((item) => item.count);
  const labels = data.map((item) => getFileExtension("", item.mime_type) || "Outros");

  const options: ApexOptions = {
    chart: {
      type: "donut",
      fontFamily: "Outfit, sans-serif",
    },
    colors: ["#3b82f6", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6", "#ec4899", "#6b7280"],
    labels: labels,
    legend: {
      show: true,
      position: "bottom",
    },
    plotOptions: {
      pie: {
        donut: {
          size: "65%",
          background: "transparent",
        },
      },
    },
    dataLabels: {
      enabled: false,
    },
    responsive: [
      {
        breakpoint: 640,
        options: {
          chart: {
            width: 200,
          },
          legend: {
            show: false,
          },
        },
      },
    ],
  };

  const total = series.reduce((a, b) => a + b, 0);

  return (
    <div className="rounded-2xl w-full border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
      <div className="flex justify-between mb-6">
        <div>
          <h3 className="text-lg font-semibold text-gray-800 dark:text-white/90">
            Distribuição Documental
          </h3>
          <p className="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
            Documentos por tipo
          </p>
        </div>
      </div>

      <div className="flex justify-center mb-6">
        <div className="w-full max-w-[300px]">
          {series.length > 0 ? (
            <Chart options={options} series={series} type="donut" />
          ) : (
            <p className="text-center text-gray-500 py-10">Nenhum dado disponível.</p>
          )}
        </div>
      </div>

      <div className="space-y-4">
        {data.slice(0, 5).map((item, index) => {
          const percentage = total > 0 ? Math.round((item.count / total) * 100) : 0;
          return (
            <div key={index} className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className={`w-3 h-3 rounded-full`} style={{ backgroundColor: options.colors![index % options.colors!.length] }}></div>
                <div>
                  <p className="font-semibold text-gray-800 text-theme-sm dark:text-white/90">
                    {getFileExtension("", item.mime_type) || "Outros"}
                  </p>
                  <span className="block text-gray-500 text-theme-xs dark:text-gray-400">
                    {item.count} Documentos
                  </span>
                </div>
              </div>

              <div className="flex w-full max-w-[140px] items-center gap-3">
                <div className="relative block h-2 w-full max-w-[100px] rounded-sm bg-gray-200 dark:bg-gray-800">
                  <div
                    className="absolute left-0 top-0 flex h-full items-center justify-center rounded-sm bg-brand-500"
                    style={{ width: `${percentage}%`, backgroundColor: options.colors![index % options.colors!.length] }}
                  ></div>
                </div>
                <p className="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                  {percentage}%
                </p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
