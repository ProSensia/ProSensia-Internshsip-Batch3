# 🤖 AI/ML Engineering Squad — Week 2, Day 4 (Thursday)
**Title:** Model Deployment API — Flask REST Endpoint for Trained ML Models
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: Model Serving & Integration

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Patrick Loeber — Deploy ML Models with Flask https://www.youtube.com/watch?v=UbCWoMf80PY
- **YouTube:** Krish Naik — Complete ML Model Deployment https://www.youtube.com/watch?v=ipFUANeStYE
- **YouTube:** Sentdex — Flask Web Framework Tutorial https://www.youtube.com/watch?v=MwZwr5Tvyxo

### 2. Required Technical Documentation
- **Primary:** Flask Quickstart Guide https://flask.palletsprojects.com/en/3.0.x/quickstart/
- **Secondary:** joblib — Model Persistence https://joblib.readthedocs.io/en/latest/persistence.html
- **Reference:** Postman — Testing REST APIs https://learning.postman.com/docs/getting-started/overview/

### 3. Engineering Assets
- **Models:** Your .pkl files from Day 3 (RandomForest + LogisticRegression)
- **Dataset:** Your Day 2 engineered CSV (for test input shapes)
- **New:** requirements.txt must list flask, joblib, scikit-learn, numpy, pandas

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Wrap your best-performing Day 3 .pkl model in a Flask REST API. Expose a POST /predict endpoint that accepts JSON feature inputs and returns a prediction with confidence score. Test the endpoint with Postman.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Understand the request/response cycle for ML APIs: JSON in → model.predict() → JSON out. Note how joblib.load() works and why you should load the model once at startup, not per request.

10:00 AM - 1:00 PM (Building):
Action 1 (API Setup): Create app.py. Load your best model with joblib.load() at module level (not inside the route). Create a /health GET endpoint returning {"status": "ok", "model": "RandomForest"}. Verify it works.
Action 2 (Prediction Endpoint): Create POST /predict that accepts JSON body {"features": [1.2, 3.4, ...]}. Validate that features length matches your model's expected input shape. Run model.predict() and model.predict_proba(). Return {"prediction": 0, "confidence": 0.87, "model": "RandomForest"}.
Action 3 (Error Handling + Testing): Add proper error responses: 400 for missing/malformed features, 500 for model errors. Use Postman to test: correct input, missing features key, wrong number of features, non-numeric values. Document each test case and response.

The Non-Negotiable Rules:
1. Model must be loaded at startup — never inside a route function (performance requirement).
2. All error responses must return proper HTTP status codes (400/422/500), not 200 with an error message.
3. Confidence score (predict_proba) must be included in every successful response.

1:00 PM - 1:30 PM (Hygiene): Add a requirements.txt. Test the app in a fresh venv by running pip install -r requirements.txt && python app.py. Confirm it starts with no import errors. Add a README section for API usage.

1:30 PM - 2:00 PM (LinkedIn): "Day 4 at ProSensia — wrapped my ML model in a Flask API. A model that can't be called via HTTP is a model that can't be used in production. Model serving is just as important as model accuracy."

---

## Section C: The Submission Protocol
1. **GitHub:** Push app.py, requirements.txt with commit: feat: add Flask prediction API for trained RF model
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #MLOps #Flask #MachineLearning
