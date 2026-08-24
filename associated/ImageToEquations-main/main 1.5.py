# Modules
import re
import webbrowser
import os
import time
import shutil

import numpy as np
import svgpathtools
from svgpathtools import Line, CubicBezier

"""
Usage:
 - Only SVG file types are supported
 - Use https://freesvg.org/ for free SVG files
 - Or convert PNG images to SVG using https://convertio.co/png-svg/
"""

# ===== NUOVA FUNZIONE PER GENERARE NOME UNIVOCO =====
def generate_unique_filename(prefix="desmos_graph"):
    """Genera un nome file unico con timestamp"""
    timestamp = int(time.time())
    return f"{prefix}_{timestamp}.html"

# ===== ENTER FILE LOCATION HERE =====
# Modifica questo percorso con il tuo file SVG di input
svg_file_path = r"C:\Users\Utente\Downloads\images_vecto_SPLINE_D24.svg"

# Leggi il file SVG
with open(svg_file_path, "r") as f:
    data = str(f.read()).replace('fill="#000000" opacity="1.000000" stroke="none"', "")

# ===== FUNZIONI ESISTENTI (invariate) =====

# Detect the types of segments
def _tokenize_path(pathfinder):
    FLOAT_RE = re.compile(r"[-+]?[0-9]*\.?[0-9]+(?:[eE][-+]?[0-9]+)?")
    for x in re.compile("([MmZzLlHhVvCcSsQqTtAa])").split(pathfinder):
        if x in set("MmZzLlHhVvCcSsQqTtAa"):
            yield x
        for token in FLOAT_RE.findall(x):
            yield token

# transform into a complex number in the for (a + bi)
def aplusbiFormat(real, imaginary):
    return real + imaginary * 1j

# Convert points to equations from the bezier points
def extract_path(pathfinder, current_pos=0j):
    # Variables
    elements = list(_tokenize_path(pathfinder))
    elements.reverse()
    segments = []
    start_pos = None
    command = None

    # Loop through all the paths
    while elements:
        if elements[-1] in set("MmZzLlHhVvCcSsQqTtAa"):
            command = elements.pop()
            absolute = command in set("MZLHVCSQTA")
            command = command.upper()
        else:
            if command is None:
                raise ValueError("idk what happened so im just gonna say error. Error!")

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

# ===== ELABORAZIONE (invariata) =====

# get all the text in between the <path and ></path>
pathArray = re.findall(r'<path d="(.*?)"', data, re.DOTALL)

pathString = ""
for path in pathArray:
    pathString += path

path = extract_path(pathString)  # Get the path from the SVG file

