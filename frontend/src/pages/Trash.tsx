import { useState, useEffect } from "react";
import { useAuth } from "@/context/AuthContext";
import { getDeletedItems, restoreDocument, restoreFolder } from "@/api/folder-document.service";
import Button from "@/components/ui/button/Button";
import Modal from "@/components/ui/modal";
import RecoveryModal from "@/components/modals/RecoveryModal";

interface DeletedItem {
    id: string;
    name: string;
    deleted_at: string;
    deleted_by: string;
    deleted_by_id: string;
    type: "folder" | "document";
}

function TrashPage() {
    const { user } = useAuth();
    const [items, setItems] = useState<DeletedItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [restoring, setRestoring] = useState<string | null>(null);
    const [showConfirm, setShowConfirm] = useState<{ item: DeletedItem | null; type: "folder" | "document" | null }>({
        item: null,
        type: null,
    });

    useEffect(() => {
        loadDeletedItems();
    }, []);

    const loadDeletedItems = async () => {
        try {
            const data = await getDeletedItems();
            setItems(data);
        } catch (error) {
            console.error("Failed to load deleted items", error);
        } finally {
            setLoading(false);
        }
    };

    const handleRestore = async (item: DeletedItem, type: "folder" | "document") => {
        setRestoring(item.id);
        try {
            if (type === "document") {
                await restoreDocument(item.id);
            } else {
                await restoreFolder(item.id);
            }
            setItems(items.filter(i => i.id !== item.id));
            setShowConfirm({ item: null, type: null });
        } catch (error) {
            console.error("Failed to restore item", error);
        } finally {
            setRestoring(null);
        }
    };

    if (loading) {
        return <div className="p-6">Carregando...</div>;
    }

    return (
        <div className="p-6">
            <h1 className="text-2xl font-bold mb-6">Itens Removidos</h1>

            {items.length === 0 ? (
                <p className="text-gray-500">Nenhum item removido encontrado.</p>
            ) : (
                <div className="space-y-4">
                    {items.map((item) => {
                        const isFolder = "is_root" in item;
                        const type = isFolder ? "folder" : "document";
                        const canRestore = user?.role === "admin" || item.deleted_by_id === user?.id;

                        return (
                            <div key={item.id} className="border rounded-lg p-4 bg-gray-50">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <h3 className="font-medium">{item.name}</h3>
                                        <p className="text-sm text-gray-500">
                                            Removido em {new Date(item.deleted_at).toLocaleDateString()} por {item.deleted_by}
                                        </p>
                                        <p className="text-sm text-gray-500">
                                            Tipo: {isFolder ? "Pasta" : "Documento"}
                                        </p>
                                    </div>
                                    {canRestore && (
                                        <Button
                                            size="sm"
                                            onClick={() => setShowConfirm({ item, type })}
                                            disabled={restoring === item.id}
                                        >
                                            {restoring === item.id ? "Restaurando..." : "Restaurar"}
                                        </Button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Confirm Restore Modal */}
            <RecoveryModal
                isOpen={!!showConfirm.item}
                onClose={() => setShowConfirm({ item: null, type: null })}
                onConfirm={() => showConfirm.item && handleRestore(showConfirm.item, showConfirm.type!)}
                itemName={showConfirm.item?.name || ""}
                loading={restoring !== null}
            />
        </div>
    );
}

export default TrashPage;