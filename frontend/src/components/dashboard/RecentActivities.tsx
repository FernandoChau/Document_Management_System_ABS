import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../ui/table";
import Badge from "../ui/badge/Badge";
import { DashboardRecentActivityItem } from "../../api/dashboard.service";

interface RecentActivitiesProps {
  title: string;
  data: DashboardRecentActivityItem[];
}

function formatBytes(bytes: number, decimals = 2) {
  if (!+bytes) return '0 Bytes'
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
}

function formatDate(dateString: string) {
  const date = new Date(dateString);
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

export function getFileExtension(filename: string, mimeType: string): string {
  if (filename && filename.includes('.')) {
    const ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();
    if (ext && ext.length >= 2 && ext.length <= 5) {
      return ext;
    }
  }
  if (!mimeType) return '.bin';
  const mimeMap: { [key: string]: string } = {
    'application/pdf': '.pdf',
    'application/msword': '.doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '.docx',
    'application/vnd.ms-excel': '.xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '.xlsx',
    'application/vnd.ms-powerpoint': '.ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation': '.pptx',
    'text/plain': '.txt',
    'text/html': '.html',
    'application/json': '.json',
    'application/zip': '.zip',
    'application/x-rar-compressed': '.rar',
    'image/jpeg': '.jpg',
    'image/png': '.png',
    'image/gif': '.gif',
    'image/svg+xml': '.svg',
  };
  return mimeMap[mimeType] || `.${mimeType.split('/')[1] || 'bin'}`;
}

export default function RecentActivities({ title, data }: RecentActivitiesProps) {
  return (
    <div className="max-h-[calc(100vh-110px)] overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 mb-10 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
      <div className="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 className="text-lg font-semibold text-gray-800 dark:text-white/90">
            {title}
          </h3>
        </div>
      </div>
      <div className="max-w-full max-h-[calc(100vh-200px)] overflow-x-auto">
        <Table>
          <TableHeader className="sticky border-gray-100 dark:border-gray-800 border-y">
            <TableRow>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Documento
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Tamanho
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Tipo
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Utilizador
              </TableCell>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Data
              </TableCell>
            </TableRow>
          </TableHeader>

          <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
            {data.length > 0 ? data.map((doc) => (
              <TableRow key={doc.id} className="">
                <TableCell className="py-3">
                  <div className="flex items-center gap-3">
                    <div>
                      <p className="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                        {doc.name}
                      </p>
                      <span className="text-gray-500 text-theme-xs dark:text-gray-400">
                        {doc.folder ? doc.folder.name : 'Raiz'}
                      </span>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="py-3 text-gray-500 text-theme-sm dark:text-gray-400">
                  {formatBytes(doc.size)}
                </TableCell>
                <TableCell className="py-3 text-gray-500 text-theme-sm dark:text-gray-400">
                  <Badge size="sm" color="success">
                    {getFileExtension(doc.name, doc.mime_type)}
                  </Badge>
                </TableCell>
                <TableCell className="py-3 text-gray-500 text-theme-sm dark:text-gray-400">
                  {doc.uploader?.name || 'Desconhecido'}
                </TableCell>
                <TableCell className="py-3 text-gray-500 text-theme-sm dark:text-gray-400">
                  {formatDate(doc.created_at)}
                </TableCell>
              </TableRow>
            )) : (
              <TableRow>
                 <TableCell className="py-6 text-center text-gray-500" colSpan={5}>
                   Nenhuma atividade recente.
                 </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
    </div>
  );
}
