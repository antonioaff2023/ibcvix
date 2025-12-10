@echo off
chcp 65001 >nul
:: -------------------------------------------------------------------------
:: Script para criação e atualização de versões (tags)
:: -------------------------------------------------------------------------

echo ========================================================
echo      GERENCIAMENTO DE VERSÕES E TAGS
echo ========================================================
echo.

:: Garante que estamos na pasta onde o arquivo .bat está salvo
cd /d "%~dp0"

:: Verifica se há mudanças não commitadas
git status --porcelain >nul 2>&1
if errorlevel 1 (
    echo [ERRO] Este não é um repositório Git válido!
    pause
    exit /b 1
)

:: Menu de opções
echo Escolha uma opção:
echo.
echo [1] Criar nova versão (commit, tag e push)
echo [2] Listar versões (tags) existentes
echo [3] Ver versão atual
echo [4] Atualizar apenas o arquivo VERSION
echo [5] Sair
echo.

set /p OPCAO="Digite a opção desejada: "

if "%OPCAO%"=="1" goto :criar_versao
if "%OPCAO%"=="2" goto :listar_versoes
if "%OPCAO%"=="3" goto :ver_versao_atual
if "%OPCAO%"=="4" goto :atualizar_version
if "%OPCAO%"=="5" goto :sair
goto :opcao_invalida

:criar_versao
echo.
echo ========================================================
echo           CRIAR NOVA VERSÃO
echo ========================================================
echo.

:: Pega a versão atual
if exist "lib\VERSION" (
    set /p VERSAO_ATUAL=<lib\VERSION
    echo Versão atual: !VERSAO_ATUAL!
) else (
    set VERSAO_ATUAL=0.0
    echo Nenhuma versão encontrada. Começando com 0.0
)

echo.
set /p NOVA_VERSAO="Digite a nova versão (ex: 1.0.0, 2.1.3): "

if "%NOVA_VERSAO%"=="" (
    echo [ERRO] Versão não pode estar vazia!
    pause
    exit /b 1
)

echo.
set /p MENSAGEM_TAG="Digite uma mensagem para esta versão (opcional): "

if "%MENSAGEM_TAG%"=="" (
    set MENSAGEM_TAG=Versão %NOVA_VERSAO%
)

echo.
echo --------------------------------------------------------
echo Resumo da operação:
echo --------------------------------------------------------
echo Nova versão: %NOVA_VERSAO%
echo Mensagem: %MENSAGEM_TAG%
echo --------------------------------------------------------
echo.

set /p CONFIRMA="Confirma a criação desta versão? (S/N): "
if /i not "%CONFIRMA%"=="S" (
    echo Operação cancelada.
    pause
    exit /b 0
)

echo.
echo [1/6] Atualizando arquivo lib\VERSION...
echo %NOVA_VERSAO%> lib\VERSION

echo [2/6] Adicionando arquivos (git add)...
git add .

echo [3/6] Criando commit...
git commit -m "Versão %NOVA_VERSAO% - %MENSAGEM_TAG%"

if errorlevel 1 (
    echo [AVISO] Nenhuma alteração para commit ou commit falhou.
)

echo [4/6] Criando tag v%NOVA_VERSAO%...
git tag -a "v%NOVA_VERSAO%" -m "%MENSAGEM_TAG%"

if errorlevel 1 (
    echo [ERRO] Falha ao criar tag!
    pause
    exit /b 1
)

echo [5/6] Enviando commit para o servidor (git push)...
git push

echo [6/6] Enviando tags para o servidor (git push --tags)...
git push origin --tags

echo.
echo ========================================================
echo      VERSÃO %NOVA_VERSAO% CRIADA COM SUCESSO!
echo ========================================================
echo.
pause
exit /b 0

:listar_versoes
echo.
echo ========================================================
echo           VERSÕES (TAGS) EXISTENTES
echo ========================================================
echo.
git tag -l -n1
echo.
echo --------------------------------------------------------
pause
exit /b 0

:ver_versao_atual
echo.
echo ========================================================
echo           VERSÃO ATUAL
echo ========================================================
echo.
if exist "lib\VERSION" (
    set /p VERSAO_ATUAL=<lib\VERSION
    echo Versão: !VERSAO_ATUAL!
    echo.
    echo Última tag no Git:
    git describe --tags --abbrev=0 2>nul
    if errorlevel 1 (
        echo [Nenhuma tag encontrada]
    )
) else (
    echo [ERRO] Arquivo lib\VERSION não encontrado!
)
echo.
echo --------------------------------------------------------
pause
exit /b 0

:atualizar_version
echo.
echo ========================================================
echo      ATUALIZAR ARQUIVO lib\VERSION
echo ========================================================
echo.
if exist "lib\VERSION" (
    set /p VERSAO_ATUAL=<lib\VERSION
    echo Versão atual: !VERSAO_ATUAL!
) else (
    echo Nenhuma versão encontrada.
)
echo.
set /p NOVA_VERSAO="Digite a nova versão: "

if "%NOVA_VERSAO%"=="" (
    echo [ERRO] Versão não pode estar vazia!
    pause
    exit /b 1
)

echo %NOVA_VERSAO%> lib\VERSION
echo.
echo Arquivo lib\VERSION atualizado para: %NOVA_VERSAO%
echo.
pause
exit /b 0

:opcao_invalida
echo.
echo [ERRO] Opção inválida!
pause
exit /b 1

:sair
exit /b 0
