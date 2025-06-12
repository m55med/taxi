from http.server import BaseHTTPRequestHandler, HTTPServer
import json

class TelegramDebugHandler(BaseHTTPRequestHandler):
    def do_POST(self):
        content_length = int(self.headers['Content-Length'])
        body = self.rfile.read(content_length)
        try:
            data = json.loads(body)
            print("\n📩 Received Telegram Request:")
            print(json.dumps(data, indent=4))
        except Exception as e:
            print("⚠️ Error decoding JSON:", e)
            print("Raw data:", body.decode())

        self.send_response(200)
        self.end_headers()
        self.wfile.write(b'OK')

if __name__ == "__main__":
    server_address = ('localhost', 5000)  # or 127.0.0.1
    httpd = HTTPServer(server_address, TelegramDebugHandler)
    print("🚀 Telegram Debug Server running at http://localhost:5000")
    httpd.serve_forever()
