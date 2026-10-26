# Document Management System — frontend

React 19 and TypeScript application for the document management API. The UI includes user authentication, folders and documents, preview, reporting, image albums, and public share pages.

## Requirements

- Node.js 20 or newer.
- The Laravel backend available at `http://localhost:8000`, unless a different API URL is configured.

## Local development

From this directory:

```powershell
npm ci
$env:VITE_API_BASE_URL = "http://localhost:8000/api"
npm run dev
```

The backend must allow the Vite origin in `FRONTEND_ALLOWED_ORIGINS` (default: `http://localhost:5173`). Authentication is sent using an HttpOnly cookie; do not add API tokens to browser storage.

## Checks and production build

```powershell
npm run lint
npm run build
npm run preview
```

`npm run build` runs the TypeScript project build before creating optimized Vite assets. Public share passwords are sent in POST request bodies, not query parameters.

ESLint currently reports pre-existing issues across the frontend, so CI gates the TypeScript/build check rather than the full lint command until that baseline is resolved.
