# ⚙️ C++ Systems Squad — Week 2, Day 3 (Wednesday)
**Title:** STL Containers, Algorithms & Iterator Architecture
**Progress:** 10% · Week 2 of 3-Month Bootcamp · Focus: Standard Template Library Mastery

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** The Cherno — std::vector in C++ https://www.youtube.com/watch?v=PocJ5jXv8No
- **YouTube:** The Cherno — std::map in C++ https://www.youtube.com/watch?v=KiB0vRi2wlc
- **YouTube:** CppCon — Back to Basics: Iterators https://www.youtube.com/watch?v=26aW6aBVpk0

### 2. Required Technical Documentation
- **Primary:** cppreference.com — STL Containers https://en.cppreference.com/w/cpp/container
- **Secondary:** cppreference.com — STL Algorithms https://en.cppreference.com/w/cpp/algorithm
- **Modern:** LearnCpp.com — Chapter 16: STL https://www.learncpp.com/cpp-tutorial/an-introduction-to-stdvector/

### 3. Engineering Assets
- **Build System:** CMakeLists.txt from Day 1 (extend it — do not create a new project)
- **Sanitizer:** AddressSanitizer must remain active: -fsanitize=address,undefined

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Extend your smart-pointer data structure from Day 2 to use STL containers. Replace raw arrays with std::vector and std::unordered_map. Apply STL algorithms (std::sort, std::find_if, std::transform) using range-based for and iterator patterns.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Note the performance characteristics: O(1) vector access vs O(log n) map lookup vs O(1) unordered_map. This knowledge informs your container choices today.

10:00 AM - 1:00 PM (Building):
Action 1 (Setup/Migrate): Refactor your Day 2 linked list to use std::vector<std::unique_ptr<Node>> as the backing store. Justify this choice in a code comment: why vector over list here?
Action 2 (Core STL): Add a std::unordered_map<std::string, std::weak_ptr<Node>> as an index/lookup table. Implement a find(key) method that returns in O(1). Use std::find_if on the vector for linear-search fallback.
Action 3 (Algorithms): Apply std::transform to produce a std::vector<std::string> of all node labels. Use std::sort with a custom comparator lambda. Write a unit test for each STL operation using a simple assert().

The Non-Negotiable Rules:
1. Raw new/delete are completely banned — use std::make_unique or std::make_shared exclusively.
2. No raw pointer returns from any public method — return references, values, or smart pointers.
3. AddressSanitizer must report zero memory errors on the final binary.

1:00 PM - 1:30 PM (Hygiene): Run cmake --build and confirm zero warnings with -Wall -Wextra. Run valgrind ./your_binary to confirm zero leaks as a second check alongside ASAN.

1:30 PM - 2:00 PM (LinkedIn): "Day 3 at ProSensia — replaced raw arrays with STL containers. std::unordered_map gives O(1) lookup vs O(n) linear scan. Modern C++ is about choosing the right abstraction, not just making it compile."

---

## Section C: The Submission Protocol
1. **GitHub:** Push with commit: feat: migrate to STL vector/unordered_map with algorithm layer
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #CPlusPlus #STL #SystemsProgramming
