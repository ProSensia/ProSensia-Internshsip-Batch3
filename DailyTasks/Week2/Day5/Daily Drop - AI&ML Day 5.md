# 🤖 AI/ML Engineering Squad — Week 2, Day 5 (Friday)
**Title:** Week 2 Review — Model Performance Report, Demo Notebook & Peer Presentation
**Progress:** 20% · Week 2 of 3-Month Bootcamp · Focus: Communication & Consolidation

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Krish Naik — How to Present ML Projects to Non-Technical Stakeholders https://www.youtube.com/watch?v=YTR7n2ELQF0
- **YouTube:** StatQuest — ROC and AUC Explained https://www.youtube.com/watch?v=4jRBRDbJemM
- **Scrimba:** Intro to AI Engineering — Final Project Showcase https://v2.scrimba.com/intro-to-ai-c01nf3

### 2. Required Technical Documentation
- **Primary:** Scikit-learn — Model Evaluation Guide https://scikit-learn.org/stable/modules/model_evaluation.html
- **Secondary:** Matplotlib — Visualisation for ML https://matplotlib.org/stable/gallery/
- **Reference:** SHAP — Model Explainability https://shap.readthedocs.io/en/latest/

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Produce a final Week 2 summary notebook (week2_report.ipynb) that tells the complete story of your ML work: data → features → models → deployment. Add ROC curves, a SHAP feature importance chart, and a section explaining results in plain English for a non-technical audience.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch both videos. Read the scikit-learn evaluation guide ROC/AUC section. Install shap (pip install shap) and skim the quickstart example.

10:00 AM - 1:00 PM (Building):
Action 1 (Report Notebook): Create week2_report.ipynb. Structure it with markdown sections: Executive Summary, Data Overview, Feature Engineering Highlights, Model Comparison Table (RF vs LR — accuracy, F1, AUC), Best Model Rationale.
Action 2 (Visualisations): Plot ROC curves for both models on the same axes. Generate a SHAP summary plot showing the top 10 most important features. Add a confusion matrix heatmap with seaborn. All plots must have titles, axis labels, and legends.
Action 3 (Plain English Section): Add a "What This Means" markdown cell explaining: what the model predicts, how confident it is, what the top 3 features mean in business terms, and any limitations or biases you observed. Write for someone who has never heard of machine learning.

The Non-Negotiable Rules:
1. The notebook must run top-to-bottom with Kernel > Restart & Run All — no broken cells.
2. Every plot must have a title and labelled axes — unlabelled charts are not acceptable.
3. The plain-English section must be written in language a non-technical manager could understand.

1:00 PM - 1:30 PM (Hygiene): Export the notebook as HTML (jupyter nbconvert --to html week2_report.ipynb). Check that all plots render in the HTML. Verify your Flask API from Day 4 still runs. Tag your GitHub commit v0.1 for the Week 2 milestone.

1:30 PM - 2:00 PM (LinkedIn): "Week 2 complete at ProSensia. From raw CSV → feature engineering → trained RF model → Flask REST API → SHAP explainability. A model no one can explain is a model no one will trust. Explainability matters as much as accuracy."

---

## Section C: The Submission Protocol
1. **GitHub:** Push week2_report.ipynb + HTML export. Tag commit: git tag v0.1-week2
2. **Kanban:** Move ALL this week's cards to "Done"
3. **LinkedIn:** Post with your best result screenshot + #ProSensia #MLOps #MachineLearning #Week2
