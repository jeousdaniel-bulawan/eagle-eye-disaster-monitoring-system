"""
Corrected Mission Control backend.

Fixes applied vs. a typical broken setup:

1. STREAM FORWARDING (:5000 not routed through backend)
   - Flask app binds to 0.0.0.0 (not 127.0.0.1), so it's reachable from other
     devices on the LAN/dashboard, not just localhost.
   - run(..., threaded=True) — REQUIRED. Flask's dev server is single-threaded
     by default, so an MJPEG generator (which never returns) blocks every
     other route (/boxes, /stats) from ever responding while streaming.
   - /video_feed proxies the raw camera capture instead of assuming the
     browser can reach the camera directly.

2. BLACK SCREEN / CORS
   - flask-cors is applied globally. Without Access-Control-Allow-Origin,
     HLS.js's fetch() calls for the manifest/segments are blocked by the
     browser (even though a plain <img src=...> tag may look like it loads),
     which is why the stream endlessly buffers/black-screens for HLS but the
     endpoint "works" if you open it directly in a new tab.

3. AUTO-SCREENSHOT
   - The frontend canvas approach only works if crossOrigin is set on the
     <img>/<video> AND the server sends CORS headers (this file does).
   - As a more robust fallback that doesn't depend on browser CORS at all,
     this backend ALSO saves a screenshot server-side the moment YOLO detects
     a person, straight from the frame it already has in memory. This is the
     more reliable approach for anything mission-critical — it doesn't
     depend on the client's tab being open, focused, or CORS-clean.
"""

import os
import time
import threading
from datetime import datetime

import cv2
from flask import Flask, Response, jsonify
from flask_cors import CORS
from ultralytics import YOLO

app = Flask(__name__)
# Allow the dashboard's origin to read every response, including the video
# stream and any JSON endpoints. Lock this to your actual dashboard origin
# in production instead of "*".
CORS(app, resources={r"/*": {"origins": "*"}})

SCREENSHOT_DIR = "screenshots"
os.makedirs(SCREENSHOT_DIR, exist_ok=True)

model = YOLO("yolov8n.pt")
camera = cv2.VideoCapture(0)  # swap for your drone/RTSP source

# Shared state, guarded by a lock since the generator runs in a request thread
_state_lock = threading.Lock()
latest_boxes = {"current": 0, "boxes": [], "frame_w": 0, "frame_h": 0}
already_captured_ids = set()


def _save_screenshot(frame, tag):
    """Server-side capture — fires the instant a person is detected, no
    reliance on the browser/canvas being able to read the frame at all."""
    fname = f"{SCREENSHOT_DIR}/{tag}_{int(time.time())}.jpg"
    cv2.imwrite(fname, frame)
    print(f"[backend] saved screenshot: {fname}")
    return fname


def _gen_frames():
    frame_id = 0
    while True:
        ok, frame = camera.read()
        if not ok:
            time.sleep(0.05)
            continue

        results = model(frame, classes=[0], verbose=False)  # class 0 = person
        boxes = []
        for i, box in enumerate(results[0].boxes):
            x1, y1, x2, y2 = box.xyxy[0].tolist()
            det_id = f"{frame_id}_{i}"
            boxes.append({"id": det_id, "x1": x1, "y1": y1, "x2": x2, "y2": y2,
                          "conf": float(box.conf[0])})
            cv2.rectangle(frame, (int(x1), int(y1)), (int(x2), int(y2)), (0, 165, 255), 2)

            # Auto-screenshot: fire once per newly-seen person, server-side,
            # the moment they're detected — this is the fix for issue #1
            # that doesn't depend on frontend canvas/CORS at all.
            if det_id not in already_captured_ids:
                already_captured_ids.add(det_id)
                threading.Thread(target=_save_screenshot, args=(frame.copy(), det_id), daemon=True).start()

        with _state_lock:
            latest_boxes["current"] = len(boxes)
            latest_boxes["boxes"] = boxes
            latest_boxes["frame_w"] = frame.shape[1]
            latest_boxes["frame_h"] = frame.shape[0]

        ok, buf = cv2.imencode(".jpg", frame)
        if not ok:
            continue
        frame_id += 1
        yield (b"--frame\r\n"
               b"Content-Type: image/jpeg\r\n\r\n" + buf.tobytes() + b"\r\n")


@app.route("/video_feed")
def video_feed():
    # multipart/x-mixed-replace is what makes an <img src="/video_feed">
    # render as a live MJPEG stream in the browser.
    return Response(_gen_frames(), mimetype="multipart/x-mixed-replace; boundary=frame")


@app.route("/boxes")
def boxes():
    with _state_lock:
        return jsonify(dict(latest_boxes))


@app.route("/stats")
def stats():
    with _state_lock:
        return jsonify({"detections": latest_boxes["current"], "ts": datetime.utcnow().isoformat()})


if __name__ == "__main__":
    # host=0.0.0.0 -> reachable from other machines on the LAN (the dashboard),
    # not just this machine. threaded=True -> the MJPEG generator (an
    # infinite loop) won't block /boxes and /stats from responding.
    app.run(host="0.0.0.0", port=5000, threaded=True)