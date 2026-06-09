# 🧪 QA Engineering Squad — Week 2, Day 5 (Friday)
**Title:** Week 2 Review — Test Strategy Document, CI Pipeline & Bug Report
**Progress:** 20% · Week 2 of 3-Month Bootcamp · Focus: QA Process & Professional Documentation

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** ISTQB — Software Testing Explained (Foundation Level) https://www.youtube.com/watch?v=TDynSmrzpXw
- **YouTube:** Automation Step by Step — Test Strategy vs Test Plan https://www.youtube.com/watch?v=W7qN5IXEt6M
- **YouTube:** GitHub Actions — CI/CD Full Tutorial https://www.youtube.com/watch?v=R8_veQiYBjI

### 2. Required Technical Documentation
- **Primary:** ISTQB Glossary — Test Strategy & Test Plan https://glossary.istqb.org/
- **Secondary:** Playwright — CI Best Practices https://playwright.dev/docs/ci
- **Reference:** GitHub Actions — Workflow Syntax https://docs.github.com/en/actions/writing-workflows/workflow-syntax-for-github-actions

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Write a professional QA Test Strategy document for your Week 2 test suite. Fix any failing CI tests from Day 4. Write one formal bug report using an industry-standard template. Consolidate all test results into a Week 2 QA Summary.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Understand the difference between Test Plan (project-specific) and Test Strategy (org-wide). Study what goes in a formal bug report: title, environment, steps to reproduce, expected vs actual, severity, priority, evidence.

10:00 AM - 1:00 PM (Building):
Action 1 (Test Strategy Doc): Write test_strategy.md covering: Testing Scope (what is and is not tested), Testing Levels (unit, API, E2E — what tool covers each), Entry/Exit Criteria for each level, Risk Assessment (top 3 test risks), and Defect Management process (how bugs are logged, prioritised, and closed).
Action 2 (Bug Report): Deliberately find or induce a real bug in the JSONPlaceholder API behaviour or your Playwright tests (e.g., edge case in todo app). Write a formal bug report in bug_report.md using this template: Title, Environment, Build/Version, Steps to Reproduce (numbered), Expected Result, Actual Result, Severity (Critical/High/Medium/Low), Priority (P1/P2/P3), Evidence (screenshot/HAR), Root Cause Analysis.
Action 3 (CI Fix + Week 2 Summary): Ensure your Playwright CI workflow from Day 4 passes on GitHub Actions. Check the Actions tab — all green. Write week2_qa_summary.md: total tests written, pass rate, test coverage (what user journeys are covered), gaps (what is NOT covered and why), and your recommendation for Week 3 improvements.

The Non-Negotiable Rules:
1. Bug report must use the exact template above — no informal "it doesn't work" descriptions.
2. CI must be green — you cannot submit with a failing Actions workflow.
3. Test Strategy must include explicit exit criteria — "tests passing" is not an exit criterion.

1:00 PM - 1:30 PM (Hygiene): Review all Day 3/Day 4 test files. Remove any .only() or .skip() calls left from debugging. Run npx playwright test --reporter=list one final time — all green. Tag commit v0.1-week2.

1:30 PM - 2:00 PM (LinkedIn): "Week 2 QA complete at ProSensia. API tests with Postman, E2E with Playwright, and a formal Test Strategy document. QA isn't just finding bugs — it's building the infrastructure that prevents them from reaching production undetected."

---

## Section C: The Submission Protocol
1. **GitHub:** Push test_strategy.md, bug_report.md, week2_qa_summary.md. Tag v0.1-week2.
2. **Kanban:** Move ALL this week's cards to "Done"
3. **LinkedIn:** Post with CI screenshot (green checkmarks) + #ProSensia #QAEngineering #Playwright #CICDPipeline
