# main.py - Converte un file SVG in equazioni Desmos (versione corretta, con modalita' "leggera")
#
# Uso:  python main_checkpoint.py percorso\del\file.svg
#
# Richiede:  pip install svgpathtools
#
# Due modalita' (scelta automatica in base al numero di segmenti):
#  - ESATTA:    un'equazione parametrica per ogni segmento (immagini semplici)
#  - POLILINEE: i tracciati vengono semplificati e raggruppati in poche liste di punti
#               (immagini dettagliate: Desmos non regge migliaia di equazioni separate)
import json
import math
import os
import re
import sys
import time

import numpy as np
from svgpathtools import Arc, CubicBezier, Line, QuadraticBezier, parse_path

# ===== IMPOSTAZIONI =====
FLIP_Y = True               # SVG ha la y verso il basso, Desmos verso l'alto: True = immagine dritta
DECIMALS = 3                # cifre decimali nelle equazioni
MAX_EXACT_SEGMENTS = 1500   # oltre questo numero di segmenti si passa alla modalita' POLILINEE
SIMPLIFY_REL = 0.0003       # tolleranza di semplificazione, in frazione della diagonale dell'immagine
                            # (piu' alta = meno punti ma meno dettaglio; 0 = nessuna semplificazione)
MAX_LIST = 5000             # punti massimi per lista (il limite di Desmos e' 10000)
DESMOS_API_KEY = "dcb31709b452b1cf9dc26972add0fda6"

# ===== INPUT =====
if len(sys.argv) < 2:
    print("ERRORE: Specificare il percorso del file SVG")
    sys.exit(1)

svg_file_path = sys.argv[1]
if not os.path.exists(svg_file_path):
    print(f"ERRORE: File non trovato - {svg_file_path}")
    sys.exit(1)

output_dir = os.path.join(os.path.expanduser("~"), "Downloads", "Equazioni_Desmos")
os.makedirs(output_dir, exist_ok=True)

with open(svg_file_path, "r", encoding="utf-8") as f:
    data = f.read()


# ===== FUNZIONI =====
def plain(v):
    """Numero senza parentesi (per le liste)."""
    s = f"{v:.{DECIMALS}f}".rstrip("0").rstrip(".")
    return "0" if s in ("", "-0") else s


def num(v):
    """Numero in formato LaTeX; i negativi vanno tra parentesi per evitare '+-'."""
    s = plain(v)
    return f"({s})" if s.startswith("-") else s


def pt(z):
    """Punto complesso -> (x, y) con eventuale inversione dell'asse y."""
    return z.real, (-z.imag if FLIP_Y else z.imag)


def bernstein(vals):
    """Polinomio di Bernstein in t per 2, 3 o 4 valori (retta, quadratica, cubica)."""
    v = [num(x) for x in vals]
    if len(v) == 2:
        return f"(1-t)\\cdot {v[0]}+t\\cdot {v[1]}"
    if len(v) == 3:
        return (f"(1-t)^2\\cdot {v[0]}+2\\cdot t\\cdot (1-t)\\cdot {v[1]}"
                f"+t^2\\cdot {v[2]}")
    return (f"(1-t)^3\\cdot {v[0]}+3\\cdot t\\cdot (1-t)^2\\cdot {v[1]}"
            f"+3\\cdot t^2\\cdot (1-t)\\cdot {v[2]}+t^3\\cdot {v[3]}")


def segment_to_latex(seg):
    """Equazione parametrica (x(t), y(t)) con t in [0, 1] (default di Desmos)."""
    if isinstance(seg, Line):
        points = [seg.start, seg.end]
    elif isinstance(seg, QuadraticBezier):
        points = [seg.start, seg.control, seg.end]
    else:  # CubicBezier
        points = [seg.start, seg.control1, seg.control2, seg.end]
    xs, ys = zip(*(pt(p) for p in points))
    return "\\left(" + bernstein(xs) + "," + bernstein(ys) + "\\right)"


