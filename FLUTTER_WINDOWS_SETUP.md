# Instalação do Flutter no Windows e validação no Android Studio

## Objetivo

Este guia cobre três pontos:

1. O que deve aparecer como OK no `flutter doctor -v`
2. Como instalar o Flutter no Windows e validar passo a passo
3. Como rodar a conferência final do ambiente com script PowerShell

## Estado atual desta máquina

Itens já encontrados:

1. Java configurado em `JAVA_HOME`
2. Android SDK em `C:\Users\bmdi\AppData\Local\Android\Sdk`
3. Android Studio em `C:\Program Files\Android\Android Studio`

Item que falta:

1. Flutter SDK instalado e disponível no `PATH`

## Checklist do `flutter doctor -v`

Depois da instalação, o esperado é algo próximo disto:

### Flutter

Deve aparecer como OK:

1. `[√] Flutter`
2. Caminho do SDK correto
3. Canal e versão visíveis

Exemplo esperado:

```text
[√] Flutter (Channel stable, 3.x.x, on Microsoft Windows ...)
```

### Windows Version

Normalmente aparece como informação de sistema. Não costuma ser bloqueante.

### Android toolchain

Deve aparecer como OK:

1. `[√] Android toolchain`
2. Android SDK encontrado
3. Platform-Tools encontrados
4. Licenças aceitas

Exemplo esperado:

```text
[√] Android toolchain - develop for Android devices
    • Android SDK at C:\Users\bmdi\AppData\Local\Android\Sdk
    • Platform android-xx, build-tools xx.x.x
    • Java binary at: ...
    • All Android licenses accepted.
```

### Chrome

Opcional para este projeto. Se vier OK, melhor. Se não vier, não bloqueia Android.

### Visual Studio

Só é necessário para Windows desktop. Para Android, pode ficar pendente sem bloquear.

### Android Studio

Deve aparecer como OK:

1. `[√] Android Studio`
2. Versão detectada
3. Plugin Flutter instalável

Exemplo esperado:

```text
[√] Android Studio (version ...)
```

### VS Code

Opcional. Não bloqueia Android Studio.

### Connected device

Deve aparecer ao menos um device Android quando o emulador estiver aberto.

Exemplo esperado:

```text
[√] Connected device (1 available)
```

## Instalação do Flutter no PATH, passo a passo

### Etapa 1. Baixar o SDK

1. Abra o site oficial:
   `https://docs.flutter.dev/get-started/install/windows`
2. Baixe o Flutter SDK para Windows.
3. Extraia em uma pasta simples. Recomendação:

```text
C:\src\flutter
```

### Validação da etapa 1

Confirme que existe:

```text
C:\src\flutter\bin\flutter.bat
```

No PowerShell:

```powershell
Test-Path C:\src\flutter\bin\flutter.bat
```

Resultado esperado:

```text
True
```

### Etapa 2. Adicionar ao PATH

1. Abra `Editar as variáveis de ambiente do sistema`
2. Clique em `Variáveis de Ambiente`
3. Em `Variáveis do usuário`, selecione `Path`
4. Clique em `Editar`
5. Adicione:

```text
C:\src\flutter\bin
```

6. Confirme todas as janelas
7. Feche o PowerShell atual
8. Abra um novo PowerShell

### Validação da etapa 2

Rode, nesta ordem:

```powershell
where.exe flutter
flutter --version
Get-Command flutter
```

Resultado esperado:

1. `where.exe flutter` mostra `C:\src\flutter\bin\flutter.bat`
2. `flutter --version` mostra a versão do SDK
3. `Get-Command flutter` encontra o comando

### Etapa 3. Rodar o diagnóstico do Flutter

No PowerShell:

```powershell
flutter doctor -v
```

### Validação da etapa 3

O esperado é:

1. Flutter OK
2. Android toolchain OK
3. Android Studio OK ou detectado
4. Connected device ainda pode estar vazio se o emulador não estiver aberto

### Etapa 4. Aceitar licenças, se necessário

Se o `flutter doctor -v` acusar licenças pendentes:

```powershell
flutter doctor --android-licenses
```

Aceite todas.

### Validação da etapa 4

Rode novamente:

```powershell
flutter doctor -v
```

O item `Android toolchain` deve ficar sem alerta de licenças.

### Etapa 5. Confirmar Android Studio

1. Abra o Android Studio
2. Vá em `Settings > Plugins`
3. Instale o plugin `Flutter`
4. O plugin `Dart` deve ser instalado junto
5. Reinicie o Android Studio

### Validação da etapa 5

Rode novamente:

```powershell
flutter doctor -v
```

O item `Android Studio` deve aparecer como detectado.

### Etapa 6. Abrir emulador Android

1. No Android Studio, abra `Device Manager`
2. Crie ou inicie um emulador Android

### Validação da etapa 6

No PowerShell:

```powershell
adb devices
flutter devices
```

O esperado é aparecer pelo menos um emulador Android.

## Preparação do projeto Flutter deste repositório

Depois que o Flutter estiver funcionando, entre na pasta do app:

```powershell
Set-Location C:\Users\bmdi\Documents\GitHub\terapia\mobile_app_flutter
```

Rode:

```powershell
flutter create . --platforms=android,ios
flutter pub get
flutter analyze
```

## Validação do projeto

1. Ajuste a URL base em [mobile_app_flutter/lib/core/config/app_config.dart](mobile_app_flutter/lib/core/config/app_config.dart)
2. Com o emulador aberto, rode:

```powershell
flutter run
```

## Conferência automatizada do ambiente

Após instalar o Flutter, rode o script:

```powershell
Set-Location C:\Users\bmdi\Documents\GitHub\terapia
.\scripts\check_flutter_env.ps1
```

Se quiser saída detalhada do `flutter doctor -v`:

```powershell
.\scripts\check_flutter_env.ps1 -RunFlutterDoctor
```

Para validar especificamente o projeto do app:

```powershell
.\scripts\check_mobile_app_flutter.ps1
```

Para gerar as plataformas nativas e validar dependências:

```powershell
.\scripts\check_mobile_app_flutter.ps1 -GeneratePlatforms -RunPubGet -RunAnalyze
```

## Ordem curta de validação, comando por comando

1.

```powershell
Test-Path C:\src\flutter\bin\flutter.bat
```

2.

```powershell
where.exe flutter
```

3.

```powershell
flutter --version
```

4.

```powershell
flutter doctor -v
```

5.

```powershell
adb devices
```

6.

```powershell
flutter devices
```

7.

```powershell
Set-Location C:\Users\bmdi\Documents\GitHub\terapia\mobile_app_flutter
flutter create . --platforms=android,ios
flutter pub get
flutter run
```