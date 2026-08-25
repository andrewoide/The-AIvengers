# main.py - Versione ADATTATA per il tuo sito (modifiche minime)
import os
import sys
import re
import webbrowser
import numpy as np
import svgpathtools
from svgpathtools import Line, CubicBezier

# ===== MODIFICA 1: Ricevi il file SVG come argomento =====
if len(sys.argv) > 1:
    svg_file_path = sys.argv[1]
else:
    print("ERRORE: Specificare il percorso del file SVG")
    sys.exit(1)

# ===== MODIFICA 2: Cartella di output in Downloads =====
output_dir = os.path.join(os.path.expanduser("~"), "Downloads", "Equazioni_Desmos")
os.makedirs(output_dir, exist_ok=True)

# Verifica che il file esista
if not os.path.exists(svg_file_path):
    print(f"ERRORE: File non trovato - {svg_file_path}")
    sys.exit(1)

# ===== LEGGI IL FILE SVG (come nell'originale) =====
with open(svg_file_path, "r", encoding="utf-8") as f:
    data = str(f.read()).replace('fill="#000000" opacity="1.000000" stroke="none"', "")

# Enter file location here
#file = open(r"C:\Users\Utente\Downloads\images_vecto_SPLINE_D40.svg", "r")
#data = str(file.read()).replace('fill="#000000" opacity="1.000000" stroke="none"', "")
#file.close()

# ===== FUNZIONI (INVARIATE) =====
def _tokenize_path(pathfinder):
    FLOAT_RE = re.compile(r"[-+]?[0-9]*\.?[0-9]+(?:[eE][-+]?[0-9]+)?")
    for x in re.compile("([MmZzLlHhVvCcSsQqTtAa])").split(pathfinder):
        if x in set("MmZzLlHhVvCcSsQqTtAa"):
            yield x
        for token in FLOAT_RE.findall(x):
            yield token

def aplusbiFormat(real, imaginary):
    return real + imaginary * 1j

def extract_path(pathfinder, current_pos=0j):
    elements = list(_tokenize_path(pathfinder))
    elements.reverse()
    segments = []
    start_pos = None
    command = None

    while elements:
        if elements[-1] in set("MmZzLlHhVvCcSsQqTtAa"):
            command = elements.pop()
            absolute = command in set("MZLHVCSQTA")
            command = command.upper()
        else:
            if command is None:
                raise ValueError("Errore nel parsing del path")

        if command == "M":
            x = elements.pop()
            y = elements.pop()
            pos = float(x) + float(y) * 1j
            if absolute:
                current_pos = pos
            else:
                current_pos += pos
            start_pos = current_pos
            command = "L"

        elif command == "Z":
            if not (current_pos == start_pos):
                segments.append(Line(current_pos, start_pos))
            current_pos = start_pos
            command = None

        elif command == "L":
            x = elements.pop()
            y = elements.pop()
            pos = float(x) + float(y) * 1j
            if not absolute:
                pos += current_pos
            segments.append(Line(current_pos, pos))
            current_pos = pos

        elif command == "C":
            control1 = float(elements.pop()) + float(elements.pop()) * 1j
            control2 = float(elements.pop()) + float(elements.pop()) * 1j
            final = float(elements.pop()) + float(elements.pop()) * 1j

            if not absolute:
                control1 += current_pos
                control2 += current_pos
                final += current_pos

            segments.append(CubicBezier(current_pos, control1, control2, final))
            current_pos = final

    return segments

# ===== ELABORAZIONE =====
# PATTERN FLESSIBILE: cerca d=" in qualsiasi punto del tag path
pathArray = re.findall(r'<path[^>]*d="([^"]*)"', data, re.DOTALL)

print(f"DEBUG: Trovati {len(pathArray)} path nel SVG")

pathString = ""
for path in pathArray:
    pathString += path

path = extract_path(pathString)

