import React from "react";
import {
  ArrowDownIcon,
  ArrowUpIcon,
} from "../../icons";
import Badge from "../ui/badge/Badge";

export interface MetricItem {
  title: string;
  value: string | number;
  icon: React.ReactNode;
  trend?: number; // optional percentage
}

interface DashboardMetricsProps {
  metrics: MetricItem[];
}

export default function DashboardMetrics({ metrics }: DashboardMetricsProps) {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 md:gap-6">
      {metrics.map((metric, index) => (
        <div
          key={index}
          className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6"
        >
          <div className="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800">
            {metric.icon}
          </div>

          <div className="flex items-end justify-between mt-5">
            <div>
              <span className="text-sm text-gray-500 dark:text-gray-400">
                {metric.title}
              </span>
              <h4 className="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">
                {metric.value}
              </h4>
            </div>
            
            {metric.trend !== undefined && (
              <Badge color={metric.trend >= 0 ? "success" : "error"}>
                {metric.trend >= 0 ? <ArrowUpIcon /> : <ArrowDownIcon />}
                {Math.abs(metric.trend)}%
              </Badge>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
