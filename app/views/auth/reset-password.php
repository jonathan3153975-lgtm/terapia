<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo \Config\Config::APP_NAME; ?> - Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --light-blue: #dbeafe;
            --dark-blue: #1e40af;
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--light-blue) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .reset-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }

        .reset-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .reset-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .reset-body {
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
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .password-strength {
            margin-top: 10px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background-color: #e5e7eb;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }

        .requirements {
            font-size: 12px;
            margin-top: 10px;
        }

        .requirement {
            padding: 5px 0;
            color: #6b7280;
        }

        .requirement.met {
            color: #10b981;
        }

        .requirement i {
            margin-right: 5px;
            width: 15px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>
                <i class="fas fa-key"></i>
                Redefinir Senha
            </h1>
        </div>

        <div class="reset-body">
            <form id="resetForm" method="POST" action="<?php echo \Config\Config::APP_URL; ?>/index.php?action=process-reset-password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group">
                    <label for="password" class="form-label">Nova Senha</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Digite sua nova senha"
                        required
                    >
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="requirements">
                            <div class="requirement" id="req-length">
                                <i class="fas fa-circle"></i> Pelo menos 8 caracteres
                            </div>
                            <div class="requirement" id="req-upper">
                                <i class="fas fa-circle"></i> Uma letra maiúscula
                            </div>
                            <div class="requirement" id="req-lower">
                                <i class="fas fa-circle"></i> Uma letra minúscula
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-circle"></i> Um número
                            </div>
                            <div class="requirement" id="req-special">
                                <i class="fas fa-circle"></i> Um caractere especial (!@#$%^&*)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm" class="form-label">Confirmar Senha</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="confirm" 
                        name="confirm_password" 
                        placeholder="Confirme sua senha"
                        required
                    >
                </div>

                <button type="submit" class="btn-submit" id="submitBtn" disabled>
                    Redefinir Senha
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm');
        const submitBtn = document.getElementById('submitBtn');
        const strengthFill = document.getElementById('strengthFill');

        const requirements = {
            length: document.getElementById('req-length'),
            upper: document.getElementById('req-upper'),
            lower: document.getElementById('req-lower'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let metCount = 0;

            // Verifica requisitos
            if (password.length >= 8) {
                requirements.length.classList.add('met');
                strength += 20;
                metCount++;
            } else {
                requirements.length.classList.remove('met');
            }

            if (/[A-Z]/.test(password)) {
                requirements.upper.classList.add('met');
                strength += 20;
                metCount++;
            } else {
                requirements.upper.classList.remove('met');
            }

            if (/[a-z]/.test(password)) {
                requirements.lower.classList.add('met');
                strength += 20;
                metCount++;
            } else {
                requirements.lower.classList.remove('met');
            }

            if (/[0-9]/.test(password)) {
                requirements.number.classList.add('met');
                strength += 20;
                metCount++;
            } else {
                requirements.number.classList.remove('met');
            }

            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                requirements.special.classList.add('met');
                strength += 20;
                metCount++;
            } else {
                requirements.special.classList.remove('met');
            }

            strengthFill.style.width = strength + '%';
            strengthFill.style.backgroundColor = strength <= 40 ? '#ef4444' : strength <= 70 ? '#f59e0b' : '#10b981';

            // Habilita/desabilita botão se todas as senhas conferem
            checkFormValidity();
        });

        confirmInput.addEventListener('input', checkFormValidity);

        function checkFormValidity() {
            const isValid = passwordInput.value.length >= 8 &&
                            /[A-Z]/.test(passwordInput.value) &&
                            /[a-z]/.test(passwordInput.value) &&
                            /[0-9]/.test(passwordInput.value) &&
                            /[!@#$%^&*(),.?":{}|<>]/.test(passwordInput.value) &&
                            passwordInput.value === confirmInput.value &&
                            confirmInput.value !== '';

            submitBtn.disabled = !isValid;
        }

        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (passwordInput.value !== confirmInput.value) {
                alert('As senhas não conferem!');
                return;
            }

            const btn = submitBtn;
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Redefinindo...';

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
                    alert('Senha redefinida com sucesso!');
                    window.location.href = data.redirect || '/terapia/index.php?action=login';
                } else {
                    alert(data.message || 'Erro ao redefinir senha');
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
