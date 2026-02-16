import { useParams, useNavigate } from "react-router";
import { ArrowLeft, Download, Share2, Heart } from "lucide-react";
import { useState } from "react";

// Interface para os dados da imagem
interface ImageData {
  id: string;
  title: string;
  src: string;
  description: string;
  category: string;
  author: string;
  date: string;
  tags: string[];
}

// Dados das imagens (mesmos do carousel + informações extras)
const imagesData: Record<string, ImageData> = {
  "mystic-mountains": {
    id: "mystic-mountains",
    title: "Mystic Mountains",
    src: "https://images.unsplash.com/photo-1494806812796-244fe51b774d?q=80&w=3534&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
    description: "Experience the breathtaking beauty of misty mountain ranges at dawn. This stunning landscape captures the ethereal quality of nature's majesty, where clouds dance between peaks and the first light of day paints the sky in soft pastels.",
    category: "Nature",
    author: "Mountain Explorer",
    date: "2024-01-15",
    tags: ["Mountains", "Nature", "Landscape", "Scenic", "Dawn"],
  },
  "urban-dreams": {
    id: "urban-dreams",
    title: "Urban Dreams",
    src: "https://images.unsplash.com/photo-1518710843675-2540dd79065c?q=80&w=3387&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
    description: "Dive into the vibrant energy of urban life captured in a single frame. This image showcases the dynamic intersection of architecture, light, and human ambition in the modern cityscape.",
    category: "Urban",
    author: "City Photographer",
    date: "2024-02-20",
    tags: ["City", "Architecture", "Urban", "Modern", "Life"],
  },
  "neon-nights": {
    id: "neon-nights",
    title: "Neon Nights",
    src: "https://images.unsplash.com/photo-1590041794748-2d8eb73a571c?q=80&w=3456&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
    description: "When darkness falls, the city comes alive with electric energy. Neon lights paint the night in vivid colors, creating an atmosphere that's both futuristic and nostalgic.",
    category: "Night",
    author: "Night Vision",
    date: "2024-03-10",
    tags: ["Neon", "Night", "City", "Lights", "Urban"],
  },
  "desert-whispers": {
    id: "desert-whispers",
    title: "Desert Whispers",
    src: "https://images.unsplash.com/photo-1679420437432-80cfbf88986c?q=80&w=3540&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
    description: "The desert speaks in whispers of sand and wind. This captivating image reveals the stark beauty and endless horizons of arid landscapes where time seems to stand still.",
    category: "Desert",
    author: "Desert Wanderer",
    date: "2024-04-05",
    tags: ["Desert", "Sand", "Nature", "Landscape", "Minimal"],
  },
  "ocean-breeze": {
    id: "ocean-breeze",
    title: "Ocean Breeze",
    src: "https://images.unsplash.com/photo-1505142468610-359e7d316be0?q=80&w=3526&auto=format&fit=crop",
    description: "Feel the refreshing ocean breeze in this stunning coastal capture. The rhythmic dance of waves and the endless horizon create a sense of peace and freedom that only the sea can provide.",
    category: "Ocean",
    author: "Coastal Explorer",
    date: "2023-06-15",
    tags: ["Ocean", "Sea", "Beach", "Waves", "Nature"],
  },
  "forest-path": {
    id: "forest-path",
    title: "Forest Path",
    src: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=3571&auto=format&fit=crop",
    description: "A winding path through an ancient forest invites you to explore nature's cathedral. Sunlight filters through the canopy, creating a magical atmosphere of tranquility and wonder.",
    category: "Forest",
    author: "Nature Wanderer",
    date: "2023-08-22",
    tags: ["Forest", "Trees", "Path", "Nature", "Green"],
  },
  "city-lights": {
    id: "city-lights",
    title: "City Lights",
    src: "https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?q=80&w=3540&auto=format&fit=crop",
    description: "The metropolitan pulse illuminated against the twilight sky. This urban panorama captures the energy and ambition of city life as day transitions into night.",
    category: "Cityscape",
    author: "Urban Photographer",
    date: "2023-11-10",
    tags: ["City", "Skyline", "Lights", "Urban", "Night"],
  },
  "sunset-valley": {
    id: "sunset-valley",
    title: "Sunset Valley",
    src: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=3540&auto=format&fit=crop",
    description: "Golden hour paints the valley in warm hues of amber and gold. This breathtaking vista showcases nature's artistry as the sun bids farewell to another day.",
    category: "Landscape",
    author: "Sunset Chaser",
    date: "2022-09-18",
    tags: ["Sunset", "Valley", "Mountains", "Golden Hour", "Nature"],
  },
  "winter-wonderland": {
    id: "winter-wonderland",
    title: "Winter Wonderland",
    src: "https://images.unsplash.com/photo-1483086431886-3590a88317fe?q=80&w=3534&auto=format&fit=crop",
    description: "A pristine blanket of snow transforms the landscape into a winter paradise. The crisp, quiet beauty of this frozen moment captures the serene magic of the coldest season.",
    category: "Winter",
    author: "Winter Explorer",
    date: "2022-12-25",
    tags: ["Winter", "Snow", "Cold", "Landscape", "Nature"],
  },
};

