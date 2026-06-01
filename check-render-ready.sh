#!/bin/bash
# ============================================================================
# Script de vérification pre-deployment Render Gratuit
# ============================================================================

echo "🔍 Vérification de la configuration Render Gratuit..."
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0

# ============================================================================
# 1. Vérifier package.json
# ============================================================================
echo "📋 Vérification package.json..."
if grep -q "@symfony/stimulus-bundle.*2\." package.json; then
    echo -e "${GREEN}✓${NC} @symfony/stimulus-bundle version OK"
else
    echo -e "${RED}✗${NC} @symfony/stimulus-bundle version incorrecte"
    ERRORS=$((ERRORS + 1))
fi

# ============================================================================
# 2. Vérifier composer.json
# ============================================================================
echo ""
echo "📦 Vérification composer.json..."
if [ -f "composer.lock" ]; then
    echo -e "${GREEN}✓${NC} composer.lock existe"
else
    echo -e "${YELLOW}⚠${NC} composer.lock manquant (exécuter: composer install)"
    ERRORS=$((ERRORS + 1))
fi

# ============================================================================
# 3. Vérifier les fichiers Render
# ============================================================================
echo ""
echo "🚀 Vérification fichiers Render..."

if [ -f "Dockerfile.render-free" ]; then
    echo -e "${GREEN}✓${NC} Dockerfile.render-free existe"
else
    echo -e "${RED}✗${NC} Dockerfile.render-free manquant"
    ERRORS=$((ERRORS + 1))
fi

if [ -f "render-free.yaml" ]; then
    echo -e "${GREEN}✓${NC} render-free.yaml existe"
else
    echo -e "${RED}✗${NC} render-free.yaml manquant"
    ERRORS=$((ERRORS + 1))
fi

# ============================================================================
# 4. Vérifier dossier Python AI
# ============================================================================
echo ""
echo "🤖 Vérification Python AI..."

if [ -f "python_ai/app_hf_spaces.py" ]; then
    echo -e "${GREEN}✓${NC} app_hf_spaces.py existe (pour Hugging Face)"
else
    echo -e "${YELLOW}⚠${NC} app_hf_spaces.py manquant"
fi

if [ -f "python_ai/requirements_hf_spaces.txt" ]; then
    echo -e "${GREEN}✓${NC} requirements_hf_spaces.txt existe"
else
    echo -e "${YELLOW}⚠${NC} requirements_hf_spaces.txt manquant"
fi

# ============================================================================
# 5. Vérifier la taille du Dockerfile
# ============================================================================
echo ""
echo "📊 Vérification taille Dockerfile..."

if [ -f "Dockerfile" ]; then
    SIZE=$(wc -l < Dockerfile)
    if grep -q "tensorflow\|python:.*full" Dockerfile; then
        echo -e "${YELLOW}⚠${NC} Le Dockerfile principal contient TensorFlow"
        echo -e "   ${YELLOW}→ Utiliser Dockerfile.render-free à la place${NC}"
    fi
fi

# ============================================================================
# 6. Vérifier variables d'environnement
# ============================================================================
echo ""
echo "🔐 Checklist variables d'environnement..."
echo -e "   ${YELLOW}À configurer dans Render Dashboard:${NC}"
echo "   • APP_ENV=prod"
echo "   • APP_SECRET=<généré>"
echo "   • DATABASE_URL=<auto>"
echo "   • HUGGINGFACE_API_KEY=<votre token>"
echo "   • EMOTION_API_URL=https://your-space.hf.space"

# ============================================================================
# 7. Vérifier la structure du projet
# ============================================================================
echo ""
echo "📁 Vérification structure du projet..."

REQUIRED_FILES=("composer.json" "package.json" "config/services.yaml" "src/Kernel.php" "public/index.php")

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file manquant"
        ERRORS=$((ERRORS + 1))
    fi
done

# ============================================================================
# RÉSUMÉ
# ============================================================================
echo ""
echo "=================================="
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ Tout est prêt pour Render Gratuit!${NC}"
    echo ""
    echo "Prochaines étapes :"
    echo "1. Vérifier la documentation : RENDER_GRATUIT_SETUP.md"
    echo "2. S'inscrire sur Hugging Face"
    echo "3. Déployer le Space HF avec python_ai/"
    echo "4. Pousser le code sur GitHub"
    echo "5. Créer un service Render"
    echo ""
else
    echo -e "${RED}❌ $ERRORS erreur(s) détectée(s)${NC}"
    echo ""
    echo "Veuillez corriger avant le déploiement."
fi
echo "=================================="
