# Roteiro Completo do App de Pacientes em Flutter

## Objetivo

Construir o app do paciente em Flutter, com base no backend PHP existente, mantendo Android e iOS como plataformas-alvo. O backend web continua ativo, mas o app consome uma camada própria de API via `api.php`.

Este roteiro assume como regra de desenvolvimento os pontos abaixo:

1. O app deve ter UX mobile própria, e não ser uma cópia do web.
2. A navegação precisa ser orientada a rotina diária do paciente.
3. Push notifications devem entrar desde a base arquitetural.
4. Analytics e eventos devem ser rastreados desde a v1.
5. A primeira validação real deve ocorrer com piloto interno antes da publicação ampla.

## O que foi preparado neste repositório

### Backend

Arquivos criados:

- `api.php`
- `app/controllers/Api/PatientAppController.php`
- `app/models/ApiAccessToken.php`
- `app/models/PatientDevice.php`
- `app/models/AppAnalyticsEvent.php`
- `app/models/PatientAppCycle.php`
- `helpers/ApiPatientAuth.php`
- `database/migration_mobile_app_flutter_api.sql`

### App Flutter

Base criada em:

- `mobile_app_flutter/`

Importante: o SDK Flutter não está instalado neste ambiente. Por isso, a pasta do app foi preparada manualmente com a camada Dart e os arquivos principais, mas os diretórios nativos `android/` e `ios/` ainda precisam ser gerados localmente com o Flutter.

## Escopo recomendado da v1

Entram na v1:

1. Login do paciente
2. Dashboard
3. Tarefas
4. Materiais
5. Livros
6. TeraTube
7. Mensageiro
8. Meditações guiadas
9. Orações
10. Diário da gratidão
11. Devocional do dia
12. Minha conta
13. Registro de dispositivo
14. Rastreamento de eventos

Ficam para fase 2:

1. Pai, fala comigo
2. Uploads de anexos em devolutiva de tarefa
3. Modo offline parcial
4. Notificações segmentadas por módulo
5. Recursos do terapeuta em app

## Regra principal de produto: pontos 10 a 12

## Ponto 10: Design system mobile obrigatório

O app deve ter identidade própria, com decisões móveis claras.

Diretrizes:

1. Tela inicial orientada por rotina e continuidade, não por menu administrativo.
2. Cartões com prioridade visual para: próxima sessão, tarefa pendente, prática do dia e retomada de conteúdo.
3. Tipografia legível em blocos curtos.
4. Componentes consistentes: botão primário, botão secundário, card de conteúdo, card de prática, banner de progresso, feedback de sucesso/erro.
5. Layout em Flutter usando tokens fixos de cor, espaçamento, raio e sombra.
6. Nada de replicar tabelas do web no mobile.
7. Navegação principal por fluxo do paciente: Início, Práticas, Conteúdos, Conta.

Implementação prática:

1. Centralizar o tema em `lib/core/theme/app_theme.dart`.
2. Criar tokens visuais únicos e reaproveitáveis.
3. Todo componente novo deve nascer a partir dos tokens do tema.
4. Toda nova tela deve ser desenhada primeiro para toque e leitura curta.

## Ponto 11: Push notifications desde a base

O push não deve ser tratado como extra.

Objetivos iniciais:

1. Lembrete de tarefa pendente
2. Aviso de novo material
3. Aviso de conteúdo do dia
4. Aviso de próxima sessão

Estratégia técnica:

1. Registrar dispositivo no backend com `patient-device-register`.
2. Salvar `device_identifier`, `platform`, `push_token`, `app_version`, `locale`.
3. Integrar FCM na fase seguinte.
4. Para iOS, planejar APNs via Firebase Messaging.
5. O app já deve ter um serviço dedicado para token e registro, mesmo antes da integração final.

Implementação futura recomendada:

1. Adicionar `firebase_core` e `firebase_messaging`.
2. Solicitar permissão no iOS.
3. Registrar token no backend após login e refresh do token.
4. Criar jobs no backend para envio por gatilho de negócio.

## Ponto 12: Analytics e eventos desde a v1

Sem eventos, o app perde capacidade de priorização.

Eventos mínimos:

1. `app_open`
2. `login_success`
3. `dashboard_view`
4. `task_open`
5. `task_response_sent`
6. `material_open`
7. `book_open`
8. `video_play`
9. `messenger_draw`
10. `guided_meditation_open`
11. `prayer_open`
12. `gratitude_saved`
13. `devotional_saved`

Implementação prática:

1. Backend já preparado com tabela `app_analytics_events`.
2. Endpoint disponível: `patient-analytics-track`.
3. No app, centralizar o rastreio em `lib/core/services/analytics_service.dart`.
4. Não espalhar chamadas de analytics diretamente nas telas sem encapsulamento.

## Arquitetura recomendada

## Backend

Responsabilidades:

1. Autenticação bearer token para o app
2. Regras de assinatura ativa
3. Payloads JSON específicos para mobile
4. Streaming protegido de mídia
5. Registro de dispositivos
6. Rastreamento de eventos
7. Persistência de ciclos aleatórios para módulos de sorteio

## App Flutter

Responsabilidades:

1. Consumir a API mobile
2. Controlar sessão local do paciente
3. Aplicar design system mobile
4. Registrar eventos e dispositivo
5. Exibir módulos de forma mais enxuta que o web

## Endpoints preparados nesta fase

### Autenticação e sessão

1. `POST api.php?action=patient-login`
2. `POST api.php?action=patient-logout`
3. `GET api.php?action=patient-me`

