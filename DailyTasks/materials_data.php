<?php
/**
 * ProSensia — Domain Materials Master Data
 * Run this file directly as CLI or include it from an admin import page.
 * Usage: php materials_data.php  (will echo JSON of all materials)
 *
 * Structure per domain:
 *   target_field  — short keyword matching daily_tasks.target_field
 *   label         — human-readable domain name
 *   scrimba_url   — primary interactive course URL
 *   youtube_channels — curated channels (name + url)
 *   documentation — official docs (name + url)
 *   tools         — required tools (name + url + notes)
 *   free_resources — ebooks, playgrounds, labs (name + url)
 *   milestones    — what interns should complete by end of each week
 */

define('PROSENSIA_MATERIALS', [

    // ─────────────────────────────────────────────────────────────────────────
    'AI' => [
        'target_field' => 'AI',
        'label'        => 'AI & ML Engineering',
        'icon'         => '🤖',
        'color'        => '#60a5fa',

        'scrimba_url'  => 'https://v2.scrimba.com/intro-to-ai-c01nf3',
        'scrimba_note' => 'Intro to AI Engineering — 6 hours, project-based',

        'youtube_channels' => [
            ['name'=>'StatQuest with Josh Starmer', 'url'=>'https://www.youtube.com/@statquest',
             'note'=>'Best ML explanations on YouTube — statistics-first approach'],
            ['name'=>'Sentdex',                    'url'=>'https://www.youtube.com/@sentdex',
             'note'=>'Python + ML practical coding tutorials'],
            ['name'=>'Krish Naik',                 'url'=>'https://www.youtube.com/@krishnaik06',
             'note'=>'End-to-end ML project deployment tutorials'],
            ['name'=>'Andrej Karpathy',            'url'=>'https://www.youtube.com/@AndrejKarpathy',
             'note'=>'Neural networks and LLMs from scratch — advanced'],
        ],

        'documentation' => [
            ['name'=>'Scikit-learn User Guide',       'url'=>'https://scikit-learn.org/stable/user_guide.html'],
            ['name'=>'Pandas Documentation',          'url'=>'https://pandas.pydata.org/docs/'],
            ['name'=>'NumPy Documentation',           'url'=>'https://numpy.org/doc/stable/'],
            ['name'=>'Matplotlib Gallery',            'url'=>'https://matplotlib.org/stable/gallery/'],
            ['name'=>'SHAP Documentation',            'url'=>'https://shap.readthedocs.io/en/latest/'],
            ['name'=>'Flask Documentation',           'url'=>'https://flask.palletsprojects.com/en/3.0.x/'],
        ],

        'tools' => [
            ['name'=>'Jupyter Notebook',    'url'=>'https://jupyter.org/install',
             'notes'=>'Primary development environment — use jupyterlab for better UX'],
            ['name'=>'Anaconda / Miniconda','url'=>'https://docs.anaconda.com/free/miniconda/',
             'notes'=>'Python environment manager — install miniconda for minimal setup'],
            ['name'=>'Google Colab',        'url'=>'https://colab.research.google.com',
             'notes'=>'Free GPU access — use when local machine is slow'],
            ['name'=>'Kaggle Datasets',     'url'=>'https://www.kaggle.com/datasets',
             'notes'=>'Free datasets for all project work'],
        ],

        'free_resources' => [
            ['name'=>'Fast.ai Practical Deep Learning',      'url'=>'https://course.fast.ai'],
            ['name'=>'Google ML Crash Course',               'url'=>'https://developers.google.com/machine-learning/crash-course'],
            ['name'=>'Kaggle Learn — ML',                    'url'=>'https://www.kaggle.com/learn/intro-to-machine-learning'],
            ['name'=>'Papers With Code',                     'url'=>'https://paperswithcode.com'],
        ],

        'milestones' => [
            'week1' => 'EDA on a Kaggle dataset, pandas profiling report',
            'week2' => 'Trained RF + LR models with cross-validation, Flask prediction API deployed',
            'week3' => 'Neural network with PyTorch, deployed to cloud (Render/Railway)',
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    'Full Stack' => [
        'target_field' => 'Full Stack',
        'label'        => 'Full Stack Development',
        'icon'         => '💻',
        'color'        => '#34d399',

        'scrimba_url'  => 'https://v2.scrimba.com/learn-react-c0e',
        'scrimba_note' => 'Learn React — 11 hours, hands-on with live scrims',

        'youtube_channels' => [
            ['name'=>'Fireship',              'url'=>'https://www.youtube.com/@Fireship',
             'note'=>'100-second explainers — best for quick concept grounding'],
            ['name'=>'Codevolution',          'url'=>'https://www.youtube.com/@Codevolution',
             'note'=>'Deep React/TypeScript tutorials'],
            ['name'=>'Josh tried coding',     'url'=>'https://www.youtube.com/@joshtriedcoding',
             'note'=>'Full Stack Next.js project builds'],
            ['name'=>'Web Dev Simplified',    'url'=>'https://www.youtube.com/@WebDevSimplified',
             'note'=>'Clean explanations of React hooks, patterns, TypeScript'],
        ],

        'documentation' => [
            ['name'=>'Next.js Docs',          'url'=>'https://nextjs.org/docs'],
            ['name'=>'React Docs (new)',       'url'=>'https://react.dev'],
            ['name'=>'TanStack Query Docs',   'url'=>'https://tanstack.com/query/latest/docs/framework/react/overview'],
            ['name'=>'Prisma Docs',           'url'=>'https://www.prisma.io/docs'],
            ['name'=>'TypeScript Handbook',   'url'=>'https://www.typescriptlang.org/docs/handbook/intro.html'],
            ['name'=>'Tailwind CSS Docs',     'url'=>'https://tailwindcss.com/docs'],
            ['name'=>'Zod Documentation',     'url'=>'https://zod.dev/'],
        ],

        'tools' => [
            ['name'=>'Node.js LTS',  'url'=>'https://nodejs.org',
             'notes'=>'v20 LTS — install via nvm for easy version switching'],
            ['name'=>'Vercel CLI',   'url'=>'https://vercel.com/docs/cli',
             'notes'=>'For deployment and local dev environment'],
            ['name'=>'Postman',      'url'=>'https://www.postman.com/downloads/',
             'notes'=>'For testing API routes during development'],
            ['name'=>'TablePlus',    'url'=>'https://tableplus.com',
             'notes'=>'GUI for viewing SQLite/Postgres database during Prisma dev'],
        ],

        'free_resources' => [
            ['name'=>'Full Stack Open (University of Helsinki)', 'url'=>'https://fullstackopen.com/en/'],
            ['name'=>'The Odin Project',                          'url'=>'https://www.theodinproject.com/'],
            ['name'=>'JSONPlaceholder API',                       'url'=>'https://jsonplaceholder.typicode.com'],
            ['name'=>'web.dev Core Web Vitals',                   'url'=>'https://web.dev/vitals/'],
        ],

        'milestones' => [
            'week1' => 'Next.js project setup, DashboardCard component with TypeScript',
            'week2' => 'React Query + Prisma backend + deployed to Vercel with Lighthouse 70+',
            'week3' => 'Authentication with NextAuth, protected routes, full CRUD app',
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    'Cyber' => [
        'target_field' => 'Cyber',
        'label'        => 'Cybersecurity',
        'icon'         => '🔐',
        'color'        => '#f87171',

        'scrimba_url'  => null,
        'scrimba_note' => 'No Scrimba course — use PortSwigger Web Security Academy (free)',

        'youtube_channels' => [
            ['name'=>'NetworkChuck',     'url'=>'https://www.youtube.com/@NetworkChuck',
             'note'=>'Engaging entry-level cyber content — attack and defence'],
            ['name'=>'John Hammond',     'url'=>'https://www.youtube.com/@_JohnHammond',
             'note'=>'CTF walkthroughs and malware analysis'],
            ['name'=>'LiveOverflow',     'url'=>'https://www.youtube.com/@LiveOverflow',
             'note'=>'Deep technical — binary exploitation, web security research'],
            ['name'=>'TCM Security',     'url'=>'https://www.youtube.com/@TCMSecurityAcademy',
             'note'=>'Professional pentesting techniques and report writing'],
            ['name'=>'David Bombal',     'url'=>'https://www.youtube.com/@davidbombal',
             'note'=>'Networking + cyber operations and tools'],
        ],

        'documentation' => [
            ['name'=>'OWASP Top 10 2021',              'url'=>'https://owasp.org/www-project-top-ten/'],
            ['name'=>'OWASP Testing Guide',            'url'=>'https://owasp.org/www-project-web-security-testing-guide/'],
            ['name'=>'PortSwigger Web Security Academy','url'=>'https://portswigger.net/web-security'],
            ['name'=>'HackTricks Book',                'url'=>'https://book.hacktricks.xyz/'],
            ['name'=>'CVSS v3.1 Calculator',           'url'=>'https://www.first.org/cvss/calculator/3.1'],
        ],

        'tools' => [
            ['name'=>'Burp Suite Community', 'url'=>'https://portswigger.net/burp/communitydownload',
             'notes'=>'Primary web proxy — mandatory for all PortSwigger labs'],
            ['name'=>'Kali Linux',           'url'=>'https://www.kali.org/get-kali/',
             'notes'=>'Use as VM or WSL2 on Windows — do not run on host'],
            ['name'=>'sqlmap',               'url'=>'https://sqlmap.org',
             'notes'=>'Reference only — exploit manually first, automate second'],
            ['name'=>'Wireshark',            'url'=>'https://www.wireshark.org',
             'notes'=>'Packet capture and analysis'],
        ],

        'free_resources' => [
            ['name'=>'PortSwigger Web Security Academy (all labs)', 'url'=>'https://portswigger.net/web-security/all-labs'],
            ['name'=>'TryHackMe',                                   'url'=>'https://tryhackme.com'],
            ['name'=>'HackTheBox Academy',                          'url'=>'https://academy.hackthebox.com'],
            ['name'=>'OWASP WebGoat',                               'url'=>'https://owasp.org/www-project-webgoat/'],
        ],

        'milestones' => [
            'week1' => 'Burp Suite configured, OWASP Top 10 read, first 3 Apprentice labs done',
            'week2' => 'XSS + SQLi Practitioner labs, professional pentest report with CVSS scores',
            'week3' => 'SSRF, XXE, OS command injection + full threat model for a sample app',
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    'C++' => [
        'target_field' => 'C++',
        'label'        => 'C++ Systems Engineering',
        'icon'         => '⚙️',
        'color'        => '#a78bfa',

        'scrimba_url'  => null,
        'scrimba_note' => 'No Scrimba course — use LearnCpp.com (free, best structured C++ resource)',

        'youtube_channels' => [
            ['name'=>'The Cherno',          'url'=>'https://www.youtube.com/@TheCherno',
             'note'=>'Best modern C++ series — smart pointers, STL, templates, game engine'],
            ['name'=>'CppCon',              'url'=>'https://www.youtube.com/@CppCon',
             'note'=>'Conference talks — "Back to Basics" playlist is essential'],
            ['name'=>'Jason Turner',        'url'=>'https://www.youtube.com/@cppweekly',
             'note'=>'C++ Weekly — short focused videos on language features and tools'],
        ],

        'documentation' => [
            ['name'=>'cppreference.com',              'url'=>'https://en.cppreference.com/'],
            ['name'=>'LearnCpp.com',                  'url'=>'https://www.learncpp.com/'],
            ['name'=>'C++ Core Guidelines',           'url'=>'https://isocpp.github.io/CppCoreGuidelines/CppCoreGuidelines'],
            ['name'=>'Compiler Explorer (Godbolt)',   'url'=>'https://godbolt.org/'],
            ['name'=>'Google Benchmark Library',      'url'=>'https://github.com/google/benchmark'],
        ],

        'tools' => [
            ['name'=>'CMake',          'url'=>'https://cmake.org/download/',
             'notes'=>'Build system — minimum version 3.20'],
            ['name'=>'Clang/LLVM',     'url'=>'https://releases.llvm.org/download.html',
             'notes'=>'For clang-tidy static analysis and ASAN/UBSAN'],
            ['name'=>'Valgrind',       'url'=>'https://valgrind.org',
             'notes'=>'Memory profiler — Linux/WSL2 only'],
            ['name'=>'vcpkg',          'url'=>'https://vcpkg.io',
             'notes'=>'C++ package manager from Microsoft'],
        ],

        'free_resources' => [
            ['name'=>'LearnCpp.com — Full Tutorial', 'url'=>'https://www.learncpp.com/'],
            ['name'=>'Exercism C++ Track',           'url'=>'https://exercism.org/tracks/cpp'],
            ['name'=>'LeetCode C++ problems',        'url'=>'https://leetcode.com/problemset/?difficulty=EASY&page=1&topicSlugs=array'],
            ['name'=>'Awesome C++ on GitHub',        'url'=>'https://github.com/fffaraz/awesome-cpp'],
        ],

        'milestones' => [
            'week1' => 'RAII, unique_ptr/shared_ptr, move semantics — no memory leaks',
            'week2' => 'STL containers, SortedContainer<T> template, benchmark analysis',
            'week3' => 'Concurrency with std::thread, mutex, condition_variable — thread-safe queue',
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    'QA' => [
        'target_field' => 'QA',
        'label'        => 'QA Engineering',
        'icon'         => '🧪',
        'color'        => '#fbbf24',

        'scrimba_url'  => null,
        'scrimba_note' => 'No Scrimba course — use Playwright official docs and ISTQB study materials',

        'youtube_channels' => [
            ['name'=>'Automation Step by Step', 'url'=>'https://www.youtube.com/@automationstepbystep',
             'note'=>'Comprehensive QA automation tutorials — Postman, Selenium, REST Assured'],
            ['name'=>'LambdaTest',              'url'=>'https://www.youtube.com/@LambdaTest',
             'note'=>'Playwright, Cypress, and cross-browser testing tutorials'],
            ['name'=>'Playwright Official',     'url'=>'https://www.youtube.com/@playwrightdev',
             'note'=>'Official Playwright YouTube — new features and best practices'],
        ],

        'documentation' => [
            ['name'=>'Playwright Official Docs',       'url'=>'https://playwright.dev/docs/intro'],
            ['name'=>'Postman Learning Center',        'url'=>'https://learning.postman.com/docs/getting-started/overview/'],
            ['name'=>'ISTQB Glossary',                 'url'=>'https://glossary.istqb.org/'],
            ['name'=>'REST Assured Documentation',     'url'=>'https://rest-assured.io/'],
            ['name'=>'GitHub Actions Workflow Syntax', 'url'=>'https://docs.github.com/en/actions/writing-workflows/workflow-syntax-for-github-actions'],
        ],

        'tools' => [
            ['name'=>'Postman Desktop',  'url'=>'https://www.postman.com/downloads/',
             'notes'=>'API testing — required, not the web version'],
            ['name'=>'Node.js LTS',      'url'=>'https://nodejs.org',
             'notes'=>'Required for Playwright'],
            ['name'=>'Playwright',       'url'=>'https://playwright.dev',
             'notes'=>'npm init playwright@latest in your test project folder'],
            ['name'=>'GitHub Actions',   'url'=>'https://github.com/features/actions',
             'notes'=>'CI pipeline — free for public repos, 2000 min/month for private'],
        ],

        'free_resources' => [
            ['name'=>'Playwright Test Generator',            'url'=>'https://playwright.dev/docs/codegen-intro'],
            ['name'=>'JSONPlaceholder (test API)',            'url'=>'https://jsonplaceholder.typicode.com'],
            ['name'=>'the-internet.herokuapp.com (test app)','url'=>'https://the-internet.herokuapp.com'],
            ['name'=>'ISTQB Foundation Level Sample Exams',  'url'=>'https://istqb-exam-institute.org/en/study-materials.html'],
        ],

        'milestones' => [
            'week1' => 'Postman collection with environment variables, 10+ assertions, Collection Runner green',
            'week2' => 'Playwright E2E suite with POM, CI workflow on GitHub Actions, test strategy doc',
            'week3' => 'Performance testing with k6, visual regression testing, full QA release checklist',
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    'IoT' => [
        'target_field' => 'IoT',
        'label'        => 'IoT/Embedded Systems',
        'icon'         => '📡',
        'color'        => '#4ade80',

        'scrimba_url'  => null,
        'scrimba_note' => 'No Scrimba course — use HiveMQ MQTT Essentials + RandomNerdTutorials',

        'youtube_channels' => [
            ['name'=>'Andreas Spiess',          'url'=>'https://www.youtube.com/@AndreasSpiess',
             'note'=>'The IoT Father — deep technical content on protocols, hardware, architecture'],
            ['name'=>'RandomNerdTutorials',     'url'=>'https://www.youtube.com/@RandomNerdTutorials',
             'note'=>'ESP32/Arduino/Raspberry Pi practical projects'],
            ['name'=>'The Hook Up',             'url'=>'https://www.youtube.com/@TheHookUp',
             'note'=>'Home automation and IoT integrations (MQTT, Node-RED, Home Assistant)'],
        ],

        'documentation' => [
            ['name'=>'HiveMQ MQTT Essentials',     'url'=>'https://www.hivemq.com/mqtt-essentials/'],
            ['name'=>'Eclipse Mosquitto Docs',     'url'=>'https://mosquitto.org/documentation/'],
            ['name'=>'Paho MQTT Python Client',    'url'=>'https://eclipse.dev/paho/files/paho.mqtt.python/html/index.html'],
            ['name'=>'Node-RED Documentation',     'url'=>'https://nodered.org/docs/'],
            ['name'=>'MQTT.js (Browser Client)',   'url'=>'https://github.com/mqttjs/MQTT.js'],
            ['name'=>'Chart.js Documentation',     'url'=>'https://www.chartjs.org/docs/latest/'],
        ],

        'tools' => [
            ['name'=>'Mosquitto MQTT Broker', 'url'=>'https://mosquitto.org/download/',
             'notes'=>'Local broker — required for all Day 3/4/5 work'],
            ['name'=>'MQTT Explorer',         'url'=>'https://mqtt-explorer.com/',
             'notes'=>'GUI MQTT client — excellent for debugging topics and payloads'],
            ['name'=>'Node-RED',              'url'=>'https://nodered.org/',
             'notes'=>'npm install -g --unsafe-perm node-red'],
            ['name'=>'Raspberry Pi Simulator','url'=>'https://wokwi.com/',
             'notes'=>'Wokwi — simulate ESP32/Arduino without hardware'],
        ],

        'free_resources' => [
            ['name'=>'HiveMQ MQTT Essentials (all 11 parts)', 'url'=>'https://www.hivemq.com/mqtt-essentials/'],
            ['name'=>'Random Nerd Tutorials — MQTT',          'url'=>'https://randomnerdtutorials.com/what-is-mqtt-and-how-it-works/'],
            ['name'=>'Wokwi ESP32 Simulator',                 'url'=>'https://wokwi.com/'],
            ['name'=>'AWS IoT Core Free Tier',                'url'=>'https://aws.amazon.com/iot-core/'],
        ],

        'milestones' => [
            'week1' => 'Mosquitto broker running, basic pub/sub with mosquitto_pub/sub CLI',
            'week2' => 'Python publisher/subscriber, real-time Chart.js dashboard, MQTT auth added',
            'week3' => 'TLS/SSL on MQTT, cloud broker (HiveMQ Cloud free tier), persistent sessions',
        ],
    ],

    // ─────────────────────────────────────────────────────────────────────────
    'Graphic' => [
        'target_field' => 'Graphic',
        'label'        => 'Graphic Design & UI/UX',
        'icon'         => '🎨',
        'color'        => '#f472b6',

        'scrimba_url'  => null,
        'scrimba_note' => 'No Scrimba course — use Figma Community files and Material Design 3 guidelines',

        'youtube_channels' => [
            ['name'=>'Figma (Official)',   'url'=>'https://www.youtube.com/@Figma',
             'note'=>'Official channel — tutorials for Auto Layout, Variables, Dev Mode'],
            ['name'=>'DesignCourse',       'url'=>'https://www.youtube.com/@DesignCourse',
             'note'=>'UI/UX and graphic design principles with practical Figma work'],
            ['name'=>'AJ&Smart',          'url'=>'https://www.youtube.com/@AJSmart',
             'note'=>'Design thinking, Sprint methodology, client presentation skills'],
            ['name'=>'Flux',              'url'=>'https://www.youtube.com/@FluxAcademy',
             'note'=>'Freelance design, portfolio building, case study writing'],
            ['name'=>'Mizko',             'url'=>'https://www.youtube.com/@Mizko',
             'note'=>'Professional Figma workflows and design system building'],
        ],

        'documentation' => [
            ['name'=>'Figma Help Center',            'url'=>'https://help.figma.com/'],
            ['name'=>'Material Design 3',            'url'=>'https://m3.material.io/'],
            ['name'=>'Apple HIG',                    'url'=>'https://developer.apple.com/design/human-interface-guidelines/'],
            ['name'=>'NN/g UX Research Reports',     'url'=>'https://www.nngroup.com/reports/'],
            ['name'=>'WCAG 2.1 Guidelines',          'url'=>'https://www.w3.org/WAI/WCAG21/quickref/'],
        ],

        'tools' => [
            ['name'=>'Figma',       'url'=>'https://www.figma.com',
             'notes'=>'Free account — use Desktop app for better performance'],
            ['name'=>'Stark',       'url'=>'https://www.figma.com/community/plugin/732603254453395948/Stark',
             'notes'=>'Figma plugin — accessibility contrast checker'],
            ['name'=>'Unsplash',    'url'=>'https://unsplash.com',
             'notes'=>'Free stock photos for mockups'],
            ['name'=>'Loom',        'url'=>'https://www.loom.com',
             'notes'=>'Free screen recorder for prototype demos'],
            ['name'=>'draw.io',     'url'=>'https://app.diagrams.net/',
             'notes'=>'Free diagramming — for IoT architecture and user flows'],
        ],

        'free_resources' => [
            ['name'=>'Figma Community Files',            'url'=>'https://www.figma.com/community'],
            ['name'=>'Dribbble — Dashboard UI',          'url'=>'https://dribbble.com/tags/dashboard_ui'],
            ['name'=>'Refactoring UI — Book Excerpts',  'url'=>'https://www.refactoringui.com/'],
            ['name'=>'Behance — UX Case Studies',        'url'=>'https://www.behance.net/galleries/ux-ui'],
            ['name'=>'Google Fonts',                     'url'=>'https://fonts.google.com'],
        ],

        'milestones' => [
            'week1' => 'Figma basics, wireframes for 3 screens, basic component library',
            'week2' => 'Hi-fi with design system, heuristic evaluation, Smart Animate prototype, case study',
            'week3' => 'Motion design with Figma Prototyping, user testing session (5 participants), portfolio presentation',
        ],
    ],

]); // end PROSENSIA_MATERIALS

// ─────────────────────────────────────────────────────────────────────────────
// CLI usage: php materials_data.php
// ─────────────────────────────────────────────────────────────────────────────
if (php_sapi_name() === 'cli') {
    echo json_encode(PROSENSIA_MATERIALS, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
