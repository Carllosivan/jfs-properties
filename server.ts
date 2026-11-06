import express from 'express';
import http from 'http';
import { spawn, ChildProcess, execSync } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';
import { createServer as createViteServer } from 'vite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = 3000;
const PHP_PORT = 8000;

// Ensure database is migrated and seeded on start
try {
  execSync('php database/run.php', { stdio: 'inherit' });
} catch (e) {
  console.error('Database migration note:', e);
}

// Spawn PHP 8.2 built-in web server in the background
let phpProcess: ChildProcess | null = null;

function startPhpServer() {
  console.log(`Starting native PHP 8.2 server on 127.0.0.1:${PHP_PORT}...`);
  phpProcess = spawn('php', ['-S', `127.0.0.1:${PHP_PORT}`, '-t', 'public', 'public/index.php'], {
    stdio: 'inherit',
    cwd: process.cwd(),
  });

  phpProcess.on('exit', (code, signal) => {
    console.log(`PHP server exited with code ${code} and signal ${signal}`);
  });
}

startPhpServer();

// Clean up child process on exit
process.on('exit', () => {
  if (phpProcess) phpProcess.kill();
});
process.on('SIGINT', () => {
  if (phpProcess) phpProcess.kill();
  process.exit();
});
process.on('SIGTERM', () => {
  if (phpProcess) phpProcess.kill();
  process.exit();
});

async function createServer() {
  const app = express();

  // 1. Health checks & internal AI studio control plane endpoints
  app.get('/health', (req, res) => res.status(200).send('OK'));
  app.get('/healthz', (req, res) => res.status(200).send('OK'));
  app.all('/__aistudio*', (req, res) => res.status(200).json({ status: 'ok' }));

  // 2. Vite middleware mode for dev integration
  const vite = await createViteServer({
    server: { middlewareMode: true },
    appType: 'custom',
  });
  app.use(vite.middlewares);

  // 3. Serve static assets from public/ directory
  app.use(express.static(path.join(__dirname, 'public')));

  // 4. Proxy application routes directly to the native PHP server
  app.use((req, res) => {
    const clientHost = req.headers.host || `localhost:${PORT}`;
    const options: http.RequestOptions = {
      hostname: '127.0.0.1',
      port: PHP_PORT,
      path: req.url,
      method: req.method,
      headers: {
        ...req.headers,
        host: clientHost,
        'x-forwarded-for': req.ip,
        'x-forwarded-proto': req.protocol,
        'x-forwarded-host': clientHost,
      },
    };

    const proxyReq = http.request(options, (proxyRes) => {
      // Rewrite any location header if PHP redirected with absolute port 8000 URL
      if (proxyRes.headers.location) {
        proxyRes.headers.location = proxyRes.headers.location.replace(/http:\/\/127\.0\.0\.1:8000/g, '');
      }

      res.writeHead(proxyRes.statusCode || 200, proxyRes.headers);
      proxyRes.pipe(res, { end: true });
    });

    proxyReq.on('error', (err) => {
      console.error('PHP Proxy Error:', err.message);
      res.status(502).send(`
        <!DOCTYPE html>
        <html>
        <head><title>JFS Properties - A inicializar</title><meta http-equiv="refresh" content="2"></head>
        <body style="font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f8fafc;">
          <div style="background: white; padding: 2rem; border-radius: 1rem; border: 1px solid #e2e8f0; text-align: center; max-width: 400px;">
            <h2 style="color: #0369a1; margin-top: 0;">JFS Properties</h2>
            <p style="color: #64748b; font-size: 0.9rem;">A inicializar o motor PHP 8.2 e base de dados SQLite...</p>
          </div>
        </body>
        </html>
      `);
    });

    req.pipe(proxyReq, { end: true });
  });

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`JFS Properties server listening on http://0.0.0.0:${PORT}`);
  });
}

createServer().catch((err) => {
  console.error('Failed to start server:', err);
});

