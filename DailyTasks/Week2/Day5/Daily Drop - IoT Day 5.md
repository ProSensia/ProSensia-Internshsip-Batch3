# 📡 IoT/Embedded Systems Squad — Week 2, Day 5 (Friday)
**Title:** Week 2 Review — System Integration Test, Architecture Diagram & Demo
**Progress:** 20% · Week 2 of 3-Month Bootcamp · Focus: System Thinking & Documentation

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Andreas Spiess — IoT System Architecture Explained https://www.youtube.com/watch?v=eUu5U7BNyCY
- **YouTube:** Fireship — Docker in 100 Seconds (containerise your broker) https://www.youtube.com/watch?v=Gjnup-PuquQ
- **YouTube:** RandomNerdTutorials — IoT Project: End-to-End Demo https://www.youtube.com/watch?v=6JuwPqhDjCE

### 2. Required Technical Documentation
- **Primary:** MQTT Security Best Practices https://www.hivemq.com/mqtt-security-fundamentals/
- **Secondary:** IoT Architecture Patterns — AWS IoT https://docs.aws.amazon.com/iot/latest/developerguide/iot-dg.pdf
- **Reference:** draw.io — Free Architecture Diagram Tool https://app.diagrams.net/

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Validate your full Week 2 IoT pipeline end-to-end (sensor → broker → dashboard). Draw a formal system architecture diagram. Add TLS security to your MQTT broker. Write a technical design document.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all videos. Study the MQTT Security fundamentals — specifically username/password authentication and TLS transport encryption. Understand the four-layer IoT architecture: Perception → Network → Processing → Application.

10:00 AM - 1:00 PM (Building):
Action 1 (Architecture Diagram): Open draw.io. Draw a system architecture diagram with all Week 2 components: Sensor (Python publisher) → Mosquitto Broker (MQTT) → WebSocket bridge → Browser Dashboard. Include: protocol labels (MQTT, ws://), port numbers, QoS levels, data flow direction arrows, and a "Threshold Alert" side path. Export as SVG and PNG.
Action 2 (Security Hardening): Add MQTT authentication to your Mosquitto broker: create a password file with mosquitto_passwd, add require_certificate false and password_file to mosquitto.conf. Update your Python publisher and subscriber to pass username/password in the MQTT connect call. Verify unauthenticated clients are rejected.
Action 3 (Full Integration Test): Document a 10-step integration test procedure in integration_test.md. Run through all 10 steps and mark pass/fail for each. Steps should cover: broker starts, publisher connects and sends, subscriber receives, dashboard displays, threshold alert fires, unauthenticated client rejected, disconnected publisher triggers LWT message, reconnect works, data logs to CSV, graceful shutdown.

The Non-Negotiable Rules:
1. Architecture diagram must be drawn in draw.io — not hand-sketched or described in text.
2. Unauthenticated MQTT connections must be rejected after security hardening — test and verify.
3. Integration test must be a formal pass/fail checklist — "it works" is not a test result.

1:00 PM - 1:30 PM (Hygiene): Update your README with the architecture diagram image. Add a "Security" section explaining the authentication setup and next steps (TLS certificates for Week 3). Tag commit v0.1-week2.

1:30 PM - 2:00 PM (LinkedIn): "Week 2 IoT complete at ProSensia. Built and documented a full MQTT sensor pipeline: Python publisher → Mosquitto → real-time WebSocket dashboard. Added broker authentication — unauthenticated clients now get Connection Refused. Security from day one."

---

## Section C: The Submission Protocol
1. **GitHub:** Push architecture diagram, integration_test.md, updated README. Tag v0.1-week2.
2. **Kanban:** Move ALL this week's cards to "Done"
3. **LinkedIn:** Post with architecture diagram image + #ProSensia #IoT #MQTT #SystemArchitecture
