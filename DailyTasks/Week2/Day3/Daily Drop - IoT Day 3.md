# 📡 IoT/Embedded Systems Squad — Week 2, Day 3 (Wednesday)
**Title:** MQTT Protocol — Publish/Subscribe Messaging with Mosquitto Broker
**Progress:** 10% · Week 2 of 3-Month Bootcamp · Focus: IoT Communication Protocols

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Andreas Spiess — MQTT Explained (The Best IoT Protocol) https://www.youtube.com/watch?v=LKz1jYngpcU
- **YouTube:** The Hook Up — MQTT Beginners Guide with Mosquitto https://www.youtube.com/watch?v=aQcJ4uHdQEA
- **YouTube:** RandomNerdTutorials — MQTT with ESP32 and Raspberry Pi https://www.youtube.com/watch?v=hqTaTNoKfgE

### 2. Required Technical Documentation
- **Primary:** MQTT Essentials — HiveMQ (all 11 parts) https://www.hivemq.com/mqtt-essentials/
- **Secondary:** Eclipse Mosquitto Documentation https://mosquitto.org/documentation/
- **Reference:** Paho MQTT Python Client https://eclipse.dev/paho/files/paho.mqtt.python/html/index.html

### 3. Engineering Assets
- **Broker:** Mosquitto (install locally OR use test.mosquitto.org for cloud testing)
- **Client Library:** paho-mqtt (pip install paho-mqtt)
- **Simulator:** If no physical hardware, use Python to simulate a sensor publisher

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Set up an MQTT broker (Mosquitto), write a Python sensor publisher that simulates temperature/humidity data, and a Python subscriber that receives, logs, and alerts on threshold breach. Demonstrate QoS levels 0, 1, and 2.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Read the HiveMQ MQTT Essentials series (parts 1-5 minimum). Understand: broker, topic hierarchy, QoS levels, retained messages, last will and testament.

10:00 AM - 1:00 PM (Building):
Action 1 (Setup): Install Mosquitto locally. Start the broker on port 1883. Verify it's running with mosquitto_pub -t "test/ping" -m "hello" and mosquitto_sub -t "test/ping". Document your broker config.
Action 2 (Publisher): Write sensor_publisher.py that publishes simulated readings every 5 seconds to topic prosensia/sensors/temperature and prosensia/sensors/humidity. Use QoS=1. Include a timestamp in the payload as JSON: {"value": 23.5, "unit": "C", "timestamp": "..."}
Action 3 (Subscriber + Alerting): Write sensor_subscriber.py that subscribes to prosensia/sensors/#. Log all incoming messages with timestamp to sensor_log.csv. If temperature exceeds 30°C, print a ALERT: Threshold breached! message to console. Demonstrate QoS 0, 1, and 2 by publishing the same message three times with different QoS levels and observing broker behavior.

The Non-Negotiable Rules:
1. All MQTT payloads must be valid JSON — no plain-text values.
2. Topic naming must follow hierarchy convention: prosensia/sensors/[sensor_type]
3. You must demonstrate and document the difference between QoS 0, 1, and 2 in your README.

1:00 PM - 1:30 PM (Hygiene): Write a README_MQTT.md explaining: your broker setup steps, topic hierarchy design decision, QoS level comparison table, and how to run publisher and subscriber. Commit everything.

1:30 PM - 2:00 PM (LinkedIn): "Day 3 IoT at ProSensia — implemented MQTT pub/sub with a Mosquitto broker. QoS 2 guarantees exactly-once delivery — critical for sensor data you can't afford to lose or duplicate in industrial systems."

---

## Section C: The Submission Protocol
1. **GitHub:** Push sensor_publisher.py, sensor_subscriber.py, README_MQTT.md with commit: feat: MQTT pub/sub with Mosquitto broker and threshold alerting
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #IoT #MQTT #EmbeddedSystems
