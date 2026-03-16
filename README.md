# Meu App - Laravel Project

Este é um projeto desenvolvido com o framework [Laravel](https://laravel.com).

## Pré-requisitos

Antes de começar, verifique se você tem as seguintes ferramentas instaladas em sua máquina:

- [PHP](https://www.php.net/downloads.php) (Versão >= 8.2 recomendada)
- [Composer](https://getcomposer.org/)
- [Node.js & NPM](https://nodejs.org/)
- Um banco de dados (SQLite, MySQL, PostgreSQL, etc.)

---

## Como Iniciar o Projeto Localmente

Siga os passos abaixo para configurar o ambiente de desenvolvimento:

### 1. Clonar o Repositório

```bash
git clone <url-do-repositorio>
cd meu-app
```

### 2. Instalar Dependências PHP

```bash
composer install
```

### 3. Instalar Dependências JavaScript

```bash
npm install
```

### 4. Configurar o Ambiente

Copie o arquivo de exemplo de ambiente e gere a chave da aplicação:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurar o Banco de Dados

No arquivo `.env`, configure os dados de acesso ao seu banco de dados. Caso deseje utilizar **SQLite** (padrão em novos projetos Laravel):

1. Crie o arquivo do banco de dados:

   ```bash
   touch database/database.sqlite
   ```

2. No seu `.env`, certifique-se de que a conexão está como:
   `DB_CONNECTION=sqlite`

3. Execute as migrações para criar as tabelas:

   ```bash
   php artisan migrate
   ```

### 6. Executar a Aplicação

Você precisará rodar dois comandos (em terminais separados ou em background):

- **Servidor de Desenvolvimento (PHP):**

  ```bash
  php artisan serve
  ```

  Acesse em: [http://127.0.0.1:8000](http://127.0.0.1:8000)

- **Compilação de Assets (Vite):**

  ```bash
  npm run dev
  ```

---

## Dicas Adicionais

### Laravel Herd (macOS)

Como este projeto está dentro do diretório `Herd`, se você tiver o [Laravel Herd](https://herd.laravel.com) instalado, basta acessar:
`http://meu-app.test` no seu navegador.

### Comandos Úteis

- `php artisan tinker`: Console interativo para testar o código PHP.

- `php artisan route:list`: Lista todas as rotas registradas.

- `php artisan make:controller NomeController`: Cria um novo controlador.
