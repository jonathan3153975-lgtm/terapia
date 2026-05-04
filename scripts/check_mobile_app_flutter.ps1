param(
    [switch]$RunPubGet,
    [switch]$RunAnalyze,
    [switch]$GeneratePlatforms
)

$ErrorActionPreference = 'SilentlyContinue'

function Write-Section {
    param([string]$Title)

    Write-Host ''
    Write-Host ('=' * 72) -ForegroundColor Yellow
    Write-Host $Title -ForegroundColor Yellow
    Write-Host ('=' * 72) -ForegroundColor Yellow
}

function Write-Status {
    param(
        [string]$Label,
        [bool]$Ok,
        [string]$Details = ''
    )

    $prefix = if ($Ok) { '[OK] ' } else { '[ERRO] ' }
    $color = if ($Ok) { 'Green' } else { 'Red' }
    Write-Host ($prefix + $Label) -ForegroundColor $color
    if ($Details -ne '') {
        Write-Host ('       ' + $Details)
    }
}

function Get-CommandPath {
    param([string]$CommandName)
    $cmd = Get-Command $CommandName -ErrorAction SilentlyContinue
    if ($null -eq $cmd) {
        return $null
    }
    return $cmd.Source
}

$repoRoot = Split-Path -Parent $PSScriptRoot
$projectPath = Join-Path $repoRoot 'mobile_app_flutter'
$flutterPath = Get-CommandPath -CommandName 'flutter'

Write-Section 'Validação do projeto mobile_app_flutter'
Write-Status -Label 'Pasta do projeto existe' -Ok (Test-Path $projectPath) -Details $projectPath
Write-Status -Label 'Flutter no PATH' -Ok ($null -ne $flutterPath) -Details ($(if ($null -ne $flutterPath) { $flutterPath } else { 'Comando flutter não encontrado no PATH' }))

if (-not (Test-Path $projectPath)) {
    Write-Host ''
    Write-Host 'Projeto mobile_app_flutter não encontrado.' -ForegroundColor Red
    exit 1
}

Write-Section 'Arquivos esperados'
$expectedFiles = @(
    'pubspec.yaml',
    'analysis_options.yaml',
    'lib\main.dart',
    'lib\app.dart',
    'lib\core\config\app_config.dart'
)

foreach ($relativeFile in $expectedFiles) {
    $fullPath = Join-Path $projectPath $relativeFile
    Write-Status -Label $relativeFile -Ok (Test-Path $fullPath) -Details $fullPath
}

Write-Section 'Plataformas geradas'
$androidPath = Join-Path $projectPath 'android'
$iosPath = Join-Path $projectPath 'ios'
Write-Status -Label 'Pasta android/' -Ok (Test-Path $androidPath) -Details $androidPath
Write-Status -Label 'Pasta ios/' -Ok (Test-Path $iosPath) -Details $iosPath

$configPath = Join-Path $projectPath 'lib\core\config\app_config.dart'
if (Test-Path $configPath) {
    Write-Section 'Configuração baseUrl'
    Get-Content $configPath | Select-Object -First 20
}

if ($null -eq $flutterPath) {
    Write-Section 'Próximo passo'
    Write-Host 'Flutter não está disponível no PATH. Instale o SDK e rode novamente este script.'
    exit 0
}

Push-Location $projectPath

try {
    if ($GeneratePlatforms) {
        Write-Section 'Gerando plataformas nativas'
        flutter create . --platforms=android,ios
    }

    if ($RunPubGet) {
        Write-Section 'flutter pub get'
        flutter pub get
    }

    if ($RunAnalyze) {
        Write-Section 'flutter analyze'
        flutter analyze
    }

    Write-Section 'flutter devices'
    flutter devices
}
finally {
    Pop-Location
}