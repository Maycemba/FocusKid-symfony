"""
test_emotion.py
Test rapide pour vérifier que le modèle reconnaît correctement les émotions
Utilise la webcam et affiche les prédictions en temps réel
"""

import cv2
import numpy as np
from tensorflow import keras
import json

# Chargement du modèle
MODEL_PATH = "model/emotion_model.keras"
LABELS_PATH = "model/labels.json"

print("Chargement du modèle...")
model = keras.models.load_model(MODEL_PATH, compile=False)
with open(LABELS_PATH) as f:
    emotions = json.load(f)["emotions"]

print(f"Modèle chargé. Émotions: {emotions}")
print("Appuyez sur 'q' pour quitter")

# Webcam
cap = cv2.VideoCapture(0)
cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")

while True:
    ret, frame = cap.read()
    if not ret:
        break
    
    frame = cv2.flip(frame, 1)
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    faces = cascade.detectMultiScale(gray, 1.1, 5, minSize=(48, 48))
    
    for (x, y, w, h) in faces[:1]:
        # Extraire et prétraiter le visage
        face = frame[y:y+h, x:x+w]
        face_gray = cv2.cvtColor(face, cv2.COLOR_BGR2GRAY)
        face_resized = cv2.resize(face_gray, (48, 48))
        
        # Prédiction
        inp = face_resized.astype(np.float32) / 255.0
        inp = np.expand_dims(inp, axis=(0, -1))  # (1, 48, 48, 1)
        
        probs = model.predict(inp, verbose=0)[0]
        emotion = emotions[np.argmax(probs)]
        confidence = max(probs) * 100
        
        # Affichage
        color = (0, 255, 0) if confidence > 50 else (0, 165, 255)
        cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
        
        label = f"{emotion}: {confidence:.1f}%"
        cv2.putText(frame, label, (x, y-10), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.6, color, 2)
        
        # Afficher les probabilités
        y_offset = y + h + 20
        for i, (em, prob) in enumerate(zip(emotions, probs)):
            if prob > 0.05:  # Afficher seulement les probas > 5%
                bar_len = int(prob * 100)
                cv2.rectangle(frame, (x, y_offset + i*15), 
                             (x + bar_len, y_offset + i*15 + 8), 
                             (100, 100, 100), -1)
                cv2.putText(frame, f"{em}: {prob*100:.1f}%", 
                           (x + bar_len + 5, y_offset + i*15 + 8),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.35, (200, 200, 200), 1)
    
    cv2.putText(frame, "Appuyez sur 'q' pour quitter", (10, 30),
               cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 1)
    cv2.imshow("Test Emotion", frame)
    
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()