import { useParams } from "react-router";

export default function AlbumDetail() {
  const { id } = useParams<{ id: string }>();

  return (
    <div className="min-h-screen bg-zinc-950 text-white p-8">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-4xl font-bold mb-8">Album {id}</h1>
        <p className="text-zinc-400">This is a blank page for album details. Images will be displayed here.</p>
      </div>
    </div>
  );
}