equations, regularEquations = [], []
for segment in path:

    if isinstance(segment, svgpathtools.path.Line):
        start = aplusbiFormat(segment.start.real, segment.start.imag)
        end = aplusbiFormat(segment.end.real, segment.end.imag)

        if end.real - start.real != 0 and end.imag - start.imag != 0:
            m = (end.imag - start.imag) / (end.real - start.real)
            b = start.imag - m * start.real
            xMin = min(start.real, end.real)
            xMax = max(start.real, end.real)
            yMin = min(start.imag, end.imag)
            yMax = max(start.imag, end.imag)

            equations.append(
                "y="
                + str(m)
                + "x+"
                + str(b)
                + "\\\\left\\\\{"
                + str(xMin)
                + "\\\\le x \\\\le "
                + str(yMin)
                + "\\\\right\\\\}\\\\left\\\\{"
                + str(yMin)
                + "\\\\le y \\\\le "
                + str(yMax)
                + "\\\\right\\\\}"
            )
            regularEquations.append(lambda x: m * x + b)
        if end.real - start.real == 0:
            xMin = min(start.real, end.real)
            xMax = max(start.real, end.real)
            yMin = min(start.imag, end.imag)
            yMax = max(start.imag, end.imag)

            equations.append(
                "x="
                + str(start.real)
                + "\\\\left\\\\{"
                + str(xMin)
                + "\\\\le x \\\\le "
                + str(yMin)
                + "\\\\right\\\\}\\\\left\\\\{"
                + str(yMin)
                + "\\\\le y \\\\le "
                + str(yMax)
                + "\\\\right\\\\}"
            )
            regularEquations.append(lambda x: start.real)
        else:
            yMin = min(start.imag, end.imag)
            yMax = max(start.imag, end.imag)

            equations.append(
                "x="
                + str(start.real)
                + "\\\\left\\\\{"
                + str(yMin)
                + "\\\\le y \\\\le "
                + str(yMax)
                + "\\\\right\\\\}"
            )
            regularEquations.append(lambda x: start.real)

    elif isinstance(segment, svgpathtools.path.CubicBezier):
        p0 = aplusbiFormat(segment.start.real, segment.start.imag)
        p1 = aplusbiFormat(segment.control1.real, segment.control1.imag)
        p2 = aplusbiFormat(segment.control2.real, segment.control2.imag)
        p3 = aplusbiFormat(segment.end.real, segment.end.imag)

        equations.append(
            "\\\\left((1-t)^3*"
            + str(p0.real)
            + "+3*t*(1-t)^2*"
            + str(p1.real)
            + "+3*t^2*(1-t)*"
            + str(p2.real)
            + "+t^3*"
            + str(p3.real)
            + ", (1-t)^3*"
            + str(p0.imag)
            + "+3*t*(1-t)^2*"
            + str(p1.imag)
            + "+3*t^2*(1-t)*"
            + str(p2.imag)
            + "+t^3*"
            + str(p3.imag)
            + ")\\\\right)"
        )
        regularEquations.append(
            lambda t: (1 - t) ** 3 * p0
                      + 3 * t * (1 - t) ** 2 * p1
                      + 3 * t ** 2 * (1 - t) * p2
                      + t ** 3 * p3
        )

    elif isinstance(segment, svgpathtools.path.QuadraticBezier):
        p0 = aplusbiFormat(segment.start.real, segment.start.imag)
        p1 = aplusbiFormat(segment.control.real, segment.control.imag)
        p2 = aplusbiFormat(segment.end.real, segment.end.imag)

        equations.append(
            "\\\\left((1-t)^2*"
            + str(p0.real)
            + "+2*t*(1-t)*"
            + str(p1.real)
            + "+t^2*"
            + str(p2.real)
            + ", (1-t)^2*"
            + str(p0.imag)
            + "+2*t*(1-t)*"
            + str(p1.imag)
            + "+t^2*"
            + str(p2.imag)
            + ")\\\\right))"
        )
        regularEquations.append(
            lambda t: (1 - t) ** 2 * p0 + 2 * t * (1 - t) * p1 + t ** 2 * p2
        )

    elif isinstance(segment, svgpathtools.path.Arc):
        p0 = aplusbiFormat(segment.start.real, segment.start.imag)
        p1 = aplusbiFormat(segment.end.real, segment.end.imag)
        r = aplusbiFormat(segment.radius.real, segment.radius.imag)

        equations.append(
            "\\\\left("
            + str(p0.real)
            + "+"
            + str(r.real)
            + "*\\cos(t), "
            + str(p0.imag)
            + "+"
            + str(r.imag)
            + "*\\sin(t)\\\\right)"
        )
        regularEquations.append(lambda t: p0 + r * np.exp(1j * t))

    else:
        print("Unknown segment type: " + str(type(segment)))

# ===== GENERAZIONE HTML (INVARIATA) =====
desmos = """
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Grafico Desmos - Image to Equations</title>
<style>
    body { margin: 0; overflow: hidden; }
    #calculator { width: 100vw; height: 100vh; }
</style>
</head>
<body>
<script src="https://www.desmos.com/api/v1.8/calculator.js?apiKey=dcb31709b452b1cf9dc26972add0fda6"></script>
<div id="calculator"></div>
<script>
 var elt = document.getElementById('calculator');
 var calculator = Desmos.GraphingCalculator(elt);
"""

desmos += (
    "calculator.setMathBounds({ left: "
    + str(-194.97)
    + ", right: "
    + str(8852.635)
    + ", bottom: "
    + str(-221.556)
    + ", top: "
    + str(6152.893)
    + " });\n"
)

for i in range(len(equations)):
    desmos += (
        "calculator.setExpression({ id: 'a-slider"
        + str(i)
        + "', latex: '"
        + equations[i]
        + "', color: Desmos.Colors.BLACK });\n"
    )

desmos += """
</script>
</body>
</html>
"""

# ===== MODIFICA 3: Salva in Downloads con timestamp =====
timestamp = int(os.times().system)
if timestamp < 1000:
    import time
    timestamp = int(time.time())

filename = f"desmos_graph_{timestamp}.html"
filepath = os.path.join(output_dir, filename)

with open(filepath, "w", encoding="utf-8") as f:
    f.write(desmos)

# ===== MODIFICA 4: Salva equazioni in Downloads =====
equations_file = os.path.join(output_dir, f"equations_{timestamp}.txt")
with open(equations_file, "w", encoding="utf-8") as f:
    for i in range(len(equations)):
        f.write(equations[i].replace("\\\\", "\\") + "\n")

# ===== OUTPUT PER DEBUG (leggi da PHP) =====
print(f"HTML salvato: {filepath}")
print(f"Equazioni salvate: {equations_file}")
print(f"Numero di equazioni: {len(equations)}")
print(f"Cartella: {output_dir}")