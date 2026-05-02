"""
PARTIE 3 : DÉTECTION TEMPS RÉEL (WEBCAM)
Utilise OpenCV + Haar Cascades pour détecter les visages
et prédit l'émotion + score de stress en temps réel.
CORRIGÉ : Utilise le grayscale comme le dataset FER2013
"""

import os, json, sys, time
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"

import cv2
import numpy as np
import tensorflow as tf
from tensorflow import keras
from collections import deque

# ─────────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────────
MODEL_PATH    = "model/emotion_model.keras"
LABELS_PATH   = "model/labels.json"
CASCADE_PATH  = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
CAMERA_INDEX  = 0
IMG_SIZE      = (48, 48)
SMOOTH_FRAMES = 5          # fenêtre de lissage des prédictions

# Couleurs par émotion (BGR)
EMOTION_COLORS = {
    "happy":    (0,   220, 100),
    "neutral":  (180, 180, 180),
    "sad":      (200, 100,  50),
    "angry":    (0,    50, 220),
    "fear":     (180,  50, 200),
    "surprise": (0,   200, 220),
}

# ─────────────────────────────────────────────
# CHARGEMENT MODÈLE + LABELS
# ─────────────────────────────────────────────
def load_model_and_labels():
    if not os.path.exists(MODEL_PATH):
        print(f"[ERREUR] Modèle introuvable : {MODEL_PATH}")
        print("  → Lancez d'abord : python train_model.py")
        sys.exit(1)

    print("[…] Chargement du modèle…")
    model = keras.models.load_model(MODEL_PATH, compile=False)

    with open(LABELS_PATH) as f:
        meta = json.load(f)
    emotions = meta["emotions"]
    stress_scores = meta["stress_scores"]

    print(f"[✓] Modèle chargé | Classes : {emotions}")
    return model, emotions, stress_scores

# ─────────────────────────────────────────────
# SCORE DE STRESS PONDÉRÉ
# ─────────────────────────────────────────────
def compute_stress(probabilities, emotions, stress_scores):
    return int(round(
        sum(stress_scores[emotions[i]] * float(probabilities[i])
            for i in range(len(emotions)))
    ))

# ─────────────────────────────────────────────
# PRÉTRAITEMENT VISAGE (CORRIGÉ POUR GRAYSCALE)
# ─────────────────────────────────────────────
def preprocess_face(face_bgr):
    """
    Reçoit un visage en BGR (couleur) et le convertit en grayscale
    pour correspondre au dataset FER2013.
    """
    # 1. Convertir en grayscale (comme FER2013)
    face_gray = cv2.cvtColor(face_bgr, cv2.COLOR_BGR2GRAY)
    
    # 2. Redimensionner à 48x48
    face_resized = cv2.resize(face_gray, IMG_SIZE)
    
    # 3. Normalisation : pixel values between 0 and 1
    arr = face_resized.astype(np.float32) / 255.0
    
    # 4. Ajouter la dimension du canal (car le modèle attend (48,48,1) ou (48,48,3))
    #    Notre modèle a été entraîné avec color_mode='grayscale' donc 1 canal
    arr = np.expand_dims(arr, axis=-1)  # (48,48) → (48,48,1)
    
    # 5. Ajouter la dimension batch
    return np.expand_dims(arr, axis=0)  # (1, 48, 48, 1)

