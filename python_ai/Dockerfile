FROM python:3.10-slim

WORKDIR /app


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
