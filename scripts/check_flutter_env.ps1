param(
    [switch]$RunFlutterDoctor
)

$ErrorActionPreference = 'SilentlyContinue'

function Write-Section {
    param([string]$Title)

    Write-Host ''
    Write-Host ('=' * 72) -ForegroundColor Cyan
    Write-Host $Title -ForegroundColor Cyan
    Write-Host ('=' * 72) -ForegroundColor Cyan
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

function Get-AndroidSdkToolPath {
    param(
        [string]$AndroidHome,
        [string]$RelativePath
    )

    $fullPath = Join-Path $AndroidHome $RelativePath
    if (Test-Path $fullPath) {
        return $fullPath
    }

    return $null
}

Write-Section 'Flutter / Android environment check'

$flutterPath = Get-CommandPath -CommandName 'flutter'
$adbPath = Get-CommandPath -CommandName 'adb'
$javaHome = $env:JAVA_HOME
$androidHome = if ($env:ANDROID_HOME) { $env:ANDROID_HOME } elseif ($env:ANDROID_SDK_ROOT) { $env:ANDROID_SDK_ROOT } else { 'C:\Users\bmdi\AppData\Local\Android\Sdk' }
$studioPath = 'C:\Program Files\Android\Android Studio'
$licensesPath = Join-Path $androidHome 'licenses'
$sdkManagerPath = Get-AndroidSdkToolPath -AndroidHome $androidHome -RelativePath 'cmdline-tools\latest\bin\sdkmanager.bat'
if ($null -eq $sdkManagerPath) {
    $sdkManagerPath = Get-AndroidSdkToolPath -AndroidHome $androidHome -RelativePath 'cmdline-tools\bin\sdkmanager.bat'
}

$emulatorExePath = Get-AndroidSdkToolPath -AndroidHome $androidHome -RelativePath 'emulator\emulator.exe'
$flutterDetails = if ($null -ne $flutterPath) { $flutterPath } else { 'Comando flutter não encontrado no PATH' }
$adbDetails = if ($null -ne $adbPath) { $adbPath } else { 'Comando adb não encontrado no PATH' }
$javaDetails = if (-not [string]::IsNullOrWhiteSpace($javaHome)) { $javaHome } else { 'JAVA_HOME não definido' }
$sdkManagerDetails = if ($null -ne $sdkManagerPath) { $sdkManagerPath } else { 'sdkmanager.bat não encontrado' }
$emulatorDetails = if ($null -ne $emulatorExePath) { $emulatorExePath } else { 'emulator.exe não encontrado' }

Write-Section 'Executáveis'
Write-Status -Label 'Flutter no PATH' -Ok ($null -ne $flutterPath) -Details $flutterDetails
Write-Status -Label 'ADB no PATH' -Ok ($null -ne $adbPath) -Details $adbDetails

Write-Section 'Variáveis e diretórios'
Write-Status -Label 'JAVA_HOME configurado' -Ok (-not [string]::IsNullOrWhiteSpace($javaHome)) -Details $javaDetails
Write-Status -Label 'Diretório do Android SDK' -Ok (Test-Path $androidHome) -Details $androidHome
Write-Status -Label 'Android Studio instalado' -Ok (Test-Path $studioPath) -Details $studioPath

Write-Section 'Componentes do Android SDK'
Write-Status -Label 'platform-tools' -Ok (Test-Path (Join-Path $androidHome 'platform-tools')) -Details (Join-Path $androidHome 'platform-tools')
Write-Status -Label 'build-tools' -Ok (Test-Path (Join-Path $androidHome 'build-tools')) -Details (Join-Path $androidHome 'build-tools')
Write-Status -Label 'cmdline-tools' -Ok (Test-Path (Join-Path $androidHome 'cmdline-tools')) -Details (Join-Path $androidHome 'cmdline-tools')
Write-Status -Label 'emulator' -Ok (Test-Path (Join-Path $androidHome 'emulator')) -Details (Join-Path $androidHome 'emulator')
Write-Status -Label 'platforms' -Ok (Test-Path (Join-Path $androidHome 'platforms')) -Details (Join-Path $androidHome 'platforms')
Write-Status -Label 'sdkmanager disponível' -Ok ($null -ne $sdkManagerPath) -Details $sdkManagerDetails
Write-Status -Label 'emulator.exe disponível' -Ok ($null -ne $emulatorExePath) -Details $emulatorDetails

Write-Section 'Licenças Android'
$licenseFiles = @()
if (Test-Path $licensesPath) {
    $licenseFiles = Get-ChildItem -Path $licensesPath -File | Select-Object -ExpandProperty Name
}
Write-Status -Label 'Diretório licenses' -Ok (Test-Path $licensesPath) -Details $licensesPath
Write-Status -Label 'Arquivos de licença encontrados' -Ok ($licenseFiles.Count -gt 0) -Details ($(if ($licenseFiles.Count -gt 0) { ($licenseFiles -join ', ') } else { 'Nenhum arquivo de licença encontrado' }))

Write-Section 'AVDs disponíveis'
if ($null -ne $emulatorExePath) {
    $avds = & $emulatorExePath -list-avds
    if ($LASTEXITCODE -eq 0 -and $avds) {
        Write-Status -Label 'AVDs encontrados' -Ok $true -Details (($avds | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne '' }) -join ', ')
    } else {
        Write-Status -Label 'AVDs encontrados' -Ok $false -Details 'Nenhum AVD configurado no momento'
    }
} else {
    Write-Status -Label 'AVDs encontrados' -Ok $false -Details 'emulator.exe não disponível para listar AVDs'
}

if ($null -ne $flutterPath) {
    Write-Section 'Versões'
    Write-Host 'flutter --version'
    flutter --version

    if ($null -ne $adbPath) {
        Write-Host ''
        Write-Host 'adb --version'
        adb --version
    }

    Write-Section 'Devices'
    if ($null -ne $adbPath) {
        Write-Host 'adb devices'
        adb devices
    }

    Write-Host ''
    Write-Host 'flutter devices'
    flutter devices

    if ($RunFlutterDoctor) {
        Write-Section 'flutter doctor -v'
        flutter doctor -v
    }
} else {
    Write-Section 'Próximo passo'
    Write-Host 'Flutter não está no PATH.'
    Write-Host 'Instale o SDK em C:\src\flutter e adicione C:\src\flutter\bin ao PATH.'
}