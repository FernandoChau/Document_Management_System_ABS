import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../ui/table";
// import Badge from "../../ui/badge/Badge";
import { DownloadIcon, FolderIcon, MoreDotIcon, PaperPlaneIcon, PencilIcon, TrashBinIcon } from "../../../icons";
import { DocumentTextIcon, FolderIcon as FolderSolid } from '@heroicons/react/24/solid';
import { AdjustmentsHorizontalIcon, DocumentPlusIcon, EyeIcon,  FolderPlusIcon, KeyIcon, MagnifyingGlassIcon } from "@heroicons/react/24/outline";
import { Dropdown } from "../../ui/dropdown/Dropdown";
import { DropdownItem } from "../../ui/dropdown/DropdownItem";
import { useState } from "react";
import Button from "../../ui/button/Button";

// Define the TypeScript interface for the table rows
interface Product {
  id: number; // Unique identifier for each product
  name: string; // Product name
  variants: string; // Number of variants (e.g., "1 Variant", "2 Variants")
  category: string; // Category of the product
  price: string; // Price of the product (as a string with currency symbol)
  owner: string;
  ownerAvatar: string;
  // status: string; // Status of the product
  image: string; // URL or path to the product image
  status: "Delivered" | "Pending" | "Canceled"; // Status of the product
}

// Define the table data using the interface
const tableData: Product[] = [
  {
    id: 1,
    name: "Recursos Humanos",
    variants: "RH",
    owner: "Fernando Chau",
    ownerAvatar: "FC",
    category: "Laptop",
    price: "$2399.00",
    status: "Delivered",
    image: "/images/product/product-01.jpg", // Replace with actual image URL
  },
  {
    id: 2,
    name: "Financas",
    variants: "FIN",
    owner: "Ivandro Bauaze",
    ownerAvatar: "IB",
    category: "Watch",
    price: "$879.00",
    status: "Pending",
    image: "/images/product/product-02.jpg", // Replace with actual image URL
  },
  {
    id: 3,
    name: "Administrativa",
    variants: "ADM",
    owner: "Mirene Mussumbe",
    ownerAvatar: "MM",
    category: "SmartPhone",
    price: "$1869.00",
    status: "Delivered",
    image: "/images/product/product-03.jpg", // Replace with actual image URL
  },
  {
    id: 4,
    name: "Tecnologias de Informacao",
    variants: "TI",
    owner: "Naila Tsenane",
    ownerAvatar: "NT",
    category: "Electronics",
    price: "$1699.00",
    status: "Canceled",
    image: "/images/product/product-04.jpg", // Replace with actual image URL
  },
  {
    id: 5,
    name: "Comunicacao e Imagem",
    variants: "CI",
    owner: "Cornelio Pessana",
    ownerAvatar: "CP",
    category: "Accessories",
    price: "$240.00",
    status: "Delivered",
    image: "/images/product/product-05.jpg", // Replace with actual image URL
  },
];

