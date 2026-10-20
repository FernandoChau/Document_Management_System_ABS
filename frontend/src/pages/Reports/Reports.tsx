import React from "react";
import PageMeta from "@/components/common/PageMeta";
import ReportsTable from "@/components/tables/ReportsTable/ReportsTable";

export default function Reports() {
  return (
    <>
      <PageMeta
        title="Relatórios & Índices Semanais | ABS DMS"
        description="Índice semanal dos documentos submetidos para a plataforma ABS DMS, organizados hierarquicamente por ano, mês e semana."
      />
      <div className="max-w-full">
        <ReportsTable />
      </div>
    </>
  );
}
