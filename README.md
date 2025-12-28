# 📌 Projeto Laravel 12

Este projeto foi desenvolvido em **Laravel 12** com integração de **TailwindCSS v4** e suporte a armazenamento em **S3 (Wasabi/AWS)** via `league/flysystem-aws-s3-v3`.

O objetivo deste README é instruir como configurar o ambiente, instalar as dependências e rodar o projeto localmente.

---

## 🚀 Tecnologias Utilizadas

- [Laravel 12](https://laravel.com/)
- [Composer 2.8.10](https://getcomposer.org/)
- [Node.js v22.17.1](https://nodejs.org/)
- [npm 10.9.2](https://www.npmjs.com/)
- [PHP 8.3.24](https://www.php.net/)
- [XAMPP v3.3.0](https://www.apachefriends.org/) (com suporte a `zip`)
- [MySQL](https://www.mysql.com/)
- [TailwindCSS v4](https://tailwindcss.com/)
- [league/flysystem-aws-s3-v3 ^3.0](https://github.com/thephpleague/flysystem-aws-s3-v3)

---

## 📥 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/FernandoChau/Document_Management_System_ABS.git
cd seu-projeto
```

### 2. Instale as dependências do PHP
```bash
composer install
```

### 3. Instale as dependências do Node.js
```bash
npm install
```

### 4. Copie o arquivo `.env.example` para `.env`
```bash
cp .env.example .env
```

Edite as variáveis de ambiente de acordo com o seu setup (banco de dados, email, storage, etc).

---

## ⚙️ Configuração do Ambiente

### Banco de Dados (MySQL)
No arquivo `.env`, configure:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Rodar migrações + seed:
```bash
php artisan migrate:fresh --seed
```
para criar a base de dados incluíndo as tabelas e os dados.

### TailwindCSS
Rodar o build do Tailwind:
```bash
npm run dev
```
---

## ☁️ Wasabi (S3 kompatível) — Obter chaves e configurar

### 1) Criar conta e bucket no Wasabi
1. Crie uma conta em https://wasabi.com/ e aceda ao **Management Console**.
2. No console, vá em **Buckets** → **Create Bucket** e crie um bucket novo com um nome DNS-compliant e escolha a região desejada.

### 2) Gerar Access Key e Secret Key
1. No console do Wasabi, vá em **Users** ou **Access Keys**.
2. Clique em **Create User** (se quiseres um usuário específico) e depois em **Create Access Key**, ou diretamente em **Create New Access Key**.  
3. Copia o **Access Key** e o **Secret Key** e guarda-os num local seguro.

### 3) Endpoints / Service URLs
- Para a região **US East (padrão)** o endpoint base é `s3.wasabisys.com`.  
- Para outras regiões usa: `s3.<region>.wasabisys.com` (e.g. `s3.eu-central-1.wasabisys.com`).  

---

## 🔐 Configurar Laravel (.env)

Edite o ficheiro `.env` e adicione:

### Configuração de Storage (Wasabi)
```env
FILESYSTEM_DRIVER=wasabi
AWS_ACCESS_KEY_ID=seu_access_key
AWS_SECRET_ACCESS_KEY=seu_secret_key
AWS_BUCKET=nome-do-bucket
AWS_DEFAULT_REGION=us-east-1              
WASABI_ENDPOINT=https://s3.us-east-1.wasabisys.com
WASABI_URL=https://s3.us-east-1.wasabisys.com/nome-do-bucket
AWS_URL=https://s3.wasabisys.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Configuração de Email
```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com                    // Seu servidor de email
MAIL_PORT=587                               // Porta
MAIL_USERNAME=seu_emaul@gmail.com           // UserName
MAIL_PASSWORD=sua_senha_de_aplicativo       // Password
MAIL_FROM_ADDRESS="seu_email@gmail.com"     // Email de envio
MAIL_FROM_NAME="${APP_NAME}"
```

> ⚠️ **Importante:** se for usar Gmail, ative a autenticação em 2 passos e crie uma **App Password** para usar como `MAIL_PASSWORD`.

---

---

## ▶️ Rodando o Servidor

### Servidor Laravel
```bash
php artisan serve
```

A aplicação estará disponível em:  
👉 [http://localhost:8000](http://localhost:8000)

### Servidor XAMPP (Apache + MySQL)
- Inicie **Apache** e **MySQL** no painel do XAMPP.
- Certifique-se de que o PHP e MySQL estão ativos.

---

## 📦 Scripts Úteis

- Limpar cache:
```bash
php artisan cache:clear
php artisan config:clear
```

- Rodar migrações + seed:
```bash
php artisan migrate:fresh --seed
```

- Compilar assets (Tailwind + JS):
```bash
npm run build
```

