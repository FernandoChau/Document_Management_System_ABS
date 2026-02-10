import Carousel from "@/components/ui/carousel";

// Interface para os dados de cada álbum organizado por ano
interface YearAlbum {
  year: number;
  albumName: string;
  images: Array<{
    id: string;
    title: string;
    button: string;
    src: string;
  }>;
}

export default function CarouselDemo() {
  // Álbuns organizados por ano
  const yearAlbums: YearAlbum[] = [
    {
      year: 2024,
      albumName: "Memories of 2024",
      images: [
        {
          id: "mystic-mountains",
          title: "Mystic Mountains",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1494806812796-244fe51b774d?q=80&w=3534&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
        },
        {
          id: "urban-dreams",
          title: "Urban Dreams",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1518710843675-2540dd79065c?q=80&w=3387&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
        },
        {
          id: "neon-nights",
          title: "Neon Nights",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1590041794748-2d8eb73a571c?q=80&w=3456&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
        },
        {
          id: "desert-whispers",
          title: "Desert Whispers",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1679420437432-80cfbf88986c?q=80&w=3540&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
        },
      ],
    },
    {
      year: 2023,
      albumName: "Adventures of 2023",
      images: [
        {
          id: "ocean-breeze",
          title: "Ocean Breeze",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1505142468610-359e7d316be0?q=80&w=3526&auto=format&fit=crop",
        },
        {
          id: "forest-path",
          title: "Forest Path",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=3571&auto=format&fit=crop",
        },
        {
          id: "city-lights",
          title: "City Lights",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?q=80&w=3540&auto=format&fit=crop",
        },
      ],
    },
    {
      year: 2022,
      albumName: "Highlights of 2022",
      images: [
        {
          id: "sunset-valley",
          title: "Sunset Valley",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=3540&auto=format&fit=crop",
        },
        {
          id: "winter-wonderland",
          title: "Winter Wonderland",
          button: "Explore Image",
          src: "https://images.unsplash.com/photo-1483086431886-3590a88317fe?q=80&w=3534&auto=format&fit=crop",
        },
      ],
    },
  ];

  return (
    <section className="space-y-16 mb-10">
      {yearAlbums.map((album) => (
        <div key={album.year}>
          {/* Year Header */}
          <div className="w-full flex flex-col items-center justify-center space-y-2">
            <div className="flex items-center gap-4 w-full max-w-md">
              <div className="flex-1 h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-700 to-transparent" />
              <h2 className="text-3xl md:text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-tr from-green-300 to-brand-500">
                {album.year}
              </h2>
              <div className="flex-1 h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-700 to-transparent" />
            </div>
            <p className="text-gray-500 dark:text-gray-400 text-sm md:text-base">
              {album.albumName} • {album.images.length} {album.images.length === 1 ? "photo" : "photos"}
            </p>
          </div>

          {/* Carousel */}
          <div className="relative overflow-hidden w-full h-full py-15">
            <Carousel slides={album.images} />
          </div>
        </div>
      ))}
    </section>
  );
}
