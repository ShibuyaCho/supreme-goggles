import express from "express";
import cors from "cors";

export function createServer() {
  const app = express();

  // Configure CORS with allowlist from env (comma-separated)
  const allowedOrigins = (process.env.ALLOWED_ORIGINS || "")
    .split(",")
    .map((o) => o.trim())
    .filter(Boolean);
  const corsOptions: cors.CorsOptions = {
    origin: (origin, callback) => {
      // Allow same-origin or server-to-server (no origin header)
      if (!origin) return callback(null, true);
      if (allowedOrigins.length === 0)
        return callback(new Error("CORS not configured"));
      if (allowedOrigins.includes(origin)) return callback(null, true);
      return callback(new Error("Not allowed by CORS"));
    },
    methods: ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
    allowedHeaders: ["Content-Type", "Authorization"],
    optionsSuccessStatus: 200,
    credentials: false,
  };
  app.use(cors(corsOptions));

  // Body parsers
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  // Minimal in-memory rate limiter for /api (configurable via env)
  const WINDOW_MS = parseInt(process.env.RATE_LIMIT_WINDOW_MS || "60000", 10); // 60s
  const MAX_REQ = parseInt(process.env.RATE_LIMIT_MAX || "120", 10); // 120 req/min per IP
  const buckets = new Map<string, { count: number; reset: number }>();
  function rateLimit(
    req: express.Request,
    res: express.Response,
    next: express.NextFunction,
  ) {
    try {
      const key = (req.ip || req.socket.remoteAddress || "unknown").toString();
      const now = Date.now();
      const rec = buckets.get(key) || { count: 0, reset: now + WINDOW_MS };
      if (now > rec.reset) {
        rec.count = 0;
        rec.reset = now + WINDOW_MS;
      }
      rec.count += 1;
      buckets.set(key, rec);
      if (rec.count > MAX_REQ) {
        return res
          .status(429)
          .json({
            error: "Too many requests",
            retry_after_ms: rec.reset - now,
          });
      }
      next();
    } catch (_) {
      next();
    }
  }
  app.use("/api", rateLimit);

  // Example API routes
  app.get("/api/ping", (_req, res) => {
    const ping = process.env.PING_MESSAGE ?? "ping";
    res.json({ message: ping });
  });

  // Demo route (kept simple)
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  const { handleDemo } = require("./routes/demo");
  app.get("/api/demo", handleDemo);

  return app;
}