def arc_to_cubics(arc):
    """Approssima un arco ellittico con una serie di Bezier cubiche (max 90 gradi l'una)."""
    rx, ry = arc.radius.real, arc.radius.imag
    phi = math.radians(arc.rotation)
    cos_p, sin_p = math.cos(phi), math.sin(phi)
    theta1 = math.radians(arc.theta)
    dtheta = math.radians(arc.delta)

    n = max(1, math.ceil(abs(dtheta) / (math.pi / 2)))
    step = dtheta / n
    k = 4.0 / 3.0 * math.tan(step / 4.0)

    def point(a):
        x, y = rx * math.cos(a), ry * math.sin(a)
        return arc.center + complex(x * cos_p - y * sin_p, x * sin_p + y * cos_p)

    def deriv(a):
        x, y = -rx * math.sin(a), ry * math.cos(a)
        return complex(x * cos_p - y * sin_p, x * sin_p + y * cos_p)

    for i in range(n):
        a1 = theta1 + i * step
        a2 = a1 + step
        p0, p3 = point(a1), point(a2)
        yield CubicBezier(p0, p0 + k * deriv(a1), p3 - k * deriv(a2), p3)


def flatten(subpath):
    """Sottotracciato continuo -> array Nx2 di punti (curve campionate)."""
    pts = []
    for seg in subpath:
        if isinstance(seg, Line):
            pts.append(seg.start)
        else:
            pts.extend(seg.point(t) for t in np.linspace(0, 1, 16, endpoint=False))
    pts.append(subpath[-1].end)
    return np.array([pt(p) for p in pts], dtype=float)


def rdp(points, eps):
    """Ramer-Douglas-Peucker iterativo: toglie i punti che deviano meno di eps."""
    n = len(points)
    if eps <= 0 or n < 3:
        return points
    keep = np.zeros(n, dtype=bool)
    keep[0] = keep[-1] = True
    stack = [(0, n - 1)]
    while stack:
        i, j = stack.pop()
        if j <= i + 1:
            continue
        a, b = points[i], points[j]
        d = b - a
        length = math.hypot(d[0], d[1])
        mid = points[i + 1:j]
        if length == 0:
            dist = np.hypot(mid[:, 0] - a[0], mid[:, 1] - a[1])
        else:
            dist = np.abs(d[0] * (mid[:, 1] - a[1]) - d[1] * (mid[:, 0] - a[0])) / length
        k = int(dist.argmax())
        if dist[k] > eps:
            k += i + 1
            keep[k] = True
            stack.append((i, k))
            stack.append((k, j))
    return points[keep]


# ===== LETTURA DEI PATH =====
translate_re = re.compile(
    r"translate\(\s*([-+0-9.eE]+)(?:[\s,]+([-+0-9.eE]+))?\s*\)"
)

subpaths = []   # lista di sottotracciati continui, ognuno e' una lista di segmenti
n_paths = 0

for tag in re.findall(r"<path\b[^>]*>", data, re.DOTALL):
    d_match = re.search(r'\bd\s*=\s*"([^"]*)"', tag, re.DOTALL)
    if not d_match:
        continue
    n_paths += 1

    # parse_path gestisce tutti i comandi (M L H V C S Q T A Z, maiuscoli e minuscoli)
    path = parse_path(d_match.group(1))

    t_match = re.search(r'\btransform\s*=\s*"([^"]*)"', tag)
    if t_match:
        tr = translate_re.fullmatch(t_match.group(1).strip())
        if tr:
            path = path.translated(complex(float(tr.group(1)), float(tr.group(2) or 0)))
        else:
            print(f"ATTENZIONE: transform non supportato ignorato: {t_match.group(1)}")

    for sub in path.continuous_subpaths():
        segs = []
        for seg in sub:
            if isinstance(seg, Arc):
                segs.extend(arc_to_cubics(seg))
            elif isinstance(seg, (Line, QuadraticBezier, CubicBezier)):
                if abs(seg.end - seg.start) > 0 or not isinstance(seg, Line):
                    segs.append(seg)
            else:
                print("Segmento sconosciuto ignorato: " + str(type(seg)))
        if segs:
            subpaths.append(segs)

segments = [s for sub in subpaths for s in sub]
print(f"DEBUG: Trovati {n_paths} path nel SVG, {len(subpaths)} tracciati, {len(segments)} segmenti")
if not segments:
    print("ERRORE: nessun segmento trovato nel file SVG")
    sys.exit(1)

