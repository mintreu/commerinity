@echo off
echo ====================================
echo Start Intelligent Index MCP Server
echo ====================================
echo.

REM Check if setup
if not exist "C:\MCP_Servers\intelligent_index\venv\Scripts\python.exe" (
    echo [ERROR] MCP Server not setup yet!
    echo Run: setup-index.bat first
    echo.
    pause
    exit /b 1
)

echo Starting MCP server...
echo.
echo Server will run in this window
echo Press Ctrl+C to stop
echo.

REM Set environment variables
set DATA_DIR=C:\MCP_Servers\data
set CURRENT_PROJECT=commerinity_pro

REM Start server
C:\MCP_Servers\intelligent_index\venv\Scripts\python.exe C:\MCP_Servers\intelligent_index\server.py
