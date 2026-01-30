# 🎮 Fila Virtual – Sala de Jogos

Sistema web para gerenciamento de fila de usuários, permitindo que cada pessoa acompanhe sua posição em tempo real e que o administrador controle as chamadas.

---

## 📋 Requisitos

- PHP 7.4 ou superior  
- PostgreSQL  
- Servidor web (Apache, Nginx, XAMPP ou WAMP)  
- pgAdmin ou outro Query Tool do PostgreSQL  
- Navegador web atualizado  

---
```
 🗂️ Estrutura do Projeto

FilaVirtual/
├── index.php # Tela inicial / entrada do usuário
├── PosicaoFila.php # Tela que mostra a posição na fila
├── TelaAdm.php # Painel do administrador
└── mandarEmail.php # Envio de e-mails (opcional)

```
---
```sql
 🛠️ Configuração do Banco de Dados

CREATE DATABASE "FilaVirtual";

CREATE TABLE fila (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    posicao INTEGER NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
---
```sql
 ⚠️ Ajuste usuário e senha conforme sua configuração do PostgreSQL.

$host = 'localhost';
$port = '5432';
$dbname = 'FilaVirtual';
$user = 'postgres';
$password = 'admin';

```
## ▶️ (Usuário)

1. Acesse o sistema pelo navegador:
http://localhost/FilaVirtual/index.php


2. Preencha os seguintes campos:
- **Nome**
- **E-mail**

3. Após o envio, o sistema:
- Adiciona o usuário à fila
- Define automaticamente sua posição

4. O usuário será redirecionado para a página:
PosicaoFila.php


5. Nesta tela, é possível visualizar:
- 📍 **Posição atual na fila**
- 👥 **Quantidade de pessoas à frente**
- 📊 **Total de pessoas na fila**
- ⏱️ **Estimativa de tempo de espera**

6. A página é **atualizada automaticamente a cada 10 segundos**, garantindo informações em tempo real.

---

## 🧑‍💼 (Administrador)

1. Acesse o painel administrativo:
http://localhost/FilaVirtual/TelaAdm.php


2. O administrador pode:
- 📋 Visualizar todos os usuários na fila
- 📢 Chamar usuários para atendimento
- ❌ Remover usuários já atendidos
- 🔄 Controlar a ordem da fila

3. Quando um usuário é chamado, ao tentar consultar novamente sua posição, será exibida a mensagem:
> **“Você já foi chamado”**