equations, regularEquations = [], []
for segment in path:

    # Iterate through each segment, a set of 4 points, in the SVG file and check what type of segment it is
    if isinstance(segment, svgpathtools.path.Line):

        # Extract the start and end points from the line segment
        start = aplusbiFormat(segment.start.real, segment.start.imag)
        end = aplusbiFormat(segment.end.real, segment.end.imag)

        # check to make sure line doesn't have undefined slope to prevent mathematical errors
        if end.real - start.real != 0 and end.imag - start.imag != 0:
            # calculate the slope and y-intercept of the line segment
            m = (end.imag - start.imag) / (end.real - start.real)
            b = start.imag - m * start.real

            # calculate the bounds of the line segment in the x direction
            xMin = min(start.real, end.real)
            xMax = max(start.real, end.real)

            # calculate the bounds of the line segment in the y direction
            yMin = min(start.imag, end.imag)
            yMax = max(start.imag, end.imag)

            # Convert the linear equation into the form y=mx+b and put it in latex format
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

            # Convert the linear equation into the form y=mx+b and put it in lambda format
            regularEquations.append(lambda x: m * x + b)
        if end.real - start.real == 0:
            # calculate the bounds of the line segment in the x direction
            xMin = min(start.real, end.real)
            xMax = max(start.real, end.real)

            # calculate the bounds of the line segment in the y direction
            yMin = min(start.imag, end.imag)
            yMax = max(start.imag, end.imag)

            # Convert the linear equation into the form x=c and put it in latex format
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

            # Convert the linear equation into the form x=c and put it in lambda format
            regularEquations.append(lambda x: start.real)
        else:
            yMin = min(start.imag, end.imag)
            yMax = max(start.imag, end.imag)

            # if the slope is undefined, then the line is vertical and the equation is in the form x=a
            equations.append(
                "x="
                + str(start.real)
                + "\\\\left\\\\{"
                + str(yMin)
                + "\\\\le y \\\\le "
                + str(yMax)
                + "\\\\right\\\\}"
            )

            # if the slope is undefined, then the line is vertical and the equation is in the form x=a
            regularEquations.append(lambda x: start.real)

    elif isinstance(segment, svgpathtools.path.CubicBezier):

        # extract the bezier points from the segment
        p0 = aplusbiFormat(segment.start.real, segment.start.imag)
        p1 = aplusbiFormat(segment.control1.real, segment.control1.imag)
        p2 = aplusbiFormat(segment.control2.real, segment.control2.imag)
        p3 = aplusbiFormat(segment.end.real, segment.end.imag)

        # Convert the bezier points into a parametric equation in latex format
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

        # Convert the bezier points into a parametric equation in lambda format
        regularEquations.append(
            lambda t: (1 - t) ** 3 * p0
                      + 3 * t * (1 - t) ** 2 * p1
                      + 3 * t ** 2 * (1 - t) * p2
                      + t ** 3 * p3
        )

    elif isinstance(segment, svgpathtools.path.QuadraticBezier):
        # Quadratic Bezier segment
        p0 = aplusbiFormat(segment.start.real, segment.start.imag)
        p1 = aplusbiFormat(segment.control.real, segment.control.imag)
        p2 = aplusbiFormat(segment.end.real, segment.end.imag)

        # Convert the bezier points into a parametric equation in latex format
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

        # Convert the bezier points into a parametric equation in lambda format
        regularEquations.append(
            lambda t: (1 - t) ** 2 * p0 + 2 * t * (1 - t) * p1 + t ** 2 * p2
        )

    elif isinstance(segment, svgpathtools.path.Arc):
        # Elliptical arc segment
        p0 = aplusbiFormat(segment.start.real, segment.start.imag)
        p1 = aplusbiFormat(segment.end.real, segment.end.imag)
        r = aplusbiFormat(segment.radius.real, segment.radius.imag)

        # Convert the bezier points into a parametric equation in latex format
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

        # Convert the bezier points into a parametric equation in lambda format
        regularEquations.append(lambda t: p0 + r * np.exp(1j * t))

    else:
        print("Unknown segment type: " + str(type(segment)))

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

# Add the bounds to the Desmos API script
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

# Add each equation to the Desmos API script
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

# ===== SALVATAGGIO CON NOME UNIVOCO =====

# Definisci il percorso diretto alla cartella di WordPress
output_dir = r"C:\Users\Utente\Local Sites\your-own-blaiprint\associated\Immagini generate"

# Crea la cartella se non esiste (per sicurezza)
if not os.path.exists(output_dir):
    os.makedirs(output_dir)

# Genera un nome file univoco
filename = generate_unique_filename()
filepath = os.path.join(output_dir, filename)

# Salva il file HTML direttamente nella cartella di WordPress
with open(filepath, "w", encoding="utf-8") as f:
    f.write(desmos)

# ===== SALVA LE EQUAZIONI IN UN FILE DI TESTO =====
equations_file = os.path.join(output_dir, f"equations_{int(time.time())}.txt")
with open(equations_file, "w", encoding="utf-8") as f:
    for i in range(len(equations)):
        f.write(equations[i].replace("\\\\", "\\") + "\n")

# ===== OUTPUT PER DEBUG (verrà catturato da PHP) =====
print(f"✅ HTML salvato: {filepath}")
print(f"✅ Equazioni salvate: {equations_file}")
print(f"📐 Numero di equazioni: {len(equations)}")

# Opzionale: non aprire il browser se eseguito da riga di comando
# webbrowser.open(filepath, new=2)