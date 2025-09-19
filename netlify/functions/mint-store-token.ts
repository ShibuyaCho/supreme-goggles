import jwt from "jsonwebtoken";

interface MintRequest {
  store_id: string;
  ttl_minutes?: number;
}

export const handler = async (event: any) => {
  try {
    if (event.httpMethod !== "POST") {
      return { statusCode: 405, body: "Method Not Allowed" };
    }

    const SUPABASE_URL = process.env.SUPABASE_URL;
    const SUPABASE_JWT_SECRET = process.env.SUPABASE_JWT_SECRET || process.env.SUPABASE_SERVICE_ROLE_KEY;
    const SUPABASE_SERVICE_ROLE_KEY = process.env.SUPABASE_SERVICE_ROLE_KEY;

    if (!SUPABASE_URL || !SUPABASE_JWT_SECRET || !SUPABASE_SERVICE_ROLE_KEY) {
      return {
        statusCode: 500,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "Missing required environment variables on the server." }),
      };
    }

    const authHeader = (event.headers?.authorization || event.headers?.Authorization || "") as string;
    if (!authHeader || !authHeader.toLowerCase().startsWith("bearer ")) {
      return {
        statusCode: 401,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "Missing Authorization Bearer token." }),
      };
    }

    const accessToken = authHeader.split(" ")[1];

    const userResp = await fetch(`${SUPABASE_URL}/auth/v1/user`, {
      method: "GET",
      headers: {
        Authorization: `Bearer ${accessToken}`,
        "Content-Type": "application/json",
      },
    });

    if (!userResp.ok) {
      return {
        statusCode: 401,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "Invalid or expired user session." }),
      };
    }

    const user = await userResp.json();
    if (!user || !user.id) {
      return {
        statusCode: 401,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "Unable to retrieve user from Supabase Auth." }),
      };
    }

    let body: MintRequest;
    try {
      body = JSON.parse(event.body || "{}");
    } catch {
      body = {} as MintRequest;
    }

    const store_id = (body && (body as any).store_id) as string;
    const ttl_minutes = Math.max(1, Math.min(60, Number((body && (body as any).ttl_minutes) ?? 5)));

    if (!store_id) {
      return {
        statusCode: 400,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "store_id is required in body." }),
      };
    }

    const userStoresUrl = `${SUPABASE_URL}/rest/v1/user_stores?user_id=eq.${encodeURIComponent(
      user.id,
    )}&store_id=eq.${encodeURIComponent(store_id)}&select=*`;

    const membershipResp = await fetch(userStoresUrl, {
      method: "GET",
      headers: {
        Authorization: `Bearer ${SUPABASE_SERVICE_ROLE_KEY}`,
        apikey: SUPABASE_SERVICE_ROLE_KEY,
        "Content-Type": "application/json",
      },
    });

    if (!membershipResp.ok) {
      return {
        statusCode: 500,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "Failed to verify store membership." }),
      };
    }

    const membership = await membershipResp.json();
    if (!Array.isArray(membership) || membership.length === 0) {
      return {
        statusCode: 403,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ error: "User is not authorized for the requested store." }),
      };
    }

    const now = Math.floor(Date.now() / 1000);
    const exp = now + ttl_minutes * 60;

    const payload: Record<string, unknown> = {
      sub: user.id,
      iat: now,
      exp: exp,
      store_id: store_id,
    };

    const token = jwt.sign(payload, SUPABASE_JWT_SECRET as string, { algorithm: "HS256" });

    return {
      statusCode: 200,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ token, expires_at: exp }),
    };
  } catch (err) {
    return {
      statusCode: 500,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ error: "Internal server error." }),
    };
  }
};
