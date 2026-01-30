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


$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if ($id) {
    $stmt = $conn->prepare("SELECT id, nome, posicao, email FROM fila WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $called = true;
        $nome = '';
        $posicao = null;
    } else {
        $called = false;
        $nome = $user['nome'];
        $posicao = (int)$user['posicao'];
    }
} elseif ($email !== '') {
    $stmt = $conn->prepare("SELECT id, nome, posicao FROM fila WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $called = true;
        $nome = $email;
        $posicao = null;
    } else {
        $called = false;
        $nome = $user['nome'];
        $posicao = (int)$user['posicao'];
    }
} else {
    header('Location: TelaUsuario.php');
    exit();
}


$totalStmt = $conn->query("SELECT COUNT(*) AS total FROM fila");
$total = (int)$totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

$pessoasAFrente = null;
$estimativaMinutos = null;

if (!$called && $posicao !== null) {
    
    $pessoasAFrenteStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM fila WHERE posicao < :posicao");
    $pessoasAFrenteStmt->bindParam(':posicao', $posicao);
    $pessoasAFrenteStmt->execute();
    $pessoasAFrente = (int)$pessoasAFrenteStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

   
    $estimativaMinutos = $pessoasAFrente * 3;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sua Posição na Fila</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#0b1220;
            --card:rgba(255,255,255,0.03);
            --accent:#b30000;--muted:#9aa4b2;
            --text:#e6eef8
        }
        html,body{
            height:100%;
            margin:0;background:linear-gradient(180deg,#07101a 0%, #0b1220 100%);
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
            padding:22px;border-radius:12px;
            box-shadow:0 8px 30px rgba(0,0,0,.6);
            border:1px solid rgba(255,255,255,0.02)
        }
        h2{
            margin:0 0 12px 0;
            font-size:clamp(1rem,2.4vw,1.25rem)
        }
        .muted{
            color:var(--muted)
        }
        .small{
            font-size:.95rem;color:var(--muted)
        }
        .btn{
            background:var(--accent);
            color:#fff;border:0;
            padding:10px 14px;
            border-radius:8px;
            font-weight:600;
            cursor:pointer;
            text-decoration:none;
            display:inline-block
        }

        @media(max-width:520px){
            html,body{
                display:flex;
                align-items:center;
                justify-content:center;
                min-height:100vh;
                margin:0
            }
            .wrap{
                margin:0;padding:12px;
                width:100%
            }
            .card{
                padding:14px
            }
            h2{
                font-size:1.1rem
            }
            .btn{
                display:block;width:100%;
                text-align:center;
                padding:12px;
                margin:0
            }
            .small{
                font-size:0.95rem
            }
        }
    </style>
    <script>
        setTimeout(function(){
            const url = new URL(window.location.href);
            url.searchParams.set('ts', Date.now());
            window.location.href = url.toString();
        }, 10000);
    </script>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Sala De Jogos 🎲</h2>

            <?php if ($called): ?>
                <p class="muted">
                    Você já foi chamado! <strong><span style="text-decoration:underline">Apresente-se com o e-mail no Laboratório de Informática A.</span></strong>
                    Se precisar entrar novamente, volte à tela de usuário.
                </p>
                <p><a class="btn" href="http://localhost/FilaVirtual/">Voltar</a></p>
            <?php else: ?>
                <p class="small">Sua posição atual na fila é:</p>
                <h1 style="margin:6px 0 12px 0;font-size:48px"><?= $posicao ? '#'.$posicao : '—' ?></h1>

                <p class="small">Pessoas à sua frente: <strong><?= $pessoasAFrente ?></strong></p>
                <p class="small">Total na fila: <strong><?= $total ?></strong></p>
                <p class="small">Estimativa de espera: <strong><?= $estimativaMinutos ?> minutos</strong> (aprox.)</p>

                <p style="margin-top:16px"><a class="btn" href="http://localhost/FilaVirtual/">Voltar / Atualizar</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
