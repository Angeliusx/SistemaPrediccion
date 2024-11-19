import numpy as np
from sklearn.linear_model import LinearRegression

# Supongamos que los registros por mes están organizados de esta manera:
# Lista con el número de registros por mes para un usuario específico
registros = [1,2]  # Ejemplo simple

# Convertimos las secuencias de tiempo en vectores de entrada y salida para la regresión
X = np.array([i for i in range(1, len(registros) + 1)]).reshape(-1, 1)  # Meses
y = np.array(registros)  # Registros

# Creamos el modelo de regresión lineal
modelo = LinearRegression()
modelo.fit(X, y)

# Predicción para el siguiente mes (mes siguiente al último del dataset)
siguiente_mes = np.array([[len(registros) + 1]])
prediccion = modelo.predict(siguiente_mes)

print(f"Predicción para el siguiente mes: {prediccion[0]}")