export default function ImageDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [liked, setLiked] = useState(false);

  // Buscar os dados da imagem pelo ID
  const image = id ? imagesData[id] : null;

  // Se não encontrar a imagem, mostrar mensagem de erro
  if (!image) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-4xl font-bold text-white mb-4">Image Not Found</h1>
          <button
            onClick={() => navigate("/images")}
            className="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
          >
            Back to Gallery
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <div className="container mx-auto px-4 py-8">
        {/* Header com botão de voltar */}
        <div className="mb-8">
          <button
            onClick={() => navigate("/images")}
            className="flex items-center gap-2 text-white hover:text-purple-400 transition group"
          >
            <ArrowLeft className="w-5 h-5 group-hover:-translate-x-1 transition" />
            <span className="text-lg font-medium">Back to Gallery</span>
          </button>
        </div>

        {/* Grid Layout */}
        <div className="grid lg:grid-cols-2 gap-8">
          {/* Imagem Principal */}
          <div className="relative rounded-2xl overflow-hidden shadow-2xl group">
            <img
              src={image.src}
              alt={image.title}
              className="w-full h-[600px] object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          </div>

          {/* Informações da Imagem */}
          <div className="text-white space-y-6">
            {/* Categoria */}
            <div>
              <span className="inline-block px-4 py-2 bg-purple-600/30 border border-purple-500/50 rounded-full text-sm font-medium">
                {image.category}
              </span>
            </div>

            {/* Título */}
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-600">
              {image.title}
            </h1>

            {/* Descrição */}
            <p className="text-gray-300 text-lg leading-relaxed">
              {image.description}
            </p>

            {/* Metadata */}
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <span className="text-gray-400">Author:</span>
                <span className="text-white font-medium">{image.author}</span>
              </div>
              <div className="flex items-center gap-3">
                <span className="text-gray-400">Date:</span>
                <span className="text-white font-medium">
                  {new Date(image.date).toLocaleDateString("en-US", {
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                  })}
                </span>
              </div>
            </div>

            {/* Tags */}
            <div className="space-y-3">
              <h3 className="text-gray-400 font-medium">Tags:</h3>
              <div className="flex flex-wrap gap-2">
                {image.tags.map((tag, index) => (
                  <span
                    key={index}
                    className="px-3 py-1 bg-slate-800/50 border border-slate-700 rounded-lg text-sm text-gray-300 hover:bg-slate-700/50 transition cursor-pointer"
                  >
                    #{tag}
                  </span>
                ))}
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex flex-wrap gap-4 pt-6">
              <button
                onClick={() => setLiked(!liked)}
                className={`flex items-center gap-2 px-6 py-3 rounded-lg transition ${
                  liked
                    ? "bg-red-600 text-white"
                    : "bg-slate-800/50 border border-slate-700 text-white hover:bg-slate-700/50"
                }`}
              >
                <Heart
                  className={`w-5 h-5 ${liked ? "fill-current" : ""}`}
                />
                <span>{liked ? "Liked" : "Like"}</span>
              </button>

              <button className="flex items-center gap-2 px-6 py-3 bg-slate-800/50 border border-slate-700 text-white rounded-lg hover:bg-slate-700/50 transition">
                <Download className="w-5 h-5" />
                <span>Download</span>
              </button>

              <button className="flex items-center gap-2 px-6 py-3 bg-slate-800/50 border border-slate-700 text-white rounded-lg hover:bg-slate-700/50 transition">
                <Share2 className="w-5 h-5" />
                <span>Share</span>
              </button>
            </div>
          </div>
        </div>

        {/* Seção de Imagens Relacionadas (opcional) */}
        <div className="mt-16">
          <h2 className="text-3xl font-bold text-white mb-8">
            Related Images
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {Object.values(imagesData)
              .filter((img) => img.id !== image.id)
              .slice(0, 3)
              .map((relatedImage) => (
                <div
                  key={relatedImage.id}
                  className="relative rounded-xl overflow-hidden shadow-lg group cursor-pointer"
                  onClick={() => navigate(`/image/${relatedImage.id}`)}
                >
                  <img
                    src={relatedImage.src}
                    alt={relatedImage.title}
                    className="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex items-end p-6">
                    <div>
                      <h3 className="text-white text-xl font-bold mb-1">
                        {relatedImage.title}
                      </h3>
                      <p className="text-gray-300 text-sm">
                        {relatedImage.category}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
          </div>
        </div>
      </div>
    </div>
  );
}
