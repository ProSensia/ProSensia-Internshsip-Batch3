# 🧪 QA Engineering Squad — Week 2, Day 4 (Thursday)
**Title:** End-to-End Test Automation with Playwright
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: Browser Automation & E2E Testing

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Playwright — Official Getting Started Tutorial https://www.youtube.com/watch?v=wawbt1cATsk
- **YouTube:** Fireship — Playwright in 100 Seconds https://www.youtube.com/watch?v=2-TP51t4Sn4
- **YouTube:** LambdaTest — Playwright Complete Tutorial https://www.youtube.com/watch?v=H0kFWtjV6Xk

### 2. Required Technical Documentation
- **Primary:** Playwright Official Docs — Getting Started https://playwright.dev/docs/intro
- **Secondary:** Playwright — Best Practices https://playwright.dev/docs/best-practices
- **Reference:** Page Object Model Pattern https://playwright.dev/docs/pom

### 3. Engineering Assets
- **Install:** npm init playwright@latest in a new folder e2e-tests/
- **Target Site:** Use https://demo.playwright.dev/todomvc or https://the-internet.herokuapp.com for testing
- **CI Target:** GitHub Actions workflow file for automated E2E on push

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Build a complete Playwright E2E test suite using the Page Object Model pattern. Cover user login flow, CRUD operations, and form validation. Configure tests to run headless in GitHub Actions CI.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Read the Playwright Best Practices doc. Understand: Page Object Model, locator strategies (getByRole over CSS selectors), parallel test execution, and screenshot/video on failure.

10:00 AM - 1:00 PM (Building):
Action 1 (Setup + First Test): Run npm init playwright@latest. Write your first test: navigate to demo.playwright.dev/todomvc, add three todos, verify they appear in the list. Run with npx playwright test. Confirm it passes in Chromium, Firefox, and WebKit.
Action 2 (Page Object Model): Create a pages/ folder with TodoPage.ts. Move all locators and actions into the class: addTodo(text), toggleTodo(text), deleteTodo(text), getTodoCount(). Rewrite your test to use TodoPage. Write 5 more tests: mark done, delete item, filter Active/Completed, clear completed, verify count updates.
Action 3 (CI Configuration): Create .github/workflows/playwright.yml that runs npx playwright test on push to main. Configure it to upload test results and HTML report as an artifact. Add --reporter=html to your playwright.config.ts.

The Non-Negotiable Rules:
1. All locators must use Playwright's semantic selectors (getByRole, getByLabel, getByText) — no raw CSS or XPath.
2. Page Object Model is mandatory — no direct page.locator() calls in test files.
3. Tests must pass in all three browsers (Chromium, Firefox, WebKit) — no browser-specific skips.

1:00 PM - 1:30 PM (Hygiene): Run npx playwright test --reporter=html. Open the HTML report — all tests green. Check that playwright-report/ is in .gitignore (it's binary/large). Commit your .github/workflows/playwright.yml.

1:30 PM - 2:00 PM (LinkedIn): "Day 4 QA at ProSensia — built a Playwright E2E suite with Page Object Model. getByRole locators are resilient to CSS refactors. Running in 3 browsers from one test suite — that's real cross-browser coverage."

---

## Section C: The Submission Protocol
1. **GitHub:** Push tests/ folder and CI config with commit: feat: add Playwright E2E suite with POM and CI workflow
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #QAEngineering #Playwright #E2ETesting