# ===== EQUAZIONI =====
if len(segments) <= MAX_EXACT_SEGMENTS:
    print("Modalita': ESATTA (un'equazione per segmento)")
    equations = [segment_to_latex(s) for s in segments]
    expressions = [
        {"id": f"seg{i}", "latex": eq, "color": "#000000"}
        for i, eq in enumerate(equations)
    ]

    x_min = y_min = float("inf")
    x_max = y_max = float("-inf")
    for s in segments:
        sx0, sx1, sy0, sy1 = s.bbox()
        if FLIP_Y:
            sy0, sy1 = -sy1, -sy0
        x_min, x_max = min(x_min, sx0), max(x_max, sx1)
        y_min, y_max = min(y_min, sy0), max(y_max, sy1)
else:
    print(f"Modalita': POLILINEE (troppi segmenti per Desmos: {len(segments)} > {MAX_EXACT_SEGMENTS})")
    chains = [flatten(sub) for sub in subpaths]

    all_pts = np.vstack(chains)
    diag = math.hypot(np.ptp(all_pts[:, 0]), np.ptp(all_pts[:, 1]))
    eps = SIMPLIFY_REL * diag
    chains = [rdp(c, eps) for c in chains]
    chains = [c for c in chains if len(c) >= 2]
    n_before = len(all_pts)
    n_after = sum(len(c) for c in chains)
    print(f"Semplificazione: {n_before} -> {n_after} punti (tolleranza {eps:.3f})")

    # Raggruppa le catene in liste da al massimo MAX_LIST elementi.
    # Tra una catena e l'altra va un punto "indefinito" che interrompe la linea.
    chunks, current = [], []

    def flush():
        global current
        if current:
            chunks.append(current)
        current = []

    for c in chains:
        pts = [tuple(p) for p in c]
        if len(pts) > MAX_LIST:                      # catena enorme: spezzala (con 1 punto in comune)
            flush()
            step = MAX_LIST - 1
            for i in range(0, len(pts) - 1, step):
                chunks.append(pts[i:i + MAX_LIST])
            continue
        if len(current) + len(pts) + 1 > MAX_LIST:
            flush()
        if current:
            current.append(None)
        current.extend(pts)
    flush()

    def list_latex(values):
        return "\\left[" + ",".join(
            "\\frac{0}{0}" if v is None else plain(v) for v in values
        ) + "\\right]"

    equations, expressions = [], []
    for i, chunk in enumerate(chunks):
        xs = [None if p is None else p[0] for p in chunk]
        ys = [None if p is None else p[1] for p in chunk]
        latex = "\\left(" + list_latex(xs) + "," + list_latex(ys) + "\\right)"
        equations.append(latex)
        expressions.append({
            "id": f"poly{i}", "latex": latex, "color": "#000000",
            "points": False, "lines": True, "lineWidth": 1,
        })

    x_min, x_max = float(all_pts[:, 0].min()), float(all_pts[:, 0].max())
    y_min, y_max = float(all_pts[:, 1].min()), float(all_pts[:, 1].max())

w = (x_max - x_min) or 1.0
h = (y_max - y_min) or 1.0
bounds = {
    "left": x_min - w * 0.05,
    "right": x_max + w * 0.05,
    "bottom": y_min - h * 0.05,
    "top": y_max + h * 0.05,
}

# ===== GENERAZIONE HTML =====
HTML_TEMPLATE = """<!DOCTYPE html>
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
<div id="calculator"></div>
<script src="https://www.desmos.com/api/v1.8/calculator.js?apiKey=__API_KEY__"></script>
<script>
var calculator = Desmos.GraphingCalculator(document.getElementById('calculator'));
calculator.setMathBounds(__BOUNDS__);
calculator.setExpressions(__EXPRESSIONS__);
</script>
</body>
</html>
"""

html = (
    HTML_TEMPLATE
    .replace("__API_KEY__", DESMOS_API_KEY)
    .replace("__BOUNDS__", json.dumps(bounds))
    .replace("__EXPRESSIONS__", json.dumps(expressions))
)

# ===== SALVATAGGIO =====
timestamp = int(time.time())

filepath = os.path.join(output_dir, f"desmos_graph_{timestamp}.html")
with open(filepath, "w", encoding="utf-8") as f:
    f.write(html)

equations_file = os.path.join(output_dir, f"equations_{timestamp}.txt")
with open(equations_file, "w", encoding="utf-8") as f:
    for eq in equations:
        f.write(eq + "\n")

print(f"HTML salvato: {filepath} ({os.path.getsize(filepath) / 1024:.0f} KB)")
print(f"Equazioni salvate: {equations_file}")
print(f"Numero di equazioni: {len(equations)}")
print(f"Cartella: {output_dir}")
