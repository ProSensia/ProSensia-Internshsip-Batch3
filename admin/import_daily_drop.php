<?php
$page_title = 'Import Daily Drop Tasks';
$page_section = 'Daily Tasks';
$page_label = 'Week 2 Import';
require __DIR__ . '/../includes/header.php';
require_role(['super_admin']);

// ─────────────────────────────────────────────────────────────────────────────
// MASTER TASK DATA — Week 2 (Day 3/4/5, 7 domains × 3 days = 21 tasks)
// target_field keywords match via INSTR against intern's team name in DB
// ─────────────────────────────────────────────────────────────────────────────
function week2_tasks(string $day3, string $day4, string $day5): array {
    return [

    // ── AI & ML ──────────────────────────────────────────────────────────────
    ['target_field'=>'AI','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'Scikit-Learn Model Training & Cross-Validation Pipeline',
     'video_url'=>'https://www.youtube.com/watch?v=7VeUPuFGJHk',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: StatQuest — Decision Trees https://www.youtube.com/watch?v=7VeUPuFGJHk\n- YouTube: StatQuest — Random Forests https://www.youtube.com/watch?v=J4Wdy0Wc_xQ\n- YouTube: Sentdex — Scikit-learn Pipeline https://www.youtube.com/watch?v=pqNCD_5r0IU\n- Scrimba: Intro to AI Engineering https://v2.scrimba.com/intro-to-ai-c01nf3\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch both StatQuest videos. Skim scikit-learn cross-validation docs.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create model_training.ipynb. Load Day 2 dataset. Split 80/20 with random_state=42.\nAction 2: Train RandomForestClassifier and LogisticRegression. Run 5-fold StratifiedKFold CV on both. Print mean accuracy ± std.\nAction 3: Generate confusion matrix with ConfusionMatrixDisplay. Print classification_report. Save both models as .pkl with joblib.dump().\n\nThe Non-Negotiable Rules:\n1. No AutoML libraries — all models manually configured.\n2. StratifiedKFold is mandatory — not plain KFold.\n3. Both .pkl files must be committed to GitHub.\n\n1:00 PM - 1:30 PM (Hygiene): Remove hardcoded paths. Add markdown cells explaining model choices.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about RF vs LR comparison with F1 score insight. #ProSensia #MachineLearning #ScikitLearn"],

    ['target_field'=>'AI','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'Model Deployment API — Flask REST Endpoint for Trained ML Models',
     'video_url'=>'https://www.youtube.com/watch?v=UbCWoMf80PY',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Patrick Loeber — Deploy ML Models with Flask https://www.youtube.com/watch?v=UbCWoMf80PY\n- YouTube: Krish Naik — Complete ML Deployment https://www.youtube.com/watch?v=ipFUANeStYE\n- YouTube: Sentdex — Flask Framework https://www.youtube.com/watch?v=MwZwr5Tvyxo\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three videos. Note: load model at startup, not per request.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create app.py. Load best .pkl model with joblib.load() at module level. Create /health GET endpoint returning {status: ok, model: RandomForest}.\nAction 2: Create POST /predict accepting JSON {features: [...]}. Run model.predict() + model.predict_proba(). Return {prediction, confidence, model}.\nAction 3: Add error handling: 400 for malformed features, 500 for model errors. Test all cases in Postman.\n\nThe Non-Negotiable Rules:\n1. Model loaded at startup — never inside a route function.\n2. All errors return proper HTTP status codes (400/422/500).\n3. Confidence score (predict_proba) in every successful response.\n\n1:00 PM - 1:30 PM (Hygiene): requirements.txt. Test in fresh venv. README API usage section.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about wrapping ML model in REST API. #ProSensia #MLOps #Flask"],

    ['target_field'=>'AI','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — Model Performance Report, Demo Notebook & Presentation',
     'video_url'=>'https://www.youtube.com/watch?v=4jRBRDbJemM',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Krish Naik — Presenting ML Projects https://www.youtube.com/watch?v=YTR7n2ELQF0\n- YouTube: StatQuest — ROC and AUC https://www.youtube.com/watch?v=4jRBRDbJemM\n- Scrimba: Intro to AI — Final Project Showcase https://v2.scrimba.com/intro-to-ai-c01nf3\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch both videos. Install shap (pip install shap).\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create week2_report.ipynb with sections: Executive Summary, Data Overview, Feature Engineering Highlights, Model Comparison Table (RF vs LR), Best Model Rationale.\nAction 2: Plot ROC curves for both models. Generate SHAP summary plot (top 10 features). Add confusion matrix heatmap with seaborn. All plots titled + labelled.\nAction 3: Write a plain-English 'What This Means' markdown cell for a non-technical manager audience.\n\nThe Non-Negotiable Rules:\n1. Notebook must run Kernel > Restart & Run All without errors.\n2. Every plot must have title and labelled axes.\n3. Plain-English section must be accessible to a non-technical reader.\n\n1:00 PM - 1:30 PM (Hygiene): Export notebook as HTML. Tag commit v0.1-week2.\n\n1:30 PM - 2:00 PM (LinkedIn): Week 2 wrap-up post. #ProSensia #MLOps #MachineLearning #Week2"],

    // ── Full Stack ────────────────────────────────────────────────────────────
    ['target_field'=>'Full Stack','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'React State Management & Real API Integration with React Query',
     'video_url'=>'https://www.youtube.com/watch?v=VtWkSCZX0Ec',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Codevolution — React Query Tutorial https://www.youtube.com/watch?v=VtWkSCZX0Ec\n- YouTube: Jack Herrington — useReducer + useContext https://www.youtube.com/watch?v=kK_Wqx3RnHk\n- Scrimba: Learn React — useState + useEffect https://v2.scrimba.com/learn-react-c0e\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch both videos. Study TanStack Query quickstart. Understand staleTime, isLoading, isError.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Install @tanstack/react-query. Wrap app in QueryClientProvider. Set defaultOptions staleTime=5 minutes.\nAction 2: Create useMetrics() hook calling JSONPlaceholder /posts?_limit=5. Define TypeScript interface Post matching response exactly. Render inside DashboardCard.\nAction 3: Implement 3 distinct UI states: skeleton loader (CSS-only), error boundary with retry button, populated data card. All fully styled with Tailwind.\n\nThe Non-Negotiable Rules:\n1. Zero use of any type — all API response fields explicitly typed.\n2. No useEffect + fetch pattern — React Query is mandatory.\n3. Loading and error states must be fully styled.\n\n1:00 PM - 1:30 PM (Hygiene): npm run build — fix all TypeScript errors. npm run lint.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about React Query staleTime pattern. #ProSensia #ReactQuery #TypeScript"],

    ['target_field'=>'Full Stack','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'Backend Integration — Next.js Route Handlers, Prisma & Database',
     'video_url'=>'https://www.youtube.com/watch?v=6-X15CQHC8I',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Fireship — Next.js App Router in 100 Seconds https://www.youtube.com/watch?v=gSSsZReIFRk\n- YouTube: Josh tried coding — Full Stack Next.js 14 https://www.youtube.com/watch?v=6-X15CQHC8I\n- YouTube: Web Dev Simplified — Learn Prisma https://www.youtube.com/watch?v=RebA5J-rlwg\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Understand app/api/ directory, Route Handlers, and Zod validation.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Run npx prisma init. Define Post model with id, title, body, createdAt. Run npx prisma migrate dev. Seed 5 posts.\nAction 2: Create app/api/posts/route.ts (GET + POST) and app/api/posts/[id]/route.ts (GET + DELETE). Zod validation on POST: title min 3, body min 10.\nAction 3: Update useMetrics() to call /api/posts. Use Prisma-generated types. Add form to create new post — calls POST /api/posts and invalidates React Query cache.\n\nThe Non-Negotiable Rules:\n1. No direct DB calls from React components — only Route Handlers.\n2. All mutation endpoints validated with Zod.\n3. Use Prisma-generated types — no duplicate manual interfaces.\n\n1:00 PM - 1:30 PM (Hygiene): npm run build. Confirm prisma/migrations committed. .env in .gitignore.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about end-to-end type safety. #ProSensia #NextJS #Prisma #TypeScript"],

    ['target_field'=>'Full Stack','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — Deploy to Vercel, Lighthouse Audit & Production Polish',
     'video_url'=>'https://www.youtube.com/watch?v=2HBIzEx6IZA',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Vercel — Deploy Next.js in 3 Minutes https://www.youtube.com/watch?v=2HBIzEx6IZA\n- YouTube: Web Dev Simplified — Next.js Performance https://www.youtube.com/watch?v=0aTRN9CSCY0\n- YouTube: Fireship — Lighthouse 101 https://www.youtube.com/watch?v=NoRYn6gOtVo\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Understand Vercel build, Core Web Vitals (LCP/CLS/FID).\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create Vercel account. Import GitHub repo. Set DATABASE_URL env var. Deploy and confirm live URL works.\nAction 2: Run Lighthouse audit (Performance + Accessibility + Best Practices). Screenshot scores. Fix top 3 issues. Re-run and confirm scores improved.\nAction 3: Add production essentials: favicon, metadata (title/description), Open Graph image, 404 not-found.tsx, loading.tsx skeleton.\n\nThe Non-Negotiable Rules:\n1. Live deployment must work — broken Vercel deploy is not a submission.\n2. Lighthouse Performance must be 70+ after fixes.\n3. All images must use Next.js <Image> component.\n\n1:00 PM - 1:30 PM (Hygiene): Remove console.log(). No API keys in code. Tag v0.1-week2.\n\n1:30 PM - 2:00 PM (LinkedIn): Post with live URL + Lighthouse screenshot. #ProSensia #NextJS #Vercel"],

    // ── Cybersecurity ─────────────────────────────────────────────────────────
    ['target_field'=>'Cyber','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'Cross-Site Scripting (XSS) — Reflected, Stored & DOM-Based Attacks',
     'video_url'=>'https://www.youtube.com/watch?v=EoaDgUgS6QA',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: NetworkChuck — XSS Explained https://www.youtube.com/watch?v=EoaDgUgS6QA\n- YouTube: LiveOverflow — XSS on Bug Bounty https://www.youtube.com/watch?v=lG7U3fuNw3A\n- PortSwigger: XSS Learning Path https://portswigger.net/web-security/cross-site-scripting\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch both videos. Complete PortSwigger XSS theory. Read OWASP prevention cheatsheet.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Complete PortSwigger lab 'Reflected XSS into HTML context'. Capture in Burp Suite. Document payload.\nAction 2: Complete 'Stored XSS into HTML context'. Explain why stored XSS is more dangerous.\nAction 3: Complete 'DOM XSS in document.write sink'. Write 200-word remediation plan citing OWASP CSP + output encoding.\n\nThe Non-Negotiable Rules:\n1. No automated scanners — all payloads crafted manually.\n2. Every finding: vulnerable parameter, payload, HTTP request screenshot, remediation.\n3. Remediation must cite OWASP techniques.\n\n1:00 PM - 1:30 PM (Hygiene): Organise findings in xss_findings.md. Commit to GitHub.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about DOM-based XSS insight. #ProSensia #CyberSecurity #XSS"],

    ['target_field'=>'Cyber','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'SQL Injection — Detection, Exploitation & Parameterised Query Defence',
     'video_url'=>'https://www.youtube.com/watch?v=ciNHn38EyRc',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: NetworkChuck — SQL Injection Attack https://www.youtube.com/watch?v=ciNHn38EyRc\n- YouTube: John Hammond — SQLi CTF Walkthrough https://www.youtube.com/watch?v=1nJgupaUPEQ\n- PortSwigger: SQL Injection Learning Path https://portswigger.net/web-security/sql-injection\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch both videos. Study OWASP SQLi prevention. Learn UNION-based extraction and blind SQLi.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Complete PortSwigger 'SQL injection in WHERE clause'. Document UNION payload that extracts hidden data.\nAction 2: Complete 'SQL injection allowing login bypass'. Explain at SQL query level why OR 1=1 -- works.\nAction 3: Complete one blind SQLi lab. Write side-by-side in sqli_findings.md: vulnerable PHP code vs PDO parameterised fix.\n\nThe Non-Negotiable Rules:\n1. Manual exploitation only — no sqlmap --dump.\n2. Writeup must explain WHY at SQL level.\n3. Remediation must use real PDO prepared statements.\n\n1:00 PM - 1:30 PM (Hygiene): Organise sqli_findings.md. Include HTTP request screenshots. Commit.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about parameterised queries as structural defence. #ProSensia #SQLInjection #WebSecurity"],

    ['target_field'=>'Cyber','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — Professional Pentest Report, OWASP Top 10 & CTF',
     'video_url'=>'https://www.youtube.com/watch?v=EOoBAq6z4os',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: TCM Security — How to Write a Pentest Report https://www.youtube.com/watch?v=EOoBAq6z4os\n- YouTube: David Bombal — OWASP Top 10 2021 https://www.youtube.com/watch?v=1k0e1bVzBFs\n- PortSwigger: All Labs Dashboard https://portswigger.net/web-security/all-labs\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch TCM report video in full. Study OWASP Top 10 2021. Read CVSS v3.1 scoring guide.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create week2_pentest_report.md with sections: Executive Summary (non-technical), Scope & Methodology, Findings Table (ID/Title/OWASP/CVSS/Severity), Detailed Findings, Conclusion.\nAction 2: Calculate CVSS v3.1 base scores for your XSS and SQLi findings. Show all scored vectors.\nAction 3: Complete one Practitioner-level lab (SSRF, XXE, or OS command injection). Add as third finding.\n\nThe Non-Negotiable Rules:\n1. CVSS scores must be calculated — not guessed. Use the online calculator.\n2. Executive Summary must be non-technical.\n3. Every finding must have a concrete, implementable remediation.\n\n1:00 PM - 1:30 PM (Hygiene): Spell-check. Verify no credentials in screenshots. Export PDF.\n\n1:30 PM - 2:00 PM (LinkedIn): Week 2 Cyber wrap-up. #ProSensia #CyberSecurity #PenetrationTesting #OWASP"],

    // ── C++ ───────────────────────────────────────────────────────────────────
    ['target_field'=>'C++','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'STL Containers, Algorithms & Iterator Architecture',
     'video_url'=>'https://www.youtube.com/watch?v=PocJ5jXv8No',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: The Cherno — std::vector in C++ https://www.youtube.com/watch?v=PocJ5jXv8No\n- YouTube: The Cherno — std::map in C++ https://www.youtube.com/watch?v=KiB0vRi2wlc\n- YouTube: CppCon — Back to Basics: Iterators https://www.youtube.com/watch?v=26aW6aBVpk0\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three videos. Note O(1) vector vs O(log n) map vs O(1) unordered_map.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Refactor Day 2 linked list to use std::vector<std::unique_ptr<Node>> as backing store.\nAction 2: Add std::unordered_map<std::string, std::weak_ptr<Node>> as O(1) index. Implement find(key) method.\nAction 3: Apply std::transform to produce vector<string> of labels. Use std::sort with custom comparator lambda. Write assert() unit tests.\n\nThe Non-Negotiable Rules:\n1. Raw new/delete completely banned.\n2. No raw pointer returns from public methods.\n3. AddressSanitizer must report zero memory errors.\n\n1:00 PM - 1:30 PM (Hygiene): cmake --build. Zero warnings -Wall -Wextra. Run valgrind for second check.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about STL container choice trade-offs. #ProSensia #CPlusPlus #STL"],

    ['target_field'=>'C++','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'Template Metaprogramming & Generic Algorithms — SortedContainer<T>',
     'video_url'=>'https://www.youtube.com/watch?v=I-hZkUa9mIs',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: The Cherno — Templates in C++ https://www.youtube.com/watch?v=I-hZkUa9mIs\n- YouTube: CppCon — Function and Class Templates https://www.youtube.com/watch?v=LMP_sxOaz6g\n- YouTube: The Cherno — Iterators in C++ https://www.youtube.com/watch?v=F9eDv-YIOQ0\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study template specialisation and SFINAE basics.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create SortedContainer<T> wrapping std::vector<T>. insert() uses std::lower_bound + vector::insert. Implement contains(), remove(), size(), begin()/end().\nAction 2: Add second template param: SortedContainer<T, Compare = std::less<T>>. Test with int, string, greater<int>.\nAction 3: Use static_assert with meaningful message to prevent non-comparable instantiation. Optional: C++20 requires clause.\n\nThe Non-Negotiable Rules:\n1. Sorted order maintained at all times — no post-insert sort().\n2. Compile with zero warnings: -Wall -Wextra -Wpedantic.\n3. All three test cases pass as assert().\n\n1:00 PM - 1:30 PM (Hygiene): cmake --build all clean. SortedContainer.hpp properly header-guarded. ASAN zero errors.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about compile-time vs runtime polymorphism. #ProSensia #CPlusPlus #Templates"],

    ['target_field'=>'C++','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — Benchmarking, Profiling & Technical Write-Up',
     'video_url'=>'https://www.youtube.com/watch?v=YG4jexlSAjc',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: CppCon — There Are No Zero-Cost Abstractions https://www.youtube.com/watch?v=rHIkrotSwcc\n- YouTube: The Cherno — Benchmarking in C++ https://www.youtube.com/watch?v=YG4jexlSAjc\n- YouTube: Jason Turner — Clang-Tidy https://www.youtube.com/watch?v=dPgBvAQFqxk\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Use Compiler Explorer to inspect SortedContainer assembly.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Benchmark insert/find/iterate at N=100, N=10000, N=1000000 for SortedContainer<int>, std::set<int>, std::vector<int>+sort. Use std::chrono.\nAction 2: Compile with -g. Run valgrind --tool=callgrind. Identify hottest function. Document.\nAction 3: Write benchmark_analysis.md with results table, analysis of small vs large N trade-offs, Callgrind hotspot, and when to use SortedContainer vs std::set in production.\n\nThe Non-Negotiable Rules:\n1. Benchmarks in release mode (-O2/-O3) — debug builds measure nothing.\n2. Report median of 3+ runs.\n3. Analysis must cite measured numbers.\n\n1:00 PM - 1:30 PM (Hygiene): Run clang-tidy on all .cpp/.hpp files. Fix modernize- and performance- warnings. Tag v0.1-week2.\n\n1:30 PM - 2:00 PM (LinkedIn): Post with benchmark results. #ProSensia #CPlusPlus #Performance"],

    // ── QA Engineering ────────────────────────────────────────────────────────
    ['target_field'=>'QA','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'API Testing with Postman — Collections, Environments & Assertions',
     'video_url'=>'https://www.youtube.com/watch?v=cGn_LTFCif0',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Postman — API Testing for Beginners https://www.youtube.com/watch?v=cGn_LTFCif0\n- YouTube: Valentin Despa — Postman Complete Tutorial https://www.youtube.com/watch?v=VywxIQ2ZXw4\n- YouTube: Automation Step by Step — REST Assured https://www.youtube.com/watch?v=IsZ20CgQpBg\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch both videos. Focus on pm.test() assertions and environment variables.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create Postman collection 'JSONPlaceholder QA Suite'. Set base_url environment variable = https://jsonplaceholder.typicode.com.\nAction 2: Write tests for 5 endpoints (GET /posts, GET /posts/1, POST /posts, PUT /posts/1, DELETE /posts/1). Min 3 pm.test() assertions per request.\nAction 3: Create 'User Flow' folder with chained test: POST /users → save returned id → GET /users/{{userId}} and assert name matches.\n\nThe Non-Negotiable Rules:\n1. Zero hard-coded URLs — use {{base_url}} throughout.\n2. Every request: status code + response time <1000ms + content-type check.\n3. Collection must pass 100% in Collection Runner.\n\n1:00 PM - 1:30 PM (Hygiene): Export collection and environment JSON. Commit to GitHub.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about chained test patterns. #ProSensia #QAEngineering #Postman"],

    ['target_field'=>'QA','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'End-to-End Test Automation with Playwright & Page Object Model',
     'video_url'=>'https://www.youtube.com/watch?v=wawbt1cATsk',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Playwright — Getting Started https://www.youtube.com/watch?v=wawbt1cATsk\n- YouTube: Fireship — Playwright in 100 Seconds https://www.youtube.com/watch?v=2-TP51t4Sn4\n- YouTube: LambdaTest — Playwright Complete Tutorial https://www.youtube.com/watch?v=H0kFWtjV6Xk\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study Page Object Model and semantic locators.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: npm init playwright@latest. Write first test: add 3 todos to demo.playwright.dev/todomvc. Confirm pass in Chromium/Firefox/WebKit.\nAction 2: Create pages/TodoPage.ts with POM. Write 5 more tests: mark done, delete, filter, clear completed, count updates.\nAction 3: Create .github/workflows/playwright.yml for CI on push. Add HTML reporter artifact.\n\nThe Non-Negotiable Rules:\n1. All locators use getByRole/getByLabel/getByText — no raw CSS or XPath.\n2. Page Object Model mandatory — no direct page.locator() in test files.\n3. Tests pass in all 3 browsers.\n\n1:00 PM - 1:30 PM (Hygiene): npx playwright test --reporter=html. All green. Commit CI workflow.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about POM and cross-browser coverage. #ProSensia #QAEngineering #Playwright"],

    ['target_field'=>'QA','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — Test Strategy Document, CI Pipeline & Bug Report',
     'video_url'=>'https://www.youtube.com/watch?v=TDynSmrzpXw',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: ISTQB — Software Testing Explained https://www.youtube.com/watch?v=TDynSmrzpXw\n- YouTube: Automation Step by Step — Test Strategy vs Test Plan https://www.youtube.com/watch?v=W7qN5IXEt6M\n- YouTube: GitHub Actions — CI/CD Tutorial https://www.youtube.com/watch?v=R8_veQiYBjI\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study formal bug report templates and ISTQB glossary.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Write test_strategy.md covering Testing Scope, Testing Levels, Entry/Exit Criteria, Risk Assessment, Defect Management.\nAction 2: Write formal bug_report.md with: Title, Environment, Steps to Reproduce, Expected/Actual Result, Severity, Priority, Evidence, Root Cause Analysis.\nAction 3: Ensure Playwright CI is green. Write week2_qa_summary.md: total tests, pass rate, coverage, gaps, Week 3 recommendations.\n\nThe Non-Negotiable Rules:\n1. Bug report must use the exact formal template.\n2. CI must be green before submission.\n3. Test Strategy must include explicit exit criteria.\n\n1:00 PM - 1:30 PM (Hygiene): Remove all .only()/.skip() from test files. Tag v0.1-week2.\n\n1:30 PM - 2:00 PM (LinkedIn): Week 2 QA wrap-up with CI screenshot. #ProSensia #QAEngineering #Playwright"],

    // ── IoT ───────────────────────────────────────────────────────────────────
    ['target_field'=>'IoT','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'MQTT Protocol — Publish/Subscribe Messaging with Mosquitto Broker',
     'video_url'=>'https://www.youtube.com/watch?v=LKz1jYngpcU',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Andreas Spiess — MQTT Explained https://www.youtube.com/watch?v=LKz1jYngpcU\n- YouTube: The Hook Up — MQTT Beginners Guide https://www.youtube.com/watch?v=aQcJ4uHdQEA\n- YouTube: RandomNerdTutorials — MQTT with ESP32/Pi https://www.youtube.com/watch?v=hqTaTNoKfgE\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Read HiveMQ MQTT Essentials parts 1-5. Study QoS levels 0/1/2.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Install Mosquitto. Start broker on port 1883. Verify with mosquitto_pub/sub.\nAction 2: Write sensor_publisher.py publishing JSON to prosensia/sensors/temperature and prosensia/sensors/humidity every 5s with QoS=1.\nAction 3: Write sensor_subscriber.py subscribing to prosensia/sensors/#. Log to sensor_log.csv. Alert if temperature >30°C. Demonstrate QoS 0/1/2 differences.\n\nThe Non-Negotiable Rules:\n1. All MQTT payloads must be valid JSON.\n2. Topic naming: prosensia/sensors/[type].\n3. Document QoS 0/1/2 comparison in README.\n\n1:00 PM - 1:30 PM (Hygiene): Write README_MQTT.md with setup steps, topic hierarchy, QoS table.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about MQTT QoS 2 exactly-once delivery. #ProSensia #IoT #MQTT"],

    ['target_field'=>'IoT','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'Sensor Data Dashboard — Real-Time Visualisation with MQTT + Chart.js',
     'video_url'=>'https://www.youtube.com/watch?v=3AR432bguOY',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Andreas Spiess — Node-RED for IoT Beginners https://www.youtube.com/watch?v=3AR432bguOY\n- YouTube: RandomNerdTutorials — MQTT Dashboard with Node-RED https://www.youtube.com/watch?v=RWbxlHToaYk\n- YouTube: Chart.js — Full Tutorial https://www.youtube.com/watch?v=sE08f4iuOhA\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study Chart.js real-time update patterns.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Configure Mosquitto WebSocket on port 9001. Verify browser MQTT.js connection to ws://localhost:9001.\nAction 2: Create dashboard.html with Chart.js. Subscribe via MQTT.js to prosensia/sensors/#. Update rolling 30-point line chart on each message. Large numeric readouts for current temp + humidity.\nAction 3: Add threshold alert panel: red badge for temp >30°C, yellow for humidity >80%. Scrollable event log with timestamps.\n\nThe Non-Negotiable Rules:\n1. Real-time updates without page refresh.\n2. Rolling 30-point max — no unbounded array growth.\n3. Run Day 3 publisher during demo to prove live data.\n\n1:00 PM - 1:30 PM (Hygiene): Test full pipeline. Screenshot dashboard. Write 5-step setup guide in README.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about event-driven MQTT architecture. #ProSensia #IoT #MQTT #RealTime"],

    ['target_field'=>'IoT','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — System Architecture Diagram, Security Hardening & Integration Test',
     'video_url'=>'https://www.youtube.com/watch?v=eUu5U7BNyCY',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Andreas Spiess — IoT System Architecture https://www.youtube.com/watch?v=eUu5U7BNyCY\n- YouTube: Fireship — Docker in 100 Seconds https://www.youtube.com/watch?v=Gjnup-PuquQ\n- YouTube: RandomNerdTutorials — IoT Project End-to-End https://www.youtube.com/watch?v=6JuwPqhDjCE\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study MQTT security best practices and four-layer IoT architecture.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Draw system architecture in draw.io showing Sensor → Broker → WebSocket → Dashboard with protocol labels, port numbers, QoS levels. Export SVG + PNG.\nAction 2: Add MQTT authentication with mosquitto_passwd. Require username/password in publisher/subscriber. Verify unauthenticated clients rejected.\nAction 3: Write 10-step integration_test.md. Run all steps. Mark pass/fail. Steps: broker starts, publisher connects, subscriber receives, dashboard updates, alert fires, auth rejects, LWT triggers, reconnect works, CSV logs, graceful shutdown.\n\nThe Non-Negotiable Rules:\n1. Architecture diagram must be from draw.io.\n2. Unauthenticated connections must be rejected.\n3. Integration test must be formal pass/fail checklist.\n\n1:00 PM - 1:30 PM (Hygiene): Update README with diagram image. Tag v0.1-week2.\n\n1:30 PM - 2:00 PM (LinkedIn): Week 2 IoT wrap-up with architecture diagram. #ProSensia #IoT #SystemArchitecture"],

    // ── Graphic Design ────────────────────────────────────────────────────────
    ['target_field'=>'Graphic','task_date'=>$day3,'est_minutes'=>300,
     'title'=>'High-Fidelity Mockups — From Wireframe to Polished UI in Figma',
     'video_url'=>'https://www.youtube.com/watch?v=FTFaQWZBqQ8',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Figma — High Fidelity Design Tutorial https://www.youtube.com/watch?v=FTFaQWZBqQ8\n- YouTube: DesignCourse — Figma UI Design Full Tutorial https://www.youtube.com/watch?v=jwCmIBJ8Jtc\n- YouTube: Mizko — Building a Design System in Figma https://www.youtube.com/watch?v=Dtd40cHQQlk\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study Material Design 3. Browse Dribbble for 20 min, save 3 references.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create Design System page: 5 semantic colours, 5 typography styles, spacing tokens (4px grid), component library (Button 3 variants, Card, Input, Nav Item).\nAction 2: Convert Day 2 wireframe to hi-fi using all design tokens. Auto Layout on every component. 3 screens: Dashboard, Detail, Form.\nAction 3: Connect all screens with prototype interactions. Export at 2x PNG.\n\nThe Non-Negotiable Rules:\n1. Every component uses Auto Layout.\n2. All colours from Design System — no ad-hoc hex values.\n3. Prototype shareable via Figma link.\n\n1:00 PM - 1:30 PM (Hygiene): Name every layer. Run Accessibility Checker plugin. Fix WCAG AA failures.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about Auto Layout game-changing workflow. #ProSensia #FigmaDesign #UIDesign"],

    ['target_field'=>'Graphic','task_date'=>$day4,'est_minutes'=>300,
     'title'=>'Usability Testing, Heuristic Evaluation & Developer Handoff',
     'video_url'=>'https://www.youtube.com/watch?v=0YL0xoSmyZI',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: NNGroup — Usability Testing 101 https://www.youtube.com/watch?v=0YL0xoSmyZI\n- YouTube: AJ&Smart — How to Run a UX Usability Test https://www.youtube.com/watch?v=0pv_TRKV8hc\n- YouTube: Figma — Developer Handoff and Inspect https://www.youtube.com/watch?v=B242nuM3y2s\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study all 10 Nielsen heuristics with failure mode examples.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Evaluate Day 3 hi-fi against all 10 Nielsen heuristics. Document 3 most severe issues: heuristic violated, failure description, severity 1-4. Record in usability_audit.md.\nAction 2: Fix all severity 3+ issues in Figma. Take before/after screenshots. Update component library if needed.\nAction 3: Create Figma Handoff page with: annotated specs, exported SVG icons, font specimen, spacing grid, component usage guide (do/don't).\n\nThe Non-Negotiable Rules:\n1. Evaluate all 10 heuristics — not just problem areas.\n2. All interactive elements: 44x44px min tap target.\n3. Handoff page must have exact measurements — no unannotated components.\n\n1:00 PM - 1:30 PM (Hygiene): Run Stark plugin. Fix WCAG AA failures. Export handoff PDF.\n\n1:30 PM - 2:00 PM (LinkedIn): Post about heuristic evaluation findings. #ProSensia #UXDesign #HeuristicEvaluation"],

    ['target_field'=>'Graphic','task_date'=>$day5,'est_minutes'=>300,
     'title'=>'Week 2 Review — Portfolio Case Study, Motion Design & Final Demo',
     'video_url'=>'https://www.youtube.com/watch?v=UrSbXOBZuSI',
     'description'=>"Section A: Today's Materials\n\nVideo Drills (Watch These First)\n- YouTube: Flux — How to Write a UX Case Study https://www.youtube.com/watch?v=UrSbXOBZuSI\n- YouTube: AJ&Smart — How Designers Present to Clients https://www.youtube.com/watch?v=8hLJV9Z5P4Q\n- YouTube: Figma — Smart Animate and Micro-interactions https://www.youtube.com/watch?v=6Id4INKEwb8\n\nSection B: The Execution Mandate (9:00 AM - 2:00 PM)\n\n9:00 AM - 10:00 AM (Learning): Watch all three. Study 2 Behance case studies. Note Problem → Research → Design → Iteration → Result structure.\n\n10:00 AM - 1:00 PM (Building):\nAction 1: Create 'Case Study — Week 2' Figma page with 10-12 frames: Problem Statement, Design Process, Key Decisions (3 with rationale), Usability Findings, Before/After, Final Showcase.\nAction 2: Add Smart Animate transitions: button scale on click, card hover elevation, modal fade+slide, nav active state.\nAction 3: Export case study as PDF. Record 5-min Loom demo in Presentation mode. Write 150-word project summary.\n\nThe Non-Negotiable Rules:\n1. Case study explains design decisions and rationale — not just screenshots.\n2. All micro-interactions use Smart Animate.\n3. Loom recording shows Presentation mode, not editor.\n\n1:00 PM - 1:30 PM (Hygiene): Final Figma review. All layers named. Stark check passed. Export 2x screens. Tag v0.1-week2.\n\n1:30 PM - 2:00 PM (LinkedIn): Week 2 Design wrap-up with Loom link. #ProSensia #UIUXDesign #FigmaDesign #PortfolioReady"],

    ]; // end return
}

// ─────────────────────────────────────────────────────────────────────────────
// Handle import action
// ─────────────────────────────────────────────────────────────────────────────
$flash = ''; $flash_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_week2'])) {
    // Determine dates from POST
    $day3_date = trim($_POST['day3_date'] ?? '');
    $day4_date = trim($_POST['day4_date'] ?? '');
    $day5_date = trim($_POST['day5_date'] ?? '');

    // Basic date validation
    $valid = true;
    foreach ([$day3_date, $day4_date, $day5_date] as $d) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) { $valid = false; break; }
    }

    if (!$valid) {
        $flash = 'Invalid date format. Please use YYYY-MM-DD.';
        $flash_type = 'danger';
    } else {
        $tasks = week2_tasks($day3_date, $day4_date, $day5_date);
        $imported = 0; $updated = 0; $errors = [];

        $stmt = $pdo->prepare("
            INSERT INTO daily_tasks
                (title, description, task_date, target_field, video_url, est_minutes, status, cadence)
            VALUES
                (:title, :description, :task_date, :target_field, :video_url, :est_minutes, 'active', 'daily')
            ON DUPLICATE KEY UPDATE
                description  = VALUES(description),
                video_url    = VALUES(video_url),
                est_minutes  = VALUES(est_minutes),
                status       = 'active'
        ");

        $pdo->beginTransaction();
        try {
            foreach ($tasks as $t) {
                $stmt->execute([
                    ':title'        => $t['title'],
                    ':description'  => $t['description'],
                    ':task_date'    => $t['task_date'],
                    ':target_field' => $t['target_field'],
                    ':video_url'    => $t['video_url'],
                    ':est_minutes'  => $t['est_minutes'],
                ]);
                if ($stmt->rowCount() === 1) $imported++;
                elseif ($stmt->rowCount() === 2) $updated++;
            }
            $pdo->commit();
            $flash = "Import complete: {$imported} new tasks created, {$updated} existing tasks updated. Total: " . count($tasks) . " tasks across 7 domains for Days 3, 4, 5.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $flash = 'Import failed: ' . htmlspecialchars($e->getMessage());
            $flash_type = 'danger';
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Fetch existing task counts for preview
// ─────────────────────────────────────────────────────────────────────────────
$existing_tasks = $pdo->query("
    SELECT target_field, task_date, COUNT(*) as cnt
    FROM daily_tasks
    WHERE task_date >= CURDATE() - INTERVAL 7 DAY
    GROUP BY target_field, task_date
    ORDER BY task_date, target_field
")->fetchAll(PDO::FETCH_ASSOC);

// Default dates: Wednesday/Thursday/Friday of current week
$today = new DateTime();
$dayOfWeek = (int)$today->format('N'); // 1=Mon ... 7=Sun
$monday = clone $today;
$monday->modify('-' . ($dayOfWeek - 1) . ' days');
$def_day3 = (clone $monday)->modify('+2 days')->format('Y-m-d');
$def_day4 = (clone $monday)->modify('+3 days')->format('Y-m-d');
$def_day5 = (clone $monday)->modify('+4 days')->format('Y-m-d');

$domains = [
    ['key'=>'AI',         'icon'=>'bi-robot',       'label'=>'AI & ML Engineering',    'color'=>'#60a5fa'],
    ['key'=>'Full Stack', 'icon'=>'bi-code-slash',   'label'=>'Full Stack Development', 'color'=>'#34d399'],
    ['key'=>'Cyber',      'icon'=>'bi-shield-lock',  'label'=>'Cybersecurity',          'color'=>'#f87171'],
    ['key'=>'C++',        'icon'=>'bi-cpu',          'label'=>'C++ Systems Engineering','color'=>'#a78bfa'],
    ['key'=>'QA',         'icon'=>'bi-bug',          'label'=>'QA Engineering',         'color'=>'#fbbf24'],
    ['key'=>'IoT',        'icon'=>'bi-wifi',         'label'=>'IoT/Embedded Systems',   'color'=>'#4ade80'],
    ['key'=>'Graphic',    'icon'=>'bi-palette',      'label'=>'Graphic Design & UI/UX', 'color'=>'#f472b6'],
];
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash_type ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?= $flash_type==='success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
    <?= $flash ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h1 class="serif" style="font-size:36px;margin:0">Daily Drop Import</h1>
        <p class="muted mb-0">Import Week 2 tasks for all 7 domains — one click, everything goes in.</p>
    </div>
    <a href="<?= base_url('admin/daily_drop_upload.php') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-upload me-1"></i>Upload individual PDF
    </a>
</div>

<!-- Domain Overview Cards -->
<div class="bento mb-4">
<?php foreach ($domains as $d): ?>
<div class="span-3 glass" style="padding:16px;border-left:3px solid <?= $d['color'] ?>">
    <div class="d-flex align-items-center gap-2 mb-1">
        <i class="bi <?= $d['icon'] ?>" style="color:<?= $d['color'] ?>;font-size:18px"></i>
        <span style="font-size:13px;font-weight:600;color:<?= $d['color'] ?>"><?= $d['label'] ?></span>
    </div>
    <div style="font-size:11px;color:var(--muted)">3 tasks (Day 3/4/5) · keyword: <code><?= $d['key'] ?></code></div>
</div>
<?php endforeach; ?>
</div>

<!-- Import Form -->
<div class="bento mb-4">
<div class="span-12 glass card-pad">
    <h4 class="serif mb-3"><i class="bi bi-calendar-plus me-2 text-primary"></i>Configure Import Dates</h4>
    <form method="POST" id="importForm">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar3 me-1 text-info"></i>Day 3 — Wednesday
                </label>
                <input type="date" name="day3_date" class="form-control"
                       value="<?= e($_POST['day3_date'] ?? $def_day3) ?>" required>
                <div class="form-text">Core Implementation / Deep Work</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar3 me-1 text-warning"></i>Day 4 — Thursday
                </label>
                <input type="date" name="day4_date" class="form-control"
                       value="<?= e($_POST['day4_date'] ?? $def_day4) ?>" required>
                <div class="form-text">Integration & Testing</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar3 me-1 text-success"></i>Day 5 — Friday
                </label>
                <input type="date" name="day5_date" class="form-control"
                       value="<?= e($_POST['day5_date'] ?? $def_day5) ?>" required>
                <div class="form-text">Review, Polish & Deploy</div>
            </div>
        </div>

        <!-- Preview Table -->
        <h5 class="mb-3" style="font-size:15px;font-weight:600">
            <i class="bi bi-table me-2 text-secondary"></i>Task Preview (21 tasks · 7 domains × 3 days)
        </h5>
        <div class="table-responsive mb-4" style="max-height:340px;overflow-y:auto">
            <table class="table table-sm table-hover align-middle" style="font-size:13px">
                <thead class="sticky-top" style="background:var(--card-bg)">
                    <tr>
                        <th>Domain</th>
                        <th>Day 3 Task</th>
                        <th>Day 4 Task</th>
                        <th>Day 5 Task</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($domains as $d):
                    $domain_tasks = array_filter(week2_tasks('D3','D4','D5'), fn($t)=>$t['target_field']===$d['key']);
                    $by_day = [];
                    foreach ($domain_tasks as $t) $by_day[$t['task_date']] = $t;
                    $t3 = $by_day['D3'] ?? null;
                    $t4 = $by_day['D4'] ?? null;
                    $t5 = $by_day['D5'] ?? null;
                ?>
                <tr>
                    <td>
                        <span class="d-flex align-items-center gap-1">
                            <i class="bi <?= $d['icon'] ?>" style="color:<?= $d['color'] ?>"></i>
                            <strong style="color:<?= $d['color'] ?>"><?= $d['key'] ?></strong>
                        </span>
                    </td>
                    <td><?= $t3 ? '<span class="badge rounded-pill" style="background:rgba(96,165,250,.15);color:#93c5fd;font-size:11px">' . e(substr($t3['title'],0,55)) . '…</span>' : '—' ?></td>
                    <td><?= $t4 ? '<span class="badge rounded-pill" style="background:rgba(52,211,153,.15);color:#6ee7b7;font-size:11px">' . e(substr($t4['title'],0,55)) . '…</span>' : '—' ?></td>
                    <td><?= $t5 ? '<span class="badge rounded-pill" style="background:rgba(251,191,36,.15);color:#fde047;font-size:11px">' . e(substr($t5['title'],0,55)) . '…</span>' : '—' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="alert alert-info py-2 mb-3" style="font-size:13px">
            <i class="bi bi-info-circle me-2"></i>
            <strong>ON DUPLICATE KEY UPDATE</strong> is used — running import twice will update existing tasks, not create duplicates.
            Domain matching uses INSTR substring match so <code>AI</code> automatically matches interns in <em>AI &amp; ML Engineering</em> team.
        </div>

        <div class="d-flex gap-2">
            <button type="submit" name="import_week2" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-cloud-upload me-2"></i>Import All 21 Tasks
            </button>
            <a href="<?= base_url('intern/tasks.php') ?>" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-eye me-2"></i>Preview Intern View
            </a>
            <a href="<?= base_url('intern/task_history.php') ?>" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-calendar-week me-2"></i>Task History
            </a>
        </div>
    </form>
</div>
</div>

<!-- Existing tasks in DB (this week) -->
<?php if ($existing_tasks): ?>
<div class="bento mb-4">
<div class="span-12 glass card-pad">
    <h4 class="serif mb-3"><i class="bi bi-database-check me-2 text-success"></i>Existing Tasks in Database (last 7 days)</h4>
    <div class="table-responsive">
        <table class="table table-sm align-middle" style="font-size:13px">
            <thead><tr><th>Domain (target_field)</th><th>Task Date</th><th>Count</th></tr></thead>
            <tbody>
            <?php foreach ($existing_tasks as $row): ?>
            <tr>
                <td><code><?= e($row['target_field'] ?: 'ALL') ?></code></td>
                <td><?= e($row['task_date']) ?></td>
                <td><span class="badge bg-primary"><?= (int)$row['cnt'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
