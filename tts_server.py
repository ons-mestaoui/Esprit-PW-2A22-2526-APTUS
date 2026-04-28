import os
import subprocess
from flask import Flask, request, send_file
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

@app.route('/tts', methods=['POST'])
def tts():
    data = request.json
    text = data.get('text', 'Bonjour')
    output = os.path.join(os.getcwd(), "aptus_voice.wav")
    
    if os.path.exists(output):
        try: os.remove(output)
        except: pass
        
    exe = r"C:\piper_tts\piper\piper.exe"
    model = r"C:\piper_tts\piper\fr_FR-upmc-medium.onnx"
    
    # Use subprocess.run for simplicity
    cmd = [exe, "--model", model, "--output_file", output]
    subprocess.run(cmd, input=text, text=True, encoding='utf-8')
    
    if os.path.exists(output):
        return send_file(output, mimetype="audio/wav")
    
    return {"error": "File not found after generation"}, 500

if __name__ == '__main__':
    app.run(port=5000)
