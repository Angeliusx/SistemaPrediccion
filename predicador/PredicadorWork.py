from flask import Flask, request, jsonify
import numpy as np
from sklearn.linear_model import LinearRegression

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    registros = data['registros']

    X = np.array([i for i in range(1, len(registros) + 1)]).reshape(-1, 1)
    y = np.array(registros)

    modelo = LinearRegression()
    modelo.fit(X, y)  

    siguiente_mes = np.array([[len(registros) + 1]])
    prediccion = modelo.predict(siguiente_mes)

    return jsonify({'prediccion': prediccion[0]})

if __name__ == '__main__':
    app.run(debug=True)
