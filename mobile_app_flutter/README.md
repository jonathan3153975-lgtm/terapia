# App Flutter do Paciente

Aplicativo Flutter do paciente da plataforma Tera-Tech.

## Estado atual

O projeto já possui estrutura Flutter completa, incluindo:

1. camadas Dart em `lib/`
2. plataformas `android/` e `ios/`
3. ícones atualizados para Android e iOS
4. configuração de ambiente com URL release apontando para `https://jw-adminix.com.br/terapia`
5. limpeza dos atalhos e credenciais de teste para publicação

## Comandos úteis

Dentro desta pasta, execute:

```powershell
flutter pub get
flutter analyze
```

Para rodar no emulador Android:

```powershell
flutter run
```

Para gerar build release Android:

```powershell
flutter build appbundle --release --dart-define=API_BASE_URL_RELEASE=https://jw-adminix.com.br/terapia
```

## Pendência para Play Store

A publicação Android ainda depende da configuração da assinatura release.

Arquivos pendentes:

1. `android/key.properties`
2. keystore de produção referenciado por esse arquivo

Sem esses arquivos, a build release falha intencionalmente para evitar publicação com assinatura incorreta.