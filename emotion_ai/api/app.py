from flask import Flask, request, jsonify
import pickle
import json
import numpy as np
import random
import os

app = Flask(__name__)

# Déterminer le répertoire de l'API
api_dir = os.path.dirname(os.path.abspath(__file__))
model_dir = os.path.join(api_dir, "..", "model")

# Chargement du modèle
model_path = os.path.join(model_dir, "model.pkl")
with open(model_path, "rb") as f:
    data = pickle.load(f)
    model = data["model"]
    le = data["label_encoder"]

# Chargement des questions
questions_path = os.path.join(model_dir, "questions.json")
with open(questions_path, "r", encoding="utf-8") as f:
    all_questions = json.load(f)["questions"]


# --- Bilans par émotion ---
BILANS_ENFANT = {
    "serein": {
        "phrase": "Tu es en pleine forme aujourd'hui ! Continue comme ça, tu es fantastique ! 🌟",
        "emoji": "😊"
    },
    "anxieux": {
        "phrase": "C'est normal d'avoir des petits soucis parfois. Tu es courageux(se) et tu vas y arriver ! 💪",
        "emoji": "😅"
    },
    "triste": {
        "phrase": "On a tous des journées difficiles. Tu n'es pas seul(e), et demain ça ira mieux ! 🌈",
        "emoji": "💙"
    },
    "agite": {
        "phrase": "Tu as beaucoup d'énergie ! Essaie de respirer doucement... tu peux le faire ! 🌬️",
        "emoji": "⚡"
    },
    "en_colere": {
        "phrase": "C'est dur de gérer sa colère, mais tu es plus fort(e) qu'elle ! Respire et ça va passer. 🌸",
        "emoji": "🔥"
    }
}

BILANS_SUPERVISEUR = {
    "serein": {
        "titre": "État émotionnel : Serein",
        "description": "L'enfant présente un état émotionnel stable et positif.",
        "indicateurs": ["Bonne régulation émotionnelle", "Faible niveau de stress", "Bonne disposition à l'apprentissage"],
        "recommandations": [
            "Profiter de cet état pour introduire de nouveaux apprentissages",
            "Valoriser cet équilibre avec des retours positifs",
            "Maintenir la routine qui semble bien fonctionner"
        ],
        "alerte": False
    },
    "anxieux": {
        "titre": "État émotionnel : Anxieux",
        "description": "L'enfant montre des signes d'anxiété. Attention particulière recommandée.",
        "indicateurs": ["Tension émotionnelle perceptible", "Besoin de réassurance", "Possible difficulté de concentration"],
        "recommandations": [
            "Proposer des activités calmes et structurées",
            "Offrir un espace sécurisant et prévisible",
            "Éviter les situations de pression ou de compétition",
            "Techniques de respiration adaptées TDAH"
        ],
        "alerte": True
    },
    "triste": {
        "titre": "État émotionnel : Triste",
        "description": "L'enfant exprime une tristesse significative. Suivi rapproché conseillé.",
        "indicateurs": ["Baisse d'énergie émotionnelle", "Possible repli sur soi", "Risque de désengagement scolaire"],
        "recommandations": [
            "Prendre le temps d'écouter l'enfant sans jugement",
            "Alerter les parents si la tristesse persiste plus de 2 jours",
            "Proposer des activités artistiques ou créatives",
            "Consulter le psychologue scolaire si nécessaire"
        ],
        "alerte": True
    },
    "agite": {
        "titre": "État émotionnel : Agité",
        "description": "L'enfant présente une agitation importante, typique du TDAH en phase active.",
        "indicateurs": ["Hyperactivité motrice élevée", "Impulsivité accrue", "Difficulté à maintenir l'attention"],
        "recommandations": [
            "Permettre des pauses motrices régulières (5 min toutes les 20 min)",
            "Utiliser des outils sensoriels (balles anti-stress, coussins)",
            "Réduire les stimulations visuelles et sonores",
            "Tâches courtes avec récompenses immédiates"
        ],
        "alerte": False
    },
    "en_colere": {
        "titre": "État émotionnel : En colère",
        "description": "L'enfant est en état de colère. Intervention douce et immédiate recommandée.",
        "indicateurs": ["Risque de crise émotionnelle", "Faible tolérance à la frustration", "Possible comportement oppositionnel"],
        "recommandations": [
            "Ne pas confronter l'enfant directement — rester calme",
            "Proposer un coin calme ou un espace de décompression",
            "Identifier le déclencheur de la colère après apaisement",
            "Informer les parents en fin de journée",
            "Utiliser la communication non-violente (CNV)"
        ],
        "alerte": True
    }
}


@app.route("/quiz/generate", methods=["GET"])
def generate_quiz():
    theme = request.args.get("theme", None)  # ?theme=colere

    if theme:
        pool = [q for q in all_questions if q.get("theme") == theme]
        if not pool:
            return jsonify({"success": False, "error": f"Aucune question trouvée pour le thème '{theme}'"}), 404
    else:
        # 1 question par thème = quiz équilibré
        from collections import defaultdict
        by_theme = defaultdict(list)
        for q in all_questions:
            theme_key = q.get("theme")
            if theme_key:
                by_theme[theme_key].append(q)

        if by_theme:
            pool = [random.choice(qs) for qs in by_theme.values()]
        else:
            pool = all_questions[:]

    selected = random.sample(pool, min(5, len(pool)))
    return jsonify({"success": True, "questions": selected})

@app.route("/analyze", methods=["POST"])
def analyze():
    """
    Reçoit les réponses, prédit l'émotion, retourne les deux bilans.
    Body JSON attendu :
    {
        "child_id": "abc123",
        "answers": [
            {"question_id": 3, "score": 2},
            {"question_id": 7, "score": 4},
            ...  (5 réponses)
        ]
    }
    """
    body = request.json
    if not body or "answers" not in body:
        return jsonify({"error": "Données manquantes"}), 400

    answers = body["answers"]
    if len(answers) != 5:
        return jsonify({"error": "5 réponses attendues"}), 400

    # Extraction des scores
    scores = [a["score"] for a in answers]
    X = np.array(scores).reshape(1, -1)

    # Prédiction
    pred_enc = model.predict(X)[0]
    proba = model.predict_proba(X)[0]
    emotion = le.inverse_transform([pred_enc])[0]

    # Score de confiance
    confidence = round(float(max(proba)) * 100, 1)

    # Bilans
    bilan_enfant = BILANS_ENFANT[emotion]
    bilan_superviseur = BILANS_SUPERVISEUR[emotion]

    return jsonify({
        "success": True,
        "child_id": body.get("child_id", "inconnu"),
        "emotion": emotion,
        "confidence": confidence,
        "score_total": sum(scores),
        "bilan_enfant": bilan_enfant,
        "bilan_superviseur": bilan_superviseur
    })


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "model": "loaded"})


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)