# FocusKid — Détection d'émotions & score de stress
# =====================================================
# PARTIE 7 : TOUTES LES COMMANDES TERMINAL
# =====================================================

# ─────────────────────────────────────────────────────
# STRUCTURE COMPLÈTE DES FICHIERS (PARTIE 6)
# ─────────────────────────────────────────────────────
# /project
# ├── python_ai/
# │   ├── prepare_dataset.py         ← Télécharge/génère le dataset
# │   ├── train_model.py             ← Entraîne le modèle CNN
# │   ├── realtime_detection.py      ← Détection webcam standalone
# │   ├── api.py                     ← API Flask locale
# │   ├── requirements.txt           ← Dépendances Python
# │   ├── model/                     ← Créé automatiquement
# │   │   ├── emotion_model.h5
# │   │   ├── labels.json
# │   │   └── training_curves.png
# │   └── dataset/                   ← Créé par prepare_dataset.py
# │       ├── train/
# │       │   ├── angry/ sad/ happy/ neutral/ fear/ surprise/
# │       └── test/
# │           ├── angry/ sad/ happy/ neutral/ fear/ surprise/
# │
# ├── src/
# │   └── Controller/
# │       └── StressController.php
# │
# ├── templates/
# │   └── stress/
# │       └── index.html.twig
# │
# └── public/
#     └── js/
#         └── camera.js


# ═══════════════════════════════════════════════════════
# ÉTAPE 1 — ENVIRONNEMENT PYTHON
# ═══════════════════════════════════════════════════════

# Créer et activer le venv
cd /chemin/vers/projet
python3 -m venv python_ai/.venv

# Linux / macOS
source python_ai/.venv/bin/activate

# Windows
# python_ai\.venv\Scripts\activate.bat

# Mettre à jour pip
pip install --upgrade pip


# ═══════════════════════════════════════════════════════
# ÉTAPE 2 — INSTALLER LES DÉPENDANCES PYTHON
# ═══════════════════════════════════════════════════════

pip install -r python_ai/requirements.txt

# Ou manuellement :
pip install tensorflow==2.15.0
pip install opencv-python==4.9.0.80
pip install flask==3.0.0
pip install flask-cors==4.0.0
pip install pillow==10.2.0
pip install numpy==1.26.3
pip install matplotlib==3.8.2
pip install tensorflow-datasets==4.9.4


# ═══════════════════════════════════════════════════════
# ÉTAPE 3 — PRÉPARER LE DATASET
# ═══════════════════════════════════════════════════════

cd python_ai

# OPTION A — Dataset synthétique (rapide, pour tester le pipeline)
python prepare_dataset.py --mode synthetic --n 500

# OPTION B — Dataset FER2013 réel (meilleure précision, ~400 MB)
python prepare_dataset.py --mode fer2013


# ═══════════════════════════════════════════════════════
# ÉTAPE 4 — ENTRAÎNER LE MODÈLE
# ═══════════════════════════════════════════════════════

python train_model.py

# Tester le modèle sur une image existante
python train_model.py --test dataset/test/happy/happy_00001.jpg

# Le modèle est sauvegardé dans :
#   model/emotion_model.h5
#   model/labels.json


# ═══════════════════════════════════════════════════════
# ÉTAPE 5 — LANCER LA DÉTECTION WEBCAM (standalone)
# ═══════════════════════════════════════════════════════

python realtime_detection.py
# Touches : q=quitter | s=screenshot | r=réinitialiser buffer


# ═══════════════════════════════════════════════════════
# ÉTAPE 6 — LANCER L'API FLASK
# ═══════════════════════════════════════════════════════

python api.py
# API disponible sur http://127.0.0.1:5001

# Mode debug
python api.py --debug

# Tester l'API (dans un autre terminal)
curl http://127.0.0.1:5001/health

# Test POST avec une image
curl -X POST http://127.0.0.1:5001/predict \
  -F "image=@dataset/test/happy/happy_00001.jpg"


# ═══════════════════════════════════════════════════════
# ÉTAPE 7 — INSTALLER SYMFONY (si pas déjà fait)
# ═══════════════════════════════════════════════════════

cd ..  # revenir à la racine du projet

# Installer les dépendances PHP (si vendor absent)
composer install

# Installer le composant HttpClient Symfony (requis)
composer require symfony/http-client

# Créer le fichier .env.local si besoin
cp .env .env.local

# Vérifier la config
php bin/console debug:router | grep stress


# ═══════════════════════════════════════════════════════
# ÉTAPE 8 — LANCER SYMFONY
# ═══════════════════════════════════════════════════════

# Via Symfony CLI (recommandé)
symfony server:start

# Via PHP built-in server
php -S 127.0.0.1:8000 -t public/

# Accéder à l'application
# http://localhost:8000/stress/


# ═══════════════════════════════════════════════════════
# RÉSUMÉ : LANCER LE PROJET COMPLET (2 terminaux)
# ═══════════════════════════════════════════════════════

# Terminal 1 — API Python
cd python_ai && source .venv/bin/activate && python api.py

# Terminal 2 — Symfony
symfony server:start
# Ouvrir : http://localhost:8000/stress/


# ═══════════════════════════════════════════════════════
# PARTIE 8 — OPTIMISATIONS & DÉPLOIEMENT
# ═══════════════════════════════════════════════════════

# ── Améliorer la précision ──────────────────────────
# 1. Utiliser FER2013 ou AffectNet (datasets réels annotés)
# 2. Augmenter les epochs (50 → 100) si pas d'overfitting
# 3. Essayer EfficientNetV2-S à la place de MobileNetV2
# 4. Ajouter BatchNormalization entre les couches denses
# 5. Data augmentation plus agressive (RandomBrightness, contrast)
# 6. Utiliser des poids pré-entraînés sur faces (VGGFace2)

# ── Accélérer le modèle ─────────────────────────────
# 1. Convertir en TFLite pour l'inférence :
#    python -c "
#    import tensorflow as tf
#    converter = tf.lite.TFLiteConverter.from_saved_model('model/emotion_model.h5')
#    converter.optimizations = [tf.lite.Optimize.DEFAULT]
#    tflite_model = converter.convert()
#    open('model/emotion_model.tflite', 'wb').write(tflite_model)"

# 2. Utiliser ONNX Runtime :
#    pip install tf2onnx onnxruntime
#    python -m tf2onnx.convert --keras model/emotion_model.h5 --output model/emotion_model.onnx

# 3. Réduire IMG_SIZE de 48→32 pour des GPUs modestes
# 4. Utiliser OpenCV DNN module au lieu de Keras pour l'inférence

# ── Déploiement production ──────────────────────────
# 1. API Python → gunicorn :
#    pip install gunicorn
#    gunicorn -w 2 -b 127.0.0.1:5001 api:app

# 2. Symfony → Apache/Nginx avec FPM
# 3. Superviser l'API Python avec supervisor ou systemd
# 4. Ajouter authentification à l'API Flask (token header)
# 5. Mettre en cache les prédictions côté Symfony (Redis)

# Exemple service systemd pour l'API Python :
# /etc/systemd/system/focuskid-api.service
# [Unit]
# Description=FocusKid Python AI API
# After=network.target
# [Service]
# User=www-data
# WorkingDirectory=/var/www/focuskid/python_ai
# ExecStart=/var/www/focuskid/python_ai/.venv/bin/gunicorn -w 2 -b 127.0.0.1:5001 api:app
# Restart=always
# [Install]
# WantedBy=multi-user.target
