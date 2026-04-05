import { useState } from "react";
import Modal from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";

interface RecoveryModalProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    itemName: string;
    loading: boolean;
}

function RecoveryModal({ isOpen, onClose, onConfirm, itemName, loading }: RecoveryModalProps) {
    return (
        <Modal isOpen={isOpen} onClose={onClose} className="max-w-md">
            <div className="p-6">
                <h3 className="text-lg font-semibold mb-4">Confirmar Restauração</h3>
                <p className="mb-6">
                    Deseja restaurar "{itemName}"?
                </p>
                <div className="flex gap-3 justify-end">
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={onClose}
                        disabled={loading}
                    >
                        Cancelar
                    </Button>
                    <Button
                        size="sm"
                        onClick={onConfirm}
                        disabled={loading}
                    >
                        {loading ? "Restaurando..." : "Restaurar"}
                    </Button>
                </div>
            </div>
        </Modal>
    );
}

export default RecoveryModal;