export default function FolderTables() {
  const [isOpen, setIsOpen] = useState(false);

  function toggleDropdown() {
    setIsOpen(!isOpen);
  }

  function closeDropdown() {
    setIsOpen(false);
  }
  return (
    <div className=" max-h-[calc(100vh-110px)] overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
      <div className="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h3 className="text-lg flex items-center gap-2 text-gray-800 dark:text-white/90">
            <FolderIcon className="size-5 -mt-0.5"/> ABS / Folder / <span className=" font-bold">Sub dir</span>
          </h3>
        </div>

        <div className="flex items-center gap-3">
          <button className=" w-40 h-10 pl-2.5 flex items-center justify-start gap-2 rounded-full border border-gray-300 bg-white text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <MagnifyingGlassIcon className="size-4.5" />
            Pesquisar...
          </button>
          <button className="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <AdjustmentsHorizontalIcon className="size-5" />
            Filtro
          </button>
          <Button variant="primary" className="h-10 bg-yellow-500 text-white dark:bg-yellow-400 dark:text-gray-950 hover:bg-yellow-400 dark:hover:bg-yellow-400">
            <FolderPlusIcon className="size-5"/>
            {/* Criar Pasta */}
          </Button>
          <Button variant="primary" className="h-10 bg-brand-500 dark:bg-brand-500 dark:text-gray-950 dark:hover:opacity-95">
            <DocumentPlusIcon className="size-5"/>
            {/* Subir Ficheiro */}
          </Button>
        </div>
      </div>
      <div className="max-w-full max-h-[calc(100vh-200px)] overflow-x-auto">
        <Table>
          {/* Table Header */}
          <TableHeader className=" sticky border-gray-100 dark:border-gray-800 border-y">
            <TableRow>
              <TableCell
                isHeader
                className="py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Ficheiro
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
                Responsável
              </TableCell>
              <TableCell
                isHeader
                className="py-3 w-5 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
              >
                Acção
              </TableCell>
            </TableRow>
          </TableHeader>

          {/* Table Body */}

          <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
            {tableData.map((product) => (
              <TableRow key={product.id} className=" cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800/30">
                <TableCell className="py-3">
                  <div className="flex items-center gap-2">
                    <div className="h-[50px] w-[50px] flex items-center justify-center overflow-hidden rounded-md">
                        <FolderSolid className="h-8 w-8 text-yellow-400 dark:text-yellow-300" />
                    </div>
                    <div>
                      <p className="font-medium text-gray-700 text-theme-m dark:text-white/80">
                        {product.name}
                      </p>
                      <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                        Ref: {product.variants}
                      </p>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="py-3 w-5 pr-15 text-gray-500 text-theme-sm dark:text-gray-400">
                  {product.price}
                </TableCell>
                <TableCell className="py-2 pr-10  whitespace-nowrap w-10  text-gray-500 text-theme-sm dark:text-gray-400">
                  <div className="flex items-center gap-2">
                    <p className="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-theme-xs font-medium w-7 h-7 flex items-center justify-center rounded-full">
                        {product.ownerAvatar}
                    </p>
                    <p className="text-gray-700 dark:text-gray-300">{product.owner}</p>
                  </div>
                </TableCell>
                <TableCell className="py-3 flex gap-2 text-gray-500 text-theme-sm dark:text-gray-400">
                    <button className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                        <PencilIcon className="size-5"/>
                    </button>
                    <button className="h-8 w-8 border text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                        <EyeIcon className="size-5"/>
                    </button>
                    <button className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                        <DownloadIcon className="size-5"/>
                    </button>
                    <div className="relative inline-block mt-1">
                        <button className="dropdown-toggle" onClick={toggleDropdown}>
                            <MoreDotIcon className="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 size-6" />
                        </button>
                        <Dropdown 
                            isOpen={isOpen}
                            onClose={closeDropdown}
                            className="w-40 p-2"
                        >
                            <DropdownItem
                            onItemClick={closeDropdown}
                            className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                            >
                                <PaperPlaneIcon className=" -rotate-45 -mt-0.5" />
                                Partilhar
                            </DropdownItem>
                            <DropdownItem
                            onItemClick={closeDropdown}
                            className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                            >
                                <KeyIcon className="size-3.5 -rotate-180"/>
                                Permissões
                            </DropdownItem>
                            <DropdownItem
                            onItemClick={closeDropdown}
                            className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                            >
                                <TrashBinIcon/>
                                Remover
                            </DropdownItem>
                        </Dropdown>
                    </div>
                </TableCell>
              </TableRow>
            ))}

            {tableData.map((product) => (
              <TableRow key={product.id} className=" cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800/30">
                <TableCell className="py-2.5">
                  <div className="flex items-center gap-2">
                    <div className="h-[50px] w-[50px] flex items-center justify-center overflow-hidden rounded-md">
                        <DocumentTextIcon className="h-8 w-8 text-brand-400 dark:text-brand-300" />
                    </div>
                    <div>
                      <p className="font-medium text-gray-700 text-theme-m dark:text-white/80">
                        {product.name}
                      </p>
                      <p className="text-gray-500 text-theme-xs dark:text-gray-400">
                        Ref: {product.variants}
                      </p>
                    </div>
                  </div>
                </TableCell>
                <TableCell className="py-2.5 w-5 pr-15 text-gray-500 text-theme-sm dark:text-gray-400">
                  {product.price}
                </TableCell>
                <TableCell className="py-2.5 pr-10  whitespace-nowrap w-10  text-gray-500 text-theme-sm dark:text-gray-400">
                  <div className="flex items-center gap-2">
                    <p className="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-theme-xs font-medium w-7 h-7 flex items-center justify-center rounded-full">
                        {product.ownerAvatar}
                    </p>
                    <p className="text-gray-700 dark:text-gray-300">{product.owner}</p>
                  </div>
                </TableCell>
                <TableCell className="py-2.5 pr-5 flex gap-2 text-gray-500 text-theme-sm dark:text-gray-400">
                    <button className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                        <PencilIcon className="size-5"/>
                    </button>
                    <button className="h-8 w-8 border text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                        <EyeIcon className="size-5"/>
                    </button>
                    <button className="h-8 w-8 border text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 duration-300 dark:duration-150 border-transparent hover:border-brand-300 dark:hover:border-transparent hover:bg-brand-100 flex dark:hover:bg-gray-700 items-center justify-center rounded-full">
                        <DownloadIcon className="size-5"/>
                    </button>
                    <div className="relative inline-block mt-1">
                        <button className="dropdown-toggle" onClick={toggleDropdown}>
                            <MoreDotIcon className="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 size-6" />
                        </button>
                        <Dropdown 
                            isOpen={isOpen}
                            onClose={closeDropdown}
                            className="w-40 p-2"
                        >
                            <DropdownItem
                            onItemClick={closeDropdown}
                            className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                            >
                                <PaperPlaneIcon className=" -rotate-45 -mt-0.5" />
                                Partilhar
                            </DropdownItem>
                            <DropdownItem
                            onItemClick={closeDropdown}
                            className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                            >
                                <KeyIcon className="size-3.5 -rotate-180"/>
                                Permissões
                            </DropdownItem>
                            <DropdownItem
                            onItemClick={closeDropdown}
                            className="flex items-center gap-1 w-full font-normal text-left text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                            >
                                <TrashBinIcon/>
                                Remover
                            </DropdownItem>
                        </Dropdown>
                    </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  );
}
