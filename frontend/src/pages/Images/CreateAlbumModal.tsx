import { useState } from "react";
import { Modal } from "@/components/ui/modal";
import Button from "@/components/ui/button/Button";
import { createAlbum } from "@/api/album.service";
import Input from "@/components/form/input/InputField";
import TextArea from "@/components/form/input/TextArea";
import Label from "@/components/form/Label";

interface CreateAlbumModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (newAlbum: any) => void;
}

export default function CreateAlbumModal({ isOpen, onClose, onSuccess }: CreateAlbumModalProps) {
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      const response = await createAlbum({ title, description });
      onSuccess(response.data);
      setTitle("");
      setDescription("");
      onClose();
    } catch (err: any) {
      setError(err.response?.data?.message || "Erro ao criar álbum");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="max-w-[500px] p-6">
      <h2 className="text-xl font-bold mb-4 dark:text-white">Criar Novo Álbum</h2>
      
      {error && <div className="mb-4 text-red-500 text-sm">{error}</div>}

      <form onSubmit={handleSubmit}>
        <div className="mb-4">
          <Label>Título</Label>
          <Input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
          />
        </div>

        <div className="mb-6">
          <Label>Descrição</Label>
          <TextArea
            rows={3}
            value={description}
            onChange={(value) => setDescription(value)}
          />
        </div>

        <div className="flex justify-end gap-3">
          <Button variant="outline" onClick={onClose} type="button">Cancelar</Button>
          <Button disabled={loading} type="submit">
            {loading ? "A criar..." : "Criar Álbum"}
          </Button>
        </div>
      </form>
    </Modal>
  );
}