### Dashboard e conta

1. `GET api.php?action=patient-dashboard`
2. `GET api.php?action=patient-my-account`
3. `POST api.php?action=patient-my-account-update`

### Tarefas e materiais

1. `GET api.php?action=patient-tasks`
2. `GET api.php?action=patient-task-show&id={id}`
3. `POST api.php?action=patient-task-respond`
4. `GET api.php?action=patient-materials`
5. `GET api.php?action=patient-material-asset&id={id}`

### Livros

1. `GET api.php?action=patient-books`
2. `POST api.php?action=patient-book-favorite-toggle`
3. `GET api.php?action=patient-book-file&id={id}`

### Vídeos

1. `GET api.php?action=patient-videos`
2. `GET api.php?action=patient-video-show&id={id}`
3. `POST api.php?action=patient-video-favorite-toggle`
4. `POST api.php?action=patient-video-rate`
5. `POST api.php?action=patient-video-comment`
6. `GET api.php?action=patient-video-file&id={id}`

### Mensageiro

1. `GET api.php?action=patient-messenger-entries`
2. `GET api.php?action=patient-messenger-draw`
3. `POST api.php?action=patient-messenger-save`

### Meditações guiadas

1. `GET api.php?action=patient-guided-meditations`
2. `GET api.php?action=patient-guided-meditation-show&id={id}`
3. `GET api.php?action=patient-guided-meditation-draw-letter&meditation_id={id}`
4. `POST api.php?action=patient-guided-meditation-save`
5. `GET api.php?action=patient-guided-meditation-audio&id={id}`

### Orações

1. `GET api.php?action=patient-prayers`
2. `GET api.php?action=patient-prayer-show&id={id}`
3. `POST api.php?action=patient-prayer-save`
4. `GET api.php?action=patient-prayer-audio&id={id}`

### Gratidão e devocional

1. `GET api.php?action=patient-gratitude`
2. `POST api.php?action=patient-gratitude-store`
3. `GET api.php?action=patient-devotionals`
4. `GET api.php?action=patient-devotional-today`
5. `POST api.php?action=patient-devotional-save`

### Base mobile

1. `POST api.php?action=patient-device-register`
2. `POST api.php?action=patient-analytics-track`

## Ordem recomendada de desenvolvimento

1. Executar a migration `database/migration_mobile_app_flutter_api.sql`.
2. Validar `api.php` e autenticação bearer em ambiente local.
3. Instalar Flutter localmente.
4. Gerar as pastas nativas Android e iOS dentro de `mobile_app_flutter/`.
5. Rodar `flutter pub get`.
6. Ajustar `baseUrl` no app.
7. Validar login com backend local.
8. Validar dashboard e tarefas.
9. Integrar analytics básico.
10. Integrar push token registration.
11. Rodar piloto com poucos pacientes.

## Como preparar Android e iOS depois

No terminal, dentro da pasta `mobile_app_flutter/`:

```powershell
flutter create . --platforms=android,ios
flutter pub get
```

Esse comando gera as pastas `android/` e `ios/` preservando a camada `lib/` já criada.

## Como emular no Android Studio depois

1. Instale Flutter SDK.
2. Instale Android Studio com Android SDK, Emulator e Android SDK Platform-Tools.
3. No terminal, rode:

```powershell
flutter doctor
```

4. Corrija tudo que aparecer como pendência.
5. Entre em `mobile_app_flutter/`.
6. Rode:

```powershell
flutter create . --platforms=android,ios
flutter pub get
flutter devices
```

7. No Android Studio, abra um emulador.
8. Configure o `baseUrl` para o backend local:

- Android Emulator usando servidor local no Windows/IIS/Apache: `http://10.0.2.2/terapia`
- Dispositivo físico: usar IP local da máquina, por exemplo `http://192.168.0.10/terapia`

9. Execute:

```powershell
flutter run
```

## Como preparar iOS depois

Pré-requisito: macOS com Xcode.

1. Copiar o projeto para um Mac ou usar repositório compartilhado.
2. Entrar em `mobile_app_flutter/`.
3. Rodar:

```bash
flutter create . --platforms=ios
flutter pub get
open ios/Runner.xcworkspace
```

4. Configurar Signing no Xcode.
5. Validar em simulador e dispositivo.
6. Ao integrar push, habilitar capacidades de notificações no app iOS.

## Plano de piloto interno, conforme ponto 13

1. Selecionar 5 a 15 pacientes reais ou controlados.
2. Medir taxa de login bem-sucedido.
3. Medir acesso diário ao dashboard.
4. Medir conclusão de tarefas.
5. Medir abertura de livros, vídeos, meditações e orações.
6. Medir falhas por endpoint.
7. Coletar feedback sobre clareza da navegação e utilidade do app.

## Critério de pronto para lojas

1. Login estável
2. Streaming de mídia estável
3. Eventos mínimos chegando no backend
4. Registro de dispositivo funcionando
5. Sem travamentos nas telas principais
6. Base visual consistente com design system

## Relatório objetivo para fazer depois

Como continuar posteriormente:

1. Executar a migration do backend.
2. Instalar Flutter localmente.
3. Rodar `flutter create . --platforms=android,ios` dentro de `mobile_app_flutter/`.
4. Rodar `flutter pub get`.
5. Ajustar a URL base do app para o ambiente local.
6. Abrir emulador Android no Android Studio.
7. Rodar `flutter run`.
8. Validar login, dashboard e tarefas primeiro.
9. Só depois integrar push com Firebase Messaging.
10. Rodar piloto interno antes de distribuição ampla.