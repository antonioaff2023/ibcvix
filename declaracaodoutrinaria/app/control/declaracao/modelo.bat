@echo off
if "%1"=="" (
    set /p "nome=Digite o nome do arquivo (sem extensão .html): "
    if "%nome%"=="" (
        set nome=arquivo
    )
) else (
    set nome=%1
)

(
echo ^<body^>
echo.
echo     ^<h3^>^</h3^>
echo.
echo     ^<p^>
echo.
echo     ^</p^>

echo ^<br /^>
echo     ^<p^>
echo.
echo     ^</p^>
echo.
echo ^</body^>
) > "%nome%.html"

echo Arquivo HTML gerado com sucesso: "%nome%.html"
pause
