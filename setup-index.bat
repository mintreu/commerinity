@echo off
echo ====================================
echo Setup Intelligent Index MCP Server
echo ====================================
echo.

REM Check if already setup
if exist "C:\MCP_Servers\intelligent_index\venv\Scripts\python.exe" (
    echo [OK] MCP Server already setup
    echo.
    goto :test
)

echo Setting up MCP server...
cd C:\MCP_Servers\intelligent_index
call setup.bat

:test
echo.
echo Testing MCP server...
C:\MCP_Servers\intelligent_index\venv\Scripts\python.exe --version
if errorlevel 1 (
    echo [ERROR] Python not found. Please install Python 3.8+
    pause
    exit /b 1
)

echo.
echo ====================================
echo [SUCCESS] MCP Server Ready!
echo ====================================
echo.
echo Server configured in .mcp.json
echo Restart Claude Code to activate
echo.
pause
