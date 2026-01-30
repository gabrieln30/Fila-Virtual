<?php

$host = 'localhost';
$port = '5432';
$dbname = 'FilaVirtual';
$user = 'postgres';
$password = 'admin';

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

$message = null;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nome === '' || $email === '') {
        $message = ['type'=>'error','text'=>'Preencha nome e email corretamente.'];
    } else {
        
        $dupStmt = $conn->prepare("SELECT id FROM fila WHERE LOWER(nome) = LOWER(:nome) AND LOWER(email) = LOWER(:email) LIMIT 1");
        $dupStmt->bindParam(':nome', $nome);
        $dupStmt->bindParam(':email', $email);
        $dupStmt->execute();
        $exists = $dupStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            
            header("Location: PosicaoFila.php?id=" . urlencode($exists['id']) . "&already=1");
            exit();
        }

        $query = $conn->query("SELECT COALESCE(MAX(posicao), 0) + 1 AS nova_posicao FROM fila");
        $nova_posicao = $query->fetch(PDO::FETCH_ASSOC)['nova_posicao'];

        $stmt = $conn->prepare("INSERT INTO fila (nome, email, posicao) VALUES (:nome, :email, :posicao)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':posicao', $nova_posicao);
        $stmt->execute();

        $novoId = $conn->lastInsertId();

        header("Location: PosicaoFila.php?id=" . urlencode($novoId));
        exit();
    }
}

$status_usuario = null;
$posicao_atual = null;
$nome_usuario = '';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT nome, posicao FROM fila WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nome_usuario = $user['nome'];
        $posicao_atual = $user['posicao'];
        $status_usuario = 'na fila';
    } else {
        $status_usuario = 'chamado';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fila Virtual - Usuário</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#0b1220;
            --card: rgba(255,255,255,0.03);
            --accent:#b30000;
            --muted:#9aa4b2;
            --text:#e6eef8;
        }
        html,body{
            height:100%;
            margin:0;
            background:linear-gradient(180deg,#07101a 0%, #0b1220 100%);
            font-family:'Poppins',sans-serif;
            color:var(--text)
        }
        .wrap{
            max-width:720px;
            margin:48px auto;
            padding:20px;
            width:calc(100% - 40px)
        }
        .card{
            background:var(--card);
            padding:22px;
            border-radius:12px;
            box-shadow:0 8px 30px rgba(0,0,0,.6);
            border:1px solid rgba(255,255,255,0.02)
        }
        h2{
            margin:0 0 12px 0;
            font-size:clamp(1rem, 2.4vw, 1.25rem)}
        form{
            display:flex;
            flex-direction:column;
            gap:12px}
        label{
            font-size:.95rem;
            color:var(--muted);
            display:block;
            margin-bottom:6px}
        input[
            type="text"],input[type="email"]{
                padding:10px 12px;
                border-radius:8px;
                border:1px solid rgba(255,255,255,0.04);
                background:transparent;color:var(--text);
                outline:none;width:100%;
                box-sizing:border-box
            }
        .btn{
            background:var(--accent);
            color:#fff;
            border:0;
            padding:12px;
            border-radius:8px;
            font-weight:600;
            cursor:pointer;
            width:auto
        }
        .message{
            padding:10px;
            border-radius:8px;
            margin-bottom:10px
        }
        .message.success{
            background:rgba(16,185,129,0.12);
            border:1px solid rgba(16,185,129,0.18);
            color:#a7f3d0
        }
        .message.error{
            background:rgba(220,38,38,0.08);
            border:1px solid rgba(220,38,38,0.12);
            color:#ffd6d6
        }
        .links{
            margin-top:12px}
        .links a{
            color:var(--muted);
            text-decoration:none}

        @media(max-width:520px){
            html, body {
                height: 100%;
            }
            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .wrap{
                margin:0 auto;
                padding:12px;
                display:flex;
                flex-direction:column;
                justify-content:center;
                width:100%}
            .card{
                padding:14px
            }
            h2{
                font-size:1.1rem
            }
            .btn{
                width:100%;
                padding:12px
            }
            input[type="text"],input[type="email"]{font-size:1rem}
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Fila Virtual</h2>

            <?php if($message): ?>
                <div class="message <?= $message['type'] === 'success' ? 'success' : 'error' ?>">
                    <?= $message['text'] ?>
                </div>
            <?php endif; ?>

            <?php if ($status_usuario === 'na fila'): ?>
                <p>✅ <?= htmlspecialchars($nome_usuario) ?>, sua posição atual na fila é: <strong><?= $posicao_atual ?></strong></p>
            <?php elseif ($status_usuario === 'chamado'): ?>
                <p>📢 <?= htmlspecialchars($nome_usuario ?: $email) ?>, você foi chamado! Compareça agora.</p>
            <?php endif; ?>

            <form method="post" novalidate>
                <div>
                    <label for="nome">Nome:</label><br>
                    <input id="nome" type="text" name="nome" required value="<?= isset($nome) ? htmlspecialchars($nome) : '' ?>">
                </div>

                <div>
                    <label for="email">Email:</label><br>
                    <input id="email" type="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                </div>

                <button class="btn" type="submit">Entrar na Fila</button>
            </form>
        </div>
    </div>
</body>
</html>
