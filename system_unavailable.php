<?php
/**
 * Pagina padrao para indisponibilidade temporaria do sistema.
 */

$technicalReason = (string)($GLOBALS['service_unavailable_reason'] ?? 'Motivo nao informado pela aplicacao.');
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema temporariamente indisponivel</title>
    <style>
        :root {
            --bg-1: #f4f7fb;
            --bg-2: #dbe6f5;
            --panel: #ffffff;
            --text: #1f2a37;
            --muted: #5d6b80;
            --accent: #005ea8;
            --border: #d6dfeb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: linear-gradient(135deg, var(--bg-1), var(--bg-2));
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .card {
            width: min(640px, 100%);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 14px 35px rgba(9, 30, 66, 0.12);
            padding: 28px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(24px, 4vw, 32px);
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            font-size: 16px;
        }

        .badge {
            display: inline-block;
            margin-bottom: 14px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e8f1fb;
            color: var(--accent);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .hint {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            font-size: 14px;
        }

        .debug-hotspot {
            position: fixed;
            top: 8px;
            right: 8px;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            opacity: 0.02;
            background: #000;
            cursor: pointer;
        }

        .debug-tab {
            display: none;
            margin-top: 16px;
            border-top: 1px dashed var(--border);
            padding-top: 14px;
        }

        .debug-tab pre {
            margin: 8px 0 0;
            background: #f7f9fc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            color: #1d3557;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 13px;
        }

        body.reveal-debug .debug-tab {
            display: block;
        }
    </style>
</head>
<body>
    <button class="debug-hotspot" id="debugHotspot" aria-label="Abrir diagnostico tecnico"></button>
    <main class="card" role="main" aria-live="polite">
        <span class="badge">Servico indisponivel</span>
        <h1>Sistema temporariamente fora do ar</h1>
        <p>
            Estamos realizando a recuperacao do servico. Tente novamente em alguns minutos.
        </p>
        <p class="hint">
            Se o problema continuar, entre em contato com o suporte de TI informando o horario da falha.
        </p>
        <section class="debug-tab" id="debugTab" aria-label="Diagnostico tecnico">
            <strong>Diagnostico tecnico (admin)</strong>
            <pre><?php echo htmlspecialchars($technicalReason, ENT_QUOTES, 'UTF-8'); ?></pre>
        </section>
    </main>
    <script>
        (function () {
            var clicks = 0;
            var hotspot = document.getElementById('debugHotspot');
            if (!hotspot) return;

            hotspot.addEventListener('click', function () {
                clicks += 1;
                if (clicks >= 5) {
                    document.body.classList.toggle('reveal-debug');
                    clicks = 0;
                }
            });
        })();
    </script>
</body>
</html>
