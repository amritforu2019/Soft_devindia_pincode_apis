<?php

declare(strict_types=1);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$projectUrl = $protocol . '://' . $host . ($basePath !== '' ? $basePath : '');
$apiUrl = $projectUrl . '/api/location.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Master API — Documentation</title>
    <style>
        :root {
            --bg: #0f1419;
            --surface: #1a2332;
            --surface-2: #243044;
            --border: #2d3a4f;
            --text: #e7ecf3;
            --muted: #8b9cb3;
            --accent: #3b82f6;
            --accent-soft: rgba(59, 130, 246, 0.12);
            --green: #22c55e;
            --orange: #f59e0b;
            --red: #ef4444;
            --code-bg: #0d1117;
            --radius: 12px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 24px 18px;
        }

        .logo {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #fff;
        }

        .logo span { color: var(--accent); }

        .subtitle {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 24px;
        }

        .nav-group {
            margin-bottom: 20px;
        }

        .nav-group h4 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .nav-group a {
            display: block;
            color: var(--text);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 7px 10px;
            border-radius: 8px;
            margin-bottom: 2px;
        }

        .nav-group a:hover {
            background: var(--surface-2);
            color: #fff;
        }

        .main {
            padding: 40px 48px 80px;
            max-width: 980px;
        }

        .hero {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.18), rgba(34, 197, 94, 0.08));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            margin-bottom: 36px;
            box-shadow: var(--shadow);
        }

        .hero h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .hero p {
            color: var(--muted);
            max-width: 640px;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 999px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--muted);
        }

        .badge.get { color: var(--green); border-color: rgba(34, 197, 94, 0.35); }
        .badge.cache { color: var(--orange); border-color: rgba(245, 158, 11, 0.35); }

        section {
            margin-bottom: 48px;
            scroll-margin-top: 24px;
        }

        h2 {
            font-size: 1.45rem;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        h3 {
            font-size: 1.1rem;
            margin: 24px 0 12px;
            color: #fff;
        }

        p, li { color: var(--muted); margin-bottom: 10px; }
        ul, ol { padding-left: 20px; margin-bottom: 16px; }
        li { margin-bottom: 6px; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .info-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
        }

        .info-card strong {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .endpoint {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            margin-bottom: 20px;
        }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .method {
            background: rgba(34, 197, 94, 0.15);
            color: var(--green);
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .endpoint-url {
            font-family: Consolas, "Courier New", monospace;
            font-size: 0.88rem;
            color: #fff;
            word-break: break-all;
        }

        .url-box {
            display: flex;
            align-items: stretch;
            gap: 8px;
            margin: 12px 0;
        }

        .url-box code {
            flex: 1;
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.82rem;
            color: #a5d6ff;
            overflow-x: auto;
        }

        .btn {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 0.82rem;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn:hover { opacity: 0.9; }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 0.88rem;
        }

        th, td {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: var(--muted);
            font-weight: 600;
            background: var(--surface-2);
        }

        td code {
            background: var(--code-bg);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.82rem;
            color: #ffa657;
        }

        pre {
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            overflow-x: auto;
            margin: 12px 0;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        pre code { color: #c9d1d9; font-family: Consolas, "Courier New", monospace; }

        .json-key { color: #79c0ff; }
        .json-str { color: #a5d6ff; }
        .json-bool { color: #ff7b72; }
        .json-num { color: #ffa657; }

        .cache-tag {
            display: inline-block;
            font-size: 0.78rem;
            color: var(--orange);
            background: rgba(245, 158, 11, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            margin-top: 8px;
        }

        .schema {
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            font-family: Consolas, monospace;
            font-size: 0.85rem;
            color: #8b949e;
            white-space: pre;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 0.85rem;
        }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; }
            .main { padding: 24px 18px 60px; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">Location <span>Master</span> API</div>
        <div class="subtitle">REST API Documentation v1.1</div>

        <nav class="nav-group">
            <h4>Overview</h4>
            <a href="#overview">Introduction</a>
            <a href="#base-url">Base URL</a>
            <a href="#response-format">Response Format</a>
        </nav>

        <nav class="nav-group">
            <h4>Endpoints</h4>
            <a href="#states">Get All States</a>
            <a href="#cities">Cities by State ID</a>
            <a href="#areas">Areas by City ID</a>
            <a href="#pincode">Location by Pincode</a>
            <a href="#search">Search by ID</a>
        </nav>

        <!-- <nav class="nav-group">
            <h4>Reference</h4>
            <a href="#schema">Database Schema</a>
            <a href="#cache"> Cache</a>
            <a href="#config">Configuration</a>
            <a href="#examples">Code Examples</a>
            <a href="#security">Security</a>
        </nav> -->
    </aside>

    <main class="main">
        <div class="hero" id="overview">
            <h1>Location Master API</h1>
            <p>High-performance REST API for Indian location data — States, Cities, Areas, and Pincodes. Built with Core PHP, MySQL, and  caching for CRM, mobile apps, websites, and third-party integrations.</p>
            <div class="badges">
                <span class="badge get">GET Only</span>
                <span class="badge">JSON / UTF-8</span>
                <span class="badge cache"> Cache 24h</span>
                <span class="badge">CORS Enabled</span>
                <span class="badge">PHP 8+</span>
            </div>
        </div>

        <section id="base-url">
            <h2>Base URL</h2>
            <div class="url-box">
                <code id="api-base"><?= e($apiUrl) ?></code>
                <button class="btn" type="button" data-copy="#api-base">Copy</button>
            </div>
            <p>Append query parameters to this URL for all API requests.</p>

            <div class="info-grid">
                <div class="info-card"><strong>Protocol</strong>HTTP / HTTPS</div>
                <div class="info-card"><strong>Method</strong>GET only</div>
                <div class="info-card"><strong>Format</strong>JSON (UTF-8)</div>
                <div class="info-card"><strong>Auth</strong>Optional JWT</div>
            </div>
        </section>

        <section id="response-format">
            <h2>Response Format</h2>

            <h3>Success (HTTP 200)</h3>
            <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Success"</span>,
  <span class="json-key">"count"</span>: <span class="json-num">1</span>,
  <span class="json-key">"data"</span>: []
}</code></pre>

            <h3>Error</h3>
            <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-bool">false</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Error Message"</span>
}</code></pre>

            <h3>HTTP Status Codes</h3>
            <table>
                <thead><tr><th>Code</th><th>Meaning</th></tr></thead>
                <tbody>
                    <tr><td><code>200</code></td><td>Success</td></tr>
                    <tr><td><code>400</code></td><td>Invalid or missing parameters</td></tr>
                    <tr><td><code>401</code></td><td>Unauthorized (JWT enabled)</td></tr>
                    <tr><td><code>404</code></td><td>Record not found</td></tr>
                    <tr><td><code>405</code></td><td>Method not allowed</td></tr>
                    <tr><td><code>500</code></td><td>Internal server error</td></tr>
                </tbody>
            </table>
        </section>

        <section id="states">
            <h2>Endpoints</h2>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method">GET</span>
                    <span class="endpoint-url">action=states</span>
                </div>
                <p>Returns a list of all states.</p>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=states</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=states" target="_blank" rel="noopener">Try it</a>
                </div>
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>action</code></td><td>string</td><td>Yes</td><td>Must be <code>states</code></td></tr>
                    </tbody>
                </table>
                <span class="cache-tag">Cache: LOC:STATE_LIST</span>
            </div>
        </section>

        <section id="cities">
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method">GET</span>
                    <span class="endpoint-url">action=cities&amp;state_id=1</span>
                </div>
                <p>Returns all cities for a given state ID.</p>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=cities&amp;state_id=1</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=cities&amp;state_id=1" target="_blank" rel="noopener">Try it</a>
                </div>
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>action</code></td><td>string</td><td>Yes</td><td>Must be <code>cities</code></td></tr>
                        <tr><td><code>state_id</code></td><td>integer</td><td>Yes</td><td>Valid state ID</td></tr>
                    </tbody>
                </table>
                <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Success"</span>,
  <span class="json-key">"count"</span>: <span class="json-num">1</span>,
  <span class="json-key">"data"</span>: [{ <span class="json-key">"city_id"</span>: <span class="json-str">"10"</span>, <span class="json-key">"city_name"</span>: <span class="json-str">"Varanasi"</span> }]
}</code></pre>
                <span class="cache-tag">Cache: LOC:CITY_{state_id}</span>
            </div>
        </section>

        <section id="areas">
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method">GET</span>
                    <span class="endpoint-url">action=areas&amp;city_id=10</span>
                </div>
                <p>Returns all areas with pincodes for a given city ID.</p>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=areas&amp;city_id=10</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=areas&amp;city_id=10" target="_blank" rel="noopener">Try it</a>
                </div>
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>action</code></td><td>string</td><td>Yes</td><td>Must be <code>areas</code></td></tr>
                        <tr><td><code>city_id</code></td><td>integer</td><td>Yes</td><td>Valid city ID</td></tr>
                    </tbody>
                </table>
                <span class="cache-tag">Cache: LOC:AREA_{city_id}</span>
            </div>
        </section>

        <section id="pincode">
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method">GET</span>
                    <span class="endpoint-url">action=pincode&amp;pin=221005</span>
                </div>
                <p>Returns complete location details for a 6-digit Indian pincode.</p>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=pincode&amp;pin=221005</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=pincode&amp;pin=221005" target="_blank" rel="noopener">Try it</a>
                </div>
                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>action</code></td><td>string</td><td>Yes</td><td>Must be <code>pincode</code></td></tr>
                        <tr><td><code>pin</code></td><td>string</td><td>Yes</td><td>6-digit pincode</td></tr>
                    </tbody>
                </table>
                <pre><code>{
  <span class="json-key">"data"</span>: [{
    <span class="json-key">"state_id"</span>: <span class="json-str">"1"</span>, <span class="json-key">"state_name"</span>: <span class="json-str">"Uttar Pradesh"</span>,
    <span class="json-key">"city_id"</span>: <span class="json-str">"10"</span>, <span class="json-key">"city_name"</span>: <span class="json-str">"Varanasi"</span>,
    <span class="json-key">"area_id"</span>: <span class="json-str">"100"</span>, <span class="json-key">"area_name"</span>: <span class="json-str">"Sigra"</span>,
    <span class="json-key">"pincode"</span>: <span class="json-str">"221005"</span>
  }]
}</code></pre>
                <span class="cache-tag">Cache: LOC:PIN_{pincode}</span>
            </div>
        </section>

        <section id="search">
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method">GET</span>
                    <span class="endpoint-url">action=search&amp;{id_param}</span>
                </div>
                <p>Lookup a single record by <strong style="color:#fff">ID</strong> (not by name). Provide exactly one ID parameter per request.</p>

                <h3>Search State by ID</h3>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=search&amp;state_id=1</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=search&amp;state_id=1" target="_blank" rel="noopener">Try it</a>
                </div>
                <span class="cache-tag">Cache: LOC:SEARCH_STATE_{state_id}</span>

                <h3>Search City by ID</h3>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=search&amp;city_id=10</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=search&amp;city_id=10" target="_blank" rel="noopener">Try it</a>
                </div>
                <span class="cache-tag">Cache: LOC:SEARCH_CITY_{city_id}</span>

                <h3>Search Area by ID</h3>
                <div class="url-box">
                    <code><?= e($apiUrl) ?>?action=search&amp;area_id=100</code>
                    <a class="btn btn-outline" href="<?= e($apiUrl) ?>?action=search&amp;area_id=100" target="_blank" rel="noopener">Try it</a>
                </div>
                <span class="cache-tag">Cache: LOC:SEARCH_AREA_{area_id}</span>

                <table>
                    <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>action</code></td><td>string</td><td>Yes</td><td>Must be <code>search</code></td></tr>
                        <tr><td><code>state_id</code></td><td>integer</td><td>One of three</td><td>Search by state ID</td></tr>
                        <tr><td><code>city_id</code></td><td>integer</td><td>One of three</td><td>Search by city ID</td></tr>
                        <tr><td><code>area_id</code></td><td>integer</td><td>One of three</td><td>Search by area ID</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

 

        <div class="footer">
            Location Master API v1.1 · PHP 8+ · MySQL ·  · Soft Dev India
        </div>
    </main>
</div>

<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var target = document.querySelector(btn.getAttribute('data-copy'));
        if (!target) return;
        navigator.clipboard.writeText(target.textContent.trim()).then(function () {
            var label = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(function () { btn.textContent = label; }, 1500);
        });
    });
});
</script>
</body>
</html>