# ─────────────────────────────────────────────
# DESSIN HUD
# ─────────────────────────────────────────────
def draw_hud(frame, x, y, w, h, emotion, stress, probabilities, emotions, color):
    # Rectangle visage
    cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)

    # Fond label
    label_bg_y = y - 35
    cv2.rectangle(frame, (x, label_bg_y), (x+w, y), color, -1)

    # Texte émotion + stress
    label = f"{emotion.upper()}  stress:{stress}%"
    cv2.putText(frame, label, (x+5, y-10),
                cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255,255,255), 2)

    # Barre de stress (en bas du cadre)
    bar_x, bar_y = 15, frame.shape[0] - 20
    bar_w = frame.shape[1] - 30
    cv2.rectangle(frame, (bar_x, bar_y), (bar_x + bar_w, bar_y + 12), (60,60,60), -1)
    fill = int(bar_w * stress / 100)
    # Dégradé couleur selon niveau
    if stress < 30:
        bar_color = (0, 200, 80)
    elif stress < 60:
        bar_color = (0, 165, 255)
    else:
        bar_color = (0, 50, 220)
    cv2.rectangle(frame, (bar_x, bar_y), (bar_x + fill, bar_y + 12), bar_color, -1)
    cv2.putText(frame, f"STRESS: {stress}/100", (bar_x, bar_y - 5),
                cv2.FONT_HERSHEY_SIMPLEX, 0.45, (200,200,200), 1)

    # Mini barres de probabilité (coin supérieur gauche)
    panel_x, panel_y = 10, 10
    cv2.rectangle(frame, (panel_x-5, panel_y-5),
                  (panel_x + 140, panel_y + len(emotions)*22 + 5),
                  (30,30,30), -1)
    for i, em in enumerate(emotions):
        prob = float(probabilities[i])
        py = panel_y + i * 22
        cv2.putText(frame, f"{em:<9}", (panel_x, py+14),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.38, (200,200,200), 1)
        max_w = 55
        bar_len = int(prob * max_w)
        c = EMOTION_COLORS.get(em, (150,150,150))
        cv2.rectangle(frame, (panel_x+72, py+4), (panel_x+72+bar_len, py+16), c, -1)
        cv2.putText(frame, f"{prob*100:4.0f}%", (panel_x + 130, py+14),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.35, (200,200,200), 1)

    return frame

# ─────────────────────────────────────────────
# BOUCLE PRINCIPALE
# ─────────────────────────────────────────────
def run():
    model, emotions, stress_scores = load_model_and_labels()
    face_cascade = cv2.CascadeClassifier(CASCADE_PATH)

    cap = cv2.VideoCapture(CAMERA_INDEX)
    if not cap.isOpened():
        print(f"[ERREUR] Impossible d'ouvrir la caméra index={CAMERA_INDEX}")
        sys.exit(1)

    cap.set(cv2.CAP_PROP_FRAME_WIDTH,  640)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)

    # Buffer de lissage
    pred_buffer = deque(maxlen=SMOOTH_FRAMES)
    fps_counter = 0
    fps_start   = time.time()
    current_fps = 0

    print("\n[✓] Détection démarrée | Appuyez sur 'q' pour quitter")
    print("    's' → sauvegarder screenshot | 'r' → réinitialiser buffer\n")

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        frame = cv2.flip(frame, 1)   # miroir
        gray  = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        # Détection visages
        faces = face_cascade.detectMultiScale(
            gray, scaleFactor=1.1, minNeighbors=5, minSize=(48,48)
        )

        # FPS
        fps_counter += 1
        if fps_counter >= 10:
            current_fps  = fps_counter / (time.time() - fps_start)
            fps_counter  = 0
            fps_start    = time.time()
        cv2.putText(frame, f"FPS:{current_fps:.0f}", (frame.shape[1]-80, 20),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.5, (150,150,150), 1)

        for (x, y, w, h) in faces[:1]:   # traiter uniquement le premier visage
            # Extraire le visage en COULEUR (pour l'affichage)
            face_color = frame[y:y+h, x:x+w]
            
            # Prétraitement : conversion en grayscale automatique
            inp = preprocess_face(face_color)

            probs = model.predict(inp, verbose=0)[0]
            pred_buffer.append(probs)

            # Moyenne glissante
            avg_probs = np.mean(pred_buffer, axis=0)
            emotion   = emotions[np.argmax(avg_probs)]
            stress    = compute_stress(avg_probs, emotions, stress_scores)
            color     = EMOTION_COLORS.get(emotion, (150,150,150))

            frame = draw_hud(frame, x, y, w, h, emotion, stress, avg_probs, emotions, color)

        if len(faces) == 0:
            cv2.putText(frame, "Aucun visage détecté", (10, frame.shape[0]//2),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.7, (100,100,100), 2)

        cv2.imshow("FocusKid — Emotion Detector", frame)

        key = cv2.waitKey(1) & 0xFF
        if key == ord("q"):
            break
        elif key == ord("s"):
            fname = f"screenshot_{int(time.time())}.jpg"
            cv2.imwrite(fname, frame)
            print(f"[✓] Screenshot sauvegardé : {fname}")
        elif key == ord("r"):
            pred_buffer.clear()
            print("[✓] Buffer réinitialisé")

    cap.release()
    cv2.destroyAllWindows()
    print("[✓] Détection arrêtée.")

if __name__ == "__main__":
    run()