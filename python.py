# Import libraries
import csv
import numpy as np

from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, confusion_matrix, classification_report

# Load dataset using csv module
filename = "creditcard.csv"

X = []
y = []

with open(filename, 'r') as file:
    reader = csv.reader(file)
    header = next(reader)  # skip header
    
    for row in reader:
        # Convert all values to float
        row = list(map(float, row))
        
        # Last column is target (Class)
        X.append(row[:-1])
        y.append(row[-1])

# Convert to numpy arrays
X = np.array(X)
y = np.array(y)

# Normalize 'Amount' column (last feature before Class)
scaler = StandardScaler()
X[:, -1] = scaler.fit_transform(X[:, -1].reshape(-1, 1)).flatten()

# Split dataset
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

model = LogisticRegression(max_iter=1000)
model.fit(X_train, y_train)
y_pred = model.predict(X_test)

# Evaluation
print("Accuracy:", accuracy_score(y_test, y_pred))
print("\nConfusion Matrix:\n", confusion_matrix(y_test, y_pred))
print("\nClassification Report:\n", classification_report(y_test, y_pred))