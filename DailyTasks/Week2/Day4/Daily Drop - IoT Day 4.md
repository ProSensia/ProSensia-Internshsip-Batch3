# 📡 IoT/Embedded Systems Squad — Week 2, Day 4 (Thursday)
**Title:** Sensor Data Dashboard — Real-Time Visualisation with Node-RED or MQTT + Chart.js
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: IoT Data Visualisation Pipeline

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Andreas Spiess — Node-RED for IoT Beginners https://www.youtube.com/watch?v=3AR432bguOY
- **YouTube:** RandomNerdTutorials — MQTT Dashboard with Node-RED https://www.youtube.com/watch?v=RWbxlHToaYk
- **YouTube:** Chart.js — Full Tutorial for Beginners https://www.youtube.com/watch?v=sE08f4iuOhA

### 2. Required Technical Documentation
- **Primary:** Node-RED Documentation — Getting Started https://nodered.org/docs/getting-started/
- **Secondary:** Chart.js — Getting Started https://www.chartjs.org/docs/latest/getting-started/
- **Reference:** MQTT.js — Browser MQTT Client https://github.com/mqttjs/MQTT.js

### 3. Engineering Assets
- **Continue:** Your Day 3 sensor_publisher.py and Mosquitto broker setup
- **New Tool:** Node-RED (npm install -g --unsafe-perm node-red) OR pure HTML/JS with MQTT.js
- **Visualisation:** Chart.js via CDN for the web dashboard

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Build a live web dashboard that displays real-time sensor data from your Day 3 MQTT broker. The dashboard must show a live-updating line chart for temperature and humidity, a current reading display, and a threshold alert panel.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch the Node-RED and MQTT dashboard videos. Study Chart.js real-time update patterns (adding data to existing datasets, shifting old data off). Understand WebSocket vs. MQTT over WebSocket for browser clients.

10:00 AM - 1:00 PM (Building):
Action 1 (MQTT WebSocket Bridge): Configure Mosquitto to accept WebSocket connections on port 9001 (add listener 9001 and protocol websockets to mosquitto.conf). Verify browser can connect using MQTT.js over ws://localhost:9001.
Action 2 (Live Dashboard): Create dashboard.html with Chart.js. Connect via MQTT.js to ws://localhost:9001. Subscribe to prosensia/sensors/#. On message: parse JSON payload, push timestamp + value to a rolling 30-point line chart dataset. Update the chart with chart.update(). Display current temperature and humidity as large numeric readouts.
Action 3 (Alert Panel): Add a threshold alert section: if temperature > 30°C, show a red flashing badge "HIGH TEMPERATURE". If humidity > 80%, show a yellow warning. Log all threshold breaches in a scrollable event log below the chart with timestamps.

The Non-Negotiable Rules:
1. Dashboard must update in real-time without page refresh — no polling, only MQTT subscriptions.
2. Chart must display rolling data (last 30 readings max) — do not grow the array unboundedly.
3. Run your Day 3 sensor_publisher.py during the demo to prove it shows live data.

1:00 PM - 1:30 PM (Hygiene): Test the full pipeline: start Mosquitto → start publisher → open dashboard. Confirm chart updates every 5 seconds. Take a screenshot of the running dashboard for your GitHub README. Write a 5-step setup guide in README.

1:30 PM - 2:00 PM (LinkedIn): "Day 4 IoT at ProSensia — built a real-time MQTT sensor dashboard with Chart.js. Data flows: Python sensor → Mosquitto broker → WebSocket → browser chart. Zero polling, pure event-driven architecture."

---

## Section C: The Submission Protocol
1. **GitHub:** Push dashboard.html, updated mosquitto.conf, README with screenshot with commit: feat: add real-time MQTT sensor dashboard
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #IoT #MQTT #RealTimeDashboard
