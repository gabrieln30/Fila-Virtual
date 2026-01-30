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


function atualizarPosicoes($conn) {
    $result = $conn->query("SELECT id FROM fila ORDER BY posicao ASC");
    $posicao = 1;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $conn->prepare("UPDATE fila SET posicao = :posicao WHERE id = :id");
        $stmt->bindParam(':posicao', $posicao);
        $stmt->bindParam(':id', $row['id']);
        $stmt->execute();
        $posicao++;
    }
}


if (isset($_POST['chamar'])) {
    $stmt = $conn->query("SELECT * FROM fila ORDER BY posicao ASC LIMIT 1");
    $primeiro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($primeiro) {
        $message = "📢 Chamando: {$primeiro['nome']} ({$primeiro['email']})";

        $stmtDelete = $conn->prepare("DELETE FROM fila WHERE id = :id");
        $stmtDelete->bindParam(':id', $primeiro['id']);
        $stmtDelete->execute();

        atualizarPosicoes($conn);

        
        require_once __DIR__ . '/mandarEmail.php';
        $emailRes = sendCallEmail($primeiro['email'], $primeiro['nome']);
        if ($emailRes['success']) {
            $message .= " — ✅ E-mail enviado para {$primeiro['email']}";
        } else {
            $message .= " — ⚠️ Falha ao enviar e-mail: " . $emailRes['message'];
        }

        header("Location: TelaAdm.php?message=" . urlencode($message));
        exit();
    } else {
        header("Location: TelaAdm.php?message=" . urlencode("Ninguém na fila no momento."));
        exit();
    }
}

if (isset($_POST['remover_id'])) {
    $removerId = (int)$_POST['remover_id'];
    $stmtRemover = $conn->prepare("SELECT * FROM fila WHERE id = :id LIMIT 1");
    $stmtRemover->bindParam(':id', $removerId);
    $stmtRemover->execute();
    $removido = $stmtRemover->fetch(PDO::FETCH_ASSOC);
    if ($removido) {
        $stmtDelete = $conn->prepare("DELETE FROM fila WHERE id = :id");
        $stmtDelete->bindParam(':id', $removerId);
        $stmtDelete->execute();
        atualizarPosicoes($conn);
        $message = "Usuário removido da fila: " . htmlspecialchars($removido['nome']) . " (" . htmlspecialchars($removido['email']) . ")";
        header("Location: TelaAdm.php?message=" . urlencode($message));
        exit();
    } else {
        $message = "Usuário não encontrado para remoção.";
        header("Location: TelaAdm.php?message=" . urlencode($message));
        exit();
    }
}

$message = isset($_GET['message']) ? $_GET['message'] : null;

$stmtFila = $conn->query("SELECT * FROM fila ORDER BY posicao ASC");
$fila = $stmtFila->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fila Virtual - Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg: #0f1724;
            --card: rgba(255,255,255,0.03);
            --accent: #b30000;
            --muted: #9aa4b2;
            --text: #e6eef8;
        }
        html,body{
            height:100%;
            margin:0;
            background:linear-gradient(180deg, rgba(6,10,19,1) 0%, rgba(15,23,36,1) 100%);
            font-family:'Poppins',sans-serif;
            color:var(--text);
        }
        .wrap{
            max-width:1100px;
            margin:36px auto;
            padding:20px;
            width:calc(100% - 40px)
        }
        header.app-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;margin-bottom:18px
        }
        
        header h1{
            margin:0;
            font-size:clamp(1rem,2.2vw,1.25rem);
            font-weight:600
        }
        .card{
            background:var(--card);
            padding:18px;
            border-radius:10px;
            box-shadow:0 8px 30px rgba(0,0,0,.6);
            border:1px solid rgba(255,255,255,0.02)
        }
        .controls{
            display:flex;gap:12px;
            align-items:center
        }
        .btn{
            background:var(--accent);
            color:#fff;border:0;
            padding:10px 14px;
            border-radius:8px;
            font-weight:600;
            cursor:pointer
        }
        .btn.secondary{
            background:transparent;
            border:1px solid rgba(255,255,255,0.06);
            color:var(--text)
        }
        .message{
            margin:12px 0;
            padding:10px;
            border-radius:8px;
            background:rgba(255,255,255,0.03);b
            order:1px solid rgba(255,255,255,0.04);
            color:var(--text)
        }
        .table-wrap{
            overflow-x:auto}
        table{
            width:100%;border-collapse:collapse;
            margin-top:14px;
            min-width:640px
        }
        th,td{
            padding:12px 10px;text-align:left;
            border-bottom:1px dashed rgba(255,255,255,0.03);
            color:var(--text)
        }
        th{
            color:var(--muted);
            font-weight:600;
            font-size:.95rem
        }
        tr:hover td{
            background:rgba(255,255,255,0.01)}
        .empty{
            color:var(--muted);
            padding:12px
        }

        @media(max-width:700px){
            .controls{flex-direction:column;
            align-items:stretch}
            th,td{padding:10px}
            .btn{width:100%;padding:12px}
            table{min-width:520px}
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="app-header">
            <h1>Painel do Administrador — Fila Virtual</h1>
            <div class="controls">
                <form method="post" style="margin:0">
                    <button class="btn" type="submit" name="chamar">Chamar Próximo</button>
                </form>
                <a href="http://localhost/FilaVirtual/" class="btn secondary" style="text-decoration:none;padding:10px 12px;border-radius:8px;">Entrar como Usuário</a>
            </div>
        </header>

        <div class="card">
            <?php if($message): ?>
                <div class="message" id="msg-flash"><?= htmlspecialchars($message) ?></div>
                <script>
                setTimeout(function(){
                    var el = document.getElementById('msg-flash');
                    if(el) el.style.display = 'none';
                }, 5000);
                </script>
            <?php endif; ?>

            <h2 style="margin:0 0 8px 0;font-size:1rem;color:#fff">Fila Atual</h2>

            <?php if (count($fila) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width:90px">Posição</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th style="width:110px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($fila as $usuario): ?>
                        <tr>
                            <td>#<?= $usuario['posicao'] ?></td>
                            <td><?= htmlspecialchars($usuario['nome']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td>
                                <form method="post" style="display:inline" onsubmit="return confirm('Remover <?= htmlspecialchars($usuario['nome']) ?> da fila?');">
                                    <input type="hidden" name="remover_id" value="<?= $usuario['id'] ?>">
                                    <button type="submit" class="btn secondary" style="padding:6px 10px;font-size:.95rem">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">Ninguém na fila no momento.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
