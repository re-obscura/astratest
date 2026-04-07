@echo off
title Task Manager — Dev Server
echo.
echo  ========================================
echo   Task Manager SPA — Starting...
echo  ========================================
echo.
echo  [1] PHP Backend  →  http://localhost:8000
echo  [2] Vite Frontend →  http://localhost:5173
echo.
echo  Открой http://localhost:8000 в браузере.
echo  Для остановки нажми Ctrl+C.
echo  ========================================
echo.

start /b cmd /c "C:\php\php.exe artisan serve --port=8000"
npm run dev
