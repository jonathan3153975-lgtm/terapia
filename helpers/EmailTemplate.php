<?php

namespace Helpers;

use Config\Config;

class EmailTemplate
{
    public static function passwordResetCredentials(string $userName, string $loginEmail, string $newPassword, string $loginUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f5f7fb; margin: 0; }
        .container { max-width: 620px; margin: 24px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 24px rgba(15,23,42,.08); }
        .header { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 26px; }
        .content { padding: 26px; color: #334155; line-height: 1.6; }
        .box { background: #f8fafc; border: 1px solid #dbeafe; border-radius: 10px; padding: 14px; }
        .btn { display: inline-block; padding: 12px 18px; border-radius: 8px; text-decoration: none; background: #2563eb; color: #fff; font-weight: 600; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h2 style="margin:0;">Nova senha de acesso</h2></div>
        <div class="content">
            <p>Olá, <strong>{$userName}</strong>.</p>
            <p>Recebemos sua solicitação de redefinição de senha.</p>
            <div class="box">
                <div><strong>Login:</strong> {$loginEmail}</div>
                <div><strong>Nova senha:</strong> {$newPassword}</div>
            </div>
            <p>Por segurança, recomendamos alterar essa senha após entrar no sistema.</p>
            <a class="btn" href="{$loginUrl}">Acessar login</a>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function patientSignupInvite(string $therapistName, string $signupLink): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f5f7fb; margin: 0; }
        .container { max-width: 620px; margin: 24px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 24px rgba(15,23,42,.08); }
        .header { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; padding: 26px; }
        .content { padding: 26px; color: #334155; line-height: 1.6; }
        .btn { display: inline-block; padding: 12px 18px; border-radius: 8px; text-decoration: none; background: #0f172a; color: #fff; font-weight: 600; }
        .hint { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h2 style="margin:0;">Ficha de cadastro do paciente</h2></div>
        <div class="content">
            <p>Olá,</p>
            <p>Seu terapeuta <strong>{$therapistName}</strong> enviou um link seguro para preencher sua ficha de cadastro.</p>
            <p><a class="btn" href="{$signupLink}">Preencher ficha</a></p>
            <div class="hint"><strong>Link direto:</strong><br>{$signupLink}</div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function patientAccessCredentials(string $patientName, string $loginEmail, string $rawPassword, string $loginUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f5f7fb; margin: 0; }
        .container { max-width: 620px; margin: 24px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 24px rgba(15,23,42,.08); }
        .header { background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #fff; padding: 26px; }
        .content { padding: 26px; color: #334155; line-height: 1.6; }
        .box { background: #f8fafc; border: 1px solid #dbeafe; border-radius: 10px; padding: 14px; }
        .btn { display: inline-block; padding: 12px 18px; border-radius: 8px; text-decoration: none; background: #2563eb; color: #fff; font-weight: 600; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header"><h2 style="margin:0;">Cadastro confirmado</h2></div>
        <div class="content">
            <p>Olá, <strong>{$patientName}</strong>.</p>
            <p>Seu cadastro foi concluído com sucesso.</p>
            <p>Estes são seus dados de acesso:</p>
            <div class="box">
                <div><strong>Login:</strong> {$loginEmail}</div>
                <div><strong>Senha:</strong> {$rawPassword}</div>
            </div>
            <p>Você já pode entrar na plataforma e iniciar seu acompanhamento.</p>
            <a class="btn" href="{$loginUrl}">Acessar sistema</a>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function taskAssigned(string $patientName, string $taskTitle, string $taskDescription, string $dueDate): string
    {
        $appUrl = Config::get('APP_URL', 'https://app.terapia.local');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 20px; }
        .task-card { background-color: #f9f9f9; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .task-card h3 { margin: 0 0 10px 0; color: #333; font-size: 18px; }
        .task-card p { margin: 8px 0; color: #666; line-height: 1.5; }
        .task-label { font-weight: 600; color: #667eea; display: inline-block; margin-bottom: 5px; }
        .cta-button { display: inline-block; background-color: #667eea; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; margin-top: 20px; font-weight: 600; }
        .cta-button:hover { background-color: #5568d3; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee; color: #888; font-size: 12px; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Nova Tarefa Recebida</h1>
        </div>
        <div class="content">
            <div class="greeting">Olá <strong>{$patientName}</strong>,</div>
            <p>Você recebeu uma nova tarefa de seu terapeuta. Confira os detalhes abaixo:</p>

            <div class="task-card">
                <h3>{$taskTitle}</h3>
                <p><span class="task-label">Descrição:</span><br>{$taskDescription}</p>
                <p><span class="task-label">Data limite:</span> {$dueDate}</p>
            </div>

            <p style="color: #666; line-height: 1.6;">
                Faça o login no portal do paciente para visualizar todos os detalhes, anexos e recursos relacionados à tarefa.
            </p>

            <a href="{$appUrl}/patient.php?action=tasks" class="cta-button">Ir para Tarefas</a>
        </div>
        <div class="footer">
            <p>Este é um email automático. Não responda diretamente a este email.</p>
            <p><a href="{$appUrl}">Tera-Tech</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function materialAssigned(string $patientName, string $materialTitle, string $materialDescription): string
    {
        $appUrl = Config::get('APP_URL', 'https://app.terapia.local');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #ffa500 0%, #ff6b6b 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 20px; }
        .material-card { background-color: #fff9f5; border-left: 4px solid #ff6b6b; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .material-card h3 { margin: 0 0 10px 0; color: #333; font-size: 18px; }
        .material-card p { margin: 8px 0; color: #666; line-height: 1.5; }
        .material-label { font-weight: 600; color: #ff6b6b; display: inline-block; margin-bottom: 5px; }
        .cta-button { display: inline-block; background-color: #ff6b6b; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; margin-top: 20px; font-weight: 600; }
        .cta-button:hover { background-color: #ff5252; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee; color: #888; font-size: 12px; }
        .footer a { color: #ff6b6b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Novo Material Disponível</h1>
        </div>
        <div class="content">
            <div class="greeting">Olá <strong>{$patientName}</strong>,</div>
            <p>Seu terapeuta compartilhou um novo material de apoio para sua terapia:</p>

            <div class="material-card">
                <h3>{$materialTitle}</h3>
                <p><span class="material-label">Descrição:</span><br>{$materialDescription}</p>
            </div>

            <p style="color: #666; line-height: 1.6;">
                Acesse a seção de materiais no portal do paciente para visualizar documentos, vídeos, exercícios e outros recursos.
            </p>

            <a href="{$appUrl}/patient.php?action=materials" class="cta-button">Ver Materiais</a>
        </div>
        <div class="footer">
            <p>Este é um email automático. Não responda diretamente a este email.</p>
            <p><a href="{$appUrl}">Tera-Tech</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function taskResponseReceived(string $therapistName, string $patientName, string $taskTitle): string
    {
        $appUrl = Config::get('APP_URL', 'https://app.terapia.local');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 20px; }
        .response-card { background-color: #f0fdf4; border-left: 4px solid #00b894; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .response-card h3 { margin: 0 0 10px 0; color: #333; font-size: 18px; }
        .response-card p { margin: 8px 0; color: #666; line-height: 1.5; }
        .response-label { font-weight: 600; color: #00b894; display: inline-block; margin-bottom: 5px; }
        .cta-button { display: inline-block; background-color: #00b894; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; margin-top: 20px; font-weight: 600; }
        .cta-button:hover { background-color: #00a880; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee; color: #888; font-size: 12px; }
        .footer a { color: #00b894; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Devolutiva da Tarefa Recebida</h1>
        </div>
        <div class="content">
            <div class="greeting">Olá <strong>{$therapistName}</strong>,</div>
            <p><strong>{$patientName}</strong> enviou sua devolutiva para a seguinte tarefa:</p>

            <div class="response-card">
                <h3>{$taskTitle}</h3>
                <p><span class="response-label">Status:</span> Devolutiva recebida e pronta para análise</p>
            </div>

            <p style="color: #666; line-height: 1.6;">
                Faça o login no painel de administração para visualizar a devolutiva, anexos e demais detalhes fornecidos pelo paciente.
            </p>

            <a href="{$appUrl}/dashboard.php?action=therapist-patients" class="cta-button">Ver Devolutiva</a>
        </div>
        <div class="footer">
            <p>Este é um email automático. Não responda diretamente a este email.</p>
            <p><a href="{$appUrl}">Tera-Tech</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public static function welcomePatient(string $patientName, string $loginUrl): string
    {
        $appUrl = Config::get('APP_URL', 'https://app.terapia.local');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .content { padding: 30px 20px; }
        .cta-button { display: inline-block; background-color: #667eea; color: #ffffff; padding: 14px 28px; border-radius: 4px; text-decoration: none; margin-top: 20px; font-weight: 600; font-size: 16px; }
        .cta-button:hover { background-color: #5568d3; }
        .footer { background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee; color: #888; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Bem-vindo(a)!</h1>
        </div>
        <div class="content">
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">Olá <strong>{$patientName}</strong>,</p>
            
            <p style="color: #666; line-height: 1.6; margin-bottom: 15px;">
                Sua conta foi criada com sucesso na Tera-Tech. Você já pode acessar seu portal para visualizar tarefas, 
                materiais de apoio e manter contato com seu terapeuta.
            </p>

            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0; color: #666; font-size: 14px;"><strong>Link de acesso:</strong></p>
                <p style="margin: 8px 0 0 0;"><a href="{$loginUrl}" style="color: #667eea; text-decoration: underline;">{$loginUrl}</a></p>
            </div>

            <p style="color: #666; line-height: 1.6;">
                Caso tenha dúvidas sobre como usar a plataforma, consulte a seção de ajuda ou entre em contato com seu terapeuta.
            </p>

            <a href="{$loginUrl}" class="cta-button">Acessar Portal</a>
        </div>
        <div class="footer">
            <p>Este é um email automático. Não responda diretamente a este email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
