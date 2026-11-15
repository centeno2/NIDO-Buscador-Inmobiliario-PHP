# Ejecutar dentro de PowerShell con Git instalado y autenticación GitHub configurada.
Set-Location $PSScriptRoot
git init
git branch -M main
git add .
git commit -m "Entrega final NIDO - Heyner Centeno"
if ((git remote) -contains "origin") { git remote remove origin }
git remote add origin https://github.com/centeno2/NIDO-Buscador-Inmobiliario-PHP.git
git push -u origin main
Write-Host "Proyecto: https://github.com/centeno2/NIDO-Buscador-Inmobiliario-PHP"
