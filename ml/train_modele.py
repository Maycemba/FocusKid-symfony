# train_modele.py
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
import xgboost as xgb
import joblib

# 1. Charger ton fichier
df = pd.read_csv('training_data2.csv', sep=';')

print(f"✅ Chargé : {len(df)} lignes")
print(df.head())

# 2. Normaliser le score (pour qu'il soit entre 0 et 100)
from sklearn.preprocessing import MinMaxScaler
scaler_score = MinMaxScaler(feature_range=(0, 100))
df['score_normalise'] = scaler_score.fit_transform(df[['score']])

# 3. Features (sans age !)
features = ['difficulte', 'type_exercice', 'score_normalise', 'temps_passe']
X = df[features]
y = df['reussite']

# 4. Entraînement
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)

modele = xgb.XGBClassifier(n_estimators=100, max_depth=4, random_state=42)
modele.fit(X_train_scaled, y_train)

# 5. Sauvegarder
joblib.dump(modele, 'modele.pkl')
joblib.dump(scaler, 'scaler.pkl')
joblib.dump(scaler_score, 'scaler_score.pkl')

print("✅ Modèle sauvegardé !")