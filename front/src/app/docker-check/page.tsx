import { Suspense } from "react";
import { connection } from "next/server";

async function LaravelHealthStatus() {
    await connection();

    const backendUrl = process.env.API_INTERNAL_URL;

    let backendStatus = "Not configured";

    if (backendUrl) {
        try {
            const response = await fetch(`${backendUrl}/up`, {
                cache: "no-store",
                signal: AbortSignal.timeout(5000),
            });

            backendStatus = response.ok
                ? "Connected to Laravel"
                : `Laravel returned HTTP ${response.status}`;
        } catch {
            backendStatus = "Cannot reach Laravel";
        }
    }

    return <p>Laravel: {backendStatus}</p>;
}

export default function DockerCheckPage() {
    return (
        <main style={{ padding: "24px" }}>
            <h1>University Docker Production Check</h1>

            <p>Next.js SSR: OK</p>

            <Suspense fallback={<p>Laravel: Checking connection...</p>}>
                <LaravelHealthStatus />
            </Suspense>
        </main>
    );
}