import React from "react";
import { TopUserItem } from "../../api/dashboard.service";

interface TopUsersProps {
  data: TopUserItem[];
  period: string;
  onPeriodChange: (period: string) => void;
}

export default function TopUsers({ data, period, onPeriodChange }: TopUsersProps) {
  const mostActive = data && data.length > 0 ? data[0] : null;
  const others = data && data.length > 1 ? data.slice(1) : [];

  const getInitials = (name: string) => {
    return name
      .split(" ")
      .map((n) => n[0])
      .slice(0, 2)
      .join("")
      .toUpperCase();
  };

  return (
    <div className="rounded-2xl w-full h-fit border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 shadow-sm">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h3 className="text-lg font-semibold text-gray-800 dark:text-white/90">
            Utilizadores Ativos (DMS)
          </h3>
          <p className="mt-1 text-gray-500 text-theme-sm dark:text-gray-400">
            Quem realiza mais operações
          </p>
        </div>
        <div>
          <select
            value={period}
            onChange={(e) => onPeriodChange(e.target.value)}
            className="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-700 outline-none transition dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
          >
            <option value="month">Mensal</option>
            <option value="quarter">Trimestral</option>
            <option value="semester">Semestral</option>
            <option value="year">Anual</option>
          </select>
        </div>
      </div>

      {mostActive ? (
        <div className="space-y-6">
          {/* Highlight #1 Active User */}
          <div className="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50/50 to-indigo-50/20 p-4 dark:border-blue-950/40 dark:from-blue-950/20 dark:to-indigo-950/10 flex items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <div className="relative flex size-14 items-center justify-center rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-lg shadow-md">
                {getInitials(mostActive.user?.name || "Desconhecido")}
                <span className="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-yellow-500 text-xs font-bold text-white ring-2 ring-white dark:ring-gray-950">
                  ★
                </span>
              </div>
              <div>
                <span className="inline-block rounded-full bg-blue-100 px-2 py-0.5 text-theme-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 mb-1">
                  Mais Ativo no DMS
                </span>
                <h4 className="font-semibold text-gray-800 dark:text-white/90 text-sm">
                  {mostActive.user?.name || "Desconhecido"}
                </h4>
                <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                  {mostActive.user?.email || "Sem email"}
                </p>
              </div>
            </div>
            <div className="text-right">
              <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {mostActive.actions_count}
              </p>
              <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                Operações
              </p>
            </div>
          </div>

          {/* List of others */}
          {others.length > 0 && (
            <div className="space-y-3 pt-2">
              <p className="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Outros Operadores
              </p>
              <div className="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                {others.map((item, index) => (
                  <div
                    key={item.user_id || index}
                    className="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/[0.02] transition"
                  >
                    <div className="flex items-center gap-3">
                      <span className="text-sm font-medium text-gray-400 dark:text-gray-500 w-4">
                        {index + 2}
                      </span>
                      <div className="flex size-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 font-semibold text-xs dark:bg-gray-800 dark:text-gray-300">
                        {getInitials(item.user?.name || "Desconhecido")}
                      </div>
                      <div>
                        <h5 className="font-medium text-gray-800 dark:text-white/95 text-xs">
                          {item.user?.name || "Desconhecido"}
                        </h5>
                        <p className="text-gray-500 text-[10px] dark:text-gray-400">
                          {item.user?.email || "Sem email"}
                        </p>
                      </div>
                    </div>
                    <div className="text-right">
                      <p className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {item.actions_count} op.
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      ) : (
        <div className="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
          Nenhuma atividade registada neste período.
        </div>
      )}
    </div>
  );
}
