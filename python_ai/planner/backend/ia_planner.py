"""
IA Planner - Génère le planning optimisé pour enfant TDAH
"""

import json
import os
from anthropic import Anthropic
from dotenv import load_dotenv

load_dotenv()

class IAPlanner:
    def __init__(self):
        self.client = Anthropic(api_key=os.getenv("ANTHROPIC_API_KEY"))
        
    def generate_weekly_plan(self, profil_enfant, contraintes_parent, taches_list):
        """
        Génère un planning hebdomadaire complet
        
        Args:
            profil_enfant: dict avec profil cognitif
            contraintes_parent: dict avec école, thérapie, activités
            taches_list: list des tâches à accomplir
        
        Returns:
            dict: Planning JSON structuré
        """
        
        system_prompt = """Tu es un expert en TDAH infantile et en organisation cognitive.
Tu génères des plannings hebdomadaires adaptés aux enfants TDAH.

RÈGLES OBLIGATOIRES (à appliquer systématiquement):
1. Placer les tâches cognitives difficiles entre {pic_debut} et {pic_fin} (pic d'attention)
2. Insérer une pause de 10-15 min après chaque 30-40 min d'activité soutenue
3. Intercaler une activité physique ou créative après chaque tâche cognitive difficile
4. Terminer chaque journée scolaire par une routine calme et prévisible (lecture, dessin, relaxation)
5. Ne jamais placer 2 tâches difficiles consécutives
6. Respecter TOUTES les contraintes fixes (école, thérapie, activités)
7. Limiter le temps d'écran à 30 min maximum par session
8. Proposer des micro-pauses (2-3 min) toutes les 15-20 min pour les enfants très agités

Réponds UNIQUEMENT en JSON valide, SANS texte autour."""

        # Construction du prompt utilisateur
        user_prompt = f"""
Voici le profil de l'enfant :
- Âge : {profil_enfant.get('age', 8)} ans
- Niveau de concentration moyen : {profil_enfant.get('niveau_concentration_moyen', 3)}/5
- Niveau d'agitation moyen : {profil_enfant.get('niveau_agitation_moyen', 2.5)}/5
- Temps max de concentration : {profil_enfant.get('temps_concentration_max_minutes', 35)} minutes
- Pic d'attention : {profil_enfant.get('pic_attention_debut', '08:00')} - {profil_enfant.get('pic_attention_fin', '11:00')}
- Meilleure matière : {profil_enfant.get('meilleure_matiere', 'Lecture')}
- Matières difficiles : {', '.join(profil_enfant.get('matieres_difficiles', []))}

Contraintes de la semaine :
{json.dumps(contraintes_parent, indent=2, ensure_ascii=False)}

Tâches à programmer :
{json.dumps(taches_list, indent=2, ensure_ascii=False)}

Génère un planning pour la semaine du lundi au dimanche.
Pour chaque jour, indique les créneaux de 30 minutes à 1h30 maximum.
Chaque créneau doit avoir : heure, durée_min, type (école/tache_difficile/pause/activite_calme/activite_physique/routine), titre, conseil_ia.
"""

        # Remplacer les variables dans le system prompt
        system_prompt_filled = system_prompt.format(
            pic_debut=profil_enfant.get('pic_attention_debut', '08:00'),
            pic_fin=profil_enfant.get('pic_attention_fin', '11:00')
        )

        try:
            response = self.client.messages.create(
                model="claude-3-sonnet-20241022",
                max_tokens=4096,
                temperature=0.7,
                system=system_prompt_filled,
                messages=[
                    {"role": "user", "content": user_prompt}
                ]
            )
            
            # Extraire le JSON de la réponse
            response_text = response.content[0].text
            # Nettoyer au cas où il y aurait du texte autour
            json_start = response_text.find('{')
            json_end = response_text.rfind('}') + 1
            if json_start != -1 and json_end > json_start:
                json_str = response_text[json_start:json_end]
                planning = json.loads(json_str)
                return planning
            else:
                # Format de secours
                return self._get_fallback_planning(profil_enfant, contraintes_parent)
                
        except Exception as e:
            print(f"Erreur API: {e}")
            return self._get_fallback_planning(profil_enfant, contraintes_parent)
    
    def _get_fallback_planning(self, profil_enfant, contraintes_parent):
        """Planning de secours si l'API échoue"""
        jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"]
        planning = {"semaine": []}
        
        for jour in jours:
            creneaux = []
            
            # École pour les jours de semaine
            if jour in ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"]:
                if "ecole" in contraintes_parent:
                    creneaux.append({
                        "heure": "08:30",
                        "duree_min": 360,
                        "type": "ecole",
                        "titre": "École",
                        "conseil_ia": "Moment d'apprentissage structuré. Assurez-vous qu'il a bien dormi."
                    })
            
            # Tâche difficile pendant le pic d'attention
            creneaux.append({
                "heure": profil_enfant.get('pic_attention_debut', '09:00'),
                "duree_min": 45,
                "type": "tache_difficile",
                "titre": f"Session {profil_enfant.get('meilleure_matiere', 'étude')}",
                "conseil_ia": f"Moment idéal selon son profil (pic d'attention). Utilisez des techniques de pomodoro."
            })
            
            # Pause
            creneaux.append({
                "heure": "10:00",
                "duree_min": 15,
                "type": "pause",
                "titre": "Pause active",
                "conseil_ia": "Laissez-le bouger, faire des étirements ou boire de l'eau."
            })
            
            planning["semaine"].append({
                "jour": jour,
                "date": "2025-01-01",
                "creneaux": creneaux
            })
        
        return planning
    
    def get_weekly_plan_as_text(self, planning_json):
        """Convertit le planning JSON en texte formaté pour affichage"""
        if not planning_json or "semaine" not in planning_json:
            return "Erreur: Planning non disponible"
        
        output = "📅 PLANNING DE LA SEMAINE\n"
        output += "=" * 50 + "\n\n"
        
        for day in planning_json["semaine"]:
            output += f"📌 {day['jour']} ({day.get('date', '')})\n"
            output += "-" * 30 + "\n"
            
            for slot in day.get("creneaux", []):
                emoji = self._get_type_emoji(slot.get("type", ""))
                output += f"{emoji} {slot['heure']} - {slot['titre']} ({slot['duree_min']} min)\n"
                output += f"   💡 {slot.get('conseil_ia', '')}\n\n"
            
            output += "\n"
        
        return output
    
    def _get_type_emoji(self, type_slot):
        emojis = {
            "ecole": "🏫",
            "tache_difficile": "📚",
            "pause": "🧘",
            "activite_calme": "🎨",
            "activite_physique": "🏃",
            "routine": "🌙"
        }
        return emojis.get(type_slot, "📋")