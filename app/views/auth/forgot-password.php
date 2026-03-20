<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \Config\Config::APP_NAME; ?> - Recuperar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --light-blue: #dbeafe;
            --dark-blue: #1e40af;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--light-blue) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .forgot-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .forgot-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .forgot-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .forgot-header p {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .forgot-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .btn-back {
            text-align: center;
            margin-top: 20px;
        }

        .btn-back a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-back a:hover {
            text-decoration: underline;
        }

        .info-text {
            background-color: #f0f9ff;
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1>
                <i class="fas fa-lock"></i>
                Recuperar Senha
            </h1>
            <p>Digite seu e-mail para receber instruções</p>
        </div>

        <div class="forgot-body">
            <div class="info-text">
                <i class="fas fa-info-circle"></i>
                Enviaremos um link de recuperação para seu e-mail cadastrado.
            </div>

            <form id="forgotForm" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/index.php?action=process-forgot-password">
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="seu@email.com" 
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Enviar Link de Recuperação
                </button>

                <div class="btn-back">
                    <a href="<?php echo \Config\Config::APP_URL; ?>/index.php?action=login">
                        <i class="fas fa-arrow-left"></i> Voltar ao Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Email de recuperação enviado com sucesso!');
                    window.location.href = '/terapia/index.php?action=login';
                } else {
                    alert(data.message || 'Erro ao processar requisição');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar requisição');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    </script>
</body>
</html>
