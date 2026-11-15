#!/usr/bin/env bash
# Requiere Git y autenticación de GitHub configurada en tu computadora.
set -euo pipefail
cd "$(dirname "$0")"
git init
git branch -M main
git add .
git commit -m "Entrega final NIDO - Heyner Centeno" || true
git remote remove origin 2>/dev/null || true
git remote add origin https://github.com/centeno2/NIDO-Buscador-Inmobiliario-PHP.git
git push -u origin main
printf '\nProyecto publicado: https://github.com/centeno2/NIDO-Buscador-Inmobiliario-PHP\n'
