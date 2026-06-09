# ⚙️ C++ Systems Squad — Week 2, Day 4 (Thursday)
**Title:** Template Metaprogramming & Generic Algorithms
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: C++ Templates & Generics

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** The Cherno — Templates in C++ https://www.youtube.com/watch?v=I-hZkUa9mIs
- **YouTube:** CppCon — Back to Basics: Function and Class Templates https://www.youtube.com/watch?v=LMP_sxOaz6g
- **YouTube:** The Cherno — Iterators in C++ https://www.youtube.com/watch?v=F9eDv-YIOQ0

### 2. Required Technical Documentation
- **Primary:** cppreference.com — Templates https://en.cppreference.com/w/cpp/language/templates
- **Secondary:** cppreference.com — std::enable_if https://en.cppreference.com/w/cpp/types/enable_if
- **Modern:** LearnCpp.com — Chapter 26: Template classes https://www.learncpp.com/cpp-tutorial/template-classes/

### 3. Engineering Assets
- **Build System:** Same CMakeLists.txt — add a new target for today's generic_container binary
- **Standard:** C++17 minimum (add set(CMAKE_CXX_STANDARD 17) if not already set)

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Extend your Day 3 STL-based structure with a generic template wrapper. Create a template class SortedContainer<T, Compare> that wraps std::vector<T>, maintains sorted order on insert, and exposes begin()/end() iterators. Use SFINAE or concepts (C++20 optional) to restrict T to comparable types.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Focus on: class templates, function templates, template specialisation, and SFINAE basics. Understand the difference between compile-time polymorphism (templates) and runtime polymorphism (virtual).

10:00 AM - 1:00 PM (Building):
Action 1 (Basic Template): Create SortedContainer<T> as a template class. It must wrap std::vector<T> and maintain sorted order: insert() should use std::lower_bound + std::vector::insert to insert in the correct position. Implement contains(T val), remove(T val), size(), and begin()/end() iterator passthrough.
Action 2 (Custom Comparator): Add a second template parameter: SortedContainer<T, Compare = std::less<T>>. Allow the user to pass std::greater<T> or a custom lambda comparator. Write tests with: SortedContainer<int>, SortedContainer<std::string>, SortedContainer<int, std::greater<int>>.
Action 3 (Type Constraint): Use static_assert with a meaningful message to prevent instantiation with non-comparable types. Optional challenge: use C++20 requires clause (std::totally_ordered concept) if your compiler supports it.

The Non-Negotiable Rules:
1. SortedContainer must maintain sorted order at all times — no sort() call after the fact.
2. Template code must compile with -Wall -Wextra -Wpedantic with zero warnings.
3. All three test cases (int, string, reverse-order) must pass as compile-time and runtime verified asserts.

1:00 PM - 1:30 PM (Hygiene): Run cmake --build. Confirm all tests pass. Check that SortedContainer.hpp is properly header-guarded (#pragma once or #ifndef). Run ASAN binary — zero errors.

1:30 PM - 2:00 PM (LinkedIn): "Day 4 C++ at ProSensia — built a generic SortedContainer<T, Compare> using templates. Compile-time polymorphism in C++ is more powerful than runtime inheritance for containers: zero virtual dispatch overhead, full type safety."

---

## Section C: The Submission Protocol
1. **GitHub:** Push SortedContainer.hpp and test file with commit: feat: add generic SortedContainer template with custom comparator
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #CPlusPlus #Templates #SystemsProgramming
