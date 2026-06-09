# Motorista Check - Transporte Escolar

Sistema de controle seguro de transporte escolar com autenticação por QR Code, autorização de retirada de alunos e acompanhamento de viagens em tempo real.

## Funcionalidades

- **QR Code Dinâmico** — Crachá digital do motorista com QR Code que expira em 5 minutos
- **Autorização de Retirada** — Inspetor escolar escaneia o QR Code e autoriza/recusa a retirada do aluno
- **Acompanhamento de Viagem** — Pais acompanham a localização do veículo em tempo real
- **Geolocalização** — Rastreamento via OpenStreetMap
- **Múltiplos Perfis** — Admin, Operador (transportadora), Motorista, Inspetor (escola), Responsável (pai)
- **Auditoria** — Log de todas as ações do sistema
- **Rate Limiting** — Proteção contra abuso nas requisições

## Stack

| Tecnologia | Versão |
|------------|--------|
| PHP | >= 8.0 |
| MySQL | 8.0 |
| Apache | 2.4 |
| Docker | 24+ |
| Composer | 2+ |

### Dependências (Composer)

- `chillerlan/php-qrcode` — Geração de QR Codes
- `phpmailer/phpmailer` — Envio de e-mails
- `vlucas/phpdotenv` — Configuração via .env

## Instalação

### Docker (recomendado)

```bash
git clone https://github.com/criffel/transporte-escolar.git
cd transporte-escolar

cp .env.example .env
docker compose up -d --build
docker exec motorista_check_app composer install
```

Acessar:
- **App:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

### Manual (PHP + MySQL)

```bash
cp .env.example .env
composer install
```

Configurar `DB_HOST`, `DB_USER`, `DB_PASS` no `.env` e importar o schema:

```bash
mysql -u root -p motorista_check < database/schema.sql
```

Iniciar servidor embutido:

```bash
php -S localhost:8080
```

## Configuração

Variáveis de ambiente (`.env`):

| Variável | Padrão | Descrição |
|----------|--------|-----------|
| `APP_NAME` | Motorista Check | Nome do sistema |
| `APP_ENV` | development | Ambiente (development/production) |
| `APP_DEBUG` | true | Exibir erros |
| `APP_URL` | http://localhost:8080 | URL base |
| `DB_HOST` | db | Host do MySQL |
| `DB_PORT` | 3306 | Porta do MySQL |
| `DB_NAME` | motorista_check | Nome do banco |
| `DB_USER` | motorista_user | Usuário do banco |
| `DB_PASS` | — | Senha do banco |
| `SESSION_LIFETIME` | 120 | Minutos de sessão |
| `QR_CODE_EXPIRY_MINUTES` | 5 | Expiração do QR Code |
| `SMTP_HOST` | — | Servidor SMTP |

## Login Padrão

| Perfil | Usuário | Senha |
|--------|---------|-------|
| Administrador | `admin` | `@Admin123` |

## Estrutura do Projeto

```
motorista-check/
├── api/                    # Endpoints REST
│   ├── autorizar-retirada.php
│   ├── finalizar-viagem.php
│   ├── localizacao.php
│   ├── localizacao-pai.php
│   └── validar-qrcode.php
├── config/                 # Configurações
│   ├── app.php
│   ├── database.php
│   └── session.php
├── database/
│   └── schema.sql          # Schema MySQL + dados iniciais
├── docker/
│   └── Dockerfile          # Imagem PHP 8.2 + Apache
├── public/                 # Front-end
│   ├── admin/              # Painel administrativo
│   ├── assets/             # CSS, JS, imagens
│   ├── inspetor/           # Escaneamento QR Code
│   ├── motorista/          # Crachá digital
│   └── pai/                # Acompanhamento de viagem
├── src/
│   ├── core/               # Database, Auth, Router, Validator
│   ├── helpers/            # Security, QRCode, Formatador
│   ├── middleware/          # AuthMiddleware, RateLimitMiddleware
│   └── models/             # 11 modelos (Aluno, Motorista, etc.)
├── views/
│   ├── layouts/            # Layouts HTML
│   └── errors/             # Páginas de erro
├── docker-compose.yml
├── index.php               # Front controller
└── composer.json
```

## Banco de Dados

15 tabelas:

- `perfis` — Perfis de acesso (admin, escola, motorista, responsavel, operador)
- `usuarios` — Usuários do sistema
- `transportadoras` — Empresas de transporte
- `motoristas` — Motoristas vinculados a usuários
- `veiculos` — Veículos dos motoristas
- `escolas` — Escolas cadastradas
- `alunos` — Alunos matriculados
- `responsaveis` — Pais/responsáveis
- `responsavel_aluno` — Relação responsável-aluno
- `autorizacoes_motorista_aluno` — Autorização motorista-aluno
- `qrcode_tokens` — Tokens de QR Code
- `retiradas` — Registro de retirada de alunos
- `viagens` — Viagens em andamento
- `localizacoes_viagem` — Pontos de geolocalização
- `logs_auditoria` — Audit logging

## API

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `/api/validar-qrcode.php` | POST | Validar token do QR Code |
| `/api/autorizar-retirada.php` | POST | Autorizar/recusar retirada |
| `/api/finalizar-viagem.php` | POST | Finalizar viagem |
| `/api/localizacao.php` | POST | Enviar localização (motorista) |
| `/api/localizacao-pai.php` | GET | Consultar localização (pai) |

## Licença

Proprietário — Uso interno.
