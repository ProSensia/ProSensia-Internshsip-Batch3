# 🤖 AI/ML Engineering Squad — Week 2, Day 3 (Wednesday)
**Title:** Scikit-Learn Model Training & Cross-Validation Pipeline
**Progress:** 10% · Week 2 of 3-Month Bootcamp · Focus: Supervised Learning

---

## Section A: Today's Materials (The Synthesis Mandate)
You must synthesise all sources below. Do not consult outside tutorials.

### 1. Video Drills (Watch These First)
- **YouTube:** StatQuest — Decision Trees, Clearly Explained! https://www.youtube.com/watch?v=7VeUPuFGJHk
- **YouTube:** StatQuest — Random Forests https://www.youtube.com/watch?v=J4Wdy0Wc_xQ
- **YouTube:** Sentdex — Scikit-learn Machine Learning Pipeline https://www.youtube.com/watch?v=pqNCD_5r0IU
- **Scrimba:** Intro to AI Engineering — "Building Your First Model" https://v2.scrimba.com/intro-to-ai-c01nf3

### 2. Required Technical Documentation
- **Primary:** Scikit-learn User Guide — Supervised Learning https://scikit-learn.org/stable/supervised_learning.html
- **Secondary:** Scikit-learn — train_test_split & Cross-Validation https://scikit-learn.org/stable/modules/cross_validation.html
- **Metrics:** Scikit-learn — Classification Metrics (precision, recall, F1) https://scikit-learn.org/stable/modules/model_evaluation.html

### 3. Engineering Assets
- **Dataset:** Continue using your Week 2 Day 2 engineered dataset (.csv from feature engineering)
- **Template:** Scikit-learn Pipeline boilerplate (use AI assistant to scaffold the Pipeline class)

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Train two supervised ML models (Random Forest + Logistic Regression) on your engineered dataset, evaluate with cross-validation, generate a confusion matrix, and compare performance using precision, recall, and F1 scores.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch both StatQuest videos in full. Skim the scikit-learn supervised learning and cross-validation docs. Note the difference between fit/predict and Pipeline.

10:00 AM - 1:00 PM (Building):
Action 1 (Setup/Init): Create a new notebook model_training.ipynb. Load your Day 2 feature-engineered dataset. Split 80/20 into X_train, X_test, y_train, y_test using train_test_split with random_state=42.
Action 2 (Core Execution): Train a RandomForestClassifier and a LogisticRegression. Run 5-fold StratifiedKFold cross-validation on both. Print mean accuracy ± std for each.
Action 3 (Integration/Evaluation): Generate a confusion matrix for each model using ConfusionMatrixDisplay. Print a classification_report. Save both models as .pkl files using joblib.dump().

The Non-Negotiable Rules:
1. No AutoML libraries (AutoSklearn, TPOT, H2O). All models must be manually configured.
2. Cross-validation must use StratifiedKFold (not KFold) to handle class imbalance correctly.
3. Both .pkl files must be committed to GitHub — no model files in .gitignore.

1:00 PM - 1:30 PM (Hygiene): Review your notebook. Remove any hardcoded paths. Add markdown cells explaining each model choice. Confirm git status is clean.

1:30 PM - 2:00 PM (LinkedIn): Write a professional post: "Trained my first ML models today at ProSensia — comparing Random Forest vs Logistic Regression with 5-fold cross-validation. Key metric: F1 score matters more than accuracy for imbalanced datasets."

---

## Section C: The Submission Protocol
1. **GitHub:** Push model_training.ipynb + both .pkl files with commit message: feat: add RF and LR models with 5-fold CV
2. **Kanban:** Move task card to "Under Review"
3. **LinkedIn:** Post your update with hashtags: #ProSensia #MachineLearning #ScikitLearn #AIEngineering
