FROM python:3.10-slim

WORKDIR /app

# Installer les dépendances système requises pour OpenCV
RUN apt-get update && apt-get install -y --no-install-recommends \
    libgl1-mesa-glx \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

# Copier le fichier des dépendances
COPY requirements.txt ./

# Installer les packages Python requis
RUN pip install --no-cache-dir -r requirements.txt

# Copier l'intégralité du sous-projet
COPY . .

# Exposer le port par défaut pour Render
EXPOSE 5001

# Lancer l'API Flask
CMD ["python", "api.py"]
