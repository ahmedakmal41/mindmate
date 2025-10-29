#!/bin/bash
# Startup script for Python 3.9 on Azure App Service

# Use Python 3.9
export PYTHONPATH=/home/site/wwwroot
cd /home/site/wwwroot

# Install dependencies if not already installed
if [ ! -f "/home/site/wwwroot/.dependencies_installed" ]; then
    python3.9 -m pip install -r requirements.txt
    touch /home/site/wwwroot/.dependencies_installed
fi

# Start the Flask app
python3.9 app.py
