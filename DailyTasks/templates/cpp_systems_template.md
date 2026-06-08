# ⚙️ Systems Engineering Squad (C++): Progress Metrics

> **Current Progress:** {{PROGRESS_PCT}}% ({{COMPLETED_SLOTS}} out of {{TOTAL_SLOTS}} core bootcamp slots completed).
> **Current Focus:** Week {{WEEK_NUM}} Mandate → {{WEEK_MANDATE}}.

---

## 🗂️ The Daily Drop Blueprint

**Title:** Systems Engineering Squad: Week {{WEEK_NUM}}, {{DAY_NAME}} — {{TASK_TITLE}}

> ⚠️ **Note:** As C++ lacks a Scrimba module, you will rely entirely on enterprise documentation and industry lecture recordings. Treat cppreference.com as your primary syntax oracle and CppCon talks as your theory foundation.

---

## 📚 Section A: Today's Materials (The Synthesis Mandate)

> You must synthesize the information from all sources below to complete today's execution mandate. **Do not consult outside tutorials.**

### 1. Video Drills (Watch These First)

- **External Anchor 1:** YouTube: The Cherno — {{CHERNO_VIDEO}}
  *(The Cherno's C++ series is the definitive visual reference for {{CHERNO_TOPIC}})*

- **External Anchor 2:** YouTube: CppCon — {{CPPCON_TALK}}
  *(Mandatory enterprise theory on {{CPPCON_TOPIC}})*

- **External Video 1:** YouTube: {{EXT_VIDEO_1}} *({{EXT_VIDEO_1_NOTE}})*

### 2. Required Technical Documentation

- **Primary Architecture Doc:** LearnCpp.com — {{LEARNCPP_TOPIC}}
  *(Foundation of {{LEARNCPP_PURPOSE}})*

- **Specific Syntax Doc:** cppreference.com — {{CPPREFERENCE_TOPIC}}
  *(Read specifically how {{CPPREFERENCE_FOCUS}} operates)*

- **Configuration Doc:** {{CONFIG_DOC}} *(Required to {{CONFIG_PURPOSE}})*

### 3. Engineering Assets

- **Architecture Asset:** GitHub: Standard CMake C++ Boilerplate
  *(Reference for how enterprise C++ directories are structured)*

- **LLM Prompting Asset:** OpenAI Official Guide: Prompt Engineering
  *(Use this to ask the LLM to decode and explain complex {{TOOL_NAME}} stack traces — never to write core logic for you)*

- **Supporting Asset:** {{SUPPORTING_ASSET}} *({{SUPPORTING_ASSET_NOTE}})*

---

## ⚡ Section B: The Execution Mandate ({{UNLOCK_TIME}} – 2:00 PM)

### 1. Execution Summary (TL;DR)

**Objective:** {{OBJECTIVE_PARAGRAPH}}

### 2. The Workflow

**{{UNLOCK_TIME}} – 10:00 AM (Learning)**
Watch the **{{PRIMARY_VIDEO_TOPIC}}** drills and the CppCon lecture on **{{CPPCON_TOPIC}}**. Read the cppreference documentation on **{{CPPREFERENCE_TOPIC}}** — understand the overhead costs before you write a single line.

**10:00 AM – 1:00 PM (Building)**

- **Action 1 (Setup / Init):** Initialize your **GCC/Clang** environment. Configure your `CMakeLists.txt` to compile with aggressive safety flags:
  ```
  -Wall -Wextra -Werror -fsanitize={{SANITIZER_FLAGS}}
  ```
  {{SETUP_DETAIL}}

- **Action 2 (Core Logic / Data Structure):** {{CORE_CLASS_DETAIL}} Build methods for **{{METHODS_LIST}}**. Every component must satisfy the {{INVARIANT}} invariant.

- **Action 3 (The Memory Mandate):** {{MEMORY_ARCHITECTURE_DETAIL}} Apply **AddressSanitizer** to verify zero memory leaks — if your terminal outputs a single byte of leaked memory or a dangling pointer upon execution, your build fails the enterprise standard.

**The Non-Negotiable Rules:**

1. **The {{BAN_1_KEYWORD}} Ban:** You are **explicitly forbidden** from using `{{BAN_1_KEYWORDS}}`. You may only allocate memory using `std::make_unique` and `std::make_shared`. Any code containing **{{BAN_1_PATTERN}}** is an automatic failure.

2. **The Leak Ban:** Your compilation process must utilize **AddressSanitizer** (`-fsanitize=address`). A single byte of leaked memory, double-free, or dangling pointer output means your build fails. There are no partial-credit exceptions for memory hygiene.

**1:00 PM – 1:30 PM (Hygiene)**
Verify your code compiles flawlessly under the **`-Werror`** flag (zero warnings tolerated). Run AddressSanitizer one final time. Push your codebase including a clean `CMakeLists.txt` to your GitHub repository.

**1:30 PM – 2:00 PM (LinkedIn)**
Draft a technical summary of your execution. Detail the architectural differences between **{{LINKEDIN_CONCEPT_A}}** (today's approach) and **{{LINKEDIN_CONCEPT_B}}** (what you're replacing/avoiding). You **must** hyperlink your ProSensia admit card within the post text.

---

## 📋 Section C: Submission Protocol *(Due strictly by 2:00 PM)*

**Step 1 (The Kanban Board):**
Verify that this specific task card is shifted to the **"Under Review"** column on your team board.

**Step 2 (The Assignments Module):**
Navigate directly to today's entry in the platform's independent Assignment block. Paste both your **public GitHub Repository URL** and your **live LinkedIn Post URL** into the designated submission form fields.

> **Operational Note:** All grading and technical feedback loops operate through this portal. **Do not DM Management your links.**

---

## 🔧 Mentor Fill-In Guide (remove before publishing)

| Placeholder | Instructions |
|-------------|-------------|
| `{{WEEK_MANDATE}}` | e.g., "Memory Hygiene, RAII, and Smart Pointer Architecture" |
| `{{TASK_TITLE}}` | e.g., "RAII & Smart Pointer Architecture" |
| `{{CHERNO_VIDEO}}` | The Cherno video title |
| `{{CPPCON_TALK}}` | CppCon talk title |
| `{{LEARNCPP_TOPIC}}` | LearnCpp section |
| `{{CPPREFERENCE_TOPIC}}` | cppreference article |
| `{{SANITIZER_FLAGS}}` | e.g., `address,undefined` |
| `{{BAN_1_KEYWORD}}` | e.g., "Raw Pointer" |
| `{{BAN_1_KEYWORDS}}` | e.g., "`new` and `delete`" |
| `{{BAN_1_PATTERN}}` | e.g., "raw pointer ownership" |
| `{{METHODS_LIST}}` | e.g., "`append()`, `print()`, `delete()`" |
| `{{INVARIANT}}` | e.g., "zero-copy ownership" |
| `{{LINKEDIN_CONCEPT_A}}` | e.g., "C++ RAII" |
| `{{LINKEDIN_CONCEPT_B}}` | e.g., "Python/Java Garbage Collection" |
| `{{TOOL_NAME}}` | e.g., "Valgrind", "AddressSanitizer" |
