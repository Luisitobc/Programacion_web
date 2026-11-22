@echo off
title ClinAgenda - no cierre esta ventana
cd /d "%~dp0"
rem Si la carpeta cambio de lugar (otra memoria USB, otra letra de disco), XAMPP corrige sus rutas.
echo.| call runtime\setup_xampp.bat >nul
start "" /B runtime\xampp_start.exe
timeout /t 8 >nul
start "" http://localhost/clinagenda/
echo ClinAgenda esta abierto en el navegador.
echo Para apagarlo, ejecute DETENER.bat
