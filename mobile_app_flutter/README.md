# Base do App Flutter do Paciente

Esta pasta contém a base Dart do app Flutter do paciente.

## Situação atual

O ambiente em que esta base foi criada não possui o SDK Flutter instalado. Por isso:

1. A camada `lib/` já foi criada.
2. `pubspec.yaml` e configurações principais já existem.
3. As pastas nativas `android/` e `ios/` ainda precisam ser geradas localmente.

## Gerar Android e iOS depois

Dentro desta pasta, execute:

```powershell
flutter create . --platforms=android,ios
flutter pub get
```

## Rodar no emulador Android depois

```powershell
flutter run
```

Antes disso, ajuste a URL base em `lib/core/config/app_config.dart`.