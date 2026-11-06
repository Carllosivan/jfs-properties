# JFS PROPERTIES
## Sistema Integrado de Gestão Imobiliária, Incidentes e Manutenção

---

### 1. Visão Geral e Arquitectura do Sistema

O **JFS Properties** é uma plataforma corporativa desenvolvida para centralizar a gestão de propriedades comerciais e residenciais, acompanhamento de incidentes, manutenção planeada preventiva e correctiva, execução de ordens de serviço no terreno, controlo de activos, gestão de inventário de peças e controlo de despesas operacionais.

#### Stack Tecnológica:
- **Backend:** PHP 8.2+ Nativo com Arquitectura MVC (Model-View-Controller) pura, sem dependência obrigatória de runtimes Node.js em produção.
- **Base de Dados:** SQLite Relacional com chaves estrangeiras (`PRAGMA foreign_keys = ON;`), transacções ACID e modo WAL.
- **Frontend:** HTML5, Tailwind CSS, JavaScript ES6+, Lucide Icons, Chart.js.
- **Servidor Web:** Compatível com Apache (mod_rewrite), cPanel, XAMPP, Nginx ou o servidor embutido do PHP (`php -S`).

---

### 2. Estrutura de Directórios

```text
jfs-properties/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          # Dashboard, Imóveis, Incidentes, Ordens, Manutenção, etc.
│   │   ├── Auth/           # Autenticação, recuperação de senhas
│   │   ├── Frontend/       # Portal exclusivo do ocupante
│   │   └── Api/            # Endpoints AJAX (unidades dinâmicas, notificações)
│   ├── Core/               # Router, Database PDO, Request, Response, Session, Auth, View, Logger
│   ├── Middleware/         # AuthMiddleware, AdminMiddleware, CsrfMiddleware
│   ├── Services/           # StockService (transacional), SlaService, MaintenanceService (idempotente)
│   └── Views/              # Layouts (app, portal, auth, print) e vistas dos módulos
├── config/                 # app.php, database.php, permissions.php
├── database/
│   ├── schema.sql          # Estrutura DDL com tabelas, índices e restrições
│   ├── Migrator.php        # Executor de migrações
│   ├── Seeder.php          # Semeador de dados iniciais e de demonstração
│   └── database.sqlite     # Ficheiro de base de dados SQLite
├── public/                 # Document Root público
│   ├── .htaccess           # Reescreve rotas para index.php e protege ficheiros
│   └── index.php           # Front Controller da aplicação
├── routes/                 # web.php, admin.php, api.php
├── storage/                # logs, uploads, relatórios, backups
├── tests/                  # Testes unitários de regras de negócio
├── .htaccess               # Protecção de raiz para alojamento partilhado cPanel
└── composer.json           # Definição do projecto e scripts
```

---

### 3. Credenciais Iniciais Pré-configuradas

Para efeitos de validação imediata, a base de dados vem inicializada com os seguintes perfis:

| Perfil | E-mail | Palavra-passe | Finalidade |
| :--- | :--- | :--- | :--- |
| **Super Administrador** | `admin@jfsproperties.co.mz` | `Admin123!` | Gestão global, imóveis, auditoria e definições |
| **Técnico de Manutenção** | `carlos.tecnico@jfsproperties.co.mz` | `Admin123!` | Execução de ordens de serviço no terreno |
| **Ocupante / Residente** | `ana.cossa@gmail.com` | `Admin123!` | Acesso ao Portal do Ocupante e reporte de avarias |

---

### 4. Instalação e Execução Local (XAMPP / PHP CLI)

1. **Requisitos:**
   - PHP 8.2 ou superior com extensões `pdo_sqlite`, `sqlite3`, `mbstring`, `curl`.

2. **Inicializar a Base de Dados (Migração e Seeds):**
   ```bash
   php database/run.php
   ```

3. **Iniciar o Servidor de Desenvolvimento:**
   ```bash
   php -S 0.0.0.0:8000 -t public public/index.php
   ```
   Aceda a `http://localhost:8000` no navegador.

---

### 5. Deployment em cPanel / Alojamento Partilhado

1. **Com Document Root personalizável:**
   - Aponte o Document Root do domínio para a pasta `public/`.
   - O ficheiro `database/database.sqlite` e a directoria `app/` ficarão automaticamente protegidos fora da área pública.

2. **Sem Document Root personalizável (Root público):**
   - O ficheiro `.htaccess` na raiz já contém as directivas de reescrita automática para `public/` e bloqueio estrito de acesso directo a ficheiros `.env`, `.sqlite` e directorias internas.

3. **Permissões de Escrita:**
   - Garanta permissões de escrita (chmod 775 ou 755) nas pastas:
     - `database/` (e no ficheiro `database/database.sqlite`)
     - `storage/logs/`
     - `storage/uploads/`
     - `public/uploads/`

4. **Tarefas Agendadas (Cron Job do cPanel):**
   Para gerar tarefas preventivas e verificar SLAs automaticamente, adicione um cron job diário no cPanel:
   ```bash
   0 6 * * * php /home/seu_usuario/public_html/database/run.php
   ```

---

### 6. Fluxos de Negócio Implementados

1. **Gestão Imobiliária Hierárquica:**
   Organização → Imóvel → Edifício/Bloco → Piso → Fracção/Unidade → Activo.
2. **Ciclo de Vida do Incidente:**
   Registo → Triagem & SLA → Atribuição → Criação de Ordem de Serviço → Resolução → Validação → Encerramento.
3. **Execução de Ordem de Serviço:**
   Diagnóstico técnico → Registo de horas de mão-de-obra → Consumo de peças do armazém (com validação e dedução transacional em stock) → Emissão de relatório de serviço imprimível com assinaturas.
4. **Manutenção Preventiva:**
   Geração idempotente de tarefas periódicas baseadas em planos cíclicos (diários, semanais, mensais, trimestrais).
5. **Inspecções e Checklists:**
   Avaliação de conformidade (Conforme, Não Conforme, Requer Atenção). Detecções críticas geram automaticamente incidentes no sistema.
6. **Portal do Ocupante:**
   Interface restrita onde o arrendatário reporta ocorrências, envia mensagens aos técnicos e valida a resolução.
