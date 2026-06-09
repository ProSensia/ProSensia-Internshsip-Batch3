# 🧪 QA Engineering Squad — Week 2, Day 3 (Wednesday)
**Title:** API Testing with Postman — Collections, Environments & Assertions
**Progress:** 10% · Week 2 of 3-Month Bootcamp · Focus: REST API Test Automation

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Postman — API Testing for Beginners (Official) https://www.youtube.com/watch?v=cGn_LTFCif0
- **YouTube:** Valentin Despa — Postman Complete Tutorial https://www.youtube.com/watch?v=VywxIQ2ZXw4
- **YouTube:** Automation Step by Step — REST Assured Full Tutorial https://www.youtube.com/watch?v=IsZ20CgQpBg

### 2. Required Technical Documentation
- **Primary:** Postman Learning Center — Writing Tests https://learning.postman.com/docs/writing-scripts/test-scripts/
- **Secondary:** REST Assured Documentation https://rest-assured.io/
- **Reference:** JSONPlaceholder API Docs https://jsonplaceholder.typicode.com/

### 3. Engineering Assets
- **Tool:** Postman Desktop (latest version)
- **Free API:** JSONPlaceholder (https://jsonplaceholder.typicode.com) — use for all tests today
- **Export Target:** Postman Collection v2.1 JSON format for GitHub commit

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Build a complete Postman test collection for a REST API. Cover GET, POST, PUT, DELETE operations with status code assertions, schema validation, response time checks, and a chained test flow using environment variables.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch both videos. Focus on: how to write pm.test() assertions, how environment variables work in Postman, and what a "Collection Runner" does. Do NOT start Postman yet — watch and take notes.

10:00 AM - 1:00 PM (Building):
Action 1 (Setup): Create a Postman collection named "JSONPlaceholder QA Suite". Create an environment "QA Env" with a variable base_url = https://jsonplaceholder.typicode.com. Create a variable userId — this will be set dynamically.
Action 2 (Core Tests): Write tests for these 5 endpoints: GET /posts (status 200, array length > 0, response < 500ms), GET /posts/1 (status 200, body.id === 1), POST /posts (status 201, body.title matches sent value), PUT /posts/1 (status 200, body.title updated), DELETE /posts/1 (status 200). Each must have minimum 3 pm.test() assertions.
Action 3 (Chaining): Create a folder "User Flow" with a chained test: POST /users to create a user, save the returned id to pm.environment.set("userId", ...), then GET /users/{{userId}} and assert the name matches what was sent.

The Non-Negotiable Rules:
1. Zero hard-coded URLs — use {{base_url}} environment variable throughout.
2. Every request must have at minimum: status code check, response time < 1000ms, content-type header check.
3. The collection must pass 100% when run via Collection Runner — no skipped tests.

1:00 PM - 1:30 PM (Hygiene): Export the collection as JSONPlaceholder_QA_Suite.postman_collection.json and the environment as QA_Env.postman_environment.json. Commit both to GitHub. Verify no API keys or tokens in the export.

1:30 PM - 2:00 PM (LinkedIn): "Day 3 at ProSensia QA — built a complete Postman collection with chained tests and environment variables. Chained tests that carry state between requests are the gateway to E2E API test automation."

---

## Section C: The Submission Protocol
1. **GitHub:** Push both JSON export files with commit: feat: add Postman collection with chained API tests
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #QAEngineering #Postman #APITesting
