"""
Profile Builder - Construit le profil cognitif de l'enfant à partir des données
"""

class ProfileBuilder:
    @staticmethod
    def build_profile_from_carnet(carnet_data_list):
        """
        Construit un profil IA à partir des carnets éducatifs
        carnet_data_list: liste de dictionnaires avec les champs du CarnetEducatif
        """
        if not carnet_data_list:
            return ProfileBuilder.get_default_profile()
        
        # Calcul des moyennes
        total_concentration = 0
        total_agitation = 0
        total_autonomie = 0
        total_interruptions = 0
        temps_moyen_concentration = []
        
        for entry in carnet_data_list:
            total_concentration += entry.get('niveau_concentration', 3)
            total_agitation += entry.get('niveau_agitation', 3)
            total_autonomie += entry.get('niveau_autonomie', 3)
            total_interruptions += entry.get('nombre_interruptions', 5)
            
            temps = entry.get('temps_avant_perte_concentration')
            if temps:
                temps_moyen_concentration.append(temps)
        
        nb_entries = len(carnet_data_list)
        
        profil = {
            "age": 8,  # À adapter selon ton entité Utilisateur
            "niveau_concentration_moyen": round(total_concentration / nb_entries, 1),
            "niveau_agitation_moyen": round(total_agitation / nb_entries, 1),
            "niveau_autonomie_moyen": round(total_autonomie / nb_entries, 1),
            "interruptions_moyennes": round(total_interruptions / nb_entries, 1),
            "temps_concentration_max_minutes": max(temps_moyen_concentration) if temps_moyen_concentration else 30,
            "pic_attention_debut": "08:00",
            "pic_attention_fin": "11:00",
            "meilleure_matiere": ProfileBuilder._get_best_matiere(carnet_data_list),
            "matieres_difficiles": ProfileBuilder._get_difficult_matieres(carnet_data_list)
        }
        
        return profil
    
    @staticmethod
    def _get_best_matiere(carnet_data_list):
        """Détermine la matière où l'enfant a le mieux réussi"""
        matieres_scores = {}
        for entry in carnet_data_list:
            matiere = entry.get('matiere', '')
            if matiere:
                score = entry.get('niveau_concentration', 3) - entry.get('niveau_agitation', 3)
                if matiere not in matieres_scores:
                    matieres_scores[matiere] = []
                matieres_scores[matiere].append(score)
        
        if matieres_scores:
            best = max(matieres_scores.items(), key=lambda x: sum(x[1])/len(x[1]))
            return best[0]
        return "Non déterminé"
    
    @staticmethod
    def _get_difficult_matieres(carnet_data_list):
        """Détermine les matières difficiles"""
        matieres_scores = {}
        for entry in carnet_data_list:
            matiere = entry.get('matiere', '')
            if matiere:
                niveau = entry.get('niveau_difficulte', 'Moyen')
                if niveau == 'Difficile':
                    if matiere not in matieres_scores:
                        matieres_scores[matiere] = 0
                    matieres_scores[matiere] += 1
        
        difficultes = [m for m, count in matieres_scores.items() if count > 0]
        return difficultes if difficultes else ["Aucune matière particulièrement difficile"]
    
    @staticmethod
    def get_default_profile():
        """Profil par défaut si aucune donnée n'existe"""
        return {
            "age": 8,
            "niveau_concentration_moyen": 3.0,
            "niveau_agitation_moyen": 2.5,
            "niveau_autonomie_moyen": 3.0,
            "interruptions_moyennes": 4,
            "temps_concentration_max_minutes": 35,
            "pic_attention_debut": "08:00",
            "pic_attention_fin": "11:00",
            "meilleure_matiere": "Lecture",
            "matieres_difficiles": ["Mathématiques", "Écriture"]
        }