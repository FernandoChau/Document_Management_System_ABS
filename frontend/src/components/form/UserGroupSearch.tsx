import type React from "react";
import { useState, useEffect, useRef } from "react";
import { listUsers } from "../../api/user.service";
import { listGroups } from "../../api/group.service";

export interface SearchResult {
    id: string;
    name: string;
    type: "user" | "group";
    email?: string; // apenas para users
}

interface UserGroupSearchProps {
    onChange?: (result: SearchResult | null) => void;
    placeholder?: string;
    disabled?: boolean;
    value?: SearchResult | null;
}

const UserGroupSearch: React.FC<UserGroupSearchProps> = ({
    onChange,
    placeholder = "Pesquise um utilizador ou grupo...",
    disabled = false,
    value,
}) => {
    const [searchTerm, setSearchTerm] = useState("");
    const [results, setResults] = useState<SearchResult[]>([]);
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [selectedResult, setSelectedResult] = useState<SearchResult | null>(value || null);
    const dropdownRef = useRef<HTMLDivElement>(null);
    const debounceTimerRef = useRef<ReturnType<typeof setTimeout>>(undefined);

    // Handle click outside to close dropdown
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };

        if (isOpen) {
            document.addEventListener("mousedown", handleClickOutside);
            return () => document.removeEventListener("mousedown", handleClickOutside);
        }
    }, [isOpen]);

    // Fetch users and groups with debounce
    useEffect(() => {
        if (debounceTimerRef.current) {
            clearTimeout(debounceTimerRef.current);
        }

        if (!searchTerm.trim()) {
            setResults([]);
            return;
        }

        setIsLoading(true);

        debounceTimerRef.current = setTimeout(async () => {
            try {
                const [usersResponse, groupsResponse] = await Promise.all([
                    listUsers(),
                    listGroups(),
                ]);

                const searchLower = searchTerm.toLowerCase();

                // Filter users
                const users: SearchResult[] = (usersResponse.data.users || [])
                    .filter((user: any) =>
                        user.name.toLowerCase().includes(searchLower) ||
                        user.email?.toLowerCase().includes(searchLower)
                    )
                    .map((user: any) => ({
                        id: user.id,
                        name: user.name,
                        email: user.email,
                        type: "user" as const,
                    }));

                // Filter groups
                const groups: SearchResult[] = (groupsResponse.data.groups || [])
                    .filter((group: any) =>
                        group.name.toLowerCase().includes(searchLower)
                    )
                    .map((group: any) => ({
                        id: group.id,
                        name: group.name,
                        type: "group" as const,
                    }));

                setResults([...users, ...groups]);
            } catch (error) {
                console.error("Erro ao buscar utilizadores/grupos:", error);
                setResults([]);
            } finally {
                setIsLoading(false);
            }
        }, 300); // 300ms debounce
    }, [searchTerm]);

    const handleSelect = (result: SearchResult) => {
        setSelectedResult(result);
        setSearchTerm(result.name);
        setIsOpen(false);
        onChange?.(result);
    };

    const handleClear = () => {
        setSelectedResult(null);
        setSearchTerm("");
        setResults([]);
        onChange?.(null);
    };

    return (
        <div className="w-full" ref={dropdownRef}>
            <div className="relative">
                <input
                    type="text"
                    value={searchTerm}
                    onChange={(e) => {
                        setSearchTerm(e.target.value);
                        setIsOpen(true);
                    }}
                    onFocus={() => {
                        if (searchTerm.trim()) {
                            setIsOpen(true);
                        }
                    }}
                    placeholder={placeholder}
                    disabled={disabled}
                    className={`w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm 
            focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-100
            dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-300
            transition ${disabled ? "opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-800" : ""}`}
                />

                {selectedResult && (
                    <button
                        type="button"
                        onClick={handleClear}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        ✕
                    </button>
                )}

                {/* Loading indicator */}
                {isLoading && (
                    <div className="absolute right-3 top-1/2 -translate-y-1/2">
                        <div className="animate-spin h-4 w-4 border-2 border-brand-300 border-t-brand-500 rounded-full" />
                    </div>
                )}

                {/* Dropdown Results */}
                {isOpen && results.length > 0 && (
                    <div className="absolute z-50 top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 
            border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        {results.map((result) => (
                            <button
                                key={`${result.type}-${result.id}`}
                                type="button"
                                onClick={() => handleSelect(result)}
                                className="w-full text-left px-3 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-700 
                  transition border-b border-gray-200 dark:border-gray-700 last:border-b-0
                  flex items-center justify-between"
                            >
                                <div className="flex-1">
                                    <div className="font-medium text-gray-900 dark:text-white">
                                        {result.name}
                                    </div>
                                    {result.email && (
                                        <div className="text-xs text-gray-500 dark:text-gray-400">
                                            {result.email}
                                        </div>
                                    )}
                                </div>
                                <span className={`text-xs font-medium px-2 py-1 rounded-full ${result.type === "user"
                                        ? "bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
                                        : "bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200"
                                    }`}>
                                    {result.type === "user" ? "Utilizador" : "Grupo"}
                                </span>
                            </button>
                        ))}
                    </div>
                )}

                {/* No results message */}
                {isOpen && searchTerm.trim() && results.length === 0 && !isLoading && (
                    <div className="absolute z-50 top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 
            border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 text-center
            text-sm text-gray-500 dark:text-gray-400">
                        Nenhum utilizador ou grupo encontrado
                    </div>
                )}
            </div>
        </div>
    );
};

export default UserGroupSearch;
