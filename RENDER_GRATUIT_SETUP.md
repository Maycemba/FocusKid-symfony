# 🚀 FocusKid - Déploiement Render GRATUIT

> **Note**: Pour rester dans le **plan gratuit de Render** (512MB RAM), vous devez utiliser une **API cloud** pour la détection d'émotions au lieu de TensorFlow local.

## 📋 Configuration Simplifiée

Votre projet inclut maintenant :
- ✅ **Application Symfony** (Rendu HTML/API REST)
- ✅ **Base PostgreSQL** managée
- ❌ **Service Python TensorFlow** (SUPPRIMÉ - trop lourd pour Render gratuit)

## 🔄 Options pour la Détection d'Émotions

Choisissez UNE des solutions suivantes :

### Option 1 : **Hugging Face Spaces** ⭐ (Recommandé)
**Gratuit, facile, pas de GPU nécessaire**

```bash
# 1. Créer un compte Hugging Face
# https://huggingface.co/join

# 2. Créer un Space
# https://huggingface.co/spaces
# - Sélectionner "Docker" template
# - Copier le contenu de python_ai/ dedans

# 3. Récupérer le token API
# https://huggingface.co/settings/tokens

# 4. Ajouter dans Render → Env Vars
HUGGINGFACE_API_KEY=hf_xxxxxxxxxxxxx
EMOTION_API_URL=https://username-emotion-model.hf.space
```

### Option 2 : **Replicate** (Alternative)
```bash
# 1. S'inscrire : https://replicate.com
# 2. Créer un modèle Docker
# 3. Token API dans Render env
REPLICATE_API_TOKEN=r8_xxxxxxxxxxxxxxxxxxxxxxxxx
```

### Option 3 : **AWS Rekognition** (Payant mais scalable)
```bash
# Détection d'émotions natif AWS
EMOTION_API_TYPE=aws-rekognition
AWS_ACCESS_KEY_ID=xxxxx
AWS_SECRET_ACCESS_KEY=xxxxx
```

## 📝 Modifier le Code Symfony

Mettez à jour votre contrôleur pour utiliser l'API cloud :

```php
// src/Controller/EmotionController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EmotionController extends AbstractController
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function detectEmotion(Request $request): JsonResponse
    {
        $imageData = $request->get('image');
        
        // Appel à Hugging Face Spaces
        $apiUrl = $_ENV['EMOTION_API_URL'] ?? 'https://your-space.hf.space';
        
        try {
            $response = $this->httpClient->request('POST', $apiUrl, [
                'json' => [
                    'image' => $imageData,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['HUGGINGFACE_API_KEY'],
                ],
            ]);
            
            $result = $response->toArray();
            
            return $this->json([
                'emotion' => $result['emotion'] ?? null,
                'confidence' => $result['confidence'] ?? null,
                'stress_level' => $this->computeStress($result['emotion'] ?? 'neutral'),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    private function computeStress(string $emotion): int
    {
        $stressMap = [
            'angry' => 90,
            'fear' => 85,
            'sad' => 65,
            'surprise' => 50,
            'neutral' => 20,
            'happy' => 5,
        ];
        
        return $stressMap[$emotion] ?? 20;
    }
}
```

## 🚀 Déployer sur Render

### 1. Pousser le code sur GitHub
```bash
git add .
git commit -m "Configure for Render free tier"
git push origin main
```

### 2. Créer un nouveau service Render
- Aller sur https://render.com
- Click "New +" → "Web Service"
- Connecter GitHub repo
- Sélectionner ce repo

### 3. Configuration Render
```
Build Command: npm run build
Start Command: (laissez vide, Apache démarre automatiquement)
Plan: Free
Dockerfile: Dockerfile.render-free
```

### 4. Ajouter les variables d'environnement
```
APP_ENV=prod
APP_SECRET=<généré>
DATABASE_URL=<auto-connecté>
HUGGINGFACE_API_KEY=<votre token>
EMOTION_API_URL=https://your-space.hf.space
```

### 5. Ajouter une base de données PostgreSQL
- Dans Render Dashboard
- "Create +" → "PostgreSQL"
- Plan: Free (256 MB)

## ⚠️ Limitations Render Gratuit

| Limite | Valeur | Votre Projet |
|--------|--------|-------------|
| RAM par service | 512 MB | ✅ OK (Symfony seul) |
| Stockage BD | 256 MB | ⚠️ Limité |
| Inactivité | 15 min → sleep | ⚠️ Peut arrêter |
| Build timeout | 45 min | ✅ OK |
| Bande passante | Illimitée | ✅ OK |

## 🔧 Tests Locaux

```bash
# Tester le Dockerfile optimisé
docker build -f Dockerfile.render-free -t focuskid:free .
docker run -p 80:80 focuskid:free

# Vérifier la taille
docker images | grep focuskid
```

## ✅ Checklist Avant Deploy

- [ ] `package.json` - `@symfony/stimulus-bundle` version OK
- [ ] `.env` - `DATABASE_URL` configurée
- [ ] `HUGGINGFACE_API_KEY` ou autre token API cloud ajoutée
- [ ] Tests locaux passants
- [ ] Git push complété

## 📞 Support

Si l'API cloud n'est pas accessible :
1. Vérifier le token API
2. Vérifier les logs Render : `render.com/dashboard`
3. Tester directement l'endpoint cloud

---

**Vous pouvez maintenant héberger GRATUITEMENT sur Render ! 🎉**
