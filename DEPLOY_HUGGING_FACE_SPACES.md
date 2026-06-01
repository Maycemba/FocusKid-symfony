# 🤖 Déployer l'API d'Émotions sur Hugging Face Spaces (GRATUIT)

## Pourquoi Hugging Face Spaces ?

- ✅ **GRATUIT** - Pas de limite de temps
- ✅ **Pas de carte de crédit** requise
- ✅ **Auto-scaling** - Démarre automatiquement
- ✅ **Intégration facile** - Simple d'appeler depuis Render

## 📋 Étapes

### 1. Créer un compte Hugging Face

Allez sur https://huggingface.co/join et créez un compte gratuit.

### 2. Générer un Token API

1. Allez sur https://huggingface.co/settings/tokens
2. Click "New token"
3. Donner un nom : `focuskid-api`
4. Type : `read` (ou `write`)
5. Copier le token (commence par `hf_...`)

### 3. Créer un Space

1. Allez sur https://huggingface.co/spaces
2. Click "Create new Space"
3. Remplir :
   - **Space name** : `focuskid-emotion-api` (ou votre nom)
   - **License** : Apache 2.0
   - **Select the Space SDK** : Docker
   - **Visibility** : Public (ou Private si vous voulez)

### 4. Uploader les fichiers

Cloner ou uploader les fichiers suivants dans votre Space :

```
focuskid-emotion-api/
├── app_hf_spaces.py          (main)
├── requirements.txt           (dépendances Python)
├── Dockerfile                 (optionnel si pas Gradio)
└── model/
    ├── emotion_model.keras
    ├── labels.json
    └── best_emotion_model.keras
```

#### Fichiers requis :

**Dockerfile** (si mode Docker) :
```dockerfile
FROM python:3.10-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Copier les fichiers du modèle
COPY model/ model/

# Gradio lance automatiquement sur port 7860
CMD ["python", "app_hf_spaces.py"]
```

**requirements.txt** :
```
gradio==4.30.0
tensorflow==2.15.0
opencv-python-headless==4.8.1.78
pillow==10.1.0
numpy==1.24.3
```

### 5. Vérifier que ça fonctionne

1. Attendre que le Space se build (~5 min)
2. Voir l'URL : `https://username-focuskid-emotion-api.hf.space`
3. Tester avec une image

### 6. Récupérer l'URL pour Render

Votre URL API sera : `https://username-focuskid-emotion-api.hf.space`

Dans Render Dashboard, ajouter :
```
EMOTION_API_URL=https://username-focuskid-emotion-api.hf.space
HUGGINGFACE_API_KEY=hf_xxxxxxxxxxxxx
```

## 🧪 Tester localement

```bash
# Cloner depuis HF (optionnel)
git clone https://huggingface.co/spaces/username/focuskid-emotion-api
cd focuskid-emotion-api

# Installer dépendances
pip install -r requirements.txt

# Lancer
python app_hf_spaces.py
```

## 📞 API Endpoints

### 1. Via Gradio (Interface Web)
```
https://username-focuskid-emotion-api.hf.space
```

### 2. Via API REST (si Gradio configuré)
```bash
curl -X POST https://username-focuskid-emotion-api.hf.space/api/predict \
  -H "Content-Type: application/json" \
  -d '{
    "data": ["base64_image_string"]
  }'
```

## 🔒 Sécurité

- ✅ Votre modèle est stocké sur les serveurs HF
- ✅ Token API = authentification
- ✅ Optionnel : rendre le Space **Private** pour plus de sécurité

## ⚠️ Limitations

| Limite | Valeur |
|--------|--------|
| Stockage | 15 GB |
| RAM | 16 GB (partagé) |
| GPU | Optionnel (payant) |
| Inactivité | Peut s'arrêter après quelques jours |

**Solution** : Si le Space s'arrête, HF le redémarrera à la première requête. Pas besoin de payer !

## 🚀 Appelé depuis Symfony

```php
// src/Service/EmotionDetectionService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmotionDetectionService
{
    public function __construct(private HttpClientInterface $httpClient) {}

    public function detectEmotion(string $base64Image): array
    {
        $apiUrl = $_ENV['EMOTION_API_URL'];
        
        $response = $this->httpClient->request('POST', "$apiUrl/api/predict", [
            'json' => [
                'data' => [$base64Image],
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['HUGGINGFACE_API_KEY'],
            ],
            'timeout' => 30,
        ]);
        
        $result = $response->toArray();
        return $result['data'][0] ?? [];
    }
}
```

---

**Une fois déployé sur HF Spaces, vous pouvez l'utiliser GRATUITEMENT avec Render ! 🎉**
