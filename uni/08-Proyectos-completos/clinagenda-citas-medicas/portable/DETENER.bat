@echo off
title Apagando ClinAgenda
cd /d "%~dp0"
runtime\xampp_stop.exe
echo ClinAgenda se apago. Ya puede cerrar esta ventana.
timeout /t 4 >nul
